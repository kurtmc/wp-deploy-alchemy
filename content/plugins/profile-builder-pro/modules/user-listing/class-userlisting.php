<?php
/**
 * User query based on the WordPress User Query class.
 *
 */
class PB_WP_User_Query {

	/**
	 * Query vars, after parsing
	 *
	 * @since 3.5.0
	 * @access public
	 * @var array
	 */
	var $query_vars = array();

	/**
	 * List of found user ids
	 *
	 * @since 3.1.0
	 * @access private
	 * @var array
	 */
	var $results;

	/**
	 * Total number of found users for the current query
	 *
	 * @since 3.1.0
	 * @access private
	 * @var int
	 */
	var $total_users = 0;

	/**
	 * PHP5 constructor
	 *
	 * @since 3.1.0
	 *
	 * @param string|array $args The query variables
	 * @return PB_WP_User_Query
	 */
	function __construct( $query = null ) {			
		if ( !empty( $query ) ) {

			$this->query_vars = wp_parse_args( $query, array(
				'blog_id'						=> $GLOBALS['blog_id'],
				'role'							=> '*',  				//can be either array or a string
				'meta_key' 						=> '',					//the meta-key of the extra field to search after
				'meta_value'					=> '',					//the meta-value of the extra field to search after
				'meta_compare' 					=> '',					//how to compare found results
				'use_wildcard' 					=> false,				//use the % wildcard in the sql search or not
				'search' 						=> '',					//search the fields for a given value
				'search_only_default_fields'	=> false,				//search the default fields only to improve search time
				'offset'						=> '', 					//where to start the LIMIT (sql) from
				'sorting_criteria'				=> 'login',			//the final sorting criteria in the listing
				'sorting_order'					=> 'ASC',				//the final sorting order in the listing
				'results_per_page'				=> '10'		//number of rows per page
			) );

			// Change where query string for included and excluded ids
            add_filter( 'wppb_fn0_query_where_filter', array( $this, 'include_exclude' ), 10, 2);

            // Change order by query string for included ids
            add_filter( 'wppb_fn0_query_orderby_filter', array( $this, 'order_by_include_values' ), 10, 2 );

            if( $this->query_vars['sorting_criteria'] == 'role' ) {
                add_filter( 'wppb_fn0_query_fields_filter', array( $this, 'order_by_role_query_fields_filter' ), 10, 2);
                add_filter( 'wppb_select_by_sorting_order_and_criteria_filter_result', array( $this, 'order_by_role_filter_query_results'), 10, 2);
            }

            $this->set_server_constants();
			$this->select_sorting_order_criteria();
			$this->select_roles();
			$this->select_approved();
			$this->select_visible();
			$this->select_meta_field();
			$this->search_results();
			$this->intersect_results();

		}
	}
	
	/**
	 * Set server constants if need be
	 * 
	 * Function identificator: fn_sc1
	 *
	 */
	function set_server_constants(){
		global $wpdb;
		
		do_action_ref_array( 'wppb_pre_set_server_constants', array( &$this ) );

		$this->fn_sc1_run_const_1 = apply_filters('wppb_run_sql_big_select', true);
		
		if ($this->fn_sc1_run_const_1)
			$wpdb->get_results("SET SQL_BIG_SELECTS=1");

		do_action_ref_array( 'wppb_post_set_server_constants', array( &$this ) );
		
	}
	
