<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions\Salesforce\Settings;

use Amnesty\Petitions\Salesforce\Init;
use Amnesty\Petitions\Salesforce\Settings;
use Amnesty\Salesforce\SObjects;
use CMB2;

/**
 * Register the Petition Signatory settings
 */
class Signature_SObject {

	/**
	 * Register the settings for counting petition signatures
	 *
	 * @param \CMB2 $settings the settings object
	 */
	public function __construct( CMB2 $settings ) {
		$objects    = SObjects::list();
		$customised = Settings::get( 'customise', 'no' );

		$group = $settings->add_field(
			[
				'id'         => 'signature',
				'name'       => __( 'Signature', 'aip-sf' ),
				'desc'       => __( 'Signature Settings', 'aip-sf' ),
				'type'       => 'group',
				'repeatable' => false,
				'classes'    => 'no' === $customised ? 'is-hidden' : '',
				'options'    => [
					'closed' => true,
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'      => 'message',
				'type'    => 'message',
				'message' => $this->get_message( 'signature' ),
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'sobject',
				'name'       => __( 'Salesforce Object type', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $objects,
				'default'    => 'CampaignMember',
				'attributes' => [
					'data-populate' => 'sobjects',
				],
			]
		);

		$object = SObjects::get( Settings::get( 'signature.0.sobject', 'CampaignMember' ) );
		$fields = $object ? $object->list() : [];

		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_petition_id',
				'name'       => __( 'Petition ID field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $fields,
				'default'    => 'CampaignId',
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_signatory_id',
				'name'       => __( 'Signatory ID field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $fields,
				'default'    => 'ContactId',
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_created_date',
				'name'       => __( 'Signature timestamp field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $fields,
				'default'    => 'CreatedDate',
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$this->register_type( $settings, $object, $group, $fields );
		$this->register_status( $settings, $object, $group, $fields );
	}

	/**
	 * Register the type field
	 *
	 * @param \CMB2       $settings the settings object
	 * @param object|null $sobject  the Salesforce object
	 * @param string      $group    the group name
	 * @param array       $fields   the fields within the group
	 *
	 * @return void
	 */
	protected function register_type( CMB2 $settings, ?object $sobject, string $group, array $fields ): void {
		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_type',
				'name'       => __( 'Type field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $fields,
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$field  = $sobject ? $sobject->get( Settings::get( 'signature.0.field_type', '' ) ) : null;
		$type   = $field ? $field->type : null;
		$values = $field ? $field->list() : [];

		$settings->add_group_field(
			$group,
			[
				'id'         => 'value_type.text',
				'name'       => __( 'Type field value', 'aip-sf' ),
				'type'       => 'text',
				'classes'    => 'text' === $type ? '' : 'is-hidden',
				'attributes' => [
					'data-show-on' => 'field_type',
					'data-show-if' => 'text',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'value_type.field',
				'name'       => __( 'Type field value', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $values,
				'classes'    => 'select' === $type ? '' : 'is-hidden',
				'attributes' => [
					'data-show-on' => 'field_type',
					'data-show-if' => 'select',
				],
			]
		);
	}

	/**
	 * Register the status field
	 *
	 * @param \CMB2       $settings the settings object
	 * @param object|null $sobject  the Salesforce object
	 * @param string      $group    the group name
	 * @param array       $fields   the fields within the group
	 *
	 * @return void
	 */
	protected function register_status( CMB2 $settings, ?object $sobject, string $group, array $fields ): void {
		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_status',
				'name'       => __( 'Status field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $fields,
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$field  = $sobject ? $sobject->get( Settings::get( 'signature.0.field_status', '' ) ) : null;
		$type   = $field ? $field->type() : null;
		$values = $field ? $field->list() : [];

		$settings->add_group_field(
			$group,
			[
				'id'         => 'value_status.text',
				'name'       => __( 'Status field value', 'aip-sf' ),
				'type'       => 'text',
				'classes'    => 'text' === $type ? '' : 'is-hidden',
				'attributes' => [
					'data-show-on' => 'field_status',
					'data-show-if' => 'text',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'value_status.field',
				'name'       => __( 'Status field value', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $values,
				'classes'    => 'select' === $type ? '' : 'is-hidden',
				'attributes' => [
					'data-show-on' => 'field_status',
					'data-show-if' => 'select',
				],
			]
		);
	}

	/**
	 * Retrieve a message from the messages directory
	 *
	 * @param string $name the message name
	 *
	 * @return string
	 */
	protected function get_message( string $name = '' ): string {
		$dir  = dirname( Init::$file );
		$file = sprintf( '%s/messages/%s.php', untrailingslashit( $dir ), $name );

		ob_start();
		include $file;
		return ob_get_clean();
	}

}
