<?php
/**
 * PromoCart Popup
 *
 * Plugin Name: PromoCart Popup
 * Plugin URI:  https://github.com/mdrejon/promocart-popup
 * Description: Displays a targeted popup with a 15% discount coupon based on cart conditions.
 * Version:     1.0.0
 * Author:      Sydur Rahman
 * Author URI:  https://github.com/mdrejon/
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: promocart-popup
 * Domain Path: /languages
 * Requires at least: 4.9
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

use WTD_PROMOCART\Admin\Admin;
use WTD_PROMOCART\App\App;

/**
 *  plugin activation.
 */
class WTD_promocart_Popup {

	/**
	 *  Instance.
	 */
	static $instance = null;

	/**
	 *  Constructor.
	 */
	public function __construct() {

		// Load Composer Autoload.
		if ( file_exists( plugin_dir_path( __FILE__ ) . 'vendor/autoload.php' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
		}

		// define constants.
		define( 'WTD_PROMOCART_POPUP_VERSION', '1.0.0' );
		define( 'WTD_PROMOCART_POPUP_PATH', plugin_dir_path( __FILE__ ) );
		define( 'WTD_PROMOCART_POPUP_URL', plugin_dir_url( __FILE__ ) );

		// plugins loaded action.
		add_action( 'plugins_loaded', array( $this, 'wtd_promocart_popup_loaded' ) );

		// activation hook.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// deactivation hook.
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
	}

	/**
	 *  Plugin Loaded.
	 */
	public function wtd_promocart_popup_loaded() {

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		// Checked WooCommerce plugin is active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			add_action( 'admin_notices', array( $this, 'wtd_promocart_popup_notice' ) );
		}

		// Load Text Domain.
		add_action( 'init', array( $this, 'wtd_promocart_popup_textdomain' ) );

		// if is admin panel.
		if ( is_admin() ) {
			Admin::init();
		}
        // Checked WooCommerce plugin is active.
        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            App::init();
        }
		
	}

	/**
	 *  Load Text Domain.
	 */
	public function wtd_promocart_popup_textdomain() {

		// Load Text Domain.
		load_plugin_textdomain( 'promocart-popup', false, WTD_PROMOCART_POPUP_PATH . 'languages' );
	}

	/**
	 *  popup notice when WooCommerce is not active
	 */
	public function wtd_promocart_popup_notice() {
		if ( current_user_can( 'activate_plugins' ) ) {
			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && ! file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo esc_html( __( 'Woocommerce requires for promocart popup. Please install and activate Woocommerce.', 'promocart-popup' ) ); ?></p>
					<a class="install-now button tf-install" href=<?php echo esc_url( admin_url( '/plugin-install.php?s=slug:woocommerce&tab=search&type=term' ) ); ?> data-plugin-slug="woocommerce"><?php esc_attr_e( 'Install Now', 'promocart-popup' ); ?></a> <br><br>
				</div>
				<?php
			} elseif ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php echo esc_html( __( 'Woocommerce requires for promocart popup. Please install and activate Woocommerce.', 'promocart-popup' ) ); ?></p>
					<a href="<?php echo esc_url( wp_nonce_url( get_admin_url() . 'plugins.php?action=activate&plugin=woocommerce/woocommerce.php', 'activate-plugin_woocommerce/woocommerce.php' ) ); ?>" class="button activate-now button-primary"><?php esc_attr_e( 'Activate', 'promocart-popup' ); ?></a> <br><br>
				</div>
				<?php

			}
		}
	}

	/**
	 * Activation hook.
	 *
	 * @since 1.0.0
	 * *
	 * */
	public function activate() {
		// after activation redirect to the admin page.
		update_option( 'wtd_promocart_popup_activated', true );  // set the activation status.
		// redirect to the admin page after activation.
		add_option( 'wtd_promocart_popup_redirect', true );
	}

	/**
	 * Deactivation hook.
	 *
	 * @since 1.0.0
	 * *
	 * */
	public function deactivate() {
		delete_option( 'wtd_promocart_popup_activated' );  // delete the activation status.
	}

	/**
	 *  Get the instance of the class.
	 *
	 * @since 1.0.0
	 * *
	 * */
	public static function get_instance() {
		return self::$instance;
	}


	/**
	 *  Initialize.
	 */
	public static function init() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

// Initialize the plugin.

WTD_promocart_Popup::init();
