<?php
defined( 'ABSPATH' ) || exit;

/**
 * Add customizations of the checkout page for Local Pickup shipping methods.
 */
class FluidCheckout_CheckoutLocalPickup extends FluidCheckout {

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
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 10 );
		add_action( 'wp', array( $this, 'prepare_local_pickup_hooks' ), 5 );
	}

	/**
	 * Prepare the hooks related to shipping method "Local Pickup".
	 */
	public function prepare_local_pickup_hooks() {
		// Bail if not checkout pages
		if ( ! is_checkout() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) { return; }

		// Hide shipping address for local pickup
		if ( $this->is_local_pickup_available() ) {
			remove_action( 'fc_output_step_shipping', array( FluidCheckout_Steps::instance(), 'output_substep_shipping_address' ), 10 );
			remove_action( 'fc_output_step_shipping', array( FluidCheckout_Steps::instance(), 'output_substep_shipping_method' ), 20 );
			add_action( 'fc_output_step_shipping', array( FluidCheckout_Steps::instance(), 'output_substep_shipping_method' ), 10 );
			add_action( 'fc_output_step_shipping', array( FluidCheckout_Steps::instance(), 'output_substep_shipping_address' ), 20 );
			add_action( 'fc_checkout_after_step_shipping_fields', array( $this, 'maybe_output_shipping_address_text' ), 10 );
			add_filter( 'woocommerce_cart_needs_shipping_address', array( $this, 'maybe_change_needs_shipping_address' ), 10 );
			add_filter( 'fc_substep_title_shipping_address', array( $this, 'change_shipping_address_substep_title' ), 50 );
			add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'add_shipping_address_substep_title_fragment' ), 10 );
			add_filter( 'fc_substep_shipping_address_text', array( $this, 'change_substep_text_shipping_address' ), 50 );
		}
	}



	/**
	 * Enqueue scripts.
	 */
	public function enqueue_assets() {
		// Bail if not at checkout page or `local_pickup` not available
		if( ! function_exists( 'is_checkout' ) || ! is_checkout() || ! $this->is_local_pickup_available() ){ return; }

		// Load scripts
		wp_enqueue_script( 'fc-checkout-local-pickup', self::$directory_url . 'js/checkout-local-pickup'. self::$asset_version . '.js', array( 'jquery', 'wc-checkout' ), NULL, true );
		wp_add_inline_script( 'fc-checkout-local-pickup', 'window.addEventListener("load",function(){CheckoutLocalPickup.init();})' );
	}



	/**
	 * Determines if a shipping address is needed depending on the shipping method selected.
	 *
	 * @return  boolean  `true` if the user has provided all the required data for this step, `false` otherwise. Defaults to `false`.
	 */
	public function maybe_change_needs_shipping_address( $needs_shipping_address ) {
		// Hides shipping addresses for `local_pickup`.
		if ( $this->is_shipping_method_local_pickup_selected() ) {
			return false;
		}

		return $needs_shipping_address;
	}



	/**
	 * Output the shipping address substep as text when "Local pickup" is selected for the shipping method.
	 */
	public function maybe_output_shipping_address_text() {
		// Bail if shipping method is not `local_pickup`
		if ( ! $this->is_shipping_method_local_pickup_selected() ) { return; }
		
		FluidCheckout_Steps::instance()->output_substep_text_shipping_address();
	}



	/**
	 * Determines if the currently selected shipping method is `local_pickup`.
	 *
	 * @return  boolean  `true` if the selected shipping method is `local_pickup`. Defaults to `false`.
	 */
	public function is_shipping_method_local_pickup_selected() {
		$checkout = WC()->checkout();
		$is_shipping_method_local_pickup_selected = false;
		
		// Make sure chosen shipping method is set
		WC()->cart->calculate_shipping();
		
		// Check chosen shipping method
		$packages = WC()->shipping()->get_packages();
		foreach ( $packages as $i => $package ) {
			$available_methods = $package['rates'];
			$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';

			if ( $chosen_method && 0 === strpos( $chosen_method, 'local_pickup' ) ) {
				$is_shipping_method_local_pickup_selected = true;
				break;
			}
		}

		return apply_filters( 'fc_is_shipping_method_local_pickup_selected', $is_shipping_method_local_pickup_selected );
	}



	/**
	 * Determines if the any `local_pickup` shipping method is available.
	 *
	 * @return  boolean  `true` if the user has provided all the required data for this step, `false` otherwise. Defaults to `false`.
	 */
	public function is_local_pickup_available() {
		$checkout = WC()->checkout();
		$is_local_pickup_available = false;

		// Make sure chosen shipping method is set
		WC()->cart->calculate_shipping();

		// Check available shipping methods
		$packages = WC()->shipping()->get_packages();
		foreach ( $packages as $i => $package ) {
			$available_methods = $package['rates'];
			foreach ( $available_methods as $method_id => $shipping_method ) {
				if ( 0 === strpos( $method_id, 'local_pickup' ) ) {
					$is_local_pickup_available = true;
					break;
				}
			}
		}

		return apply_filters( 'fc_is_local_pickup_available', $is_local_pickup_available );
	}



	/**
	 * Add shipping address title as checkout fragment.
	 *
	 * @param array $fragments Checkout fragments.
	 */
	public function add_shipping_address_substep_title_fragment( $fragments ) {
		$substep_id = 'shipping_address';
		// TODO: Get substep title from registered substeps titles (needs changes to FluidCheckout_Steps)
		$substep_title = $this->change_shipping_address_substep_title( __( 'Shipping to', 'fluid-checkout' ) );
		$html = FluidCheckout_Steps::instance()->get_substep_title_html( $substep_id, $substep_title );
		$fragments['.fc-step__substep-title--shipping_address'] = $html;
		return $fragments;
	}

	/**
	 * Change the Shipping Address substep title.
	 */
	public function change_shipping_address_substep_title( $substep_title ) {
		// Change substep title for `local_pickup` shipping methods
		if ( $this->is_shipping_method_local_pickup_selected() ) {
			$substep_title = apply_filters( 'fc_shipping_address_local_pickup_point_title', __( 'Pickup point', 'fluid-checkout' ) );
		}

		return $substep_title;
	}



	/**
	 * Output shipping address substep in text format for when the step is completed.
	 */
	public function change_substep_text_shipping_address( $html ) {
		// Use store base address for `local_pickup`
		if ( $this->is_shipping_method_local_pickup_selected() ) {
			$address_data = array(
				'address_1' => WC()->countries->get_base_address(),
				'address_2' => WC()->countries->get_base_address_2(),
				'city' => WC()->countries->get_base_city(),
				'state' => WC()->countries->get_base_state(),
				'country' => WC()->countries->get_base_country(),
				'postcode' => WC()->countries->get_base_postcode(),
			);

			$html = '<div class="fc-step__substep-text-content fc-step__substep-text-content--shipping-address">';
			$html .= '<div class="fc-step__substep-text-line">' . WC()->countries->get_formatted_address( $address_data ) . '</div>'; // WPCS: XSS ok.
			$html .= '</div>';
		}

		return $html;
	}
	
}

FluidCheckout_CheckoutLocalPickup::instance();