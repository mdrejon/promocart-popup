<?php
namespace WTD_PROMOCART\App;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * App class.
 */

class App {

	/**
	 *  Instance.
	 */
	private static $instance;

	/**
	 *  Constructor.
	 */
	private function __construct() {

		// apply discount action.
		add_action( 'woocommerce_cart_calculate_fees', array( $this, 'wtd_promocart_apply_discount_callback' ), 10, 1 );
 

		// Check popup visible status.
		if(false == self::check_visible_status()) {
			return;
		}
		
		// load scripts and styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add popup into footer.
		add_action( 'wp_footer', array( $this, 'wtd_promocart_popup' ) );

		// Close popup ajax action.
		add_action( 'wp_ajax_wtd_promocart_close_popup', array( $this, 'wtd_promocart_close_popup_callback' ) );
        add_action( 'wp_ajax_nopriv_wtd_promocart_close_popup', array( $this, 'wtd_promocart_close_popup_callback' ) );

		// Ajax apply coupon action.
		add_action( 'wp_ajax_wtd_promocart_apply_coupon', array( $this, 'wtd_promocart_apply_coupon_callback' ) );
		add_action( 'wp_ajax_nopriv_wtd_promocart_apply_coupon', array( $this, 'wtd_promocart_apply_coupon_callback' ) );

		// ajax check popup status action.
		add_action( 'wp_ajax_wtd_promocart_check_popup_status', array( $this, 'wtd_promocart_check_popup_status_callback' ) );
		add_action( 'wp_ajax_nopriv_wtd_promocart_check_popup_status', array( $this, 'wtd_promocart_check_popup_status_callback' ) );

		
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 1.0.0
	 * */
	public function enqueue_scripts() {
		// Register admin styles.
		wp_enqueue_style( 'wtd-promocart-popup-app-style', WTD_PROMOCART_POPUP_URL . '/Assets/app/css/wtd-promocart-popup-app.css', array(), WTD_PROMOCART_POPUP_VERSION, 'all' );
		// Register admin scripts.
		wp_enqueue_script( 'wtd-promocart-popup-app-script', WTD_PROMOCART_POPUP_URL . '/Assets/app/js/wtd-promocart-popup-app.js', array( 'jquery', 'wc-cart-fragments' ), WTD_PROMOCART_POPUP_VERSION, true );

		// localize script with data.
		$data = array(
			'ajaxurl'              => admin_url( 'admin-ajax.php' ),
			'nonce'                => wp_create_nonce( 'wtd_promocart_popup_nonce' ),
			'checkout_button_text' => esc_html__( 'Go to checkout', 'woocart-popup' ),
			'coupon_applied'       => esc_html__( 'Coupon applied', 'woocart-popup' ),

		);
		wp_localize_script( 'wtd-promocart-popup-app-script', 'wtd_promocart_popup_script', $data );
	}

	/**
	 * Add popup into footer.
	 *
	 * @since 1.0.0
	 * */
	public function wtd_promocart_popup() {
		$settings      = ! empty( get_option( 'wtd_promocart_popup_settings' ) ) ? get_option( 'wtd_promocart_popup_settings' ) : array();
		$enable_status = isset( $settings['enable_status'] ) ? $settings['enable_status'] : 'off';
		if ( $enable_status === 'off' ) {
			return;
		}

		ob_start();
		// if file exists, require once it.
		if ( file_exists( WTD_PROMOCART_POPUP_PATH . '/App/template/template-promocart-popup.php' ) ) {
			require_once WTD_PROMOCART_POPUP_PATH . '/App/template/template-promocart-popup.php';
		}
		$output = ob_get_clean();  // clean output buffer and output it.

		$allowed_html = wp_kses_allowed_html( 'post' );

		// Add SVG tags and attributes.
		$allowed_html['svg']  = array(
			'xmlns'        => true,
			'width'        => true,
			'height'       => true,
			'viewBox'      => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
			'class'        => true,
		);
		$allowed_html['path'] = array(
			'd'            => true,
			'fill'         => true,
			'stroke'       => true,
			'stroke-width' => true,
		);

		echo wp_kses( $output, $allowed_html );
	}

	/**
	 * Ajax apply coupon action callback.
	 *
	 * @since 1.0.0
	 * */
	public function wtd_promocart_apply_coupon_callback() {

		$response['success'] = false;
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wtd_promocart_popup_nonce' ) ) {
			$response['message'] = __( 'Invalid nonce', 'promocart-popup' );
			wp_send_json( $response );
			return;
		}

		if ( ! WC()->session ) {
			// return response with error.
			$response['message'] = __( 'Session not found', 'promocart-popup' );
			wp_send_json( $response );
			return;

		}
  
		if ( ! WC()->session->get( 'wtd_promocart_discount_applied' ) ) {
			WC()->session->set( 'wtd_promocart_discount_applied', true );

			// Update Popup status.
			self::update_popup_status('applied' );

			// Recalculate cart totals.
			WC()->cart->calculate_totals();

			$response['success'] = true;
			// go to checkout page.
			$response['url']     = wc_get_checkout_url();
			$response['message'] = __( 'Coupon applied successfully.', 'promocart-popup' );
			wp_send_json( $response );
			return;
		} else {
			$response['message'] = __( 'Coupon already applied.', 'promocart-popup' );
			wp_send_json( $response );
			return;
		}
		wp_die();
	}

