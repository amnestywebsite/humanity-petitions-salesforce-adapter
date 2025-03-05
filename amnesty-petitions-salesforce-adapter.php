<?php // phpcs:ignore WordPress.Files.FileName

/**
 * Plugin Name:       Humanity Petitions Salesforce Adapter
 * Plugin URI:        https://github.com/amnestywebsite/humanity-petitions-salesforce-adapter
 * Description:       Add Salesforce data synchronisation to the Humanity Petitions plugin
 * Version:           1.0.1
 * Author:            Amnesty International
 * Author URI:        https://www.amnesty.org
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       aip-sf
 * Domain Path:       /languages
 * Network:           true
 * Requires PHP:      8.2
 * Requires at least: 5.8.0
 * Tested up to:      6.7.2
 * Requires Plugins:  humanity-petitions-develop, humanity-salesforce-connector-develop
 */

declare( strict_types = 1 );

namespace Amnesty\Petitions\Salesforce;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

register_deactivation_hook(
	__FILE__,
	function (): void {
		Settings::clear();
	}
);

new Init();

/**
 * Plugin instantiation class
 */
class Init {

	/**
	 * Absolute path to this file
	 *
	 * @var string
	 */
	public static $file = __FILE__;

	/**
	 * Plugin data
	 *
	 * @var array
	 */
	protected $data = [];

	/**
	 * Bind hooks
	 */
	public function __construct() {
		$this->data = get_plugin_data( __FILE__ );

		add_filter( 'amnesty_translatable_packages', [ $this, 'register_translatable_package' ], 12 );

		add_action( 'plugins_loaded', [ $this, 'textdomain' ] );
		add_action( 'plugins_loaded', [ $this, 'boot' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'amnesty_salesforce_connector_settings', [ $this, 'settings' ], 10, 2 );
		add_filter( 'amnesty_petitions_adapters', [ $this, 'adapter' ] );
	}

	/**
	 * Register this plugin as a translatable package
	 *
	 * @param array<int,array<string,string>> $packages existing packages
	 *
	 * @return array<int,array<string,string>>
	 */
	public function register_translatable_package( array $packages = [] ): array {
		$packages[] = [
			'id'     => 'humanity-petitions-salesforce-adapter',
			'path'   => realpath( __DIR__ ),
			'pot'    => realpath( __DIR__ ) . '/languages/aip-sf.pot',
			'domain' => 'aip-sf',
		];

		return $packages;
	}

	/**
	 * Register textdomain
	 *
	 * @return void
	 */
	public function textdomain(): void {
		load_plugin_textdomain( 'aip-sf', false, basename( __DIR__ ) . '/languages' );
	}

	/**
	 * Require classes. Has to be done after plugin load
	 * to ensure that parent classes have been loaded.
	 *
	 * @return void
	 */
	public function boot(): void {
		require_once __DIR__ . '/includes/abstract-class-singleton.php';

		if ( interface_exists( '\\Amnesty\\Petitions\\Adapter' ) ) {
			require_once __DIR__ . '/includes/class-salesforce-adapter.php';
		}

		require_once __DIR__ . '/includes/class-option.php';
		require_once __DIR__ . '/includes/class-settings.php';
		require_once __DIR__ . '/includes/settings/class-petition-signature-count.php';
		require_once __DIR__ . '/includes/settings/class-petition-sobject.php';
		require_once __DIR__ . '/includes/settings/class-signatory-sobject.php';
		require_once __DIR__ . '/includes/settings/class-signature-sobject.php';
		require_once __DIR__ . '/includes/class-page-settings.php';
	}

	/**
	 * Enqueue assets
	 *
	 * @return void
	 */
	public function enqueue(): void {
		// v no need for nonce verification
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = sanitize_key( $_GET['page'] ?? '' );

		if ( 'amnesty_salesforce_petitions' !== $page ) {
			return;
		}

		wp_add_inline_style( 'cmb2-styles', '.cmb-td ul:not([class]){padding:1em;list-style:initial}.is-hidden{display:none}' );
	}

	/**
	 * Register settings with CMB2
	 *
	 * @param \CMB2  $settings  CMB2 settings object
	 * @param string $menu_hook parent page menu hook slug
	 *
	 * @return void
	 */
	public function settings( \CMB2 $settings, string $menu_hook = 'admin_menu' ): void {
		if ( ! is_admin() || ! wp_get_referer() || false === strpos( wp_get_referer(), '/wp-admin/' ) ) {
			return;
		}

		$settings = new_cmb2_box(
			[
				'id'              => Settings::key(),
				'title'           => __( 'Petitions', 'aip-sf' ),
				'object_types'    => [ 'options-page' ],
				'option_key'      => Settings::key(),
				'parent_slug'     => $settings->prop( 'id' ),
				'admin_menu_hook' => $menu_hook,
			]
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page   = sanitize_key( $_GET['page'] ?? '' );
		$method = 'GET';
		if ( isset( $_SERVER['REQUEST_METHOD'] ) ) {
			$method = sanitize_text_field( $_SERVER['REQUEST_METHOD'] );
		}

		if ( 'GET' === strtoupper( $method ) && Settings::key() !== $page ) {
			return;
		}

		new Page_Settings( $settings );
	}

	/**
	 * Register the Salesforce adapter with the Petitions plugin
	 *
	 * @param array $adapters the plugin adapters
	 *
	 * @return array
	 */
	public function adapter( array $adapters = [] ): array {
		if ( interface_exists( '\\Amnesty\\Petitions\\Adapter' ) ) {
			$adapters[ Adapter::class ] = __( 'Salesforce', 'aip-sf' );
		}

		return $adapters;
	}

}
