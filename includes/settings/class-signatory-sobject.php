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
class Signatory_SObject {

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
				'id'         => 'signatory',
				'name'       => __( 'Signatory', 'aip-sf' ),
				'desc'       => __( 'Signatory Settings', 'aip-sf' ),
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
				'message' => $this->get_message( 'signatory' ),
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'sobject',
				'name'       => __( 'Salesforce Object type', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $objects,
				'default'    => 'Contact',
				'attributes' => [
					'data-populate' => 'sobjects',
				],
			]
		);

		$object  = SObjects::get( Settings::get( 'signatory.0.sobject', 'Contact' ) );
		$options = $object ? $object->list() : [];

		$this->register_signatory( $settings, $object, $group, $options );
		$this->register_type( $settings, $object, $group, $options );
		$this->register_status( $settings, $object, $group, $options );
	}

	/**
	 * Register the signatory fields
	 *
	 * @param \CMB2       $settings the settings object
	 * @param object|null $sobject  the Salesforce object
	 * @param string      $group    the group name
	 * @param array       $options  the fields within the group
	 *
	 * @return void
	 */
	protected function register_signatory( CMB2 $settings, ?object $sobject, string $group, array $options ): void {
		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_first_name',
				'name'       => __( 'First Name field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $options,
				'default'    => 'FirstName',
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_last_name',
				'name'       => __( 'Last Name field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $options,
				'default'    => 'LastName',
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_email',
				'name'       => __( 'Email field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $options,
				'default'    => 'Email',
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_phone',
				'name'       => __( 'Telephone Number field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $options,
				'default'    => 'OtherPhone',
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_newsletter',
				'name'       => __( 'Newsletter preferences field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $options,
				'default'    => 'HasOptedOutOfEmail',
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$field = $sobject ? $sobject->get( Settings::get( 'signatory.0.field_newsletter', 'HasOptedOutOfEmail' ) ) : null;
		$type  = $field ? $field->type() : null;

		$settings->add_group_field(
			$group,
			[
				'id'         => 'value_newsletter',
				'name'       => __( 'Newsletter preferences value', 'aip-sf' ),
				'type'       => 'text',
				'classes'    => 'text' === $type ? '' : 'is-hidden',
				'attributes' => [
					'placeholder'  => 'e.g. signed up to newsletter: %s (where %s = yes/no)',
					'data-show-on' => 'field_newsletter',
					'data-show-if' => 'text',
				],
			]
		);
	}

	/**
	 * Register the type field
	 *
	 * @param \CMB2       $settings the settings object
	 * @param object|null $sobject  the Salesforce object
	 * @param string      $group    the group name
	 * @param array       $options  the fields within the group
	 *
	 * @return void
	 */
	protected function register_type( CMB2 $settings, ?object $sobject, string $group, array $options ): void {
		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_type',
				'name'       => __( 'Type field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $options,
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$field  = $sobject ? $sobject->get( Settings::get( 'signatory.0.field_type', '' ) ) : null;
		$type   = $field ? $field->type() : null;
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
	 * @param array       $options  the fields within the group
	 *
	 * @return void
	 */
	protected function register_status( CMB2 $settings, ?object $sobject, string $group, array $options ): void {
		$settings->add_group_field(
			$group,
			[
				'id'         => 'field_status',
				'name'       => __( 'Status field', 'aip-sf' ),
				'type'       => 'select',
				'options'    => $options,
				'attributes' => [
					'data-populate' => 'sobject',
				],
			]
		);

		$field  = $sobject ? $sobject->get( Settings::get( 'signatory.0.field_status', '' ) ) : null;
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
