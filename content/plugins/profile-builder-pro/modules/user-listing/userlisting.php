<?php
/**
 * Function that creates the "Userlisting" custom post type
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_create_userlisting_forms_cpt(){
    $labels = array(
        'name' 					=> __( 'User Listing', 'profile-builder'),
        'singular_name' 		=> __( 'User Listing', 'profile-builder'),
        'add_new' 				=> __( 'Add New', 'profile-builder'),
        'add_new_item' 			=> __( 'Add new User Listing', 'profile-builder' ),
        'edit_item' 			=> __( 'Edit the User Listing', 'profile-builder' ) ,
        'new_item' 				=> __( 'New User Listing', 'profile-builder' ),
        'all_items' 			=> __( 'User Listing', 'profile-builder' ),
        'view_item' 			=> __( 'View the User Listing', 'profile-builder' ),
        'search_items' 			=> __( 'Search the User Listing', 'profile-builder' ),
        'not_found' 			=> __( 'No User Listing found', 'profile-builder' ),
        'not_found_in_trash' 	=> __( 'No User Listing found in trash', 'profile-builder' ),
        'parent_item_colon' 	=> '',
        'menu_name' 			=> __( 'User Listing', 'profile-builder' )
    );

    $args = array(
        'labels' 				=> $labels,
        'public' 				=> false,
        'publicly_queryable' 	=> false,
        'show_ui' 				=> true,
        'query_var'          	=> true,
        'show_in_menu' 			=> 'profile-builder',
        'has_archive' 			=> false,
        'hierarchical' 			=> false,
        'capability_type' 		=> 'post',
        'supports' 				=> array( 'title' )
    );

    $wppb_addonOptions = get_option('wppb_module_settings');
    if( $wppb_addonOptions['wppb_userListing'] == 'show' )
        register_post_type( 'wppb-ul-cpt', $args );
}
add_action( 'init', 'wppb_create_userlisting_forms_cpt');

/* Userlisting change classes based on Visible only to logged in users field start */
add_filter( 'wck_add_form_class_wppb_ul_page_settings', 'wppb_userlisting_add_form_change_class_based_on_visible_field', 10, 3 );
function wppb_userlisting_add_form_change_class_based_on_visible_field( $wck_update_container_css_class, $meta, $results ){
    if( !empty( $results ) ){
        if (!empty($results[0]["visible-only-to-logged-in-users"]))
            $votliu_val = $results[0]["visible-only-to-logged-in-users"];
        else
            $votliu_val = '';
        $votliu = Wordpress_Creation_Kit_PB::wck_generate_slug($votliu_val);
        return "update_container_$meta update_container_$votliu visible_to_logged_$votliu";
    }
}
/* Userlisting change classes based on Visible only to logged in users field end */


function wppb_userlisting_scripts() {
    wp_enqueue_script( 'wppb-userlisting-js', WPPB_PLUGIN_URL . '/modules/user-listing/userlisting.js', array('jquery'), PROFILE_BUILDER_VERSION, true );
}
add_action( 'wp_footer', 'wppb_userlisting_scripts' );

/**
 * Function that generates the merge tags for userlisting
 *
 * @since v.2.0
 *	
 * @param string $type The type of merge tags which we want to generate. It can be meta or sort, meaning the actual data or the links with which we can sort the data
 * @return array $merge_tags the array of merge tags and their details
 */
function wppb_generate_userlisting_merge_tags( $type ){
	$wppb_manage_fields = apply_filters('wppb_userlisting_merge_tags' , get_option( 'wppb_manage_fields', 'not_found' ));
	$merge_tags = array();
	
	if( $type == 'meta' ){
		$default_field_type = 'default_user_field';
		$user_meta = 'user_meta';
		$number_of_posts = 'number_of_posts';
	}
	else if( $type == 'sort' ){
		$default_field_type = $user_meta = $number_of_posts = 'sort_tag';
	}
	
	if ( $wppb_manage_fields != 'not_found' )
		foreach( $wppb_manage_fields as $key => $value ){
			if ( ( $value['field'] == 'Default - Name (Heading)' ) || ( $value['field'] == 'Default - Contact Info (Heading)' ) || ( $value['field'] == 'Default - About Yourself (Heading)' ) || ( $value['field'] == 'Heading' ) || ( $value['field'] == 'Default - Password' ) || ( $value['field'] == 'Default - Repeat Password' ) || ( $value['field'] == 'Select (User Role)' ) ){
				//do nothing for the headers and the password fields
				
			}elseif ( $value['field'] == 'Default - Username' )
				$merge_tags[] = array( 'name' => $type.'_user_name', 'type' => $default_field_type, 'label' => __( 'Username', 'profile-builder' ) );
				
			elseif ( $value['field'] == 'Default - Display name publicly as' )
				$merge_tags[] = array( 'name' => $type.'_display_name', 'type' => $default_field_type, 'label' => __( 'Display name as', 'profile-builder' ) );
				
			elseif ( $value['field'] == 'Default - E-mail' )
				$merge_tags[] = array( 'name' => $type.'_email', 'type' => $default_field_type, 'label' => __( 'E-mail', 'profile-builder' ) );
				
			elseif ( $value['field'] == 'Default - Website' )
				$merge_tags[] = array( 'name' => $type.'_website', 'type' => $default_field_type, 'label' => __( 'Website', 'profile-builder' ) );

            elseif ( $value['field'] == 'Default - Biographical Info' )
                $merge_tags[] = array( 'name' => $type.'_biographical_info', 'type' => $default_field_type, 'unescaped' => true, 'label' => __( 'Biographical Info', 'profile-builder' ) );

            elseif ( $value['field'] == 'Upload' ){
				$merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'], 'type' => $user_meta, 'label' => $value['field-title'] );
			}
            elseif ( $value['field'] == 'Textarea' ){
                $merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'], 'type' => $user_meta, 'unescaped' => true, 'label' => $value['field-title'] );
            }
            elseif ( $value['field'] == 'WYSIWYG' ){
                if( $user_meta == 'user_meta' )
                    $wysiwyg_user_meta = 'user_meta_wysiwyg';
                else
                    $wysiwyg_user_meta = $user_meta;

                $merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'], 'type' => $wysiwyg_user_meta, 'unescaped' => true, 'label' => $value['field-title'] );
            }
            elseif( ( $value['field'] == 'Checkbox' || $value['field'] == 'Radio' || $value['field'] == 'Select' || $value['field'] == 'Select (Multiple)' ) && ( $type == 'meta' ) ){
                $merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'], 'type' => $user_meta, 'label' => $value['field-title'] );
                $merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'].'_labels', 'type' => $user_meta.'_labels', 'label' => $value['field-title']. ' Labels' );
            }
            else
				$merge_tags[] = array( 'name' => $type.'_'.$value['meta-name'], 'type' => $user_meta, 'label' => $value['field-title'] );
			
		}
	
	$merge_tags[] = array( 'name' => $type.'_role', 'type' => $default_field_type, 'label' => __( 'Role', 'profile-builder' ) );
	$merge_tags[] = array( 'name' => $type.'_registration_date', 'type' => $default_field_type, 'label' => __( 'Registration Date', 'profile-builder' ) );
	$merge_tags[] = array( 'name' => $type.'_number_of_posts', 'type' => $number_of_posts, 'unescaped' => true, 'label' => __( 'Number of Posts', 'profile-builder' ) );
	
	// we can't sort by this fields so only generate the meta
	if( $type == 'meta' ){
		$merge_tags[] = array( 'name' => 'more_info', 'type' => 'more_info', 'unescaped' => true, 'label' => __( 'More Info', 'profile-builder' ) );
		$merge_tags[] = array( 'name' => 'more_info_url', 'type' => 'more_info_url', 'unescaped' => true, 'label' => __( 'More Info Url', 'profile-builder' ) );
		$merge_tags[] = array( 'name' => 'avatar_or_gravatar', 'type' => 'avatar_or_gravatar', 'unescaped' => true, 'label' => __( 'Avatar or Gravatar', 'profile-builder' ) );
		$merge_tags[] = array( 'name' => 'user_id', 'type' => 'user_id', 'label' => __( 'User Id', 'profile-builder' ) );
		$merge_tags[] = array( 'name' => 'user_nicename', 'type' => 'user_nicename', 'unescaped' => true, 'label' => __( 'User Nicename', 'profile-builder' ) );
	}
	
	// for sort tags add unescaped true
	if( !empty( $merge_tags ) ){
		foreach( $merge_tags as $key => $merge_tag ){
			if( $merge_tag['type'] == 'sort_tag' )
				$merge_tags[$key]['unescaped'] = true;
		}
	}
	
	return apply_filters( 'wppb_userlisting_get_merge_tags', $merge_tags );
}

