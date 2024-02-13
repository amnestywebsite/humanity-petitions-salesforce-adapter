<?php // phpcs:ignore BigBite.Files.FileName

declare( strict_types = 1 );

namespace Amnesty\Petitions\Salesforce;

use Amnesty\Petitions\Adapter as BaseAdapter;
use Amnesty\Salesforce\Exception;
use Amnesty\Salesforce\Request;
use Amnesty\Salesforce\SObjects;
use WP_Post;

/**
 * Salesforce communication handler class
 */
class Adapter extends Singleton implements BaseAdapter {

	/**
	 * Instance variable
	 *
	 * @var static
	 */
	protected static $instance = null;

	/**
	 * Salesforce settings
	 *
	 * @var array
	 */
	protected static $settings = [
		'sigcount'  => [
			'method'   => 'field',
			'sobject'  => 'Campaign',
			'field'    => 'NumberOfContacts',
			'soql'     => '',
			'resp_key' => '',
		],
		'petition'  => [
			'sobject'      => 'Campaign',
			'field_title'  => 'Name',
			'field_date'   => 'StartDate',
			'field_type'   => 'Type',
			'field_status' => 'Status',
			'field_active' => 'IsActive',
			'value_type'   => 'Direct Mail',
			'value_status' => 'In Progress',
		],
		'signatory' => [
			'sobject'          => 'Contact',
			'field_first_name' => 'FirstName',
			'field_last_name'  => 'LastName',
			'field_email'      => 'Email',
			'field_phone'      => 'Phone',
			'field_newsletter' => 'HasOptedOutOfEmail',
			'value_newsletter' => '',
		],
		'signature' => [
			'sobject'            => 'CampaignMember',
			'field_petition_id'  => 'CampaignId',
			'field_signatory_id' => 'ContactId',
			'field_created_date' => 'CreatedDate',
			'field_type'         => '',
			'field_status'       => 'Status',
			'value_type'         => '',
			'value_status'       => 'Responded',
		],
	];

	/**
	 * Retrieve and organise settings
	 */
	protected function __construct() {
		// no custom settings, use defaults
		if ( 'yes' !== Settings::get( 'customise', 'no' ) ) {
			return;
		}

		// strip placeholder on empty value
		$replace = function ( string $item ): string {
			return '~' === $item ? '' : $item;
		};

		// check whether settings have been set
		$has = function ( array $data = [] ): bool {
			return count( array_filter( $data ) ) > 0;
		};

		$sigcount  = $this->get_setting( 'sigcount' );
		$petition  = $this->get_setting( 'petition' );
		$signatory = $this->get_setting( 'signatory' );
		$signature = $this->get_setting( 'signature' );

		// merge user settings with defaults
		$settings = wp_parse_args( compact( 'sigcount', 'petition', 'signatory', 'signature' ), static::$settings );

		static::$settings = $settings;
	}

	/**
	 * Retrieve user settings
	 *
	 * @param string $setting the setting key
	 *
	 * @return mixed
	 */
	protected function get_setting( string $setting ) {
		$raw_data = Settings::get( "{$setting}.0" ) ?: [];
		$filtered = array_map( [ $this, 'replace' ], $raw_data );

		if ( $this->has( $filtered ) ) {
			return $filtered;
		}

		return static::$settings[ $setting ] ?? [];
	}

	/**
	 * Strip placeholder text from empty values
	 *
	 * @param string $item the item to strip
	 *
	 * @return string
	 */
	protected function replace( string $item ): string {
		return '~' === $item ? '' : $item;
	}

	/**
	 * Check whether a setting has been set
	 *
	 * @param array $data the data to check
	 *
	 * @return bool
	 */
	protected function has( array $data = [] ): bool {
		return count( array_filter( $data ) ) > 0;
	}

	/**
	 * Record a petition signature
	 *
	 * @param \WP_Post $petition  the signatory's petition
	 * @param array    $signature the sanitised signatory data
	 *
	 * @return int
	 */
	public static function record_signature( WP_Post $petition, array $signature = [] ): int {
		static::instance();

		$petition_id = get_post_meta( $petition->ID, 'salesforce_id', true );

		if ( ! $petition_id ) {
			$petition_id = static::create_petition( $petition );
			add_post_meta( $petition->ID, 'salesforce_id', $petition_id, true );
		}

		$sig_hash     = sprintf( '%s:%s:%s', $signature['first_name'], $signature['last_name'], $signature['email'] );
		$sig_hash     = bin2hex( hash( 'sha256', $sig_hash, true ) );
		$meta_key     = sprintf( 'signatory_%s', $sig_hash );
		$signatory_id = get_post_meta( $petition->ID, $meta_key, true );

		if ( ! $signatory_id ) {
			$signatory_id = static::create_signatory( $signature, $petition->ID );
			add_post_meta( $petition->ID, $meta_key, $signatory_id, true );
		}

		$signature_id = static::create_signature( $petition_id, $signatory_id );

		if ( ! $signature_id ) {
			return 0;
		}

		static::count_signatures( $petition, true );

		$meta_id = add_post_meta( $petition->ID, sprintf( 'salesforce_%s', $signatory_id ), $signature_id, true );

		return $meta_id ?: 0;
	}

