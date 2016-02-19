<?php
include_once "../Custom/config.php";
session_start();
include_once "../examples/templates/base.php";
/************************************************
  Make an API request authenticated with a service account.
 ************************************************/
require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

$client_id              = YOUR_CLIENT_ID;       //Client ID
$service_account_name   = SERVICE_ACCOUNT_NAME; //Email Address
$key_file_location      = KEY_FILE_LOCATION;    //key.p12

echo pageHeader("Service Account Access");
if (strpos($client_id, "googleusercontent") == false
    || !strlen($service_account_name)
    || !strlen($key_file_location)) {
  echo missingServiceAccountDetailsWarning();
  exit;
}

$client = new Google_Client();
$client->setApplicationName("Client_Library_Examples");
$client->addScope("https://www.googleapis.com/auth/fusiontables", "https://www.googleapis.com/auth/fusiontables.readonly");

$service = new Google_Service_Fusiontables($client);

/************************************************
  If we have an access token, we can carry on. Otherwise, we'll get one with the help of an
  assertion credential. In other examples the list of scopes was managed by the Client, but here
  we have to list them manually. We also supply the service account
 ************************************************/
if (isset($_SESSION['service_token'])) {
  $client->setAccessToken($_SESSION['service_token']);
}
$key = file_get_contents($key_file_location);
$cred = new Google_Auth_AssertionCredentials(
    $service_account_name,
    array('https://www.googleapis.com/auth/fusiontables', 'https://www.googleapis.com/auth/fusiontables.readonly'),
    $key
);
$client->setAssertionCredentials($cred);
if ($client->getAuth()->isAccessTokenExpired()) {$client->getAuth()->refreshTokenWithAssertion($cred);}
$_SESSION['service_token'] = $client->getAccessToken();

if($client->getAccessToken()) echo "\nlogged in\n\n";
else echo "\nnot logged in\n\n";
//************************************************

$new_tbl = "eli_tbl1";
$my_table = "1YPvGpDseeNeODm8uAdd-TPm_WjI89c-uat0Dy-H8";

/*
$sql = "DELETE FROM $my_table WHERE ROWID = '10'";
$sql = "INSERT INTO $my_table ('sciname') VALUES ('elicha')";
$sql = "CREATE TABLE Persons (PersonID int, LastName varchar(255), FirstName varchar(255),Address varchar(255),City varchar(255));";
$results = $service->query->sql($sql);
echo "<pre>";print_r($results);echo "</pre>";
*/

/*
$results = $service->table->listTable();
foreach ($results as $item) echo "<br>" . $item['name'];
*/


$postBody = '{"kind": "fusiontables#table", "name": "eli_tbl1", "isExportable": 1,
  "columns": [{"kind": "fusiontables#column", "columnId": "1", "name": "catalogNumber", "type": "STRING"},
              {"kind": "fusiontables#column", "columnId": "2", "name": "latitude", "type": "LOCATION"}]
              }';

/*
$col1 = new Google_Service_Fusiontables_Column();
$col1->columnId = 1;
$col1->name = "catalogNumber";
$col1->type = "STRING";
echo "<pre>";print_r($col1);echo"<pre>";
*/

$cols = '[{"kind": "fusiontables#column", "columnId": "1", "name": "catalogNumber", "type": "STRING"}, 
          {"kind": "fusiontables#column", "columnId": "2", "name": "latitude", "type": "LOCATION"}]';

$postBody = new Google_Service_Fusiontables_Table();
$postBody->name = "eli_tbl4";
$postBody->isExportable = true;
$postBody->columns = json_decode($cols);
$postBody->kind = "fusiontables#table";


echo "<pre>";print_r($postBody);echo"<pre>";
$results = $service->table->insert($postBody, array());
print_r($results);

