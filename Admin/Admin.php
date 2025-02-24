<?php
namespace WTD_PROMOCART\Admin;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Admin Class.
 */

class Admin {

	/**
	 *  Instance.
	 */
	private static $instance;

	/**
	 *  Constructor.
	 */
	private function __construct() {

		// load scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// load admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

		// Admin init.
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// ajax submit action.
		add_action( 'wp_ajax_wtd_promocart_popup_save_settings', array( $this, 'wtd_promocart_popup_save_settings_callback' ) );
	}

	/**
	 * Enqueue Scripts and Styles.
	 *
	 * @since 1.0.0
	 * */
	public function enqueue_scripts() {

		// Register admin styles.
		wp_enqueue_style( 'wtd-promocart-popup-admin-style', WTD_PROMOCART_POPUP_URL . '/Assets/admin/css/wtd-promocart-popup-admin.css', array(), WTD_PROMOCART_POPUP_VERSION, 'all' );

		// Select 2 script and style.
		wp_enqueue_style( 'wtd-select-2-style', WTD_PROMOCART_POPUP_URL . '/Assets/admin/css/select2.min.css', array(), WTD_PROMOCART_POPUP_VERSION, 'all' );
		wp_register_script( 'wtd-select-2-script', WTD_PROMOCART_POPUP_URL . '/Assets/admin/js/select2.min.js', array( 'jquery' ), WTD_PROMOCART_POPUP_VERSION, true );

		// Register admin scripts.
		wp_register_script( 'wtd-promocart-popup-admin-script', WTD_PROMOCART_POPUP_URL . '/Assets/admin/js/wtd-promocart-popup-admin.js', array( 'jquery' ), WTD_PROMOCART_POPUP_VERSION, true );

		// Localized script for admin.
		$localize_script_array = array(
			'ajaxurl'       => admin_url( 'admin-ajax.php' ),
			'trans_string' => array(
				'Please select cart type' => __( 'Please select cart type', 'promocart-popup' ),
				'Please select condition' => __( 'Please select condition', 'promocart-popup' ),
				'Please select total amount' => __( 'Please select total amount', 'promocart-popup' ),
				'Please select total items' => __( 'Please select total items', 'promocart-popup' ),
				'Please select specific products' => __( 'Please select specific products', 'promocart-popup' ),
				'string' => __( 'string', 'promocart-popup' ),
			),
		);
		wp_localize_script( 'wtd-promocart-popup-admin-script', 'wtd_promocart_popup_admin', $localize_script_array );
	}

	/**
	 * Add Admin Menu.
	 *
	 * @since 1.0.0
	 * *
	 * */
	public function add_admin_menu() {
		add_menu_page(
			__( 'PromoCart Popup', 'promocart-popup' ),  // Page title.
			__( 'PromoCart Popup', 'promocart-popup' ),  // Menu title.
			'manage_options',                           // Capability.
			'wtd-promocart-popup',                        // Menu slug.
			array( $this, 'render_promocart_popup_admin_page' ), // Callback function.
			'',                                         // Icon (optional).
			59                                          // Menu position (adjust as needed).
		);
	}

	/**
	 * Render promocart popup admin page.
	 *
	 * @since 1.0.0
	 * *
	 * */
	public function render_promocart_popup_admin_page() {

		// if style and script are registered, enqueue them.
		wp_enqueue_script( 'wtd-promocart-popup-admin-script' );

		// load select2 css and js.
		wp_enqueue_script( 'wtd-select-2-script' );

		// render the admin page template.
        if ( file_exists( WTD_PROMOCART_POPUP_PATH . '/Admin/template/promocart-popup-admin.php' ) ) {
			require_once WTD_PROMOCART_POPUP_PATH . '/Admin/template/promocart-popup-admin.php';
		}
		
	}


	/**
	 * Admin init.
	 *
	 * @since 1.0.0
	 * *
	 * */
	public function admin_init() {
		// redirect to  admin dashboard after activation.
		if ( get_option( 'wtd_promocart_popup_redirect', false ) ) {
			delete_option( 'wtd_promocart_popup_redirect' );
			wp_safe_redirect( admin_url( 'admin.php?page=wtd-promocart-popup' ) );
			exit;
		}
	}


	/**
	 * Save settings.
	 *
	 * @since 1.0.0
	 * *
	 * *
	 * */
	public function wtd_promocart_popup_save_settings_callback() {
		// Save settings.
		$response['success'] = false;

		if ( ! isset( $_POST['wtd_promocart_popup_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wtd_promocart_popup_nonce'] ) ), 'wtd_promocart_popup_nonce_action' ) ) {
			$response['message'] = __( 'Invalid nonce', 'promocart-popup' );
			wp_send_json( $response );
			return;
		}
		$wtd_promocart_popup_settings = ! empty( get_option( 'wtd_promocart_popup_settings' ) ) ? get_option( 'wtd_promocart_popup_settings' ) : array();
		$settings                     = isset( $_POST['wtd_promocart_popup_settings'] ) ? $_POST['wtd_promocart_popup_settings'] : array();

		// sanitize and validate inputs.
		$wtd_promocart_popup_settings['enable_status'] = isset( $settings['enable_status'] ) ? sanitize_text_field( $settings['enable_status'] ) : 'off';
		$wtd_promocart_popup_settings['cart_type']     = isset( $settings['cart_type'] ) ? sanitize_text_field( $settings['cart_type'] ) : '';
		$wtd_promocart_popup_settings['condition']     = isset( $settings['condition'] ) ? sanitize_text_field( $settings['condition'] ) : '';
		$wtd_promocart_popup_settings['total_amount']  = isset( $settings['total_amount'] ) ? sanitize_text_field( $settings['total_amount'] ) : '';
		$wtd_promocart_popup_settings['total_items']   = isset( $settings['total_items'] ) ? sanitize_text_field( $settings['total_items'] ) : '';
		$wtd_promocart_popup_settings['products']      = isset( $settings['products'] ) ? $settings['products'] : array();

		// update the options.
		update_option( 'wtd_promocart_popup_settings', $wtd_promocart_popup_settings );
		$response['success'] = true;
		$response['message'] = __( 'Settings saved successfully', 'promocart-popup' );
		// return success message.
		wp_send_json( $response );
		wp_die();
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
