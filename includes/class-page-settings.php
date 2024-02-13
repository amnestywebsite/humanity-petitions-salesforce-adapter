<?php

declare( strict_types = 1 );

namespace Amnesty\Petitions\Salesforce;

use Amnesty\Petitions\Salesforce\Settings\Petition_SObject;
use Amnesty\Petitions\Salesforce\Settings\Petition_Signature_Count;
use Amnesty\Petitions\Salesforce\Settings\Signatory_SObject;
use Amnesty\Petitions\Salesforce\Settings\Signature_SObject;
use Amnesty\Salesforce\Tokens;
use CMB2;

/**
 * Settings page handler class
 */
class Page_Settings {

	/**
	 * Setting key
	 *
	 * @var string
	 */
	protected $id = 'amnesty_salesforce_petitions';

	/**
	 * Settings page slug
	 *
	 * @var string
	 */
	protected $slug = 'salesforce_settings';

	/**
	 * Register settings page
	 *
	 * @param \CMB2 $cmb2 CMB2 settings object
	 */
	public function __construct( CMB2 $cmb2 ) {
		$this->register_settings( $cmb2 );
	}

	/**
	 * Register the adapter's required settings
	 *
	 *  @param CMB2 $settings the CMB2 instance
	 *
	 * @return void
	 */
	public function register_settings( CMB2 $settings ): void {
		if ( ! Tokens::has( 'refresh_token' ) ) {
			$settings->add_field(
				[
					'id'      => 'message',
					'type'    => 'message',
					'message' => $this->get_message( 'not-authorised' ),
				] 
			);
			return;
		}

		$settings->add_field(
			[
				'id'      => 'message',
				'type'    => 'message',
				'message' => $this->get_message( 'default-info' ),
			] 
		);

		$settings->add_field(
			[
				'id'      => 'customise',
				'name'    => __( 'Customise', 'aip-sf' ),
				'desc'    => __( 'Customise how data is stored in Salesforce.', 'aip-sf' ),
				'type'    => 'select',
				'options' => [
					'no'  => __( 'No', 'aip-sf' ),
					'yes' => __( 'Yes', 'aip-sf' ),
				],
			] 
		);

		new Petition_Signature_Count( $settings );
		new Petition_SObject( $settings );
		new Signatory_SObject( $settings );
		new Signature_SObject( $settings );
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
