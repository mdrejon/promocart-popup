<?php
/**
 * Popu Template
 */


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="wtd-promocart-popup">
	<div class="wtd-promocart-popup-wrap ">
	   
		<div class="wtd-promocart-popup-inner">
			<span class="close">
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
		</span>
			<div class="wtd-promocart-popup-promp-icon"> 
				<img src="<?php echo esc_url( WTD_promocart_POPUP_URL . 'Assets/app/images/discount-image.png' ); ?>" alt="">
			</div>
			<div class="wtd-promocart-popup-content">
				<h2> <span><?php echo esc_html( __( 'Get 15%', 'promocart-popup' ) ); ?></span> <br> <?php echo esc_html( __( 'coupon now', 'promocart-popup' ) ); ?></h2>
				
				<p> <?php echo esc_html( __( 'Enjoy our amazing products for a 15% discount code', 'promocart-popup' ) ); ?></p>

				<button class="wtd-apply-cuppon"><?php echo esc_html( __( 'Apply code', 'promocart-popup' ) ); ?></button>
			</div>
		</div>
	</div>
</div>