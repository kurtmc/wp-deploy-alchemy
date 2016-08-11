<?php

// Get site configuration
require_once("configuration.php");
require_once("common.php");
$configuration = getConfiguration();

require_once( 'wordpress/wp-load.php' );

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
  $res = PutEndpoint($configuration, '/api/customer_users/' . $res['id'], array('password' => $password));
  Redirect(constant('WP_HOME') . '/customer-login/', false);
} else {
  echo "You entered your current password incorrectly, please go back and try again";
}


?> 
