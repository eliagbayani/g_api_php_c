<?php

  include_once "../Custom/config.php";
  require_once '../src/Google/autoload.php'; // or wherever autoload.php is located

  $client = new Google_Client();
  $client->setApplicationName("Client_Library_Examples");
  $client->setDeveloperKey(YOUR_API_KEY); //YOUR_APP_KEY

  $service = new Google_Service_Books($client);
  $optParams = array('filter' => 'free-ebooks');
  $results = $service->volumes->listVolumes('Henry David Thoreau', $optParams);

  foreach ($results as $item) {
    echo $item['volumeInfo']['title'], "<br /> \n";
  }
?>
