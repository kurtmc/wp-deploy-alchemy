<?php
//
// Recommended way to include parent theme styles.
//  (Please see http://codex.wordpress.org/Child_Themes#How_to_Create_a_Child_Theme)
//  


function footer_title() {
    echo '<div style="text-align: center; border-top: 1px solid #ddd; padding-top: 20px;"><h1>CONTACT US</h1></div>';
}
add_action( 'presscore_after_main_container', 'footer_title' );


function new_excerpt_more( $more ) {
	global $post;
	return '<a href="' . get_permalink( $post->ID ) . '"><strong>Read More!</strong></a>'; 
}
add_filter('excerpt_more', 'new_excerpt_more');



function shortcode_products( $atts ) {
    $a = shortcode_atts( array(
        'id' => '',
    ), $atts );
	$html = '';
	if( is_user_logged_in() ) {
		global $wpdb;
		$current_user = wp_get_current_user();
		$allowed_products_array = get_products_for_user( $current_user->user_email );
		$products_array = get_products( $allowed_products_array );
		$html .= '
			<div class="product-preview-container">
				<div class="product-preview-wrapper">
					<div class="product-preview-list">
						<div class="product-preview-list-wrapper">
							<strong>Product List</strong><br><br>
							<input id="product-filter-input" class="product-filter-input desktop-only" placeholder="Product ID / SKU" />
								<ul class="desktop-only">';
								for( $i = 0; $i < count( $products_array ); $i++ ) {
									$html .= '<li class="product-id-' . $products_array[$i]->id . '" data-product-id="' . $products_array[$i]->id . '" data-title="' . strtolower( $products_array[$i]->description ) . '"><a href="javascript:void(0);">' . $products_array[$i]->description . '</a></li>';
								}
		$html .= '
							</ul>
							<select class="mobile-only">
								<option value="-">Select Product</option>';
								for( $i = 0; $i < count( $products_array ); $i++ ) {
									$html .= '<option class="product-id-' . $products_array[$i]->id . '" data-product-id="' . $products_array[$i]->id . '" data-title="' . strtolower( $products_array[$i]->description ) . '"  value="' . $products_array[$i]->id . '">' . $products_array[$i]->description . '</option>';
								}
		$html .= '
							</select>
						</div>
					</div>
		';
		$html .= '<div class="product-preview-content"></div></div>';
		
	}
    return $html;
}
add_shortcode( 'products', 'shortcode_products' );



