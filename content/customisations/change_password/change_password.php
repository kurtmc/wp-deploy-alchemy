<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');
//
// Get site configuration
require_once($_SERVER['DOCUMENT_ROOT'] . "/configuration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/common.php");
$configuration = getConfiguration();


function Redirect($url, $permanent = false)
{
  header('Location: ' . $url, true, $permanent ? 301 : 302);

  exit();
}

$current_user = wp_get_current_user();


$current = $_POST['current'];
$password = $_POST['password'];
$confirm = $_POST['confirm'];

$res = GetEndpoint($configuration, '/api/customer_users/email', array('email' => $current_user->get('user_login')));

if ($current == $res['password']) {
  // Change password on internal site
  $res = PutEndpoint($configuration, '/api/customer_users/' . $res['id'], array('password' => $password));
  // Change password on external site
  wp_set_password($password, $current_user->ID); 

  Redirect(constant('WP_HOME') . '/customer-login/', false);
} else {
  echo "You entered your current password incorrectly, please go back and try again";
}

?> 