	/**
	 * Get the users based on sorting criteria and order
	 * 
	 * Function identificator: fn0
	 *
	 */
	function select_sorting_order_criteria(){
		global $wpdb;
	
		$qv = &$this->query_vars;
		$this->fn0_query_results_array = array();
		
		$this->fn0_query_fields = "wppb_t1.ID";
		$this->fn0_query_from = "FROM $wpdb->users AS wppb_t1";
		
		if ( $qv['sorting_criteria'] == 'ID' )
			$criteria = 'wppb_t1.ID';
			
		elseif ( $qv['sorting_criteria'] == 'login' )
			$criteria = 'wppb_t1.user_login';
			
		elseif ( $qv['sorting_criteria'] == 'email' )
			$criteria = 'wppb_t1.user_email';
			
		elseif ( $qv['sorting_criteria'] == 'url' )
			$criteria = 'wppb_t1.user_url';
			
		elseif ( $qv['sorting_criteria'] == 'registered' )
			$criteria = 'wppb_t1.user_registered';
			
		elseif ( $qv['sorting_criteria'] == 'nicename' )
			$criteria = 'wppb_t1.display_name';
			
		elseif ( $qv['sorting_criteria'] == 'post_count' ){
			$where = get_posts_by_author_sql('post');
			$this->fn0_query_from .= " LEFT OUTER JOIN (SELECT post_author, COUNT(*) AS post_count FROM $wpdb->posts $where GROUP BY post_author) p ON wppb_t1.ID = p.post_author";
			$criteria = 'wppb_t1.ID';
			
		}elseif ( $qv['sorting_criteria'] == 'bio' ){
			$this->fn0_query_from .= " LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = 'description'";
			$criteria = 'wppb_t2.meta_value';
			
		}elseif ( $qv['sorting_criteria'] == 'aim' ){
			$this->fn0_query_from .= " LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = 'aim'";
			$criteria = 'wppb_t2.meta_value';
			
		}elseif ( $qv['sorting_criteria'] == 'yim' ){
			$this->fn0_query_from .= " LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = 'yim'";
			$criteria = 'wppb_t2.meta_value';
			
		}elseif ( $qv['sorting_criteria'] == 'jabber' ){
			$this->fn0_query_from .= " LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = 'jabber'";
			$criteria = 'wppb_t2.meta_value';
			
		}elseif ( $qv['sorting_criteria'] == 'firstname' ){
			$this->fn0_query_from .= " LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = 'first_name'";
			$criteria = 'wppb_t2.meta_value';
			
		}elseif ( $qv['sorting_criteria'] == 'lastname' ) {
            $this->fn0_query_from .= " LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = 'last_name'";
            $criteria = 'wppb_t2.meta_value';

        }elseif ( $qv['sorting_criteria'] == 'nickname' ) {
            $this->fn0_query_from .= " LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = 'nickname'";
            $criteria = 'wppb_t2.meta_value';

        }elseif ( $qv['sorting_criteria'] == 'role' ) {
            $this->fn0_query_from .= " LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = 'wp_capabilities'";
            $criteria = 'wppb_t1.user_login';

		}elseif ( $qv['sorting_criteria'] == 'RAND()'){
			$criteria = 'RAND()';
		} else {
			$wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );

			if ( $wppb_manage_fields != 'not_found' ){
				foreach( $wppb_manage_fields as $key => $value ){
					if ( $qv['sorting_criteria'] == $value['meta-name'] ){
						$this->fn0_query_from .= $wpdb->prepare( " LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = %s", $value['meta-name'] );
						$criteria = 'wppb_t2.meta_value';
					}
				}
			}
		}

		$this->fn0_query_where = "WHERE 1";
		if ( strtoupper( trim( $qv['sorting_order'] ) ) === 'ASC' )
			$this->fn0_query_orderby = "ORDER BY ".$criteria." ASC";
		
		elseif ( strtoupper( trim( $qv['sorting_order'] ) ) === 'DESC' )
			$this->fn0_query_orderby = "ORDER BY ".$criteria." DESC";
			
		$this->fn0_query_limit = "";
		
		do_action_ref_array( 'wppb_pre_select_by_sorting_order_and_criteria', array( &$this ) );
		
		$this->fn0_query_fields = apply_filters( 'wppb_fn0_query_fields_filter', $this->fn0_query_fields, $qv );
		$this->fn0_query_from = apply_filters( 'wppb_fn0_query_from_filter', $this->fn0_query_from, $qv );
		$this->fn0_query_where = apply_filters( 'wppb_fn0_query_where_filter', $this->fn0_query_where, $qv );
		$this->fn0_query_orderby = apply_filters( 'wppb_fn0_query_orderby_filter', $this->fn0_query_orderby, $qv );
		$this->fn0_query_limit = apply_filters( 'wppb_fn0_query_limit_filter', $this->fn0_query_limit, $qv );

		$this->fn0_query_results = apply_filters( 'wppb_select_by_sorting_order_and_criteria_result', $wpdb->get_results( trim( "SELECT $this->fn0_query_fields $this->fn0_query_from $this->fn0_query_where $this->fn0_query_orderby $this->fn0_query_limit" ) ) );

        $this->fn0_query_results = apply_filters( 'wppb_select_by_sorting_order_and_criteria_filter_result', $this->fn0_query_results, $this->query_vars );