	/**
	 * Ajax check popup status action callback.
	 */
	function wtd_promocart_apply_discount_callback( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

        if(true === self::check_popup_status()){
            if ( WC()->session->get( 'wtd_promocart_discount_applied' ) ) {
                $discount_percentage = 15;
                $cart_total          = $cart->subtotal;
                $discount_amount     = ( $cart_total * $discount_percentage ) / 100;
    
                if ( $discount_amount > 0 ) {
                    $cart->add_fee( '15% Discount', -$discount_amount, false );
                }
            }
        }else{
            
            WC()->session->__unset( 'wtd_promocart_discount_applied');
            // already added discount then remove the discount.
            $cart_total = WC()->cart->subtotal;
            $cart_items = WC()->cart->get_cart();
            foreach ( $cart_items as $cart_item_key => $cart_item ) {
                if ( '15% Discount' === $cart_item['data']->get_name() ) {
                    $cart->remove_cart_item( $cart_item_key ); 
                    break;
                }
            }
        }
		
	}

	/**
	 * Update Popup status.
	 * 
	 */
	public function wtd_promocart_close_popup_callback(){
		$response['success'] = false;
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wtd_promocart_popup_nonce' ) ) {
			$response['message'] = __( 'Invalid nonce', 'promocart-popup' );
			wp_send_json( $response );
			return;
		}

		// Update Popup status.
		self::update_popup_status('closed' );

		$response['success'] = true;
		wp_send_json( $response );

	}

	/**
	 * Update Popup status.
	 * 
	 */
	public static function update_popup_status( $status ) {
        // if current user logged in and has user id then update the status.
		if ( is_user_logged_in() ) {
            update_user_meta( get_current_user_id(), 'wtd_promocart_popup_status', $status );
        }else {
			// if not logged in then set it into the cookie for 7 days.
			$cookie_name  = 'wtd_promocart_popup_status';
            $cookie_value = $status;
            $expiration   = time() + ( 7 * 24 * 60 * 60 );
            setcookie( $cookie_name, $cookie_value, $expiration, '/' );
		}
    }


	/**
	 * Update Popup status.
	 * 
	 */
	public static function check_visible_status() {
		
		// if already applied or closed return false.
		if(is_user_logged_in()) {
			$user_status = get_user_meta( get_current_user_id(), 'wtd_promocart_popup_status', true );
            if ( 'applied' === $user_status || 'closed' === $user_status ) {
                return false;
            }
        } else {
			$cookie_name  = 'wtd_promocart_popup_status';
			$cookie_value = isset( $_COOKIE[ $cookie_name ] )? $_COOKIE[ $cookie_name ] : '';
			if ( 'applied' === $cookie_value || 'closed' === $cookie_value ) {
                return false;
            }
		}

		return true;

	}

	/**
	 * Check discount popup visibility.
	 */
	public static function check_popup_status() {

		// if already applied discount return false.
		

		$settings      = ! empty( get_option( 'wtd_promocart_popup_settings' ) ) ? get_option( 'wtd_promocart_popup_settings' ) : array();
		$enable_status = isset( $settings['enable_status'] ) ? esc_html($settings['enable_status']) : 'off';
		$cart_type     = isset( $settings['cart_type'] ) ? esc_html($settings['cart_type']) : '';
		$condition     = isset( $settings['condition'] ) ? esc_html($settings['condition']) : '';
		$total_amount  = isset( $settings['total_amount'] ) ? esc_html($settings['total_amount']) : '';
		$total_items   = isset( $settings['total_items'] ) ? esc_html($settings['total_items']) : '';
		$products      = isset( $settings['products'] ) ? $settings['products'] : array();

		// get all cart product ids  as incrimental  using array search.
		$cart_product_ids = array();
		foreach ( WC()->cart->get_cart_contents() as $cart_item_key => $cart_item ) {
			$cart_product_ids[] = $cart_item['product_id'];
		}
		$product_ids = array_intersect( $cart_product_ids, $products );

		if ( 'off' === $enable_status ) {
			return false;
		} elseif (
			( 'cart_total' == $cart_type && 'over_equal' == $condition && WC()->cart->subtotal >= (float) $total_amount )
			|| ( 'cart_total' == $cart_type && 'under' == $condition && WC()->cart->subtotal < (float) $total_amount )
			|| ( 'cart_total' == $cart_type && 'equal' == $condition && WC()->cart->subtotal == (float) $total_amount )
		) {

			return true;

		} elseif (
			( 'cart_total_items' == $cart_type && 'over_equal' == $condition && WC()->cart->get_cart_contents_count() >= (int) $total_items )
			|| ( 'cart_total_items' == $cart_type && 'under' == $condition && WC()->cart->get_cart_contents_count() < (int) $total_items )
			|| ( 'cart_total_items' == $cart_type && 'equal' == $condition && WC()->cart->get_cart_contents_count() == (int) $total_items )
		) {

			return true;

		} elseif ( 'specific_products' === $cart_type && count( array_intersect( $cart_product_ids, $product_ids ) ) > 0
		) {
			return true;

		} else {
			return false;
		}
	}

	/**
	 * Ajax check popup status action callback.
	 *
	 * @since 1.0.0
	 *
	 * *
	 * */
	public function wtd_promocart_check_popup_status_callback() {
		// Save settings.
		$response['success'] = false;

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wtd_promocart_popup_nonce' ) ) {
			$response['message'] = __( 'Invalid nonce', 'promocart-popup' );
			wp_send_json( $response );
			return;
		} 
        if ( WC()->session->get( 'wtd_promocart_discount_applied' ) == true ) { 
			$response['success'] = false;
			$response['message'] = 'Popup is not visible.';
		}elseif ( true === self::check_popup_status() ) {
			$response['success'] = true;
			$response['message'] = 'Popup is visible.';
		} else {
			$response['success'] = false;
			$response['message'] = 'Popup is not visible.';
		}

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
