<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions\Salesforce\Settings;

use Amnesty\Petitions\Salesforce\Init;
use Amnesty\Petitions\Salesforce\Settings;
use Amnesty\Salesforce\SObjects;
use CMB2;

/**
 * Register the Petitions Signatures settings
 */
class Petition_Signature_Count {

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
				'id'         => 'sigcount',
				'name'       => __( 'Signature Count', 'aip-sf' ),
				'desc'       => __( 'Signature Count Settings', 'aip-sf' ),
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
				'message' => $this->get_message( 'sigcount' ),
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'      => 'method',
				'name'    => __( 'Count method', 'aip-sf' ),
				'desc'    => __( 'How would a count of signatures for a petition be retrieved.', 'aip-sf' ),
				'type'    => 'select',
				'options' => [
					'field' => __( 'Field on Object', 'aip-sf' ),
					'query' => __( 'SOQL Query', 'aip-sf' ),
				],
			]
		);

		$method = Settings::get( 'sigcount.0.method', 'field' );

		$settings->add_group_field(
			$group,
			[
				'id'         => 'sobject',
				'name'       => __( 'Salesforce Object type', 'aip-sf' ),
				'desc'       => __( 'Select which Salesforce Object the field is found on', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $objects,
				'default'    => 'Campaign',
				'classes'    => 'query' === $method ? 'is-hidden' : '',
				'attributes' => [
					'data-show-on'  => 'field',
					'data-populate' => 'sobjects',
				],
			]
		);

		$sobject = Settings::get( 'sigcount.0.sobject', 'Campaign' );
		$object  = SObjects::get( $sobject );
		$fields  = $object ? $object->list() : [];

		$settings->add_group_field(
			$group,
			[
				'id'         => 'field',
				'name'       => __( 'Salesforce Object field', 'aip-sf' ),
				'desc'       => __( 'Select which Salesforce field the signature count should be retrieved from', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $fields,
				'default'    => 'NumberOfContacts',
				'classes'    => 'query' === $method ? 'is-hidden' : '',
				'attributes' => [
					'data-show-on'  => 'field',
					'data-populate' => 'sobject',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'soql',
				'name'       => __( 'SOQL Query', 'aip-sf' ),
				'desc'       => __( "Enter the Salesforce SOQL Query necessary to retrieve the count<br>e.g. <code>SELECT EntityId FROM TopicAssignment WHERE TopicId='{petition}' AND EntityType='Contact'</code>", 'aip-sf' ),
				'type'       => 'text',
				'classes'    => 'field' === $method ? 'is-hidden' : '',
				'attributes' => [
					'data-show-on' => 'query',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'resp_key',
				'name'       => __( 'SOQL Query response field', 'aip-sf' ),
				'desc'       => __( 'Enter the Salesforce SOQL query response field from which to retrieve the count, e.g. <code>totalSize</code>', 'aip-sf' ),
				'type'       => 'text',
				'classes'    => 'field' === $method ? 'is-hidden' : '',
				'attributes' => [
					'data-show-on' => 'query',
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
