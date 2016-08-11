<?php

if (!function_exists('getConfiguration')) {
  function getConfiguration() {
    $document_root = '/var/www/html/current';
    if (file_exists($document_root . "/site-configuration.json")) {
      $configuration_string = file_get_contents($document_root . "/site-configuration.json");
    } else { // Use non-production
      $configuration_string = file_get_contents($document_root . "/site-configuration.json.example");
    }
    return json_decode($configuration_string, true);
  }
}

?>
