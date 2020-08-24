<?php

use setasign\Fpdi\Tcpdf\Fpdi;

function nv_cfi_ghent_dropzone_upload() {
	add_filter( 'wp_handle_upload', 'nv_cfi_maybe_create_preview', PHP_INT_MAX );
}

add_action( 'wp_ajax_nopriv_pewc_dropzone_upload', 'nv_cfi_ghent_dropzone_upload', 8 ); //allow on front-end
add_action( 'wp_ajax_pewc_dropzone_upload', 'nv_cfi_ghent_dropzone_upload', 8 );

function nv_cfi_maybe_create_preview( $upload ) {

    $variation_preview_bg = get_post_meta( $_REQUEST['variationId'], 'logo_prev_bg', true );
    if ( empty( $variation_preview_bg ) ) {
        error_log( 'no preview bg' );
        return $upload;
    }
    if ( ! empty( $upload['error'] ) ) {
        error_log( 'error in upload data' );
        return $upload;
    }
    nv_cfi_ghent_create_preview( $upload );

	remove_filter( 'wp_handle_upload', 'nv_cfi_maybe_create_preview', PHP_INT_MAX );
	return $upload;
}

add_action( 'pewc_start_upload_script_init', function( $id ) { ?>
	this.on( 'sendingmultiple', function( file, xhr, formData ) {
		var variation_id = document.getElementsByName( 'variation_id' )[0].value;
		formData.append( 'variationId', variation_id );
	});
    this.on( 'successmultiple', function( file, response ) {
        ghentCreatePreview( this, response.data.files );
    });
    this.on( 'removedfile', function() {
        ghentRemovePreview();
    });
<?php });

/**
 * @param $upload
 * @return string
 * @throws \setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException
 * @throws \setasign\Fpdi\PdfParser\Filter\FilterException
 * @throws \setasign\Fpdi\PdfParser\PdfParserException
 * @throws \setasign\Fpdi\PdfParser\Type\PdfTypeException
 * @throws \setasign\Fpdi\PdfReader\PdfReaderException
 */
function nv_cfi_ghent_create_preview( $upload ) {
    $logo_file      = $upload['file'];
    $logo_detail    = pathinfo( $logo_file );
	$logo_dir_path  = $logo_detail['dirname'];
	$logo_file_name = $logo_detail['basename'];
	$logo_name      = $logo_detail['filename'];
	$logo_extension = $logo_detail['extension'];
    $variationID    = $_REQUEST['variationId'];

    // get the variation meta
	$logo_placement     = strtolower( str_replace( ' ', '-', get_post_meta( $variationID, 'attribute_logo-placement', true ) ) );
	$logo_position_top  = get_post_meta( $variationID, 'logo_placement_top', true );
	$logo_max_height    = get_post_meta( $variationID, 'logo_placement_height', true );
	$logo_max_width     = get_post_meta( $variationID, 'logo_placement_width', true );
	$preview_background = get_post_meta( $variationID, 'logo_prev_bg', true );

	if ( empty( $preview_background ) ) {
	    return;
    }
    $preview_background = GHENT_PREVIEW_BACKGROUND_DIR . $preview_background;

	if ( 'top-right' === $logo_placement ) {
		$logo_position_right = get_post_meta($variationID, 'logo_placement_right', true);
	}

	$inches_to_point_multiplier = 25.4;

	// calculate adjusted log size
	$logo_position_top = $logo_position_top * $inches_to_point_multiplier;
	if ( 'top-right' === $logo_placement ) {
		$logo_position_right = $logo_position_right * $inches_to_point_multiplier;
	}

	$logo_max_height = $logo_max_height * $inches_to_point_multiplier;
	$logo_max_width  = $logo_max_width * $inches_to_point_multiplier;

	$completed_file_uri = $logo_dir_path . '/preview-' . $logo_placement . '_' . $logo_name . '.pdf';

	$pdf = new Fpdi();
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);

	$page_count = $pdf->setSourceFile( $preview_background ); // should always be 1 for our use
	$template   = $pdf->importPage(1);

	$template_stats = $pdf->getTemplateSize( $template );

	$pdf->AddPage( $template_stats['orientation'], $template_stats );
	$pdf->useTemplate( $template );

	if ( 'pdf' === strtolower( $logo_extension ) ) {
		$pdf->setSourceFile( $logo_file );
		$pdf_logo = $pdf->importPage( 1 );
		$pdf_logo_placement = nv_cfi_ghent_get_pdf_logo_params( $pdf, $pdf_logo, $logo_max_width, $logo_max_height );

		if ( $logo_max_height > $pdf_logo_placement['height'] ) {
			$logo_position_top += round( ( ( $logo_max_height - $pdf_logo_placement['height'] ) / 2 ), 2 );
        }
		$logo_max_width  = $pdf_logo_placement['width'];
		$logo_max_height = $pdf_logo_placement['height'];

    }

	$fitbox = true;
	$palign = '';

	switch ( $logo_placement ) {
		case "top-center":
			$logo_x = ( $template_stats['width'] / 2 ) - ( $logo_max_width / 2 );
			$logo_y = $logo_position_top;
			$fitbox = 'CM';
			$palign = 'C';
			break;
		case "top-right":
		default:
			$logo_x = $template_stats['width'] - ( $logo_max_width + $logo_position_right );
			$logo_y = $logo_position_top;
    		$fitbox = 'RM';
    		$palign = 'R';
	}

    if ( 'svg' === strtolower( $logo_extension ) ) {
        $pdf->ImageSVG($logo_file, $logo_x, $logo_y, $logo_max_width, $logo_max_height, '', '', $palign);
    } else if ( 'pdf' === strtolower( $logo_extension ) ) {
        $pdf->useTemplate( $pdf_logo, $logo_x, $logo_y, $logo_max_width, $logo_max_height, false );
    } else if ( in_array( strtolower( $logo_extension ), ['eps', 'ai'], true ) ) {
        $pdf->ImageEps( $logo_file, $logo_x, $logo_y, $logo_max_width, $logo_max_height, '', true, '', $palign );
    } else {
	    $pdf->Image( $logo_file, $logo_x, $logo_y, $logo_max_width, $logo_max_height, '', '', 'T', true, 72, '', false, false, 0, $fitbox, false, false );
    }

	$pdf->Output( $completed_file_uri, 'F' );

	unset( $pdf );
}

