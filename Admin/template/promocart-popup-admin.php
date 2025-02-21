<?php 
/**
 * Admin Settings Template.
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get Option Value.
$settings      = ! empty( get_option( 'wtd_promocart_popup_settings' ) ) ? get_option( 'wtd_promocart_popup_settings' ) : array();
$enable_status = isset( $settings['enable_status'] ) ? esc_html($settings['enable_status']) : 'off';
$cart_type     = isset( $settings['cart_type'] ) ? esc_html($settings['cart_type']) : '';
$condition     = isset( $settings['condition'] ) ? esc_html($settings['condition']) : '';
$total_amount  = isset( $settings['total_amount'] ) ? esc_html($settings['total_amount']) : '';
$total_items   = isset( $settings['total_items'] ) ? esc_html($settings['total_items']) : '';
$products      = isset( $settings['products'] ) ? $settings['products'] : array();

// Ensure WooCommerce is active.
if ( class_exists( 'WooCommerce' ) ) {
	// get all products with.
	$args           = array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
	);
	$products_query = new WP_Query( $args );
	
	
	$currency = get_woocommerce_currency_symbol();

	$products_list = array();
	if ( $products_query->have_posts() ) {
		while ( $products_query->have_posts() ) {
			$products_query->the_post();
			$product_id                   = get_the_ID();
			$product_title                = get_the_title();
			$products_list[ $product_id ] = $product_title;
		}
	}
	wp_reset_postdata();
} else {
	$products_list = array();
	$currency = 'USD';
}

?>

<div class="wrap">
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
	
	<?php if ( isset( $_GET['action'] ) && $_GET['action'] == 'updated' ) : ?>
		<div class="updated notice">
			<p><?php esc_html_e( 'Settings saved.', 'promocart-popup' ); ?></p>
		</div>
	<?php endif; ?>
	
	<div class="wtd-popup-settings-wrap">   
		<form id="wtd-popup-settings-form" method="post"> 
			<?php wp_nonce_field( 'wtd_promocart_popup_nonce_action', 'wtd_promocart_popup_nonce' ); ?>
			<div class="wtd-popup-settings-card">
				<h3><?php echo esc_html( __( 'General Settings', 'promocart-popup' ) ); ?></h3>

				<!-- Single From Field -->
				<div class="wtd-popup-form-field">
					<label for="enable_status"> <?php echo esc_html( __( 'Enable Popup', 'promocart-popup' ) ); ?></label>
					<!-- short desc -->
					<p><?php echo esc_html( __( 'Show/Hide the popup on the checkout page.', 'promocart-popup' ) ); ?></p>

					<!-- markup for swicher -->
					<label class="switch">
						<input type="checkbox" id="enable_status" name="wtd_promocart_popup_settings[enable_status]" <?php checked( $enable_status, 'on' ); ?>>
						<span class="slider"></span>
					</label>
 

				</div>
				<!-- Single From Field -->

				<!-- Single From Field -->
				<div class="wtd-popup-form-field">
					<!-- Cart money value/Number of cart items/Products in the cart -->
					<label for="cart_type"><?php echo esc_html( __( 'Cart Type', 'promocart-popup' ) ); ?></label>
					<!-- short desc --> 
					<p><?php echo esc_html( __( 'Choose the type of total amount of cart, number of cart items or specific product.', 'promocart-popup' ) ); ?></p>
					<!-- markup for swicher -->
					<select name="wtd_promocart_popup_settings[cart_type]" id="cart_type">
						<option value="" ><?php echo esc_html( __( 'Select cart Type', 'promocart-popup' ) ); ?></option>
						<option value="cart_total" <?php selected( $cart_type, 'cart_total' ); ?>><?php echo esc_html( __( 'Cart Total', 'promocart-popup' ) ); ?></option>
						<option value="cart_total_items" <?php selected( $cart_type, 'cart_total_items' ); ?>><?php echo esc_html( __( 'Number of cart items', 'promocart-popup' ) ); ?></option>
						<option value="specific_products" <?php selected( $cart_type, 'specific_products' ); ?>><?php echo esc_html( __( 'Specific product', 'promocart-popup' ) ); ?></option>
					</select>

				</div>
				<!-- Single From Field -->
				 
				<!-- Single From Field -->
				<div class="wtd-popup-form-field condition-wrap <?php echo 'cart_total' == $cart_type || 'cart_total_items' == $cart_type ? 'active' : ''; ?>">
					<!-- Cart money value/Number of cart items/Products in the cart -->
					<label for="condition"><?php echo esc_html( __( 'Condition', 'promocart-popup' ) ); ?> </label>
					<!-- short desc -->  
					<p><?php echo esc_html( __( 'Choose the condition to show the popup.', 'promocart-popup' ) ); ?></p>
					<!-- markup for swicher -->
					 
					<!-- markup for swicher -->
					<select name="wtd_promocart_popup_settings[condition]" id="condition">
						<option value="" ><?php echo esc_html( __( 'Select Condition', 'promocart-popup' ) ); ?></option>
						<option value="over_equal" <?php selected( $condition, 'over_equal' ); ?>><?php echo esc_html( __( 'Over or Equal', 'promocart-popup' ) ); ?></option>
						<option value="equal" <?php selected( $condition, 'equal' ); ?>><?php echo esc_html( __( 'Equal', 'promocart-popup' ) ); ?></option>
						<option value="under" <?php selected( $condition, 'under' ); ?>><?php echo esc_html( __( 'Under', 'promocart-popup' ) ); ?></option>
					</select>

				</div>
				<!-- Single From Field -->

				<!-- Single From Field -->
				<div class="wtd-popup-form-field total-amount-wrap <?php echo 'cart_total' == $cart_type ? 'active' : ''; ?>">
					<!-- Cart money value/Number of cart items/Products in the cart -->
					<label for="total_amount"><?php echo esc_html( __( 'Amount', 'promocart-popup' ) ); ?>  ( <?php echo esc_html($currency); ?>)</label>
					<!-- short desc -->   
					<p><?php echo esc_html( __( 'Enter the desired amount of cart to show the popup.', 'promocart-popup' ) ); ?></p>
					<!-- input field -->
					<input type="number" id="total_amount" name="wtd_promocart_popup_settings[total_amount]" value="<?php echo esc_attr( $total_amount ); ?>" >
					 
					
					 
				</div>
				<!-- Single From Field -->

				<!-- Single From Field -->
				<div class="wtd-popup-form-field total-item-wrap <?php echo 'cart_total_items' == $cart_type ? 'active' : ''; ?>">
					<!-- Cart money value/Number of cart items/Products in the cart -->
					<label for="total_items"><?php echo esc_html( __( 'Items', 'promocart-popup' ) ); ?> </label>
					<!-- short desc -->   
					<p><?php echo esc_html( __( 'Enter the desired total cart items to show the popup.', 'promocart-popup' ) ); ?></p>
					<!-- input field -->
					<input type="number" id="total_items" name="wtd_promocart_popup_settings[total_items]"  value="<?php echo esc_attr( $total_items ); ?>" >
					 
					 
				</div> 


				<!-- Single From Field -->
				<div class="wtd-popup-form-field products-wrap <?php echo 'specific_products' == $cart_type ? 'active' : ''; ?>" >
					<!-- Cart money value/Number of cart items/Products in the cart -->
					<label for="specific_products"> <?php echo esc_html( __( 'Select Products', 'promocart-popup' ) ); ?></label>
					<!-- short desc --> 
					<p><?php echo esc_html( __( 'Select the products you want to show the popup.', 'promocart-popup' ) ); ?></p>
					<!-- markup for swicher -->
					<select name="wtd_promocart_popup_settings[products][]" id="specific_products" multiple="multiple">
						<option value="" ><?php echo esc_html( __( 'Select Products', 'promocart-popup' ) ); ?></option>
						<?php
						foreach ( $products_list as $product_id => $product_title ) {
							// with selected
							$selected = in_array( $product_id, $products ) ? 'selected="selected"' : '';
							echo ' <option value="' . esc_attr( $product_id ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $product_title ) . '</option>';
						}
						?>
												</select>

				</div>
				<!-- Single From Field -->

				<!-- Single From Field -->
				<div class="wtd-popup-form-field">
					<!-- button submit -->
					<button type="submit" class=" wtd-promocart-settings-submit" name="wtd_promocart_popup_save_settings">Save Settings</button>
					<!-- button submit -->
				</div>
				<!-- Single From Field -->
			
				 
			</div>
		</form>
	</div>
   
</div>
