<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/wordpress/wp-load.php');
//
// Get site configuration
require_once($_SERVER['DOCUMENT_ROOT'] . "/configuration.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/common.php");
$configuration = getConfiguration();


$current_user = wp_get_current_user();

if ($current_user->ID == 0) {
  print json_encode(array ( "valid" => false ));
} else {
  print json_encode(array ( "valid" => true ));
}

?> 