	/**
	 * Get signatures for a petition
	 *
	 * @param \WP_Post $petition the petition to get signatures for
	 * @param int      $per_page the signatures per page
	 * @param int      $page the page number
	 *
	 * @return array
	 */
	public static function get_signatures( WP_Post $petition, int $per_page = 10, int $page = 1 ): array {
		static::instance();

		$campaign  = get_post_meta( $petition->ID, 'salesforce_id', true );
		$prev_key  = sprintf( '%s:%s:%s', $campaign, $per_page, $page - 1 );
		$prev_page = wp_cache_get( $prev_key, 'aip-sf' );

		// if we have the previous page, and the previous page was the last, bail
		if ( false !== $prev_page && true === wp_validate_boolean( $prev_page['done'] ) ) {
			return [];
		}

		$cache_key = sprintf( '%s:%s:%s', $campaign, $per_page, $page );
		$cached    = wp_cache_get( $cache_key, 'aip-sf' );

		// if we have the current page cached
		if ( false !== $cached ) {
			return $cached['records'];
		}

		$petition  = static::$settings['petition'];
		$signature = static::$settings['signature'];
		$signatory = static::$settings['signatory'];

		$fields = SObjects::get( $signature['sobject'] )->field_names();
		$offset = ( $per_page * $page ) - $per_page;
		$query  = sprintf(
			"SELECT %s FROM %s WHERE %s = '%s' LIMIT %d OFFSET %d",
			implode( ', ', $fields ),
			$signature['sobject'],
			$signature['field_petition_id'],
			sanitize_text_field( $campaign ),
			$per_page,
			$offset
		);

		$resp_data = Request::get( '/query', [ 'q' => rawurlencode( $query ) ] );

		if ( empty( $resp_data['records'] ) ) {
			$to_cache = [
				'records' => [],
				'done'    => wp_validate_boolean( $resp_data['done'] ?? true ),
			];

			wp_cache_add( $cache_key, $to_cache, 'aip-sf', 10 * MINUTE_IN_SECONDS );
			return [];
		}

		$field_keys = array_merge( $signature, $signatory );
		$field_keys = array_filter( $field_keys, fn ( $value, $key ) => 0 === strpos( $key, 'field_' ) && ! ! $value, ARRAY_FILTER_USE_BOTH );
		$field_keys = array_flip( $field_keys );
		$field_keys = array_map( fn ( $key ) => str_replace( 'field_', '', $key ), $field_keys );

		$records = [];

		foreach ( $resp_data['records'] as $index => $record ) {
			$records[ $index ] = [];

			foreach ( $field_keys as $salesforce => $local ) {
				if ( ! isset( $record[ $salesforce ] ) ) {
					continue;
				}

				$records[ $index ][ $local ] = sanitize_text_field( $record[ $salesforce ] );
			}
		}

		$to_cache = [
			'records' => $records,
			'done'    => wp_validate_boolean( $resp_data['done'] ),
		];

		wp_cache_add( $cache_key, $to_cache, 'aip-sf', 10 * MINUTE_IN_SECONDS );

		return $records;
	}

	/**
	 * Count recorded signatures for a petition
	 *
	 * @param \WP_Post $petition the petition to count signatures for
	 * @param bool     $refetch  whether to invalidate cache and recount
	 *
	 * @return int
	 */
	public static function count_signatures( WP_Post $petition, bool $refetch = false ): int {
		static::instance();

		$settings  = static::$settings['sigcount'];
		$object_id = get_post_meta( $petition->ID, 'salesforce_id', true );

		$cached = wp_cache_get( sprintf( '%s-count', $object_id ), 'aip-sf' );
		if ( $cached && false === $refetch ) {
			return absint( $cached );
		}

		$count = 0;
		switch ( $settings['method'] ) {
			case 'field':
				$count = static::count_signatures_field( $object_id, $settings['sobject'], $settings['field'] );
				break;

			case 'query':
				$count = static::count_signatures_query( $object_id, $settings['soql'], $settings['resp_key'] );
				break;

			default:
				break;
		}

		wp_cache_add( sprintf( '%s-count', $object_id ), $count, 'aip-sf' );

		return absint( $count );
	}