add_action( 'wp_ajax_get_product_item' , 'fo_ajax_get_product_item' );
add_action( 'wp_ajax_nopriv_get_product_item' , 'fo_ajax_get_product_item' );
function fo_ajax_get_product_item() {
	if( isset( $_POST['product_id'] ) && is_numeric( $_POST['product_id'] ) ) { $product_id = $_POST['product_id']; } else { $product_id = 0; }
	$html = '';
	if( is_user_logged_in() ) {
		$current_user = wp_get_current_user();
		if( $product_id > 0 ) {
			$allowed_products = get_products_for_user( $current_user->user_email );
			if( in_array( $product_id , $allowed_products ) ) {
				$products_array = get_products( array( $product_id ) );
				if( !empty( $products_array ) ) {
					foreach( $products_array as $product ) {
						$product_vendor = get_vendor( $product->vendor_id );
						$supplier_logo_file_name = $product_vendor->image_filename;
						if( $supplier_logo_file_name ) { 
							$supplier_logo_html = '<div class="product-supplier-logo"><img src="/wp-content/uploads/' . $supplier_logo_file_name . '" /></div>'; 
						} else { 
							$supplier_logo_html = ''; 
						}
						$html .= '
							<div class="product-preview-item product-item-' . $product->id . '">
								<div class="product-preview-item-wrapper">
									' . $supplier_logo_html . '
									<div class="product-title">' . $product->description . '</div>
									<div class="product-desc">'. $product->new_description . '</div>
									<div class="left-col">
										<div class="left-col-wrapper">
											<div class="product-fields">
												<div class="product-field">' . $product->description2 . '</div>
											</div>
										</div>
									</div>
									<div class="right-col">
										<div class="right-col-wrapper">';
											$server_folder_path = $_SERVER['DOCUMENT_ROOT'];
											$website_folder_path = '/wp-content/uploads/pdf_folders/';
											$product_folder_path = $product->directory;
											$product_files = scandir( $server_folder_path . $website_folder_path . $product_folder_path , 1  );
											for( $i = 0; $i < count( $product_files ); $i++ ) {
												if( $product_files[$i] != '.' && $product_files[$i] != '..' ) {
													$filename_title = $product_files[$i];
													$filename_prefix = substr( $product_files[$i] , 0 , 6 );
													switch( $filename_prefix ) {
														case 'SDS - ':
															$filename_title = 'Safety Document Sheet';
														break;
														case 'PDS - ':
															$filename_title = 'Product Document Sheet';
														break;
														default : break;
													}
													$html .= '<a target="_blank" class="dt-btn" href="' . $website_folder_path . $product_folder_path . '/' . $product_files[$i] . '">' . $filename_title . '</a>';
												}
											}
						$html .= '
										</div>
									</div>
								</div>
							</div>
						';	
					}
					$response_array = array(
					   'product_id' => $product_id,
					   'html' => $html,
					   'success'=> 1,
					);
				} else {
					$html = '<div class="product-error">No product result was found.</div>';
					$response_array = array(
					   'product_id' => $product_id,
					   'html' => $html,
					   'success'=> 0,
					);
				} 
				
			} else {
				$html = '<div class="product-error">No product result was found.</div>';
				$response_array = array(
				   'product_id' => $product_id,
				   'html' => $html,
				   'success'=> 0,
				);
			}
		} else {
			$html = '<div class="product-error">No product result was found.</div>';
			$response_array = array(
			   'product_id' => $product_id,
			   'html' => $html,
			   'success'=> 0,
			);
		}
		echo json_encode( $response_array );
	}
	die();
}
add_action( 'wp_footer' , 'insert_ajax_get_product_item_script' , 50 );
function insert_ajax_get_product_item_script() {
	echo '<script id="ajax-get-product-item-script" src="' . get_bloginfo( 'stylesheet_directory' ) . '/js/ajax-get-product-item.js"></script>';
}

function pre( $array ) { echo '<pre>'; print_r( $array ); echo '</pre>'; }

function get_vendor( $vendor_id ) {
	$vendor_json_data = json_decode( file_get_contents( $_SERVER['DOCUMENT_ROOT'] . '/local-json/vendors.json' ) );
	$vendor_array = NULL;
	foreach( $vendor_json_data as $vendor ) {
		if( (int)$vendor->id == (int)$vendor_id ) {
			$vendor_array = $vendor;
		}
	}
	return $vendor_array;
}

function get_products( $product_ids ) {
	$product_json_data = json_decode( file_get_contents( $_SERVER['DOCUMENT_ROOT'] . '/local-json/products.json' ) );
	$products_array = array();
	foreach( $product_json_data as $product ) {
		if( in_array( (int)$product->id , $product_ids ) ) {
			array_push( $products_array , $product );
		}
	}
	if( empty( $products_array ) ) { $products_array = NULL; }
	return $products_array;
}

function get_products_for_user( $email_address ) {
	//$user_product_relation_json_data = json_decode( file_get_contents( $_SERVER['DOCUMENT_ROOT'] . '/local-json/user_product_relation.json' ) );
	$user_product_relation_json_data = json_decode( file_get_contents( $_SERVER['DOCUMENT_ROOT'] . '/local-json/customer_users.json' ) );
	$allowerd_products_array = NULL;
	foreach( $user_product_relation_json_data as $user_product_relation ) {
		if( $user_product_relation->email == $email_address ) {
			$allowerd_products_array_temp = $user_product_relation->products;
			$allowerd_products_array = array();
			for( $i = 0; $i < count( $allowerd_products_array_temp ); $i++ ) {
				array_push( $allowerd_products_array , $allowerd_products_array_temp[$i]->id );
			}
		}
	}
	return $allowerd_products_array;
}

