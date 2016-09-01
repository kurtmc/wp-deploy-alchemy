<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');
//
// Get site configuration
require_once($_SERVER['DOCUMENT_ROOT'] . "/configuration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/common.php");
$configuration = getConfiguration();


$current_user = wp_get_current_user();

$res = GetEndpoint($configuration, '/api/customer_users/email', array('email' => $current_user->get('user_login')));

$result = PutEndpoint($configuration, '/api/customer_users/' . $res['id'], array('terms_of_use' => true));

?>