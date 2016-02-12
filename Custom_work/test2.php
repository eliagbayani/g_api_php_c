<?php

  include_once "../Custom/config.php";
  require_once '../src/Google/autoload.php'; // or wherever autoload.php is located

  $client = new Google_Client();
  $client->setApplicationName("Client_Library_Examples");
  $client->setDeveloperKey(YOUR_API_KEY); //YOUR_APP_KEY

  $service = new Google_Service_Fusiontables($client);

  $my_table = "1YPvGpDseeNeODm8uAdd-TPm_WjI89c-uat0Dy-H8";
  $my_table = "1LHfg3v4BTByQiyo7w0Di5uaumLPZtJHwij4jT53u"; //eli_tbl1
  // $results = $service->query->sql("SELECT count('*') FROM $my_table"); //Chanos chanos
  $results = $service->query->sql("SELECT '*' FROM $my_table"); //Chanos chanos
  
  echo "<pre>";print_r($results);echo "</pre>";
  
  
  
?>