/**
 * Function that generates the variable array that we give to mustache classes for the multiple user listing
 *
 * @since v.2.0
 *
 * @return array $mustache_vars the array of variable groups and their details
 */
function wppb_generate_mustache_array_for_user_list(){
	$meta_tags = wppb_generate_userlisting_merge_tags( 'meta' );	
	$sort_tags = wppb_generate_userlisting_merge_tags( 'sort' );
	
	$mustache_vars = array( 
						array(
							'group-title' => __( 'Meta Variables', 'profile-builder' ),
							'variables' => array(
												array( 'name' => 'users', 'type' => 'loop_tag', 'children' => $meta_tags  ), 																							
											)
						),
						array(
							'group-title' => __( 'Sort Variables', 'profile-builder' ),
							'variables' => $sort_tags
						),
						array(
							'group-title' => __( 'Extra Functions', 'profile-builder' ),
							'variables' => array(
												array( 'name' => 'pagination', 'type' => 'pagination', 'unescaped' => true, 'label' => __( 'Pagination', 'profile-builder' ) ),
												array( 'name' => 'extra_search_all_fields', 'type' => 'extra_search_all_fields', 'unescaped' => true, 'label' => __( 'Search all Fields', 'profile-builder' ) ),
											)
						)
					);
					
	return $mustache_vars;
}

/**
 * Function that generates the variable array that we give to mustache classes for the single user listing
 *
 * @since v.2.0
 *
 * @return array $mustache_vars the array of variable groups and theyr details
 */
function wppb_generate_mustache_array_for_single_user_list(){
	$meta_tags = wppb_generate_userlisting_merge_tags( 'meta' );	
	
	$mustache_vars = array(  
						array(
							'group-title' => 'Available Variables',
							'variables' => $meta_tags
						),
						array(
							'group-title' => __('Extra Functions', 'profile-builder'),
							'variables' => array(												
												array( 'name' => 'extra_go_back_link', 'type' => 'go_back_link', 'unescaped' => true, 'label' => __( 'Go Back Link', 'profile-builder' ) ),
											)
						)
					);
	return $mustache_vars;
}



/**
 * Function that ads the mustache boxes in the backend for userlisting
 *
 * @since v.2.0
 */
function wppb_userlisting_add_mustache_in_backend(){
	require_once( WPPB_PLUGIN_DIR.'/modules/class-mustache-templates/class-mustache-templates.php' );
	
	// initiate box for multiple users listing
	new PB_Mustache_Generate_Admin_Box( 'wppb-ul-templates', __( 'All-userlisting Template', 'profile-builder' ), 'wppb-ul-cpt', 'core', wppb_generate_mustache_array_for_user_list(), wppb_generate_allUserlisting_content() );
	
	// initiate box for single user listing
	new PB_Mustache_Generate_Admin_Box( 'wppb-single-ul-templates', __( 'Single-userlisting Template', 'profile-builder' ), 'wppb-ul-cpt', 'core', wppb_generate_mustache_array_for_single_user_list(), wppb_generate_singleUserlisting_content() );
}
add_action( 'init', 'wppb_userlisting_add_mustache_in_backend' );

/**
 * Function that generates the default template for all user listing
 *
 * @since v.2.0
 * 
 */
