<?php

function HandleEnpoint($configuration, $endpoint, $payload, $method) {
  $jsonBody = array(
    'user' => array (
      'email' => $configuration['email'],
      'password' => $configuration['password']
    )
  );

  $jsonBody = array_merge($jsonBody, $payload);
  $curl = curl_init();
  curl_setopt_array($curl, array(
    CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json',
      'Accept: application/json'
    ),
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_POST => false,
    CURLOPT_CUSTOMREQUEST => $method,
    CURLOPT_URL => $configuration['webservice_address'] . $endpoint,
    CURLOPT_POSTFIELDS => json_encode($jsonBody)
  ));

  $response = curl_exec($curl);

  if(!$response){
        die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
  }

  curl_close($curl);

  return (array)json_decode($response);
}

function GetEndpoint($configuration, $endpoint, $payload) {
  return HandleEnpoint($configuration, $endpoint, $payload, 'GET');
}

function PutEndpoint($configuration, $endpoint, $payload) {
  return HandleEnpoint($configuration, $endpoint, $payload, 'PUT');
}

?>