	/**
	 * Retrieve petition signature count from sObject's properties
	 *
	 * @param string $object_id the petition's Salesforce ID
	 * @param string $sobject the petition's Salesforce Object
	 * @param string $field the petition's sObject property name
	 *
	 * @return int
	 */
	protected static function count_signatures_field( string $object_id, string $sobject, string $field ): int {
		$resp_data = Request::get( sprintf( '/sobjects/%s/%s', $sobject, $object_id ) );
		$resp_data = filter_var_array(
			$resp_data,
			[
				$field => FILTER_SANITIZE_NUMBER_INT,
			]
		);

		return absint( $resp_data[ $field ] );
	}

	/**
	 * Retrieve petition signature count using SOQL query
	 *
	 * @param string $object_id the petition's Salesforce ID
	 * @param string $query the SOQL query to execute
	 * @param string $field the key to retrieve the value from in the query response
	 *
	 * @return int
	 */
	protected static function count_signatures_query( string $object_id, string $query, string $field ): int {
		$resp_data = Request::get(
			'/query/',
			[
				'q' => rawurlencode( str_replace( '{petition}', $object_id, $query ) ),
			]
		);

		$resp_data = filter_var_array(
			$resp_data,
			[
				$field => FILTER_SANITIZE_NUMBER_INT,
			]
		);

		return absint( $resp_data[ $field ] );
	}

	/**
	 * Search for existing petition in Salesforce.
	 * Only called if petition ID not stored in postmeta
	 *
	 * @param WP_Post $petition the petition
	 *
	 * @return string|null
	 */
	protected static function find_petition( WP_Post $petition ): ?string {
		$fields = static::$settings['petition'];

		$resp_data = Request::post(
			'/parameterizedSearch',
			[
				'q'        => $petition->post_title,
				'in'       => $fields['field_title'],
				'sobjects' => [ [ 'name' => $fields['sobject'] ] ],
				'fields'   => [ 'Id', $fields['field_title'] ],
			]
		);

		if ( empty( $resp_data['searchRecords'] ) ) {
			return null;
		}

		$found = false;

		foreach ( $resp_data['searchRecords'] as $result ) {
			if ( $petition->post_title !== $result[ $fields['field_title'] ] ) {
				continue;
			}

			$found = $result;
		}

		if ( ! $found ) {
			return null;
		}

		return sanitize_text_field( $found['Id'] );
	}

	/**
	 * Create the petition object within Salesforce
	 *
	 * @param \WP_Post $petition the petition
	 *
	 * @throws \Amnesty\Petitions\Exception if Salesforce ID not found
	 *
	 * @return string
	 */
	protected static function create_petition( WP_Post $petition ): string {
		$petition_id = static::find_petition( $petition );

		if ( $petition_id ) {
			return static::update_petition( $petition, $petition_id );
		}

		$fields = static::$settings['petition'];
		$data   = array_filter(
			[
				$fields['field_title']  => $petition->post_title,
				$fields['field_date']   => preg_replace( '/^(\d{4}-\d{2}-\d{2})(.*)$/', '$1', $petition->post_date_gmt ),
				$fields['field_type']   => $fields['value_type'],
				$fields['field_status'] => $fields['value_status'],
				$fields['field_active'] => true,
			]
		);

		$resp_data = Request::post( '/sobjects/' . $fields['sobject'], $data );

		if ( ! isset( $resp_data['id'] ) ) {
			throw new Exception( 'Petition ID Not Found', 'error' );
		}

		return sanitize_text_field( $resp_data['id'] );
	}

	/**
	 * Create the petition object within Salesforce
	 *
	 * @param \WP_Post $petition    the petition
	 * @param string   $petition_id the Salesforce ID
	 *
	 * @return string
	 */
	protected static function update_petition( WP_Post $petition, string $petition_id = '' ): string {
		$fields = static::$settings['petition'];
		$data   = array_filter(
			[
				$fields['field_title']  => $petition->post_title,
				$fields['field_date']   => preg_replace( '/^(\d{4}-\d{2}-\d{2})(.*)$/', '$1', $petition->post_date_gmt ),
				$fields['field_type']   => $fields['value_type'],
				$fields['field_status'] => $fields['value_status'],
			]
		);

		Request::patch( sprintf( '/sobjects/%s/%s', $fields['sobject'], $petition_id ), $data );

		return $petition_id;
	}