function wppb_generate_allUserlisting_content(){
return '
<table class="wppb-table">
	<thead>
		<tr>
		  <th scope="col" colspan="2" class="wppb-sorting">{{{sort_user_name}}}</th>
		  <th scope="col" class="wppb-sorting">{{{sort_first_name}}}</th>
		  <th scope="col" class="wppb-sorting">{{{sort_role}}}</th>
		  <th scope="col" class="wppb-sorting">{{{sort_number_of_posts}}}</th>
		  <th scope="col" class="wppb-sorting">{{{sort_registration_date}}}</th>
		  <th scope="col">More</th>
		</tr>
	</thead>	
	<tbody>
		{{#users}}
		<tr>
		  <td data-label="' . __( 'Avatar', 'profile-builder' ) . '" class="wppb-avatar">{{{avatar_or_gravatar}}}</td>
		  <td data-label="' . __( 'Username', 'profile-builder' ) . '" class="wppb-login">{{meta_user_name}}</td>
		  <td data-label="' . __( 'Firstname', 'profile-builder' ) . '" class="wppb-name">{{meta_first_name}} {{meta_last_name}}</td>
		  <td data-label="' . __( 'Role', 'profile-builder' ) . '" class="wppb-role">{{meta_role}}</td>
		  <td data-label="' . __( 'Posts', 'profile-builder' ) . '" class="wppb-posts">{{{meta_number_of_posts}}}</td>
		  <td data-label="' . __( 'Sign-up Date', 'profile-builder' ) . '" class="wppb-signup">{{meta_registration_date}}</td>
		  <td data-label="' . __( 'More', 'profile-builder' ) . '" class="wppb-moreinfo">{{{more_info}}}</td>
		</tr>
		{{/users}}
	</tbody>
</table>
{{{pagination}}}';
}

/**
 * Function that generates the default template for single user listing
 *
 * @since v.2.0
 * 
 */
function wppb_generate_singleUserlisting_content(){
	return '
{{{extra_go_back_link}}}
<ul class="wppb-profile">
  <li>
    <h3>Name</h3>
  </li>
  <li class="wppb-avatar">
    {{{avatar_or_gravatar}}}
  </li>
  <li>
    <label>Username:</label>
    <span>{{meta_user_name}}</span>
  </li>
  <li>
    <label>First Name:</label>
    <span>{{meta_first_name}}</span>
  </li>
  <li>
    <label>Last Name:</label>
    <span>{{meta_last_name}}</span>
  </li>
  <li>
    <label>Nickname:</label>
    <span>{{meta_nickname}}</span>
  </li>
  <li>
    <label>Display name:</label>
	<span>{{meta_display_name}}</span>
  </li>
  <li>
    <h3>Contact Info</h3>
  </li>
  <li>
  	<label>Website:</label>
	<span>{{meta_website}}</span>
  </li>
  <li>
    <h3>About Yourself</h3>
  </li>
  <li>
	<label>Biographical Info:</label>
	<span>{{meta_biographical_info}}</span>
  </li>
</ul>
{{{extra_go_back_link}}}';
}


/**
 * Function that handles the userlisting shortcode
 *
 * @since v.2.0
 *
 * @param array $atts the shortcode attributs
 * @return the shortcode output
 */
function wppb_user_listing_shortcode( $atts ){
	global $roles;

	//get value set in the shortcode as parameter, default to "public" if not set
	extract( shortcode_atts( array('meta_key' => '', 'meta_value' => '', 'name' => 'userlisting', 'include' => '', 'exclude' => '' ), $atts ) );
	
	$userlisting_posts = get_posts( array( 'posts_per_page' => -1, 'post_status' =>'publish', 'post_type' => 'wppb-ul-cpt', 'orderby' => 'post_date', 'order' => 'ASC' ) );
	foreach ( $userlisting_posts as $key => $value ){
		if ( trim( Wordpress_Creation_Kit_PB::wck_generate_slug( $value->post_title ) ) == $name ){

            /* check here the visibility and roles for which to display the userlisting */
            $userlisting_args = get_post_meta( $value->ID, 'wppb_ul_page_settings', true );
            if( !empty( $userlisting_args[0]['visible-only-to-logged-in-users'] ) && $userlisting_args[0]['visible-only-to-logged-in-users'] == 'yes' ){
                if( !is_user_logged_in() )
                    return apply_filters( 'wppb_userlisting_no_permission_to_view', '<p>'. __( 'You do not have permission to view this user list.', 'profile-builder' ) .'</p>' );

                if( !empty( $userlisting_args[0]['visible-to-following-roles'] ) ){
                    if( strpos( $userlisting_args[0]['visible-to-following-roles'], '*' ) === false ){
                        $current_user = wp_get_current_user();
                        $roles = $current_user->roles;
                        if( empty( $roles ) )
                            $roles = array();

                        $visibility_for_roles = explode( ', ',$userlisting_args[0]['visible-to-following-roles'] );
                        $check_intersect_roles = array_intersect( $visibility_for_roles, $roles );

                        if( empty( $check_intersect_roles ) )
                            return apply_filters( 'wppb_userlisting_no_role_to_view', '<p>'. __( 'You do not have the required user role to view this user list.', 'profile-builder' ) .'</p>' );
                    }
                }
            }

			$userID = wppb_get_query_var( 'username' );

			if( !empty( $userID ) ){
                $user_object = new WP_User( $userID );
                $list_display_roles = explode( ', ', $userlisting_args[0]["roles-to-display"] );
                $role_present = array_intersect( $list_display_roles, $user_object->roles );

                if( ( !empty( $exclude ) && in_array( $userID, wp_parse_id_list( $exclude ) ) ) || ( !empty( $include ) && !in_array( $userID, wp_parse_id_list( $include ) ) ) || ( !in_array( '*', $list_display_roles ) && empty( $role_present ) ) ) {
                    return __( 'User not found', 'profile-builder' );
                }
                else {
                    $single_userlisting_template = get_post_meta( $value->ID, 'wppb-single-ul-templates', true );
                    if( empty( $single_userlisting_template ) )
                        $single_userlisting_template = wppb_generate_singleUserlisting_content();
                    return (string) new PB_Mustache_Generate_Template( wppb_generate_mustache_array_for_single_user_list(), $single_userlisting_template, array( 'userlisting_form_id' => $value->ID, 'meta_key' => $meta_key, 'meta_value' => $meta_value, 'include' => $include, 'exclude' => $exclude ) );
                }
            }else{
                $userlisting_template = get_post_meta( $value->ID, 'wppb-ul-templates', true );
                if( empty( $userlisting_template ) )
                    $userlisting_template = wppb_generate_allUserlisting_content();
				return (string) new PB_Mustache_Generate_Template( wppb_generate_mustache_array_for_user_list(), $userlisting_template, array( 'userlisting_form_id' => $value->ID, 'meta_key' => $meta_key, 'meta_value' => $meta_value, 'include' => $include, 'exclude' => $exclude ) );
            }
		}
	}
}



/**
 * Function that returns the meta-values for the default fields
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return the value for the meta-field
 */
function wppb_userlisting_show_default_user_fields( $value, $name, $children, $extra_info ){
	$userID = wppb_get_query_var( 'username' );
	if( !empty( $extra_info['user_id'] ) )
		$user_id = $extra_info['user_id'];
	else
		$user_id = '';
	
	if( empty( $userID ) )	
		$user_info = get_userdata($user_id);
	else
		$user_info = get_userdata($userID);
	
	if( $name == 'meta_user_name' ){
        $wppb_general_settings = get_option( 'wppb_general_settings' );
        if( isset( $wppb_general_settings['loginWith'] ) && ( $wppb_general_settings['loginWith'] == 'email' ) )
            return apply_filters('wppb_userlisting_extra_meta_email', $user_info->user_email, new WP_User( $user_info->ID ) );
        else
		    return apply_filters('wppb_userlisting_extra_meta_user_name', $user_info->user_login, new WP_User( $user_info->ID ) );
    }
    else if( $name == 'meta_email' )
        return apply_filters('wppb_userlisting_extra_meta_email', $user_info->user_email, new WP_User( $user_info->ID ) );
	else if( $name == 'meta_display_name' )
		return $user_info->display_name;
	else if( $name == 'meta_first_name' )
		return $user_info->user_firstname;
	else if( $name == 'meta_last_name' )
		return $user_info->user_lastname;
	else if( $name == 'meta_nickname' )
		return $user_info->nickname;
	else if( $name == 'meta_website' )
		return $user_info->user_url;
    else if( $name == 'meta_biographical_info' )
        return apply_filters('wppb_userlisting_autop_biographical_info', wpautop($user_info->description), $user_info->description);
	else if( $name == 'meta_role' ){
        if( !empty( $user_info->roles ) ){
			include_once(ABSPATH . 'wp-admin/includes/user.php');
			$editable_roles = array_keys( get_editable_roles() );
			if ( count( $user_info->roles ) <= 1 ) {
				$role = reset( $user_info->roles );
			} elseif ( $roles = array_intersect( array_values( $user_info->roles ), $editable_roles ) ) {
				$role = reset( $roles );
			} else {
				$role = reset( $user_info->roles );
			}
			$WP_Roles = new WP_Roles();
			$role_name = isset( $WP_Roles->role_names[$role] ) ? translate_user_role( $WP_Roles->role_names[$role] ) : __( 'None' );

			return apply_filters('wppb_userlisting_extra_meta_role', $role_name, $user_info );
        }

	}
	else if( $name == 'meta_registration_date' ){		
		$time = '';
		for ($i=0; $i<strlen( $user_info->user_registered); $i++){
			if ( $user_info->user_registered[$i] == ' ')
				break;
			
			else
				$time .= $user_info->user_registered[$i];
		}
		return apply_filters('wppb_userlisting_extra_meta_registration_date', $time, $user_info );	
	}
}
add_filter( 'mustache_variable_default_user_field', 'wppb_userlisting_show_default_user_fields', 10, 4 );



/**
 * Function that returns the number of posts related to each user
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return the value for the meta-field
 */
function wppb_userlisting_show_number_of_posts( $value, $name, $children, $extra_info ){
	$userID = wppb_get_query_var( 'username' );
	
	$user_id = ( !empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : '' );
	$user_info = ( empty( $userID ) ? get_userdata( $user_id ) : get_userdata( $userID ) );
	
	$allPosts = get_posts( array( 'author'=> $user_info->ID, 'numberposts'=> -1 ) );
	$number_of_posts = count( $allPosts );
		
	return apply_filters('wppb_userlisting_extra_meta_number_of_posts', '<a href="'.get_author_posts_url($user_info->ID).'" id="postNumberLink" class="postNumberLink">'.$number_of_posts.'</a>', $user_info, $number_of_posts);
}
add_filter( 'mustache_variable_number_of_posts', 'wppb_userlisting_show_number_of_posts', 10, 4 );



/**
 * Function that returns the meta-value for the respectiv meta-field
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return the value for the meta-field
 */
function wppb_userlisting_show_user_meta( $value, $name, $children, $extra_info ){
	$userID = wppb_get_query_var( 'username' );
	
	$user_id = ( !empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : '' );
	
	if( empty( $userID ) )	
		$userID = $user_id;
	
	// strip first meta_ from $name
	$name = preg_replace('/meta_/', '', $name, 1);
	$value = get_user_meta( $userID, $name, true );
	return apply_filters('wppb_userlisting_user_meta_value', $value, $name);
}
add_filter( 'mustache_variable_user_meta', 'wppb_userlisting_show_user_meta', 10, 4 );

function wppb_userlisting_show_user_meta_wysiwyg( $value, $name, $children, $extra_info ){
    $value = wppb_userlisting_show_user_meta( $value, $name, $children, $extra_info );
    $wpautop = apply_filters( 'wppb_userlisting_wysiwyg_wpautop', true );
    if( $wpautop )
        return wpautop( $value );
    else
        return $value;
}
add_filter( 'mustache_variable_user_meta_wysiwyg', 'wppb_userlisting_show_user_meta_wysiwyg', 10, 4 );

/* select, checkbox and radio can have their labels displayed */
function wppb_userlisting_show_user_meta_labels( $value, $name, $children, $extra_info ){
    $userID = wppb_get_query_var( 'username' );

    $user_id = ( !empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : '' );

    if( empty( $userID ) )
        $userID = $user_id;

    // strip first meta_ from $name
    $name = preg_replace( '/meta_/', '', $name, 1 );
    $name = preg_replace( '/_labels$/', '', $name, 1 );

    $value = get_user_meta( $userID, $name, true );
    /* get manage fields */
    $fields = get_option( 'wppb_manage_fields', 'not_found' );
    if( !empty( $fields ) ) {
        foreach ($fields as $field) {
            if( $field['meta-name'] == $name ){
                /* get label corresponding to value. the values and labels in the backend settings are comma separated so we assume that as well here ? */
                $saved_values = array_map( 'trim', explode( ',', $value ) );
                $field['options'] = array_map( 'trim', explode( ',', $field['options'] ) );
                $field['labels'] = array_map( 'trim', explode( ',', $field['labels'] ) );
                /* get the position for each value */
                $key_array = array();
                if( !empty( $field['options'] ) ){
                    foreach( $field['options'] as $key => $option ){
                        if( in_array( $option, $saved_values ) )
                            $key_array[] = $key;
                    }
                }

                $show_values = array();
                if( !empty( $key_array ) ){
                    foreach( $key_array as $key ){
                        if( !empty( $field['labels'][$key] ) )
                            $show_values[] = $field['labels'][$key];
                        else
                            $show_values[] = $field['options'][$key];
                    }
                }

                return apply_filters( 'wppb_userlisting_user_meta_value_label', implode( ', ', $show_values ), $name, $show_values  );
            }
        }
    }
}
add_filter( 'mustache_variable_user_meta_labels', 'wppb_userlisting_show_user_meta_labels', 10, 4 );

function wppb_modify_userlisting_user_meta_value($value, $name){
    $fields = get_option( 'wppb_manage_fields', 'not_found' );
    foreach ($fields as $field){
        if ( ($field['field'] == 'Textarea')&& ($field['meta-name'] == $name)) {
            return wpautop($value);
        }
        if( ( $field['field'] == 'Avatar' || $field['field'] == 'Upload' ) && $field['meta-name'] == $name ){
            if( is_numeric($value) ){
                $img_attr = wp_get_attachment_image_src( $value, 'full' );
                if( !empty( $img_attr[0] ) )
                    return $img_attr[0];
            }
            else
                return $value;
        }
		if( $field['field'] == 'Select (Country)' && $field['meta-name'] == $name ) {
			$country_array = wppb_country_select_options( 'front_end' );
			
			if( ! empty( $country_array[$value] ) )
				return $country_array[$value];
		}
    }
    return $value;
}
add_filter('wppb_userlisting_user_meta_value', 'wppb_modify_userlisting_user_meta_value', 10, 2);

/**
 * Function that creates the sort-link for the various fields
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return sort-link
 */
function wppb_userlisting_sort_tags( $value, $name, $children, $extra_info ){

	if ( $name == 'sort_user_name' )
        return '<a href="'.wppb_get_new_url( 'login', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'login' ) . '" id="sortLink1">'.apply_filters( 'sort_user_name_filter', __( 'Username', 'profile-builder' ) ).'</a>';
	
	elseif ($name == 'sort_first_last_name')
		return apply_filters( 'sort_first_last_name_filter', __( 'First/Lastname', 'profile-builder' ) );
		
	elseif ( $name == 'sort_email' )
        return '<a href="'.wppb_get_new_url( 'email', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'email' ) . '" id="sortLink2">'.apply_filters( 'sort_email_filter', __( 'Email', 'profile-builder' ) ).'</a>';
		
	elseif ( $name == 'sort_registration_date' )
        return '<a href="'.wppb_get_new_url( 'registered', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'registered' ) . '" id="sortLink3">'.apply_filters( 'sort_registration_date_filter', __( 'Sign-up Date', 'profile-builder' ) ).'</a>';
		
	elseif ( $name == 'sort_first_name' )
        return '<a href="'.wppb_get_new_url( 'firstname', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'firstname' ) . '" id="sortLink4">'.apply_filters( 'sort_first_name_filter', __( 'Firstname', 'profile-builder' ) ).'</a>';
		
	elseif ( $name == 'sort_last_name' )
        return '<a href="'.wppb_get_new_url( 'lastname', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'lastname' ) . '" id="sortLink5">'.apply_filters( 'sort_last_name_filter', __( 'Lastname', 'profile-builder' ) ).'</a>';
		
	elseif ( $name == 'sort_display_name' )		
        return '<a href="'.wppb_get_new_url( 'nicename', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'nicename' ) . '" id="sortLink6">'.apply_filters( 'sort_display_name_filter', __( 'Display Name', 'profile-builder' ) ).'</a>';
		
	elseif ( $name == 'sort_website' )
		return '<a href="'.wppb_get_new_url( 'url', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'website' ) . '" id="sortLink7">'.apply_filters('sort_website_filter', __( 'Website', 'profile-builder' ) ).'</a>';
	
	elseif ( $name == 'sort_biographical_info' )
        return '<a href="'.wppb_get_new_url( 'bio', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'bio' ) . '" id="sortLink8">'.apply_filters( 'sort_biographical_info_filter', __( 'Biographical Info', 'profile-builder' ) ).'</a>';
		
	elseif ( $name == 'sort_number_of_posts' )
        return '<a href="'.wppb_get_new_url( 'post_count', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'post_count' ) . '" id="sortLink9">'.apply_filters( 'sort_number_of_posts_filter', __( 'Posts', 'profile-builder' ) ).'</a>';
		
	elseif ( $name == 'sort_aim' )
        return '<a href="'.wppb_get_new_url( 'aim', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'aim' ) . '" id="sortLink10">'.apply_filters( 'sort_aim_filter', __( 'Aim', 'profile-builder' ) ).'</a>';
	
	elseif ( $name == 'sort_yim' )
        return '<a href="'.wppb_get_new_url( 'yim', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'yim' ) . '" id="sortLink11">'.apply_filters( 'sort_yim_filter', __( 'Yim', 'profile-builder' ) ).'</a>';
	
	elseif ( $name == 'sort_jabber' )
        return '<a href="'.wppb_get_new_url( 'jabber', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'jabber' ) . '" id="sortLink12">'.apply_filters( 'sort_jabber_filter', __( 'Jabber', 'profile-builder' ) ).'</a>';

    elseif ( $name == 'sort_nickname' )
        return '<a href="'.wppb_get_new_url( 'nickname', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'nickname' ) . '" id="sortLink13">'.apply_filters( 'sort_nickname_filter', __( 'Nickname', 'profile-builder' ) ).'</a>';

    elseif ( $name == 'sort_role' )
        return '<a href="'.wppb_get_new_url( 'role', $extra_info ).'" class="sortLink ' . wppb_get_sorting_class( 'role' ) . '" id="sortLink14">'.apply_filters( 'sort_role_filter', __( 'Role', 'profile-builder' ) ).'</a>';

    else{
		$wppb_manage_fields = get_option( 'wppb_manage_fields', 'not_found' );
		
		if ( $wppb_manage_fields != 'not_found' ){		
			$i = 12;
			
			foreach( $wppb_manage_fields as $key => $value ){
				if ( $name == 'sort_'.$value['meta-name'] ){
					$i++;
				
					return '<a href="'.wppb_get_new_url( $value['meta-name'], $extra_info ).'" class="sortLink" id="sortLink'.$i.'">'.$value['field-title'].'</a>';
				}
			}
		}
	}
	
}
add_filter( 'mustache_variable_sort_tag', 'wppb_userlisting_sort_tags', 10, 4 );



/**
 * Function that loops through the users for the respective page
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return sort-link
 */
function wppb_userlisting_users_loop( $value, $name, $children, $extra_values ){
	if( $name == 'users' ){
		$userlisting_form_id = $extra_values['userlisting_form_id'];		
		$userlisting_args = get_post_meta( $userlisting_form_id, 'wppb_ul_page_settings', true );

        if( !empty( $userlisting_args[0] ) ){
			$pageNum = wppb_get_query_var ('page');
			$pageNum = ( ( $pageNum > 0 ) ? $pageNum - 1 : $pageNum );
            if( !is_int( (int)$userlisting_args[0]['number-of-userspage'] ) || (int)$userlisting_args[0]['number-of-userspage'] == 0 )
                $userlisting_args[0]['number-of-userspage'] = 5;

			//set query args
			$args = array(
				'results_per_page'				=> (int)$userlisting_args[0]['number-of-userspage'],
				'offset'						=> $pageNum * (int)$userlisting_args[0]['number-of-userspage'],
				'role' 							=> $userlisting_args[0]['roles-to-display'],
				'meta_key' 						=> $extra_values['meta_key'],
				'meta_value'					=> $extra_values['meta_value'],
				'meta_compare'					=> 'LIKE',
				'use_wildcard'					=> true,
				'sorting_criteria'				=> $userlisting_args[0]['default-sorting-criteria'],
				'sorting_order'					=> $userlisting_args[0]['default-sorting-order'],
                'include'                       => $extra_values['include'],
                'exclude'                       => $extra_values['exclude']
			);

			// Check if some of the listing parameters have changed
			if ( isset( $_REQUEST['setSortingCriteria'] ) && ( trim( $_REQUEST['setSortingCriteria'] ) !== '' ) )
				$args['sorting_criteria'] = $_REQUEST['setSortingCriteria'];
				
			if ( isset( $_REQUEST['setSortingOrder'] ) && ( trim( $_REQUEST['setSortingOrder'] ) !== '' ) )
				$args['sorting_order'] = $_REQUEST['setSortingOrder'];
			
			if ( isset( $_REQUEST['searchFor'] ) ){
				//was a valid string enterd in the search form?
				$searchText = apply_filters( 'wppb_userlisting_search_field_text', __( 'Search Users by All Fields', 'profile-builder' ) );
		
				if ( trim( $_REQUEST['searchFor'] ) !== $searchText )
					$args['search'] = $_REQUEST['searchFor'];
			}
			
			$args = apply_filters( 'wppb_userlisting_user_query_args', $args );

			//query users
			require_once ( 'class-userlisting.php' );
			$wp_user_search = new PB_WP_User_Query( $args );
			
			$thisPageOnly = $wp_user_search->get_results();	
			global $totalUsers;
			$totalUsers = $wp_user_search->get_total();
			
			$children_vals = array();
			
			if( !empty( $thisPageOnly ) ){
				$i = 0;
				foreach( $thisPageOnly as $user ){
					foreach( $children as $child ){
						$children_vals[$i][ $child['name'] ] = apply_filters( 'mustache_variable_'. $child['type'], '', $child['name'], array(), array( 'user_id' => $user->ID, 'userlisting_form_id' => $userlisting_form_id ) );
					}
					$i++;
				}
			}			
			
			return  $children_vals;
		}
	}
}
add_filter( 'mustache_variable_loop_tag', 'wppb_userlisting_users_loop', 10, 4 );



/**
 * Function that returns the user_id for the currently displayed user
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return ID
 */
function wppb_userlisting_user_id( $value, $name, $children, $extra_info ){
	$user_id = ( ! empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : get_query_var( 'username' ) );
	$userID = wppb_get_query_var( 'username' );
	$user_info = ( empty( $userID ) ? get_userdata( $user_id ) : get_userdata( $userID ) );

	if( ! empty( $user_info ) )
		return $user_info->ID;
}
add_filter( 'mustache_variable_user_id', 'wppb_userlisting_user_id', 10, 4 );



/**
 * Function that returns the user_nicename for the currently displayed user
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return user_nicename
 */
function wppb_userlisting_user_nicename( $value, $name, $children, $extra_info ){
	$user_id = ( ! empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : get_query_var( 'username' ) );
	$userID = wppb_get_query_var( 'username' );
	$user_info = ( empty( $userID ) ? get_userdata( $user_id ) : get_userdata( $userID ) );

	if( ! empty( $user_info ) )
		return $user_info->user_nicename;
}
add_filter( 'mustache_variable_user_nicename', 'wppb_userlisting_user_nicename', 10, 4 );



/**
 * Function that returns the link for the more_info link in html form
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_more_info( $value, $name, $children, $extra_info ){
	$more_url = wppb_userlisting_more_info_url( $value, $name, $children, $extra_info );
	
	if ( apply_filters( 'wbb_userlisting_extra_more_info_link_type', true ) )
		return apply_filters( 'wppb_userlisting_more_info_link', '<span id="wppb-more-span" class="wppb-more-span"><a href="'.$more_url.'" class="wppb-more" id="wppb-more" title="'.__( 'Click here to see more information about this user', 'profile-builder' ) .'" alt="'.__( 'More...', 'profile-builder' ).'">'.__( 'More...', 'profile-builder').'</a></span>', $more_url );
	
	else	
		return apply_filters( 'wppb_userlisting_more_info_link_with_arrow', '<a href="'.$more_url.'" class="wppb-more"><img src="'.WPPB_PLUGIN_URL.'assets/images/arrow_right.png" title="'.__( 'Click here to see more information about this user.', 'profile-builder' ).'" alt=">"></a>' );
}
add_filter( 'mustache_variable_more_info', 'wppb_userlisting_more_info', 10, 4 );



/**
 * Function that returns the URL only for the more_info
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_more_info_url( $value, $name, $children, $extra_info ){		
	$user_id = ( !empty( $extra_info['user_id'] ) ? $extra_info['user_id'] : get_query_var( 'username' ) );
	$userID = wppb_get_query_var( 'username' );
	$user_info = ( empty( $userID ) ? get_userdata( $user_id ) : get_userdata( $userID ) );	
	
	//filter to get current user by either username or id(default);
	$get_user_by_ID = apply_filters( 'wppb_userlisting_get_user_by_id', true );
	$url = apply_filters( 'wppb_userlisting_more_base_url', get_permalink() );

	$user_data = get_the_author_meta( 'user_nicename', $user_info->ID );
	
	if ( isset( $_GET['page_id'] ) )
		return apply_filters ( 'wppb_userlisting_more_info_link_structure1', $url.'&userID='.$user_info->ID, $url, $user_info );
	
	else{
		if ( $get_user_by_ID === true )
			return apply_filters ( 'wppb_userlisting_more_info_link_structure2', trailingslashit( $url ).'user/'.$user_info->ID, $url, $user_info );
		
		else
			return apply_filters ( 'wppb_userlisting_more_info_link_structure3', trailingslashit( $url ).'user/'.$user_data, $url, $user_data );
	}
}
add_filter( 'mustache_variable_more_info_url', 'wppb_userlisting_more_info_url', 10, 4 );


/* we need to check if we have the filter that turns the link for the single user from /id/ to /username/
   if we have then the wppb_get_query_var needs to return the user id becuse that's what we expect in our functions that output the data
 */
add_action('init', 'wppb_check_userlisting_get_user_by');
function wppb_check_userlisting_get_user_by(){
    if ( has_filter( 'wppb_userlisting_get_user_by_id' ) ){
        add_filter( 'wppb_get_query_var_username', 'wppb_change_returned_username_query_var' );
        function wppb_change_returned_username_query_var( $var ){
            /* $var should be username and we want to change it into user id */
            if( !is_numeric($var) && !empty( $var ) ){
                $args= array(
                    'search' => $var,
                    'search_fields' => array( 'user_nicename' )
                );
                $user = new WP_User_Query($args);
                if( !empty( $user->results ) )
                    $var = $user->results[0]->ID;
            }

            return $var;
        }
    }
}

/* when we are on default permalinks we need to return $_GET['userID'] */
add_filter( 'wppb_get_query_var_username', 'wppb_change_returned_username_var_on_default_permalinks' );
function wppb_change_returned_username_var_on_default_permalinks( $var ){
    if( empty( $var ) && isset( $_GET['userID'] ) )
        return $_GET['userID'];

    return $var;
}

/**
 * Function that returns the link for the previous page
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_go_back_link( $value, $name, $children, $extra_values ){	
	if ( apply_filters( 'wppb_userlisting_go_back_link_type', true ) )
		return apply_filters( 'wppb_userlisting_go_back_link', '<div id="wppb-back-span" class="wppb-back-span"><a href=\'javascript:history.go(-1)\' class="wppb-back" id="wppb-back" title="'. __( 'Click here to go back', 'profile-builder' ) .'" alt="'. __( 'Back', 'profile-builder' ) .'">'. __( 'Back', 'profile-builder' ) .'</a></div>' );
	
	else	
		return apply_filters( 'wppb_userlisting_go_back_link_with_arrow', '<a href=\'javascript:history.go(-1)\' class="wppb-back"><img src="'.WPPB_PLUGIN_URL.'assets/images/arrow_left.png" title="'. __( 'Click here to go back', 'profile-builder' ) .'" alt="<"/></a>' );
}
add_filter( 'mustache_variable_go_back_link', 'wppb_userlisting_go_back_link', 10, 4 );



/**
 * Function that returns the pagination created
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_pagination( $value, $name, $children, $extra_info ){	
	global $totalUsers;
	
	require_once ( 'class-userlisting-pagination.php' );
	
	$this_form_settings = get_post_meta( $extra_info['userlisting_form_id'], 'wppb_ul_page_settings', true );
	
	if( !empty( $this_form_settings ) ){
		if ( ( $totalUsers != '0' ) || ( $totalUsers != 0 ) ){
			$pagination = new WPPB_Pagination;
			
			$first = __( '&laquo;&laquo; First', 'profile-builder' );
			$prev = __( '&laquo; Prev', 'profile-builder' );
			$next = __( 'Next &raquo; ', 'profile-builder' );
			$last = __( 'Last &raquo;&raquo;', 'profile-builder' );

            if( !is_int( (int)$this_form_settings[0]['number-of-userspage'] ) || (int)$this_form_settings[0]['number-of-userspage'] == 0 )
                $this_form_settings[0]['number-of-userspage'] = 5;

			$currentPage = wppb_get_query_var( 'page' );
			if ( $currentPage == 0 )
				$currentPage = 1;
		
			if ( isset( $_POST['searchFor'] ) ){
				$searchtext_label = apply_filters( 'wppb_userlisting_search_field_text', __( 'Search Users by All Fields', 'profile-builder' ) );
			
				if ( ( trim( $_POST['searchFor'] ) == $searchtext_label ) || ( trim( $_POST['searchFor'] ) == '' ) )
					$pagination->generate( $totalUsers, $this_form_settings[0]['number-of-userspage'], '', $first, $prev, $next, $last, $currentPage ); 
				
				else
					$pagination->generate( $totalUsers, $this_form_settings[0]['number-of-userspage'], trim($_POST['searchFor']), $first, $prev, $next, $last, $currentPage );
					
			}elseif ( isset( $_GET['searchFor'] ) ){
				$pagination->generate( $totalUsers, $this_form_settings[0]['number-of-userspage'], trim( $_GET['searchFor'] ), $first, $prev, $next, $last, $currentPage );
			
			}else{
				$pagination->generate( $totalUsers, $this_form_settings[0]['number-of-userspage'], '', $first, $prev, $next, $last, $currentPage );
			}
			
			return apply_filters( 'wppb_userlisting_userlisting_table_pagination', '<div class="userlisting_pagination" id="userlisting_pagination" align="right">'.$pagination->links().'</div>' );
		}
	}
	else
		return apply_filters( 'wppb_userlisting_no_pagination_settings', '<p class="error">'.__( 'You don\'t have any pagination settings on this userlisting!', 'profile-builder' ). '</p>' );
	
	return;
}
add_filter( 'mustache_variable_pagination', 'wppb_userlisting_pagination', 10, 4 );


/**
 * Function that returns the search field
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_extra_search_all_fields( $value, $name, $children, $extra_info ){	
	$userlisting_settings = get_post_meta( $extra_info['userlisting_form_id'], 'wppb_ul_page_settings', true );
	$set_new_sorting_order = ( isset( $userlisting_settings[0]['default-sorting-order'] ) ? $userlisting_settings[0]['default-sorting-order'] : 'asc' );	

	$searchText = apply_filters( 'wppb_userlisting_search_field_text', __( 'Search Users by All Fields', 'profile-builder' ) );
	
	if ( isset($_REQUEST['searchFor'] ) )
		if ( trim( $_REQUEST['searchFor'] ) != $searchText )
			$searchText = trim( $_REQUEST['searchFor'] );
	
	$setSortingCriteria = ( isset( $userlisting_settings[0]['default-sorting-criteria'] ) ? $userlisting_settings[0]['default-sorting-criteria'] : 'login' );	
	$setSortingCriteria = ( isset( $_REQUEST['setSortingCriteria'] ) ? $_REQUEST['setSortingCriteria'] : $setSortingCriteria );
	
	$setSortingOrder = ( isset( $userlisting_settings[0]['default-sorting-order'] ) ? $userlisting_settings[0]['default-sorting-order'] : 'asc' );	
	$setSortingOrder = ( isset( $_REQUEST['setSortingOrder'] ) ? $_REQUEST['setSortingOrder'] : $setSortingOrder );

	return '
		<form method="post" action="'.add_query_arg( array( 'page' => 1, 'setSortingCriteria' => $setSortingCriteria, 'setSortingOrder' => $setSortingOrder ) ).'" class="wppb-search-users wppb-user-forms">
            <div class="wppb-search-users-wrap">
                <input onfocus="if(this.value == \''.$searchText.'\'){this.value = \'\';}" type="text" onblur="if(this.value == \'\'){this.value=\''.$searchText.'\';}" id="wppb-search-fields" name="searchFor" title="'. $searchText .'" value="'.$searchText.'" />
		        <input type="hidden" name="action" value="searchAllFields" />
		        <input type="submit" name="searchButton" class="wppb-search-button" value="'.__( 'Search', 'profile-builder' ).'" />
			    <a class="wppb-clear-results" href="'.wppb_clear_results().'">'.__( 'Clear Results', 'profile-builder' ).'</a>
		    </div>
		</form>';
}
add_filter( 'mustache_variable_extra_search_all_fields', 'wppb_userlisting_extra_search_all_fields', 10, 4 );


/**
 * Function that returns the avatar or gravatar (based on what is set)
 *
 * @since v.2.0
 *
 * @param str $value undefined value
 * @param str $name the name of the field
 * @param array $children an array containing all other fields
 * @param array $extra_info various extra information about the user
 *
 *
 * @return string
 */
function wppb_userlisting_avatar_or_gravatar( $value, $name, $children, $extra_information ){
	$this_form_settings = get_post_meta( $extra_information['userlisting_form_id'], 'wppb_ul_page_settings', true );
	
	$all_userlisting_avatar_size = apply_filters( 'all_userlisting_avatar_size', ( isset( $this_form_settings[0]['avatar-size-all-userlisting'] ) ? (int)$this_form_settings[0]['avatar-size-all-userlisting'] : 100 ) );
	$single_userlisting_avatar_size = apply_filters( 'single_userlisting_avatar_size', ( isset( $this_form_settings[0]['avatar-size-single-userlisting'] ) ? (int)$this_form_settings[0]['avatar-size-single-userlisting'] : 100 ) );
	
	$userID = wppb_get_query_var( 'username' );

	$user_info = ( empty( $userID ) ? get_userdata( $extra_information['user_id'] ) : get_userdata( $userID ) );
	$avatar_size = ( empty( $userID ) ? $all_userlisting_avatar_size : $single_userlisting_avatar_size );
	$avatar_crop = apply_filters( 'all_userlisting_avatar_crop', true, $userID );

	$avatar_or_gravatar = get_avatar( (int)$user_info->data->ID, $avatar_size );

	$wp_upload_array = wp_upload_dir();

	if ( strpos( $avatar_or_gravatar, $wp_upload_array['baseurl'] ) ){
		wppb_resize_avatar( (int)$user_info->data->ID, $avatar_size, $avatar_crop );
		$avatar_or_gravatar = get_avatar( (int)$user_info->data->ID, $avatar_size );
	}

	return apply_filters( 'wppb_userlisting_extra_avatar_or_gravatar', $avatar_or_gravatar, $user_info, $avatar_size, $userID );	
}
add_filter( 'mustache_variable_avatar_or_gravatar', 'wppb_userlisting_avatar_or_gravatar', 10, 4 );



/**
 * Remove certain actions from post list view
 *
 * @since v.2.0
 *
 * @param array $actions
 *
 * return array
 */
function wppb_remove_ul_view_link( $actions ){
	global $post;
	
	if ( $post->post_type == 'wppb-ul-cpt' ){
		unset( $actions['view'] );
		
		if ( wppb_get_post_number ( $post->post_type, 'singular_action' ) )
			unset( $actions['trash'] );
	}

	return $actions;
}
add_filter( 'post_row_actions', 'wppb_remove_ul_view_link', 10, 1 );


/**
 * Remove certain bulk actions from post list view
 *
 * @since v.2.0
 *
 * @param array $actions
 *
 * return array
 */
function wppb_remove_trash_bulk_option_ul( $actions ){
	global $post;
	if( !empty( $post ) ){	
		if ( $post->post_type == 'wppb-ul-cpt' ){
			unset( $actions['view'] );
			
			if ( wppb_get_post_number ( $post->post_type, 'bulk_action' ) )
				unset( $actions['trash'] );
		}
	}

	return $actions;
}
add_filter( 'bulk_actions-edit-wppb-ul-cpt', 'wppb_remove_trash_bulk_option_ul' );


/**
 * Function to hide certain publishing options
 *
 * @since v.2.0
 *
 */
function wppb_hide_ul_publishing_actions(){
	global $post;

	if ( $post->post_type == 'wppb-ul-cpt' ){
		echo '<style type="text/css">#misc-publishing-actions, #minor-publishing-actions{display:none;}</style>';
		
		$ul = get_posts( array( 'posts_per_page' => -1, 'post_status' => apply_filters ( 'wppb_check_singular_ul_form_publishing_options', array( 'publish' ) ) , 'post_type' => 'wppb-ul-cpt' ) );
		if ( count( $ul ) == 1 )
			echo '<style type="text/css">#major-publishing-actions #delete-action{display:none;}</style>';
	}
}
add_action('admin_head-post.php', 'wppb_hide_ul_publishing_actions');
add_action('admin_head-post-new.php', 'wppb_hide_ul_publishing_actions');


/**
 * Add custom columns to listing
 *
 * @since v.2.0
 *
 * @param array $columns
 * @return array $columns
 */
function wppb_add_extra_column_for_ul( $columns ){
	$columns['ul-shortcode'] = __( 'Shortcode', 'profile-builder' );
	
	return $columns;
}
add_filter( 'manage_wppb-ul-cpt_posts_columns', 'wppb_add_extra_column_for_ul' );


/**
 * Add content to the displayed column
 *
 * @since v.2.0
 *
 * @param string $column_name
 * @param integer $post_id
 * @return void
 */
function wppb_ul_custom_column_content( $column_name, $post_id ){
	if( $column_name == 'ul-shortcode' ){
		$post = get_post( $post_id );
		
		if( empty( $post->post_title ) )
			$post->post_title = __( '(no title)', 'profile-builder' );

        echo "<input readonly spellcheck='false' type='text' class='wppb-shortcode input' value='[wppb-list-users name=\"" . Wordpress_Creation_Kit_PB::wck_generate_slug( $post->post_title ) . "\"]' />";
	}
}
add_action("manage_wppb-ul-cpt_posts_custom_column",  "wppb_ul_custom_column_content", 10, 2);


/**
 * Add side metaboxes
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_ul_content(){
	global $post;
	
	$form_shortcode = trim( Wordpress_Creation_Kit_PB::wck_generate_slug( $post->post_title ) );
	if ( $form_shortcode == '' )
		echo '<p><em>' . __( 'The shortcode will be available after you publish this form.', 'profile-builder' ) . '</em></p>';
	else{
        echo '<p>' . __( 'Use this shortcode on the page you want the form to be displayed:', 'profile-builder' );
        echo '<br/>';
        echo "<textarea readonly spellcheck='false' class='wppb-shortcode textarea'>[wppb-list-users name=\"" . $form_shortcode . "\"]</textarea>";
        echo '</p><p>';
        echo __( '<span style="color:red;">Note:</span> changing the form title also changes the shortcode!', 'profile-builder' );
        echo '</p>';

        echo '<h4>'. __('Extra shortcode parameters', 'profile-builder') .'</h4>';
        
        echo '<a href="wppb-extra-shortcode-parameters" class="wppb-open-modal-box">' . __( "View all extra shortcode parameters", "profile-builder" ) . '</a>';

        echo '<div id="wppb-extra-shortcode-parameters" title="' . __( "Extra shortcode parameters", "profile-builder" ) . '" class="wppb-modal-box">';

        	echo '<p>';
	        echo '<strong>meta_key="key_here"<br /> meta_value="value_here"</strong> - '. __( 'displays users having a certain meta-value within a certain (extra) meta-field', 'profile-builder' );
	        echo '<br/><br/>'.__( 'Example:', 'profile-builder' ).'<br/>';
	        echo '<strong>[wppb-list-users name="' . $form_shortcode . '" meta_key="skill" meta_value="Photography"]</strong><br/><br/>';
	        echo __( 'Remember though, that the field-value combination must exist in the database.', 'profile-builder' );
	        echo '</p>';

	        echo '<hr />';

	        echo '<p>';
	        echo '<strong>include="user_id_1, user_id_2"</strong> - '. __( 'displays only the users that you specified the user_id for', 'profile-builder' );
	        echo '</p>';

	        echo '<hr />';

	        echo '<p>';
	        echo '<strong>exclude="user_id_1, user_id_2"</strong> - '. __( 'displays all users except the ones you specified the user_id for', 'profile-builder' );
	        echo '</p>';

        echo '</div>';
    }
}

function wppb_ul_side_box(){
	add_meta_box( 'wppb-ul-side', __( 'Form Shortcode', 'profile-builder' ), 'wppb_ul_content', 'wppb-ul-cpt', 'side', 'low' );
}
add_action( 'add_meta_boxes', 'wppb_ul_side_box' );



/**
 * Function that manages the Userlisting CPT
 *
 * @since v.2.0
 *
 * @return void
 */
function wppb_manage_ul_cpt(){
	global $wp_roles;
	//$default_wp_role = trim( get_option( 'default_role' ) );
	$available_roles = $sorting_order = $sorting_criteria = $avatar_size = array();
	
	// Set role
	$available_roles[] = '%*%*';
	foreach ( $wp_roles->roles as $slug => $role )
		$available_roles[] = '%'.trim( $role['name'] ).'%'.$slug;

	// Set sorting criteria
	$sorting_criteria[] = '%'.__( 'Username', 'profile-builder' ).'%login';
	$sorting_criteria[] = '%'.__( 'Email', 'profile-builder' ).'%email';
	$sorting_criteria[] = '%'.__( 'Website', 'profile-builder' ).'%url';
	$sorting_criteria[] = '%'.__( 'Biographical Info', 'profile-builder' ).'%bio';
	$sorting_criteria[] = '%'.__( 'Registration Date', 'profile-builder' ).'%registered';
	$sorting_criteria[] = '%'.__( 'Firstname', 'profile-builder' ).'%firstname';
	$sorting_criteria[] = '%'.__( 'Lastname', 'profile-builder' ).'%lastname';
	$sorting_criteria[] = '%'.__( 'Display Name', 'profile-builder' ).'%nicename';
    $sorting_criteria[] = '%'.__( 'Nickname', 'profile-builder' ).'%nickname';
	$sorting_criteria[] = '%'.__( 'Number of Posts', 'profile-builder' ).'%post_count';
    $sorting_criteria[] = '%'.__( 'Role', 'profile-builder' ).'%role';
	
	// Default contact methods were removed in WP 3.6. A filter dictates contact methods.
	if ( apply_filters( 'wppb_remove_default_contact_methods', get_site_option( 'initial_db_version' ) < 23588 ) ){
		$sorting_criteria[] = '%'.__( 'Aim', 'profile-builder' ).'%aim';
		$sorting_criteria[] = '%'.__( 'Yim', 'profile-builder' ).'%yim';
		$sorting_criteria[] = '%'.__( 'Jabber', 'profile-builder' ).'%jabber';
	}
	
	$exclude_fields_from_settings = apply_filters( 'wppb_exclude_field_list_userlisting_settings', array( 'Default - Name (Heading)', 'Default - Contact Info (Heading)', 'Default - About Yourself (Heading)', 'Default - Username', 'Default - First Name', 'Default - Last Name', 'Default - Nickname', 'Default - E-mail', 'Default - Website', 'Default - AIM', 'Default - Yahoo IM', 'Default - Jabber / Google Talk', 'Default - Password', 'Default - Repeat Password', 'Default - Biographical Info', 'Default - Display name publicly as', 'Heading' ) );
	
	$wppb_manage_fields = get_option( 'wppb_manage_fields' );
	foreach ( $wppb_manage_fields as $key => $value ){
		if ( !in_array( $value['field'], $exclude_fields_from_settings ) )
			$sorting_criteria[] = '%'.$value['field-title'].'%'.$value['meta-name'];
	}
	$sorting_criteria[] = '%'.__( 'Random (very slow on large databases > 10K user)', 'profile-builder' ).'%RAND()';
	
	// Set sorting order
	$sorting_order[] = '%'.__( 'Ascending', 'profile-builder' ).'%asc';
	$sorting_order[] = '%'.__( 'Descending', 'profile-builder' ).'%desc';
	
	// Avatar size
	for( $i=0; $i<=200; $i++ )
		$avatar_size[] = $i;
		

	// set up the fields array
	$settings_fields = array( 		
		array( 'type' => 'checkbox', 'slug' => 'roles-to-display', 'title' => __( 'Roles to Display', 'profile-builder' ), 'options' => $available_roles, 'default' => '*', 'description' => __( 'Restrict the userlisting to these selected roles only<br/>If not specified, defaults to all existing roles', 'profile-builder' ) ),
		array( 'type' => 'text', 'slug' => 'number-of-userspage', 'title' => __( 'Number of Users/Page', 'profile-builder' ), 'default' => '5', 'description' => __( 'Set the number of users to be displayed on every paginated part of the all-userlisting', 'profile-builder' ) ),
		array( 'type' => 'select', 'slug' => 'default-sorting-criteria', 'title' => __( 'Default Sorting Criteria', 'profile-builder' ), 'options' => $sorting_criteria, 'default' => 'login', 'description' => __( 'Set the default sorting criteria<br/>This can temporarily be changed for each new session', 'profile-builder' ) ),
		array( 'type' => 'select', 'slug' => 'default-sorting-order', 'title' => __( 'Default Sorting Order', 'profile-builder' ), 'options' => $sorting_order, 'default' => 'asc', 'description' => __( 'Set the default sorting order<br/>This can temporarily be changed for each new session', 'profile-builder' ) ),
		array( 'type' => 'select', 'slug' => 'avatar-size-all-userlisting', 'title' => __( 'Avatar Size (All-userlisting)', 'profile-builder' ), 'options' => $avatar_size, 'default' => '40', 'description' => __( 'Set the avatar size on the all-userlisting only', 'profile-builder' ) ),
		array( 'type' => 'select', 'slug' => 'avatar-size-single-userlisting', 'title' => __( 'Avatar Size (Single-userlisting)', 'profile-builder' ), 'options' => $avatar_size, 'default' => '60', 'description' => __( 'Set the avatar size on the single-userlisting only', 'profile-builder' ) ),
		array( 'type' => 'checkbox', 'slug' => 'visible-only-to-logged-in-users', 'title' => __( 'Visible only to logged in users?', 'profile-builder' ), 'options' => array( '%'.__( 'Yes', 'profile-builder' ).'%yes' ), 'description' => __( 'The userlisting will only be visible only to the logged in users', 'profile-builder' ) ),
        array( 'type' => 'checkbox', 'slug' => 'visible-to-following-roles', 'title' => __( 'Visible to following Roles', 'profile-builder' ), 'options' => $available_roles, 'default' => '*', 'description' => __( 'The userlisting will only be visible to the following roles', 'profile-builder' ) ),
	);
	
	// set up the box arguments
	$args = array(
		'metabox_id' => 'wppb-ul-settings-args',
		'metabox_title' => __( 'Userlisting Settings', 'profile-builder' ),
		'post_type' => 'wppb-ul-cpt',
		'meta_name' => 'wppb_ul_page_settings',
		'meta_array' => $settings_fields,			
		'sortable' => false,
		'single' => true
	);
	new Wordpress_Creation_Kit_PB( $args );
	
}
add_action( 'init', 'wppb_manage_ul_cpt', 10 );


add_filter( "wck_before_listed_wppb_ul_fields_element_0", 'wppb_manage_fields_display_field_title_slug', 10, 3 );
add_filter( 'wck_update_container_class_wppb_ul_fields', 'wppb_update_container_class', 10, 4 );
add_filter( 'wck_element_class_wppb_ul_fields', 'wppb_element_class', 10, 4 );



// function to display an error message in the front end in case the shortcode was used but the userlisting wasn't activated
function wppb_list_all_users_display_error($atts){
	return apply_filters( 'wppb_not_addon_not_activated', '<p class="error">'.__( 'You need to activate the Userlisting feature from within the "Modules" tab!', 'profile-builder' ).'<br/>'.__( 'You can find it in the Profile Builder menu.', 'profile-builder' ).'</p>' );
}



//function to return to the userlisting page without the search parameters
function wppb_clear_results(){
	$args = array( 'searchFor', 'setSortingOrder', 'setSortingCriteria' );
	
	return remove_query_arg( $args );
}



//function to return the links for the sortable headers
function wppb_get_new_url( $criteria, $extra_info ){
	$set_new_sorting_criteria = ( ( isset( $_REQUEST['setSortingCriteria'] ) && ( $_REQUEST['setSortingCriteria'] == $criteria ) ) ? $_REQUEST['setSortingCriteria'] : $criteria );
	
	$userlisting_settings = get_post_meta( $extra_info['userlisting_form_id'], 'wppb_ul_page_settings', true );
	$set_new_sorting_order = ( isset( $userlisting_settings[0]['default-sorting-order'] ) ? $userlisting_settings[0]['default-sorting-order'] : 'asc' );
	$set_new_sorting_order = ( ( isset( $_REQUEST['setSortingOrder'] ) && ( $_REQUEST['setSortingOrder'] == 'desc' ) ) ? 'asc' : 'desc' );
	
	$args = array( 'setSortingCriteria' => $set_new_sorting_criteria, 'setSortingOrder' => $set_new_sorting_order );	
	
	$searchText = apply_filters( 'wppb_userlisting_search_field_text', __( 'Search Users by All Fields', 'profile-builder' ) );

	if ( ( isset( $_REQUEST['searchFor'] ) ) && ( trim( $_REQUEST['searchFor'] ) != $searchText ) )
		$args['searchFor'] = trim( $_REQUEST['searchFor'] );

	return add_query_arg( $args );
}

//function that returns a class for the sort link depending on what sorting is selected
function wppb_get_sorting_class( $criteria ) {
    $output = '';

    if( isset( $_REQUEST['setSortingCriteria'] ) && ( $_REQUEST['setSortingCriteria'] == $criteria ) ) {
        if( isset( $_REQUEST['setSortingOrder'] ) && $_REQUEST['setSortingOrder'] == 'asc' ) {
            $output = 'sort-asc';
        } elseif( $_REQUEST['setSortingOrder'] == 'desc' ) {
            $output = 'sort-desc';
        }
    }

    return $output;
}

//function to render 404 page in case a user doesn't exist
function wppb_set404(){
	global $wp_query;
	global $wpdb;

    /* we should only do this if we are on a userlisting single page username query arg or $_GET['userID'] is set */
    $username_query_var = wppb_get_query_var( 'username' );
    if( isset($_GET['userID']) || ( !empty( $username_query_var ) && !isset( $_POST['username'] ) ) ){
        $arrayID = array();
        $nrOfIDs = 0;

        //check if certain users want their profile hidden
        $extraField_meta_key = apply_filters( 'wppb_display_profile_meta_field_name', '' );	//meta-name of the extra-field which checks if the user wants his profile hidden
        $extraField_meta_value = apply_filters( 'wppb_display_profile_meta_field_value', '' );	//the value of the above parameter; the users with these 2 combinations will be excluded

        if ( ( trim($extraField_meta_key) != '' ) && ( trim( $extraField_meta_value) != '' ) ){
            $results = $wpdb->get_results( $wpdb->prepare( "SELECT wppb_t1.ID FROM $wpdb->users AS wppb_t1 LEFT OUTER JOIN $wpdb->usermeta AS wppb_t2 ON wppb_t1.ID = wppb_t2.user_id AND wppb_t2.meta_key = %s WHERE wppb_t2.meta_value LIKE %s ORDER BY wppb_t1.ID", $extraField_meta_key, '%'. PB_WP_User_Query::wppb_esc_like(trim($extraField_meta_value)).'%' ) );
            if( !empty( $results ) ){
                foreach ($results as $result){
                    array_push($arrayID, $result->ID);
                }
            }
        }

        //if admin approval is activated, then give 404 if the user was manually requested
        $wppb_generalSettings = get_option('wppb_general_settings', 'not_found');
        if( $wppb_generalSettings != 'not_found' )
            if( !empty( $wppb_generalSettings['adminApproval'] ) && $wppb_generalSettings['adminApproval'] == 'yes' ){

                // Get term by name ''unapproved'' in user_status taxonomy.
                $user_statusTaxID = get_term_by('name', 'unapproved', 'user_status');
                if( $user_statusTaxID != false ){
                    $term_taxonomy_id = $user_statusTaxID->term_taxonomy_id;

                    $results = $wpdb->get_results( $wpdb->prepare ( "SELECT wppb_t3.ID FROM $wpdb->users AS wppb_t3 LEFT OUTER JOIN $wpdb->term_relationships AS wppb_t4 ON wppb_t3.ID = wppb_t4.object_id WHERE wppb_t4.term_taxonomy_id = %d ORDER BY wppb_t3.ID", $term_taxonomy_id ) );
                    if( !empty( $results ) ){
                        foreach ($results as $result){
                            array_push($arrayID, $result->ID);
                        }
                    }
                }
            }

        $nrOfIDs=count($arrayID);

        //filter to get current user by either username or id(default); get user by username?
        $get_user_by_ID = apply_filters('wppb_userlisting_get_user_by_id', true);

        $invoke404 = false;

        //get user ID
        if (isset($_GET['userID'])){
            $userID = get_userdata($_GET['userID']);
            if ( is_object( $userID ) ){
                if ( $nrOfIDs ){
                    if ( in_array( $userID->ID, $arrayID ) )
                        $invoke404 = true;
                }else{
                    $username = $userID->user_login;
                    $user = get_user_by('login', $username);
                    if ( ( $user === false ) || ( $user == null ) )
                        $invoke404 = true;
                }
            }
        }else{
            if ( $get_user_by_ID === true ){
                $userID = $username_query_var;
                if ($nrOfIDs){
                    if ( in_array( $userID, $arrayID ) )
                        $invoke404 = true;
                }else{
                    $user = get_userdata($userID);
                    if ( is_object( $user ) ){
                        $username = $user->user_login;
                        $user = get_user_by( 'login', $username );
                        if ( ( $userID !== '' ) && ( $user === false ) )
                            $invoke404 = true;
                    }
                    else
                        $invoke404 = true;
                }

            }else{
                $username = $username_query_var;
                $user = get_userdata($username);
                if ( is_object( $user ) ){
                    if ( $nrOfIDs ){
                        if ( in_array($user->ID, $arrayID ) )
                            $invoke404 = true;
                    }else{
                        if ( ( $username !== '' ) && ( $user === false ) )
                            $invoke404 = true;
                    }
                }
                else
                    $invoke404 = true;
            }
        }

        if ( $invoke404 )
            $wp_query->set_404();
    }
}
add_action('template_redirect', 'wppb_set404');


//function to handle the case when a search was requested but there were no results
function no_results_found_handler($content){

	$retContent = '';
	$formEnd = strpos( (string)$content, '</form>' );
	
	for ($i=0; $i<$formEnd+7; $i++){
		$retContent .= $content[$i];
	}
	
	return apply_filters( 'wppb_no_results_found_message', '<p class="noResults" id="noResults">'.__( 'No results found!', 'profile-builder' ) .'</p>' );
}


// flush_rules() if our rules are not yet included
function wppb_flush_rewrite_rules(){
	$rules = get_option( 'rewrite_rules' );

	if ( !isset( $rules['(.+?)/user/([^/]+)'] ) ){
		global $wp_rewrite;
	   	
		$wp_rewrite->flush_rules();
	}
}
add_action( 'wp_loaded', 'wppb_flush_rewrite_rules' );


// Adding a new rule
function wppb_insert_userlisting_rule( $rules ){
	$new_rule = array();
	
	$new_rule['(.+?)/user/([^/]+)'] = 'index.php?pagename=$matches[1]&username=$matches[2]';
	
	return $new_rule + $rules;
}
add_filter( 'rewrite_rules_array', 'wppb_insert_userlisting_rule' );


// Adding the username var so that WP recognizes it
function wppb_insert_query_vars( $vars ){
    array_push( $vars, 'username' );
	
    return $vars;
}
add_filter( 'query_vars', 'wppb_insert_query_vars' );