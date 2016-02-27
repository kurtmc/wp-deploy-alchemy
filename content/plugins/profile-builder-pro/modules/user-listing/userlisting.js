jQuery( function() {
    jQuery('.wppb-table .wppb-sorting').each( function() {
        $sortLink = jQuery(this).children('.sortLink');

        if( $sortLink.length > 0 ) {
            jQuery(this).append('<span class="wppb-sorting-default"></span>');

            if( $sortLink.hasClass('sort-asc') ) {
                $sortLink.siblings('.wppb-sorting-default').addClass('wppb-sorting-ascending');
            }

            if( $sortLink.hasClass('sort-desc') ) {
                $sortLink.siblings('.wppb-sorting-default').addClass('wppb-sorting-descending');
            }
        }
    });
});