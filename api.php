<?php

$credentials_string = file_get_contents("api-credentials.json");
$credentials_json = json_decode($credentials_string, true);
$document_root = shell_exec("grep -e DocumentRoot  /etc/apache2/sites-available/000-default.conf | awk '{ printf \"%s\", $2 }'");
echo 'Document root:', $document_root, PHP_EOL;



require_once( 'wordpress/wp-load.php' );


//function pre( $array ) { echo '<pre>'; print_r( $array ); echo '</pre>'; }



$api_actions = array(
  'update_local_json_data',
  'update_product_data',
  'run_product_updater',
  'update_user_data'
);

$ch_url_prefixs = array(
  'products',
  'vendors',
  'customer_users'
);

function is_valid_email_address( $email_address ) {
  if( !filter_var( $email_address , FILTER_VALIDATE_EMAIL ) === false ) {	return true; } else { return false; }
}



( isset( $_GET['csv'] ) && $_GET['csv'] === 'true' ) ? $ch_csv = 'true' : $ch_csv = 'false';
( isset( $_GET['prefix'] ) && in_array( $_GET['prefix'] , $ch_url_prefixs ) ) ? $ch_url_prefix = $_GET['prefix'] : $ch_url_prefix = '';
( isset( $_GET['update_index'] ) && is_numeric( $_GET['update_index'] ) ) ? $update_index = $_GET['update_index'] : $update_index = NULL;
( isset( $_GET['action'] ) && in_array( $_GET['action'] , $api_actions ) ) ? $action = $_GET['action'] : $action = false;
( isset( $_GET['pre'] ) && $_GET['pre'] === 'true' ) ? $pre = true : $pre = false;
( isset( $_GET['product_relation'] ) && $_GET['product_relation'] === 'true' ) ? $ch_product_relation = 'true' : $ch_product_relation = 'false';
( isset( $_GET['users_email'] ) && is_valid_email_address( $_GET['users_email'] ) ) ? $users_email = $_GET['users_email'] : $users_email = NULL;

$ch_url_prefix = 'customer_users';

$ch_url = 'http://14.1.51.192/api/' . $ch_url_prefix;
$ch_init = curl_init();
$ch_email = $credentials['email'];
$ch_password = $credentials['password'];
$ch_post_json = '{"user":{"email":"' . $ch_email . '","password":"' . $ch_password . '"},"csv":"' . $ch_csv . '","product_relation":"' . $ch_product_relation . '"}';
$ch_http_header = array( 'Content-Type: application/json' , 'Accept: application/json' );

$action = 'update_user_data';

