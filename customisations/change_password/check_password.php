<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');
//
// Get site configuration
require_once($_SERVER['DOCUMENT_ROOT'] . "/configuration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/common.php");
$configuration = getConfiguration();


$current_user = wp_get_current_user();


$current = $_POST['password'];

$res = GetEndpoint($configuration, '/api/customer_users/email', array('email' => $current_user->get('user_login')));

if ($current == $res['password']) {
  print json_encode(array ( "valid" => true ));
} else {
  print json_encode(array ( "valid" => false ));
}

?> 
