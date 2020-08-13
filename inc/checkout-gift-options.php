<?php

/**
 * Feature for adding gift options to checkout
 */
class FluidCheckout_CheckoutGiftOptions extends FluidCheckout {

	/**
	 * __construct function.
	 */
	public function __construct() {
		$this->hooks();
	}



	/**
	 * Initialize hooks.
	 */
	public function hooks() {
		if ( get_option( 'wfc_enable_checkout_gift_options', 'false' ) == 'true' ) {
			add_filter( 'body_class', array( $this, 'add_body_class' ) );

			add_filter( 'woocommerce_after_order_notes' , array( $this, 'maybe_output_gift_options_fields' ), 10 );
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'update_order_meta_with_gift_options_fields' ), 10, 1 );
			
			add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'display_gift_options_fields_order_admin_screen' ), 100, 1 );
			add_action( 'woocommerce_process_shop_order_meta', array( $this, 'save_order_gift_details' ) );
		}
	}



	/**
	 * Add page body class for feature detection
	 */
	public function add_body_class( $classes ) {
		// Bail if not on checkout page.
		if( ! function_exists( 'is_checkout' ) || ! is_checkout() || is_order_received_page() ){ return $classes; }

		return array_merge( $classes, array( 'has-wfc-gift-options' ) );
	}



	/**
	 * Output gift options fields.
	 */
	public function maybe_output_gift_options_fields( $checkout ) {
		// Bail if shipping not needed
		if ( ! WC()->cart->needs_shipping() ) { return; }
		
		$checkbox_field = apply_filters( 'wfc_gift_options_checkbox_field', array(
			'_wfc_has_gift_options' => array(
				'type'          => 'checkbox',
				'class'         => array( 'form-row-wide '),
				'label'         => __( 'Do you want to add a gift message?', 'woocommerce-fluid-checkout' ),
				'default'		=> false,
			),
		) );

		$gift_option_fields = apply_filters( 'wfc_gift_options_fields', array(
			'_wfc_gift_message' => array(
				'type'          => 'textarea',
				'class'         => array( 'form-row-wide '),
				'label'         => __( 'Gift message', 'woocommerce-fluid-checkout' ),
				'placeholder'   => __( 'Write a special message...', 'woocommerce-fluid-checkout' ),
				'default'		=> $checkout->get_value( '_wfc_gift_message' ),
			),
			'_wfc_gift_from' => array(
				'type'          => 'text',
				'class'         => array( 'form-row-wide '),
				'label'         => __( 'From', 'woocommerce-fluid-checkout' ),
				'placeholder'   => __( 'Who is sending this gift?', 'woocommerce-fluid-checkout' ),
				'default'		=> $checkout->get_value( 'billing_first_name' ),
			)
		) );
		
		wc_get_template(
			'checkout/form-gift-options.php',
			array(
				'checkout'          => WC()->checkout(),
				'checkbox_field'    => $checkbox_field,
				'display_fields'    => $gift_option_fields,
			)
		);
	}



	/**
	 * Update the order meta with gift fields value.
	 **/
	public function update_order_meta_with_gift_options_fields( $order_id ) {
		$has_gift_options = isset( $_POST['_wfc_has_gift_options'] ) && boolval( $_POST['_wfc_has_gift_options'] );
		$gift_message = isset( $_POST['_wfc_gift_message'] ) ? $_POST['_wfc_gift_message'] : '';
		$gift_from = isset( $_POST['_wfc_gift_from'] ) ? $_POST['_wfc_gift_from'] : '';

		// Update order meta
		update_post_meta( $order_id, '_wfc_has_gift_options', $has_gift_options ? 'Yes' : 'No' );
		update_post_meta( $order_id, '_wfc_gift_message', $has_gift_options ? $gift_message : '' );
		update_post_meta( $order_id, '_wfc_gift_from', $has_gift_options ? $gift_from : '' );
	}



	/**
	 * Display gift options fields on order admin screen.
	 **/
	public function display_gift_options_fields_order_admin_screen( $order ) {
		$order_id = $order->id;
		$gift_message = get_post_meta( $order_id, '_wfc_gift_message', true );
		$gift_from = get_post_meta( $order_id, '_wfc_gift_from', true );
		

		if ( $gift_message || $gift_from ) : ?>
		
		<br class="clear" />
		<h4>Gift Order <a href="#" class="edit_address">Edit</a></h4>

		<div class="address">
			<?php
			if ( $gift_message ) {
				echo '<p><strong>'. __( 'Gift Message:', 'woocommerce-fluid-checkout' ) . '</strong>' . $gift_message . '</p>';
			}

			if ( $gift_from ) {
				echo '<p><strong>'. __( 'Gift From:', 'woocommerce-fluid-checkout' ) . '</strong>' . $gift_from . '</p>';
			}
			?>
		</div>
		<div class="edit_address">
			<?php
			if ( $gift_message ) {
				woocommerce_wp_textarea_input( array(
				'id' => '_wfc_gift_message',
				'label' => __( 'Gift Message:', 'woocommerce-fluid-checkout' ),
				'value' => $gift_message,
				'wrapper_class' => 'form-field-wide'
				) );
			}

			if ( $gift_from ) {
				woocommerce_wp_text_input( array(
				'id' => '_wfc_gift_from',
				'label' => __( 'Gift From:', 'woocommerce-fluid-checkout' ),
				'value' => $gift_from,
				'wrapper_class' => 'form-field-wide'
				) );
			}
			?>
		</div>

		<?php
		endif;
	}


	
	/**
	 * Save order meta data for gift message
	 */
	public function save_order_gift_details( $order_id ){
		update_post_meta( $order_id, '_wfc_gift_message', wc_clean( $_POST[ '_wfc_gift_message' ] ) );
		update_post_meta( $order_id, '_wfc_gift_from', wc_sanitize_textarea( $_POST[ '_wfc_gift_from' ] ) );
	}

}

FluidCheckout_CheckoutGiftOptions::instance();