	/**
	 * Search for existing signatory in Salesforce
	 *
	 * @param array $signature the signatory
	 *
	 * @return string|null
	 */
	protected static function find_signatory( array $signature = [] ): ?string {
		$fields = static::$settings['signatory'];

		$resp_data = Request::post(
			'/parameterizedSearch',
			[
				'q'        => $signature['email'],
				'in'       => $fields['field_email'],
				'sobjects' => [ [ 'name' => $fields['sobject'] ] ],
				'fields'   => array_filter(
					[
						'Id',
						$fields['field_first_name'],
						$fields['field_last_name'],
						$fields['field_email'],
						$fields['field_phone'],
					]
				),
			]
		);

		if ( empty( $resp_data['searchRecords'] ) ) {
			return null;
		}

		$found = false;

		foreach ( $resp_data['searchRecords'] as $result ) {
			if ( $signature['email'] !== $result[ $fields['field_email'] ] ) {
				continue;
			}

			$found = $result;
		}

		if ( ! $found ) {
			return null;
		}

		return sanitize_text_field( $found['Id'] );
	}

	/**
	 * Create a signatory in Salesforce
	 *
	 * @param array $signature the signatory
	 *
	 * @return string|null
	 */
	protected static function create_signatory( array $signature = [] ): ?string {
		$signatory = static::find_signatory( $signature );

		if ( $signatory ) {
			return static::update_signatory( $signature, $signatory );
		}

		$fields = static::$settings['signatory'];
		$type   = $fields['value_type.text'] ?? $fields['value_type.field'] ?? null;
		$status = $fields['value_status.text'] ?? $fields['value_status.field'] ?? null;
		$data   = array_filter(
			[
				$fields['field_first_name'] => $signature['first_name'],
				$fields['field_last_name']  => $signature['last_name'],
				$fields['field_email']      => $signature['email'],
				$fields['field_phone']      => $signature['phone'],
				$fields['field_newsletter'] => $fields['value_newsletter'] ?? 'no' === $signature['newsletter'],
				$fields['field_type']       => $type,
				$fields['field_status']     => $status,
			]
		);

		$resp_data = Request::post( '/sobjects/' . $fields['sobject'], $data );

		return sanitize_text_field( $resp_data['id'] );
	}

	/**
	 * Update an existing signatory in Salesforce
	 *
	 * @param array  $signature    the signatory data
	 * @param string $signatory_id the signatory's Salesforce ID
	 *
	 * @return string
	 */
	protected static function update_signatory( array $signature = [], string $signatory_id = '' ): string {
		$fields = static::$settings['signatory'];
		$type   = $fields['value_type.text'] ?? $fields['value_type.field'] ?? null;
		$status = $fields['value_status.text'] ?? $fields['value_status.field'] ?? null;
		$data   = array_filter(
			[
				$fields['field_first_name'] => $signature['first_name'],
				$fields['field_last_name']  => $signature['last_name'],
				$fields['field_email']      => $signature['email'],
				$fields['field_phone']      => $signature['phone'],
				$fields['field_newsletter'] => $fields['value_newsletter'] ?? 'no' === $signature['newsletter'],
				$fields['field_type']       => $type,
				$fields['field_status']     => $status,
			]
		);

		Request::patch( sprintf( '/sobjects/%s/%s', $fields['sobject'], $signatory_id ), $data );

		return $signatory_id;
	}

	/**
	 * Associate a signatory with a petition in Salesforce
	 *
	 * @param string $petition_id  the petition's Salesforce ID
	 * @param string $signatory_id the signatory's Salesforce ID
	 *
	 * @throws \Amnesty\Petitions\Exception if Salesforce ID not found
	 *
	 * @return string|null
	 */
	protected static function create_signature( string $petition_id = '', string $signatory_id = '' ): ?string {
		$fields = static::$settings['signature'];
		$type   = $fields['value_type.text'] ?? $fields['value_type.field'] ?? null;
		$status = $fields['value_status.text'] ?? $fields['value_status.field'] ?? null;
		$data   = array_filter(
			[
				$fields['field_petition_id']  => $petition_id,
				$fields['field_signatory_id'] => $signatory_id,
				$fields['field_type']         => $type,
				$fields['field_status']       => $status,
			]
		);

		$resp_data = Request::post( '/sobjects/' . $fields['sobject'], $data );

		if ( ! empty( $resp_data['id'] ) ) {
			return sanitize_text_field( $resp_data['id'] );
		}

		if ( ! isset( $resp_data[0]['errorCode'] ) ) {
			throw new Exception( esc_html__( 'An unknown error occurred.', 'aip-sf' ), 'error' );
		}

		return null;
	}

}
