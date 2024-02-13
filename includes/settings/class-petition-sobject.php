<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions\Salesforce\Settings;

use Amnesty\Petitions\Salesforce\Init;
use Amnesty\Petitions\Salesforce\Settings;
use Amnesty\Salesforce\SObjects;
use CMB2;

/**
 * Register the Petitions settings
 */
class Petition_SObject {

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
				'id'         => 'petition',
				'name'       => __( 'Petition', 'aip-sf' ),
				'desc'       => __( 'Petition Settings', 'aip-sf' ),
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
				'message' => $this->get_message( 'petition' ),
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'sobject',
				'name'       => __( 'Salesforce Object type', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $objects,
				'default'    => 'Campaign',
				'attributes' => [
					'data-populate' => 'sobjects',
				],
			]
		);

		$object = SObjects::get( Settings::get( 'petition.0.sobject', 'Campaign' ) );
		$fields = $object ? $object->list() : [];

		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_title',
				'name'       => __( 'Title field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $fields,
				'default'    => 'Name',
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_date',
				'name'       => __( 'Date commenced field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $fields,
				'default'    => 'StartDate',
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_type',
				'name'       => __( 'Type field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $fields,
				'default'    => 'Type',
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$field  = $object ? $object->get( Settings::get( 'petition.0.field_type', 'Type' ) ) : null;
		$values = $field ? $field->list() : [];

		$settings->add_group_field(
			$group,
			[
				'id'         => 'value_type',
				'name'       => __( 'Type field value', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $values,
				'default'    => 'Other',
				'classes'    => $field ? '' : 'is-hidden',
				'attributes' => [
					'data-show-on' => 'field_type',
					'data-show-if' => 'select',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_status',
				'name'       => __( 'Status field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $fields,
				'default'    => 'Status',
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$field  = $object ? $object->get( Settings::get( 'petition.0.field_status', 'Status' ) ) : null;
		$values = $field ? $field->list() : [];

		$settings->add_group_field(
			$group,
			[
				'id'         => 'value_status',
				'name'       => __( 'Status field value', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $values,
				'default'    => 'In Progress',
				'classes'    => $field ? '' : 'is-hidden',
				'attributes' => [
					'data-show-on' => 'field_status',
					'data-show-if' => 'select',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_active',
				'name'       => __( 'Is Active checkbox field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $fields,
				'default'    => 'IsActive',
				'attributes' => [
					'data-populate' => 'sobject',
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
