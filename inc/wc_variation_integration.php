<?php
/*
 *
 */

function nv_cfi_ghent_variation_settings_fields( $loop, $variation_data, $variation ) {
	// no logo placement, leave
	if ( ! isset( $variation_data['attribute_logo-placement'] ) || 'No Logo' === $variation_data['attribute_logo-placement'] ) {
		return;
	}
	?>
	<div>
		<?php
		woocommerce_wp_select(
			array(
				'id'            => "logo_prev_bg{$loop}",
				'name'          => "logo_prev_bg[{$loop}]",
				'value'         => get_post_meta( $variation->ID, 'logo_prev_bg', true ),
				'label'         => __( 'Preview Background', 'woocommerce' ),
				'options'       => nv_cfi_ghent_get_preview_backgrounds(),
				'desc_tip'      => true,
				'description'   => __( 'Controls which background is used to add the selected logo.', 'woocommerce' ),
				'wrapper_class' => 'form-row form-row-full variable_preview_background hide_if_variation_virtual',
			)
		);

		$logo_placement_top    = get_post_meta( $variation->ID, 'logo_placement_top', true );
		$logo_placement_right  = get_post_meta( $variation->ID, 'logo_placement_right', true );
		$logo_placement_height = get_post_meta( $variation->ID, 'logo_placement_height', true );
		$logo_placement_width  = get_post_meta( $variation->ID, 'logo_placement_width', true );
		$placement_vars = 'Top, Max Height, Max Width';
		if ( 'Top Right' === $variation_data['attribute_logo-placement'] ) {
			$placement_vars = 'Top, Right, Max Height, Max Width';
		}
		?>
		<p class="form-field form-row logo_placement_field hide_if_variation_virtual form-row-full">
			<label for="logo_placement_top_<?php echo $loop; ?>">Logo Placement (<?php echo esc_html( $placement_vars ); ?>) in Inches</label>
			<?php echo wc_help_tip( __( 'Top, Right, Max Width, Max Height in inches in decimal form. Right will only apply to Top Right placed logos.', 'woocommerce' ) ); ?>
			<span class="wrap"">
				<input id="logo_placement_top_<?php echo $loop; ?>" placeholder="<?php echo esc_attr( 'Top' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="logo_placement_top[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $logo_placement_top ); ?>" />
				<?php if ( 'Top Right' === $variation_data['attribute_logo-placement'] ) : ?>
				<input placeholder="<?php echo esc_attr( 'Right' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="logo_placement_right[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $logo_placement_right ); ?>" />
				<?php endif; ?>
				<input placeholder="<?php echo esc_attr( 'Max Height' ); ?>" class="input-text wc_input_decimal" size="6" type="text" name="logo_placement_height[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $logo_placement_height ); ?>" />
				<input placeholder="<?php echo esc_attr( 'Max Width' ); ?>" class="input-text wc_input_decimalt" size="6" type="text" name="logo_placement_width[<?php echo esc_attr( $loop ); ?>]" value="<?php echo esc_attr( $logo_placement_width ); ?>" />
			</span>
		</p>
	</div>
	<?php
}

add_action( 'woocommerce_product_after_variable_attributes', 'nv_cfi_ghent_variation_settings_fields', 10, 3 );
function nv_cfi_ghent_get_setting_array( $setting_name, $setting_label, $variation_id ) {
	return [
		'id'            => $setting_name . "{$loop}",
		'name'          => $setting_name . "[{$loop}]",
		'value'         => get_post_meta( $variation_id, $setting_name, true ),
		'label'         => __( $setting_label, 'woocommerce' ),
		'wrapper_class' => 'form-row form-row-full',
	];
}

function nv_cfi_ghent_get_preview_backgrounds() {

	$preview_backgrounds = [ 'none' => 'Select A Preview Background' ];

	foreach ( glob( GHENT_PREVIEW_BACKGROUND_DIR . '*.pdf' ) as $filename ) {
		$info = pathinfo( $filename );
		$display_name = ucwords( str_replace( ['-','_'], ' ', $info['filename'] ) );
		$preview_backgrounds[ $info['basename'] ] = $display_name;
	}

	return $preview_backgrounds;
}

/**
 * @param $variation_id
 * @param $loop
 */
function nv_cfi_ghent_save_variation_settings_fields( $variation_id, $loop ) {

	if ( isset( $_POST['logo_prev_bg'][ $loop ] ) ) {
		$preview_background = $_POST['logo_prev_bg'][ $loop ];
		update_post_meta( $variation_id, 'logo_prev_bg', esc_attr( $preview_background ) );
	}

	if ( isset( $_POST['logo_placement_top'][ $loop ] ) ) {
		$logo_top = $_POST['logo_placement_top'][ $loop ];
		update_post_meta( $variation_id, 'logo_placement_top', esc_attr( $logo_top ) );
	}

	if ( isset( $_POST['logo_placement_right'][ $loop ] ) ) {
		$logo_right = $_POST['logo_placement_right'][ $loop ];
		update_post_meta( $variation_id, 'logo_placement_right', esc_attr( $logo_right ) );
	}

	if ( isset( $_POST['logo_placement_height'][ $loop ] ) ) {
		$logo_height = $_POST['logo_placement_height'][ $loop ];
		update_post_meta( $variation_id, 'logo_placement_height', esc_attr( $logo_height ) );
	}

	if ( isset( $_POST['logo_placement_width'][ $loop ] ) ) {
		$logo_width = $_POST['logo_placement_width'][ $loop ];
		update_post_meta( $variation_id, 'logo_placement_width', esc_attr( $logo_width ) );
	}
}

add_action( 'woocommerce_save_product_variation', 'nv_cfi_ghent_save_variation_settings_fields', 10, 2 );

/**
 * @param $variation
 * @return mixed
 */
function nv_cfi_ghent_load_variation_settings_fields( $variation ) {
	$variation['logo_prev_bg']          = get_post_meta( $variation[ 'variation_id' ], 'logo_prev_bg', true );
	$variation['logo_placement_top']    = get_post_meta( $variation[ 'variation_id' ], 'logo_placement_top', true );
	$variation['logo_placement_right']  = get_post_meta( $variation[ 'variation_id' ], 'logo_placement_right', true );
	$variation['logo_placement_height'] = get_post_meta( $variation[ 'variation_id' ], 'logo_placement_height', true );
	$variation['logo_placement_width']  = get_post_meta( $variation[ 'variation_id' ], 'logo_placement_width', true );

	return $variation;
}

add_filter( 'woocommerce_available_variation', 'nv_cfi_ghent_load_variation_settings_fields' );