		//create an array with IDs from result
		foreach ( $this->fn0_query_results as $qr_key => $qr_value )
			array_push( $this->fn0_query_results_array, $qr_value->ID );
			
		$this->fn0_query_results_array = apply_filters( 'wppb_select_by_sorting_order_and_criteria_array', $this->fn0_query_results_array );

		do_action_ref_array( 'wppb_post_select_by_sorting_order_and_criteria', array( &$this ) );
	}	
	

	/**
	 * Get the users with a specific role
	 * 
	 * Function identificator: fn1
	 *
	 */
	function select_roles(){
		global $wpdb;
	
		$qv = &$this->query_vars;
		$this->fn1_query_results_array = array();
	
		$this->fn1_query_fields = "wppb_t1.ID";

		if ( is_string( $qv['role'] ) && ( trim( $qv['role'] ) != '*' ) ){
			$qv['role'] = explode( ',', trim( $qv['role'] ) );
		}
		
		if ( ( count($qv['role'] ) > 0 ) && ( $qv['role'][0] != '*' ) ){
			$this->fn1_query_from = "FROM  $wpdb->users AS wppb_t1 LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = '".$wpdb->prefix."capabilities'";
			
			$this->fn1_query_where = "WHERE (";
			foreach ( $qv['role'] as $thisKey => $thisValue ){
				$this->fn1_query_where .= $wpdb->prepare( "wppb_t2.meta_value LIKE %s", '%"' .  $this->wppb_esc_like( trim( $thisValue ) ) . '"%' );
				if ($thisKey < count($qv['role'])-1)
					$this->fn1_query_where .= " OR ";
			}
			$this->fn1_query_where .= ")";
			
		}else{
			$this->fn1_query_from = "FROM  $wpdb->users AS wppb_t1";
			$this->fn1_query_where = "WHERE 1";
		}
			
		$this->fn1_query_orderby = "ORDER BY wppb_t1.ID ASC";
		$this->fn1_query_limit = "";
		
		do_action_ref_array( 'wppb_pre_user_role_select_query', array( &$this ) );
	
		$this->fn1_query_fields = apply_filters( 'wppb_fn1_query_fields_filter', $this->fn1_query_fields, $qv );
		$this->fn1_query_from = apply_filters( 'wppb_fn1_query_from_filter', $this->fn1_query_from, $qv );
		$this->fn1_query_where = apply_filters( 'wppb_fn1_query_where_filter', $this->fn1_query_where, $qv );
		$this->fn1_query_orderby = apply_filters( 'wppb_fn1_query_orderby_filter', $this->fn1_query_orderby, $qv );
		$this->fn1_query_limit = apply_filters( 'wppb_fn1_query_limit_filter', $this->fn1_query_limit, $qv );
	
		$this->fn1_query_results = apply_filters( 'wppb_user_role_select_query_result', $wpdb->get_results( trim( "SELECT $this->fn1_query_fields $this->fn1_query_from $this->fn1_query_where $this->fn1_query_orderby $this->fn1_query_limit" ) ) );
		
		//create an array with IDs from result 
		foreach ( $this->fn1_query_results as $qr_key => $qr_value )
			array_push( $this->fn1_query_results_array, $qr_value->ID );

		$this->fn1_query_results_array = apply_filters('wppb_user_role_select_query_result_array', $this->fn1_query_results_array);

		do_action_ref_array( 'wppb_post_user_role_select_query', array( &$this ) );
	}	
	
	
	/**
	 * Get only the approved users
	 * 
	 * Function identificator: fn2
	 *
	 */
	function select_approved(){
		global $wpdb;	
		
		$this->fn2_query_results_array = array();
		$this->fn2_found_unapproved = false;
		
		$wppb_generalSettings = get_option( 'wppb_general_settings' );
		
		if( isset( $wppb_generalSettings['adminApproval'] ) && ( $wppb_generalSettings['adminApproval'] == 'yes' ) ){
			$arrayID = array();
		
			// Get term by name ''unapproved'' in user_status taxonomy.
			$user_statusTaxID = get_term_by( 'name', 'unapproved', 'user_status' );
            if( $user_statusTaxID != false ){
                $term_taxonomy_id = $user_statusTaxID->term_taxonomy_id;

                $results = $wpdb->get_results( $wpdb->prepare( "SELECT wppb_t1.ID FROM $wpdb->users AS wppb_t1 LEFT OUTER JOIN $wpdb->term_relationships AS wppb_t0 ON wppb_t1.ID = wppb_t0.object_id WHERE wppb_t0.term_taxonomy_id = %d", $term_taxonomy_id ) );

                foreach ( $results as $result )
                    array_push( $arrayID, $result->ID );

                $arrayID = implode( ',', $arrayID );

                //now exclude certain users
                if ( $arrayID !== '' ){
                    $this->fn2_found_unapproved = true;

                    $this->fn2_query_results_array = $this->select_users_not_in_array( $arrayID, 'fn2' );
                    $this->fn2_query_results_array = apply_filters( 'wppb_approved_users_select_query_result_array', $this->fn2_query_results_array );

                    do_action_ref_array( 'wppb_post_approved_users_select_query1', array( &$this ) );
                }
            }
		}
	}	
	
	
	/**
	 * Get only the users who want their profiles shown; this function eliminates those who hmustache_variable_user_metave selected "no" to list their profile
	 * 
	 * Function identificator: fn3
	 *
	 */
	function select_visible(){
		global $wpdb;
		
		$qv = &$this->query_vars;
	
		$this->fn3_query_results_array = array();
		$this->fn3_found_matching_hiden_users = false;
	
		$extraField_meta_key = apply_filters( 'wppb_display_profile_meta_field_name', '' );	//meta-name of the extra-field which checks if the user wants his profile hidden
		$extraField_meta_value = apply_filters( 'wppb_display_profile_meta_field_value', '' );	//the value of the above parameter; the users with these 2 combinations will be excluded
		
		if ( ( trim( $extraField_meta_key ) != '' ) && ( trim( $extraField_meta_value ) != '' ) ){
			$arrayID = array();
			
			$results = $wpdb->get_results( $wpdb->prepare( "SELECT wppb_t1.ID FROM $wpdb->users AS wppb_t1 LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = %s WHERE wppb_t2.meta_value LIKE %s ORDER BY wppb_t1.ID", $extraField_meta_key, '%' .  $this->wppb_esc_like( trim( $extraField_meta_value ) ) . '%' ) );
			
			foreach ( $results as $result )
				array_push( $arrayID, $result->ID );

			$arrayID = implode( ',', $arrayID );
			
			//now exclude certain users
			if ( $arrayID !== '' ){
				$this->fn3_found_matching_hiden_users = true;			
				
				$this->fn3_query_results_array = $this->select_users_not_in_array( $arrayID, 'fn3' );
				$this->fn3_query_results_array = apply_filters( 'wppb_display_profile_select_query_result_array1', $this->fn3_query_results_array );

				do_action_ref_array( 'wppb_post_display_profile_select_query1', array( &$this ) );
			}
		
			
		}else{
			$this->fn3_query_fields = "wppb_t1.ID";
			$this->fn3_query_from = "FROM $wpdb->users AS wppb_t1";
			$this->fn3_query_where = "WHERE 1";
			$this->fn3_query_orderby = "ORDER BY wppb_t1.ID ASC";
			$this->fn3_query_limit = "";

			do_action_ref_array( 'wppb_pre_display_profile_select_query2', array( &$this ) );
		
			$this->fn3_query_fields = apply_filters( 'wppb_fn3_query_fields_filter2', $this->fn3_query_fields, $qv );
			$this->fn3_query_from = apply_filters( 'wppb_fn3_query_from_filter2', $this->fn3_query_from, $qv );
			$this->fn3_query_where = apply_filters( 'wppb_fn3_query_where_filter2', $this->fn3_query_where, $qv );
			$this->fn3_query_orderby = apply_filters( 'wppb_fn3_query_orderby_filter2', $this->fn3_query_orderby, $qv );
			$this->fn3_query_limit = apply_filters( 'wppb_fn3_query_limit_filter2', $this->fn3_query_limit, $qv );
		
			$this->fn3_query_results = apply_filters( 'wppb_display_profile_select_query_result2', $wpdb->get_results( trim( "SELECT $this->fn3_query_fields $this->fn3_query_from $this->fn3_query_where $this->fn3_query_orderby $this->fn3_query_limit" ) ) );
			
			//create an array with IDs from result 
			foreach ( $this->fn3_query_results as $qr_key => $qr_value )
				array_push($this->fn3_query_results_array, $qr_value->ID);
			
			$this->fn3_query_results_array = apply_filters('wppb_display_profile_select_query_result_array2', $this->fn3_query_results_array);

			do_action_ref_array( 'wppb_post_display_profile_select_query2', array( &$this ) );
		}
	}	
	
	
	/**
	 * Get only the users who have a certain meta_field and meta_value combination
	 * 
	 * Function identificator: fn4
	 *
	 */
	function select_meta_field(){
		global $wpdb;
	
		$qv = &$this->query_vars;
		$this->fn4_query_results_array = array();
	
		$meta_key = apply_filters( 'wppb_select_meta_field_key', $qv['meta_key'] );
		$meta_value = apply_filters( 'wppb_select_meta_field_value', $qv['meta_value'] );
		$meta_compare = apply_filters( 'wppb_select_meta_field_compare', $qv['meta_compare'] );
		$use_wildcard = apply_filters( 'wppb_select_meta_field_wildcard', $qv['use_wildcard'] );

		if ( ( trim( $meta_key ) != '' ) && ( trim( $meta_value ) != '' ) && ( trim( $meta_compare ) != '' ) ){
			$card = ( ( $use_wildcard ) ? '%' : '' );
		
			$this->fn4_query_fields = "wppb_t1.ID";
			$this->fn4_query_from = $wpdb->prepare( "FROM $wpdb->users AS wppb_t1 LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = %s", $meta_key );
			$this->fn4_query_where = $wpdb->prepare( "WHERE wppb_t2.meta_value " . $meta_compare . " %s", $card.$this->wppb_esc_like( trim( $meta_value ) ).$card );
			$this->fn4_query_orderby = "ORDER BY wppb_t1.ID ASC";
			$this->fn4_query_limit = "";

			do_action_ref_array( 'wppb_pre_custom_meta_select_query1', array( &$this ) );
		
			$this->fn4_query_fields = apply_filters( 'wppb_fn4_query_fields_filter', $this->fn4_query_fields, $qv );
			$this->fn4_query_from = apply_filters( 'wppb_fn4_query_from_filter', $this->fn4_query_from, $qv, $meta_key, $meta_value, $meta_compare, $card );
			$this->fn4_query_where = apply_filters( 'wppb_fn4_query_where_filter', $this->fn4_query_where, $qv, $meta_key, $meta_value, $meta_compare, $card );
			$this->fn4_query_orderby = apply_filters( 'wppb_fn4_query_orderby_filter', $this->fn4_query_orderby, $qv );
			$this->fn4_query_limit = apply_filters( 'wppb_fn4_query_limit_filter', $this->fn4_query_limit, $qv );
		
			$this->fn4_query_results = apply_filters( 'wppb_custom_meta_select_query_result1', $wpdb->get_results( trim( "SELECT $this->fn4_query_fields $this->fn4_query_from $this->fn4_query_where $this->fn4_query_orderby $this->fn4_query_limit" ) ) );

			//create an array with IDs from result
			foreach ( $this->fn4_query_results as $qr_key => $qr_value )
				array_push( $this->fn4_query_results_array, $qr_value->ID );

			$this->fn4_query_results_array = apply_filters('wppb_custom_meta_select_query_result_array1', $this->fn4_query_results_array);

			do_action_ref_array( 'wppb_post_custom_meta_select_query1', array( &$this ) );

		}else{
			$this->fn4_query_fields = "wppb_t1.ID";
			$this->fn4_query_from = "FROM $wpdb->users AS wppb_t1";
			$this->fn4_query_where = "WHERE 1";
			$this->fn4_query_orderby = "ORDER BY wppb_t1.ID ASC";
			$this->fn4_query_limit = "";

			do_action_ref_array( 'wppb_pre_custom_meta_select_query2', array( &$this ) );
		
			$this->fn4_query_fields = apply_filters( 'wppb_fn4_query_fields_filter2', $this->fn4_query_fields, $qv );
			$this->fn4_query_from = apply_filters( 'wppb_fn4_query_from_filter2', $this->fn4_query_from, $qv );
			$this->fn4_query_where = apply_filters( 'wppb_fn4_query_where_filter2', $this->fn4_query_where, $qv );
			$this->fn4_query_orderby = apply_filters( 'wppb_fn4_query_orderby_filter2', $this->fn4_query_orderby, $qv );
			$this->fn4_query_limit = apply_filters( 'wppb_fn4_query_limit_filter2', $this->fn4_query_limit, $qv );
		
			$this->fn4_query_results = apply_filters( 'wppb_custom_meta_select_query_result2', $wpdb->get_results( trim( "SELECT $this->fn4_query_fields $this->fn4_query_from $this->fn4_query_where $this->fn4_query_orderby $this->fn4_query_limit" ) ) );
			
			//create an array with IDs from result
			foreach ( $this->fn4_query_results as $qr_key => $qr_value )
				array_push( $this->fn4_query_results_array, $qr_value->ID );
				
			$this->fn4_query_results_array = apply_filters( 'wppb_custom_meta_select_query_result_array2', $this->fn4_query_results_array );

			do_action_ref_array( 'wppb_post_custom_meta_select_query2', array( &$this ) );
		}
	}
	
	/**
	 * Get the results if a search has been requested
	 * 
	 * Function identificator: fn5
	 *
	 */
	function search_results(){
		global $wpdb;
	
		$qv = &$this->query_vars;
		$this->fn5_query_results_array = array();
		$this->fn5_search_requested = false;
		
		$searchText = apply_filters( 'wppb_userlisting_search_field_text', __( 'Search Users by All Fields', 'profile-builder' ) );
		
		//only search the fields if the entered search-string differs from the default one
		if ( ( trim( $qv['search'] ) !== $searchText ) && ( trim( $qv['search'] ) !== '' ) ){

			$esc_search = $this->wppb_esc_like( trim( $qv['search'] ) );
			$wppb_exclue_search_fields = apply_filters('wppb_exclude_search_fields', array());

			$default_fields = array('user_login', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'display_name');
			$meta_keys = array();

			$this->fn5_search_requested = true;
			$wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
			if ( $wppb_manage_fields != 'not_found' ) {
				foreach( $wppb_manage_fields as $key => $value ){
					if( !empty( $value['meta-name'] ) ){
						$meta_keys[] = $value['meta-name'];
					}
				}
			}

			$this->fn5_query_fields = "wppb_t1.ID";
			$this->fn5_query_from = "FROM  {$wpdb->users} AS wppb_t1 ";
			$this->fn5_query_where = "WHERE ";

			// Construct the WHERE query for fields from wp_users table.
			$first_where = true;
			foreach($default_fields as $value){
				if ( !in_array($value, $wppb_exclue_search_fields) ) {
					if ( $first_where ){
						$this->fn5_query_where .=  $wpdb->prepare( "wppb_t1.{$value} LIKE %s ", '%'.$esc_search.'%' );
						$first_where = false;
					} else {
						$this->fn5_query_where .= $wpdb->prepare( "OR wppb_t1.{$value} LIKE %s ", '%'.$esc_search.'%' ) ;
					}
				}
			}

			// We need at least a default to search in.
			if( $this->fn5_query_where == "WHERE " ){
				$this->fn5_query_where = $wpdb->prepare( "WHERE wppb_t1.user_login LIKE %s", $esc_search );
			}

			// Construct the WHERE and FROM queries from the wp_usermeta table.
			$i = 1;
			foreach($meta_keys as $value){
				if ( !in_array($value, $wppb_exclue_search_fields) ){
					$i++;

					//set the FROM condition for the custom fields
					$this->fn5_query_from .= " ";
					$this->fn5_query_from .= $wpdb->prepare( "LEFT OUTER JOIN $wpdb->usermeta AS wppb_t".$i." ON wppb_t1.ID = wppb_t".$i.".user_id AND wppb_t".$i.".meta_key = %s", $value );

					//add WHERE conditions for the custom fields
					$this->fn5_query_where .= " ";
					$this->fn5_query_where .= $wpdb->prepare( "OR wppb_t".$i.".meta_value LIKE %s", '%'.$esc_search.'%' );
				}
			}

			$this->fn5_query_orderby = "ORDER BY wppb_t1.ID ASC";
			$this->fn5_query_limit = "";

			do_action_ref_array( 'wppb_pre_search_select_query', array( &$this ) );
		
			$this->fn5_query_fields = apply_filters( 'wppb_fn5_query_fields_filter', $this->fn5_query_fields, $qv );
			$this->fn5_query_from = apply_filters( 'wppb_fn5_query_from_filter', $this->fn5_query_from, $qv );
			$this->fn5_query_where = apply_filters( 'wppb_fn5_query_where_filter', $this->fn5_query_where, $qv );
			$this->fn5_query_orderby = apply_filters( 'wppb_fn5_query_orderby_filter', $this->fn5_query_orderby, $qv );
			$this->fn5_query_limit = apply_filters( 'wppb_fn5_query_limit_filter', $this->fn5_query_limit, $qv );
		
			$this->fn5_query_results = apply_filters( 'wppb_search_select_query', $wpdb->get_results( trim( "SELECT $this->fn5_query_fields $this->fn5_query_from $this->fn5_query_where $this->fn5_query_orderby $this->fn5_query_limit" ) ) );
			
			//create an array with IDs from result
			foreach ( $this->fn5_query_results as $qr_key => $qr_value )
				array_push($this->fn5_query_results_array, $qr_value->ID);
			
			$this->fn5_query_results_array = apply_filters( 'wppb_search_select_query_result_array', $this->fn5_query_results_array );

			do_action_ref_array( 'wppb_post_search_select_query', array( &$this ) );
		}
	}
	 
		
	/**
	 * Get the results from the above 6 functions into one result (intersect results)
	 * 
	 * Function identificator: fn6
	 *
	 */
	function intersect_results(){
		
		$qv = &$this->query_vars;
	
		$this->fn6_query_results_intersected = apply_filters( 'wppb_array_intersect_results', array_intersect( $this->fn0_query_results_array, $this->fn1_query_results_array, $this->fn4_query_results_array ) );
		
		$wppb_generalSettings = get_option( 'wppb_general_settings' );
		
		if ( ( isset( $wppb_generalSettings['adminApproval'] ) && ( $wppb_generalSettings['adminApproval'] == 'yes' ) ) && ( isset($this->fn2_found_unapproved ) && $this->fn2_found_unapproved ) )
			$this->fn6_query_results_intersected = apply_filters('wppb_array_intersect_results_with_admin_approval', array_intersect($this->fn0_query_results_array, $this->fn1_query_results_array, $this->fn2_query_results_array, $this->fn3_query_results_array, $this->fn4_query_results_array));
			
		if ( isset( $this->fn3_found_matching_hiden_users ) && $this->fn3_found_matching_hiden_users )
			$this->fn6_query_results_intersected = apply_filters('wppb_array_intersect_results_with_hidden_users', array_intersect($this->fn6_query_results_intersected, $this->fn3_query_results_array));
		
		if ( isset( $this->fn5_search_requested ) && $this->fn5_search_requested )
			$this->fn6_query_results_intersected = apply_filters('wppb_array_intersect_results_wtih_search', array_intersect($this->fn6_query_results_intersected, $this->fn5_query_results_array));
	}

    /*
     * Function that adds the meta_value field of the wp_usermeta table
     * to the current added query fields string
     *
     * @since v.2.0.8
     *
     * @param string $query_fields      - current query fields
     * @param array $qv                 - the query variables
     */
    function order_by_role_query_fields_filter( $query_fields, $qv ) {
        return $query_fields . ", wppb_t2.meta_value";
    }

    /*
     * Function that orders by role the returned query results that consists of an array of objects
     *
     * @since v.2.0.8
     *
     * @param array $query_results      - returned array of objects
     * @param array $qv                 - the query variables
     */
    function order_by_role_filter_query_results( $query_results, $qv ) {

        foreach( $query_results as $key => $value ) {
            $query_results[$key] = $value;
            $query_results[$key]->role = unserialize($value->meta_value);
            $query_results[$key]->role = key( $query_results[$key]->role );

            unset( $query_results[$key]->meta_value );
        }

        if( $qv['sorting_order'] == 'asc' ) {
            usort( $query_results, array( &$this, 'order_by_role_asc' ) );
        } elseif ( $qv['sorting_order'] == 'desc' ) {
            usort( $query_results, array( &$this, 'order_by_role_desc' ) );
        }

        return $query_results;
    }

    //Sort function ascending order
    static function order_by_role_asc( $a, $b ) {
        return strcmp($a->role, $b->role);
    }

    //Sort function descending order
    static function order_by_role_desc( $a, $b ) {
        return strcmp($b->role, $a->role);
    }


    /*
     * Function that we add as a filter for the first database query in order to
     * include or exclude certain IDs from the listing
     *
     * @param string $query_where       The query string so far
     * @param array $gv                 The query var
     *
     * @return string
     */
    function include_exclude( $query_where, $qv ) {

        if( !empty( $qv['include'] ) ) {
            $ids = implode( ',', wp_parse_id_list( $qv['include'] ) );
            $query_where .= " AND wppb_t1.ID IN ($ids)";
        } elseif ( !empty( $qv['exclude'] ) ) {
            $ids = implode( ',', wp_parse_id_list( $qv['exclude'] ) );
            $query_where .= " AND wppb_t1.ID NOT IN ($ids)";
        }

        return $query_where;
    }


    /*
     * Function that changes the orderby query string so that the rows returned are in the same
     * order as the ids from the include parameter
     *
     * @param string $query_orderby         The query string so far
     * @param array $gv                     The query var
     *
     * @return string
     */
    function order_by_include_values( $query_orderby, $qv ) {

        if( isset( $qv['include'] ) && !empty( $qv['include'] ) ) {
            $include_ids_string = implode( ',', wp_parse_id_list( $qv['include'] ) );
            $query_orderby = 'ORDER BY FIELD( wppb_t1.ID, ' . $include_ids_string . ')';
        }

        return $query_orderby;
    }

	
	/**
	 * Return the total number of users for the current query
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @return array
	 */
	function get_total() {
		$qv = &$this->query_vars;
		return apply_filters( 'wppb_found_total_users', count ( $this->fn6_query_results_intersected ), $qv );
	}
	
	
	/**
	 * Return the list of users
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @return array
	 */
	function get_results() {
		
		$result = array();
		$qv = &$this->query_vars;
		$nrOfUsers = $iterator = -1;
		
		if ( !empty( $this->fn6_query_results_intersected ) )
			foreach ( $this->fn6_query_results_intersected as $userRes => $userID ){
				$iterator++;
				
				if ( $iterator >= $qv['offset'] ){	
					$nrOfUsers++;
				
					if ( $nrOfUsers < $qv['results_per_page'] )
						$result[$userRes] = new WP_User( $userID, '', $qv['blog_id'] );
				}
			}

		return apply_filters( 'wppb_get_results', $result, $qv );
	}
	
	/**
	 * Select users that are not in array
	 *
	 * @since 3.1.0
	 * @access public
	 *
	 * @return array
	 */
	function select_users_not_in_array( $arrayID, $identifier ){
		global $wpdb;
		
		$qv = &$this->query_vars;		
		$query_results_array = array();		
		
		if( $identifier == 'fn2' )
			$action_name = 'wppb_pre_approved_users_select_query1';
		else if( $identifier == 'fn3' )
			$action_name = 'wppb_pre_display_profile_select_query1';
		
		do_action_ref_array( $action_name, array( &$this ) );
	
		$query_fields = apply_filters( 'wppb_'. $identifier .'_query_fields_filter', "wppb_t1.ID", $qv );
		$query_from = apply_filters( 'wppb_'. $identifier .'_query_from_filter', "FROM $wpdb->users AS wppb_t1", $qv );
		$query_where = apply_filters( 'wppb_'. $identifier .'_query_where_filter', "WHERE wppb_t1.ID NOT IN ($arrayID)", $qv, $arrayID );
		$query_orderby = apply_filters( 'wppb_'. $identifier .'_query_orderby_filter', "ORDER BY wppb_t1.ID ASC", $qv );
		$query_limit = apply_filters( 'wppb_'. $identifier .'_query_limit_filter', "", $qv );
	
		$query_results = apply_filters( 'wppb_approved_users_select_query_result_array', $wpdb->get_results( trim( "SELECT $query_fields $query_from $query_where $query_orderby $query_limit" ) ) );
		
		//create an array with IDs from result 
		foreach ( $query_results as $qr_key => $qr_value )
			array_push( $query_results_array, $qr_value->ID );
			
		return $query_results_array;
	}


    public static function wppb_esc_like( $string ){
        global $wpdb;
        if( method_exists( $wpdb, 'esc_like') )
            return $wpdb->esc_like(  $string );
        else
            return like_escape( $string );
    }
}