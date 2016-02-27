jQuery( window ).load( function() {  
	"use strict";
	function get_product_item( product_id ) {
		jQuery( ".product-preview-content" ).html( "" );
		jQuery( ".product-preview-content" ).addClass( "product-preview-loading" );
		var ajaxurl = "/wordpress/wp-admin/admin-ajax.php";
		jQuery.post(
			ajaxurl, 
			{
				"action": "get_product_item",
				"product_id": product_id
			}, 
			function( response ){
				var response_array = jQuery.parseJSON( response );
				console.log( response_array );
				jQuery( ".product-preview-content" ).removeClass( "product-preview-loading" );
				jQuery( ".product-preview-content" ).html( response_array.html );
			}
		);
	}
	jQuery( ".product-preview-list ul > li > a" ).click( function() {
		var product_id = jQuery( this ).parent().attr( "data-product-id" );
		console.log( product_id );
		get_product_item( product_id );
	} );
	jQuery( ".product-preview-list select" ).change( function() {
		var product_id = jQuery( this ).val();
		console.log( product_id );
		get_product_item( product_id );
	} );
	jQuery( "#product-filter-input" ).on( 'input ', function() {
		var search_term = jQuery( this ).val();
		search_term = search_term.toLowerCase();
		console.log( search_term );
		if( search_term !=="" ) {
			jQuery( ".product-preview-list ul li" ).hide();
			jQuery( ".product-preview-list ul [data-product-id*="+search_term+"]" ).show();
			jQuery( ".product-preview-list ul [data-title*="+search_term+"]" ).show();
		} else {
			jQuery( ".product-preview-list ul li" ).show();
		}
	} ); 
} );