switch( $action ) {

case 'update_local_json_data' :

  switch( $ch_url_prefix ) {

  case 'products' :

    curl_setopt( $ch_init , CURLOPT_SSL_VERIFYPEER , false );
    curl_setopt( $ch_init , CURLOPT_HTTPHEADER , $ch_http_header );
    curl_setopt( $ch_init , CURLOPT_RETURNTRANSFER , true );
    curl_setopt( $ch_init , CURLOPT_CUSTOMREQUEST , "GET" );
    curl_setopt( $ch_init , CURLOPT_POST , 1 );
    curl_setopt( $ch_init , CURLOPT_POSTFIELDS , $ch_post_json );
    curl_setopt( $ch_init , CURLOPT_URL , $ch_url );

    $ch_result = curl_exec( $ch_init );

    curl_close( $ch_init );

    $ch_result_array = json_decode( $ch_result , true );

    echo 'The local copy of the json data file has been updated with the copy from the products api..';

    if( $pre ) { pre( $ch_result_array ); }

    $api_json_content = $ch_result;
    $save_file = fopen($document_root . '/local-json/products.json' , 'wb' );
    fwrite( $save_file  ,$api_json_content );
    fclose( $save_file );

    $save_csv_file = fopen($document_root . '/local-json/products.csv', 'w');
    foreach ($ch_result_array as $fields) {
      fputcsv($save_csv_file, $fields);
    }
    fclose($save_csv_file);

    break;

  case 'vendors' :

    curl_setopt( $ch_init , CURLOPT_SSL_VERIFYPEER , false );
    curl_setopt( $ch_init , CURLOPT_HTTPHEADER , $ch_http_header );
    curl_setopt( $ch_init , CURLOPT_RETURNTRANSFER , true );
    curl_setopt( $ch_init , CURLOPT_CUSTOMREQUEST , "GET" );
    curl_setopt( $ch_init , CURLOPT_POST , 1 );
    curl_setopt( $ch_init , CURLOPT_POSTFIELDS , $ch_post_json );
    curl_setopt( $ch_init , CURLOPT_URL , $ch_url );

    $ch_result = curl_exec( $ch_init );

    curl_close( $ch_init );

    $ch_result_array = json_decode( $ch_result , true );

    echo 'The local copy of the json data file has been updated with the copy from the vendors api..';

    if( $pre ) { pre( $ch_result_array ); }

    $api_json_content = $ch_result;
    $save_file = fopen($document_root . '/local-json/vendors.json' , 'wb' );
    fwrite( $save_file  ,$api_json_content );
    fclose( $save_file );

    $save_csv_file = fopen($document_root . '/local-json/vendors.csv', 'w');
    foreach ($ch_result_array as $fields) {
      fputcsv($save_csv_file, $fields);
    }
    fclose($save_csv_file);

    break;

  case 'customer_users' :

    curl_setopt( $ch_init , CURLOPT_SSL_VERIFYPEER , false );
    curl_setopt( $ch_init , CURLOPT_HTTPHEADER , $ch_http_header );
    curl_setopt( $ch_init , CURLOPT_RETURNTRANSFER , true );
    curl_setopt( $ch_init , CURLOPT_CUSTOMREQUEST , "GET" );
    curl_setopt( $ch_init , CURLOPT_POST , 1 );
    curl_setopt( $ch_init , CURLOPT_POSTFIELDS , $ch_post_json );
    curl_setopt( $ch_init , CURLOPT_URL , $ch_url );

    $ch_result = curl_exec( $ch_init );

    curl_close( $ch_init );

    $ch_result_array = json_decode( $ch_result , true );

    echo 'The local copy of the json data file has been updated with the copy from the customer users api..';

    if( $pre ) { pre( $ch_result_array ); }

    $api_json_content = $ch_result;
    $save_file = fopen($document_root . '/local-json/customer_users.json' , 'wb' );
    fwrite( $save_file  ,$api_json_content );
    fclose( $save_file );

    $save_csv_file = fopen($document_root . '/local-json/customer_users.csv', 'w');
    foreach ($ch_result_array as $fields) {
      fputcsv($save_csv_file, $fields);
    }
    fclose($save_csv_file);

    break;

  default: echo 'No prefix was found..'; break;

  }

  break;

  /*case 'update_product_data' :

    if( in_array( $ch_url_prefix , $ch_url_prefixs ) && is_numeric( $update_index ) ) {

      $local_products_json_file = file_get_contents( $document_root . '/local-json/products.json' );

      $local_products_array = json_decode( $local_products_json_file , true );

      $found_product_post_id = 0;

      $product_json_id = $local_products_array[$update_index]['id'];
      $product_json_title = $local_products_array[$update_index]['product_id'];
      $product_json_directory = $local_products_array[$update_index]['directory'];
      $product_json_description = $local_products_array[$update_index]['description'];
      $product_json_description_2 = $local_products_array[$update_index]['description2'];
      $product_json_directory = $local_products_array[$update_index]['directory'];
      $product_json_vendor_id = $local_products_array[$update_index]['vendor_id'];
      $product_json_vendor_name = $local_products_array[$update_index]['vendor_name'];
      $product_json_sds_expiry = $local_products_array[$update_index]['sds_expiry'];
      $product_json_unit_measure = $local_products_array[$update_index]['unit_measure'];
      $product_json_shelf_life = $local_products_array[$update_index]['shelf_life'];
      $product_json_inventory = $local_products_array[$update_index]['inventory'];
      $product_json_quantity_purchase_order = $local_products_array[$update_index]['quantity_purchase_order'];
      $product_json_quantity_packing_slip = $local_products_array[$update_index]['quantity_packing_slip'];
      $product_json_sds_required = $local_products_array[$update_index]['sds_required'];
      $product_json_new_description = $local_products_array[$update_index]['new_description'];


      $products_query = new WP_Query( array(
          'post_type' => 'fo_product',
          'meta_query' => array(
            array(
              'key'     => 'pd_product_id',
              'value'   => $product_json_id,
              'compare' => '==',
            ),
          ),
        )
      );

      if( $products_query->have_posts() ) {
        while( $products_query->have_posts() ) { $products_query->the_post();
          $found_product_post_id = get_the_ID();
          $product_post_update_array = array(
            'ID'           => $found_product_post_id,
            'post_title'   => $product_json_description,
            'post_content' => $product_json_new_description,
          );
          wp_update_post( $product_post_update_array );
          update_post_meta( get_the_ID() , 'pd_pack_size' , $product_json_description_2 );
          update_post_meta( get_the_ID() , 'pd_directory' , $product_json_directory );
          update_post_meta( get_the_ID() , 'pd_vendor_id' , $product_json_vendor_id );
          update_post_meta( get_the_ID() , 'pd_vendor_name' , $product_json_vendor_name );
          update_post_meta( get_the_ID() , 'pd_sds_expiry' , $product_json_sds_expiry );
          update_post_meta( get_the_ID() , 'pd_unit_measure' , $product_json_unit_measure );
          update_post_meta( get_the_ID() , 'pd_shelf_life' , $product_json_shelf_life );
          update_post_meta( get_the_ID() , 'pd_inventory' , $product_json_inventory );
          update_post_meta( get_the_ID() , 'pd_quantity_purchase_order' , $product_json_quantity_purchase_order );
          update_post_meta( get_the_ID() , 'pd_quantity_packing_slip' , $product_json_quantity_packing_slip );
          update_post_meta( get_the_ID() , 'pd_sds_required' , $product_json_sds_required );
        } 
      }

      wp_reset_postdata();

      if( $pre ) { pre( $local_products_array[$update_index] ); }
      $return_array = array(
        'json_id' => $product_json_id,
        'post_id' => $found_product_post_id,
        'title' => $product_json_description,
        'description' => $product_json_new_description,
        'pack_size' => $product_json_description_2,
        'success' => 1,
      );
      echo json_encode( $return_array );

    }

  break;*/

  /*case 'run_product_updater' :

    if( in_array( $ch_url_prefix , $ch_url_prefixs ) ) {

      $local_products_json_file = file_get_contents( $document_root . '/local-json/products.json' );

      $total_products = count( json_decode( $local_products_json_file , true ) );

      $html = '
        <html>
          <head>
            <title>Updating Product Data</title>
            <style>
            table { width: 100%; table-layout: fixed; }
            table td { padding: 5px 10px; }
            table thead tr { background-color: #ddd; }
            table tbody tr td { border-bottom: 1px solid #ddd; font-size: 14px; }
            </style>
          </head>
          <body>
            <div id="results-container">
              <table>
                <thead>
                  <tr>
                    <td>JSON ID</td>
                    <td>Post ID</td>
                    <td>Title</td>
                    <td>Description</td>
                    <td>Pack Size</td>
                    <td>Progress</td>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
            <script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
            <script>
              $( window ).load( function() { 
                var timer_update_index = 0;
                function run_update_timer( update_index ) {
                  $.get( "/api.php?action=update_product_data&prefix=products&update_index="+update_index, function( data ) {
                    var json_data = jQuery.parseJSON( data );
                    var new_row_html = "<tr><td>"+json_data.json_id+"</td><td>"+json_data.post_id+"</td><td>"+json_data.title+"</td><td>"+json_data.description+"</td><td>"+json_data.pack_size+"</td><td>"+(timer_update_index+1)+"/' . $total_products . '</td></tr>";
                    $( "#results-container table tbody" ).prepend( new_row_html );
                    timer_update_index = timer_update_index + 1;
                    if( timer_update_index < ' . $total_products . ' ) { run_update_timer( timer_update_index ); }
                    $( "title" ).html( "Updateing.. "+(timer_update_index+1)+"/' . $total_products . '" );
                  });
                }
                run_update_timer( timer_update_index );
              } );
            </script>
          </body>
        </html>
      ';

      echo $html;

    }

  break;*/

case 'update_user_data' :

  echo 'update_user_data', PHP_EOL;

  if( in_array( $ch_url_prefix , $ch_url_prefixs ) ) {
    echo 'in array', PHP_EOL;

    $local_customer_users_json_file = file_get_contents( $document_root . '/local-json/customer_users.json' );

    $local_customer_users_array = json_decode( $local_customer_users_json_file , true );

    $found_product_post_id = 0;

    $restricted_emails = array(
      'support@forgeonline.co.nz',
      'developer@forgeonline.co.nz',
      'perry@forgeonline.co.nz',
      'rahul@forgeonline.co.nz'
    );

    for( $i = 0; $i < count( $local_customer_users_array ); $i++ ) {

      $customer_users_json_user_id = $local_customer_users_array[$i]['id'];
      $customer_users_json_email = $local_customer_users_array[$i]['email'];
      $customer_users_json_password = $local_customer_users_array[$i]['password'];
      $customer_users_json_created_at = $local_customer_users_array[$i]['created_at'];
      $customer_users_json_updated_at = $local_customer_users_array[$i]['updated_at'];
      $customer_users_json_products = $local_customer_users_array[$i]['products'];

      if( !in_array( $customer_users_json_email , $restricted_emails ) ) { 

        if( $pre ) { pre( $local_customer_users_array[$i] ); }

        $found_user = get_user_by( 'email' , $customer_users_json_email );

        if( $pre ) { pre( $found_user ); }

        if( $found_user ) {

          echo 'User found:', var_export($found_user, true), PHP_EOL;

          $wordpress_user_id = $found_user->ID;
          $wordpress_user_website = '';
          $wordpress_new_password = $customer_users_json_password;
          $success = wp_update_user( array( 'ID' => $wordpress_user_id, 'user_url' => $wordpress_user_website ) );

          wp_set_password( $wordpress_new_password, $wordpress_user_id );

          if ( is_wp_error( $success ) ) {
            echo 'The email address', $customer_users_json_email, 'was not found in the wordpress user list.', PHP_EOL;
          } else {
            echo 'The email address', $customer_users_json_email, 'was found with the user id of', $wordpress_user_id, PHP_EOL;
          }		

        } else  {

          $wordpress_new_password = $customer_users_json_password;
          wp_create_user( $email , $email , $password );	
          echo 'Attemp to create user.', PHP_EOL;

        }
      }
    }
  }

  break;

default : break;
}
