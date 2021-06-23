<?php

/**
 * Customizations to the checkout optional fields.
 */
class FluidCheckout_CheckoutHideOptionalFields extends FluidCheckout {

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
		// WooCommerce fields output
		add_filter( 'woocommerce_form_field', array( $this, 'add_optional_form_field_link_button' ), 100, 4 );
	}



	/**
	 * Return Checkout Steps class instance.
	 */
	public function checkout_steps() {
		return FluidCheckout_Steps::instance();
	}



	/**
	 * Get the checkout fields args.
	 *
	 * @param   string  $field  Field html markup to be changed.
	 * @param   string  $key    Field key.
	 * @param   arrray  $args   Field args.
	 * @param   mixed   $value  Value of the field. Defaults to `null`.
	 */
	public function add_optional_form_field_link_button( $field, $key, $args, $value ) {
		// Bail if field is required
		if ( array_key_exists( 'required', $args ) && $args['required'] == true ) { return $field; }

		// Bail if optional field by its type
		if ( in_array( $args['type'], apply_filters( 'fc_hide_optional_fields_skip_types', array( 'checkbox', 'radio' ) ) ) ) { return $field; }

		// Always skip these fields
		$skip_list = array( 'state', 'billing_state', 'shipping_state' );

		// Maybe skip "address line 2" fields
		if ( get_option( 'fc_hide_optional_fields_skip_address_2', 'no' ) === 'yes' ) {
			$skip_list[] = 'address_2';
			$skip_list[] = 'shipping_address_2';
			$skip_list[] = 'billing_address_2';
		}

		// Check if should skip current field
		if ( in_array( $key, apply_filters( 'fc_hide_optional_fields_skip_list', $skip_list ) ) ) { return $field; }

		// Set attribute `data-autofocus` to focus on the optional field when expanding the section
		$field = str_replace( 'name="'. esc_attr( $key ) .'"', 'name="'. esc_attr( $key ) .'" data-autofocus', $field );

		// Move container classes to expansible block
		$container_class_esc = esc_attr( implode( ' ', $args['class'] ) );
		$expansible_section_args = array(
			'section_attributes' => array(
				'class' => 'form-row ' . $container_class_esc,
			),
		);

		// Remove the container class from the field element
		$field = str_replace( 'form-row '. $container_class_esc, 'form-row ', $field );

		// Maybe set field as `expanded` when it contains a value
		if ( ! empty( $value ) ) {
			$expansible_section_args[ 'initial_state' ] = 'expanded';
		}

		// Start buffer
		ob_start();

		// Add expansible block markup for the field
		/* translators: %s: Form field label */
		$toggle_label = apply_filters( 'fc_expansible_section_toggle_label_'.$key, sprintf( __( 'Add %s', 'fluid-checkout' ), strtolower( $args['label'] ) ) );
		$this->checkout_steps()->output_expansible_form_section_start_tag( $key, $toggle_label, $expansible_section_args );
		echo $field; // WPCS: XSS ok.
		$this->checkout_steps()->output_expansible_form_section_end_tag();

		// Get value and clear buffer
		$field = ob_get_clean();

		return $field;
	}

}

FluidCheckout_CheckoutHideOptionalFields::instance();