/**
 * delete preview files if the logo is deleted
 */
function nv_cfi_ghent_dropzone_remove() {
	$existing_file_data = $_REQUEST['file_data'];
	if ( $existing_file_data ) {
		$existing_files = json_decode( stripslashes( $existing_file_data ) );
	}

	foreach( $existing_files as $existing_file ) {
		$logo_detail    = pathinfo( $existing_file->file );
		$logo_dir_path  = $logo_detail['dirname'];
		$logo_name      = $logo_detail['filename'];

		$potential_preview_files = glob( $logo_dir_path . '/preview-*_' . $logo_name . '.pdf' );

		foreach ( $potential_preview_files as $file ) {
			unlink( $file );
        }

    }

}

/*
 * calcs for getting the size of a pdf logo and adjusting its size to fit the space
 */
function nv_cfi_ghent_get_pdf_logo_params( $pdf, $pdf_logo, $max_width, $max_height ) {
    $logo_adjusted_size = [];
    $logo_size = $pdf->getTemplateSize( $pdf_logo );

	$ratio = $logo_size['width'] / $logo_size['height'];

	$logo_adjusted_size['height'] = $max_height;
	$logo_adjusted_size['width']  = round( $logo_adjusted_size['height'] * $ratio, 2 );

	if ( $logo_adjusted_size['width'] > $max_width ) {
	    $ratio = $logo_size['height'] / $logo_size['width'];

		$logo_adjusted_size['width']  = $max_width;
		$logo_adjusted_size['height'] = round( $logo_adjusted_size['width'] * $ratio, 2 );
    }

	return $logo_adjusted_size;
}

add_action( 'wp_ajax_nopriv_pewc_dropzone_remove', 'nv_cfi_ghent_dropzone_remove' ); //allow on front-end
add_action( 'wp_ajax_pewc_dropzone_remove', 'nv_cfi_ghent_dropzone_remove' );

function nv_cfi_ghent_add_cart_item_data( $cart_item_data, $product_id, $variation_id, $quantity=0 ) {
	error_log( print_r( $cart_item_data, true ) );
	error_log( print_r( $product_id, true ) );
	error_log( print_r( $variation_id, true ) );
    error_log( print_r( $quantity, true ) );

    return $cart_item_data;
}

add_filter( 'woocommerce_add_cart_item_data', 'nv_cfi_ghent_add_cart_item_data', 15, 4 );

function nv_cfi_ghent_pewc_add_cart_data( $item_data, $item, $group_id, $field_id, $uploads ) {
	error_log( print_r( $item_data, true ) );
	error_log( print_r( $item, true ) );
	error_log( print_r( $group_id, true ) );
	error_log( print_r( $field_id, true ) );
	error_log( print_r( $uploads, true ) );

	return $item_data;

}
add_filter( 'pewc_filter_cart_item_data', 'nv_cfi_ghent_pewc_add_cart_data', 10, 5 );