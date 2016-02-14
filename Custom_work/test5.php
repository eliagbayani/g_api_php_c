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
$client->setApplicationName("Client_Library_Examples eli");
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
    // array('https://www.googleapis.com/auth/fusiontables', 'https://www.googleapis.com/auth/fusiontables.readonly'),
    array('https://www.googleapis.com/auth/drive', 'https://www.googleapis.com/auth/drive.apps.readonly', 
          'https://www.googleapis.com/auth/drive.file',
          'https://www.googleapis.com/auth/fusiontables', 'https://www.googleapis.com/auth/fusiontables.readonly'),
    $key
);
$client->setAssertionCredentials($cred);
if ($client->getAuth()->isAccessTokenExpired()) {$client->getAuth()->refreshTokenWithAssertion($cred);}
$_SESSION['service_token'] = $client->getAccessToken();

if($client->getAccessToken()) echo "\nlogged in\n\n";
else echo "\nnot logged in\n\n";
//************************************************

$my_table = "1LHfg3v4BTByQiyo7w0Di5uaumLPZtJHwij4jT53u"; //eli_tbl1
// $my_table = "1N4ua-naIOf8rVSjsoqNkDqTiiA0PtvfrE7As-E-E"; //eli_tbl2
// $my_table = "1YPvGpDseeNeODm8uAdd-TPm_WjI89c-uat0Dy-H8"; //Chanos chanos
// $my_table = "1sHg1xKApgcbSVKTtHUKeOiGGCAP3kjLPJiq_eu7y"; //copy of Chanos chanos
// $my_table = "1USTwiVIYKd333fvGcdIuYuhtmaL6YJJkgWIkT9e6";//eli_tbl3
// $my_table = "1Oeyld88agmOuZm9wKaMduDqXoia7MQpkieU6-fNx"; //eli_tbl4



/*
You said, that you had already populated a fusion table from php using a service account . You're through the worst part, don't give up in the finish. 
You most probably used the google-php-api-client, right? 
If so, you already have to have a Google_Client $client in your script. 
Use this to get your hands on the Drive API permission service: 
$permissionsService = new Google_DriveService($client)->permissions; – sanya Dec 17 '12 at 19:05
   	 
Once you have it, create a new Permission, that will make your table public to anyone 
like this: 
$permission = new Google_Permission(); 
$permission->setRole('reader'); 
$permission->setType('anyone'); 
Then add this permission to your fusion table: 
$permissionsService->insert($tableId, $permission); 

That's it, you're done. From now, your table is readable(but for everyone, not just you) and public. 
Now you can put it on a Google Maps "Fusion Layer". – sanya Dec 17 '12 at 19:09
*/

// /* //working OK ===========================
$permissionsService = new Google_Service_Drive($client);
$permissionsService = $permissionsService->permissions;

$permission = new Google_Service_Drive_Permission();
// $permission = new Google_Service_Drive_Permissions_Resource();
$permission->setRole('reader'); 
$permission->setType('anyone'); 

$result = $permissionsService->create($my_table, $permission); 
echo"<pre>";print_r($result);echo"</pre>";exit;
 // =========================== */


/* //working OK  ===========================
$service = new Google_Service_Drive($client);
printFile($service, $my_table);
function printFile($service, $fileId) {
  try {
    $file = $service->files->get($fileId);
    // print "Title: "         . $file->getTitle();
    print "Description: "   . $file->getDescription();
    print "MIME type: "     . $file->getMimeType();
    
  } catch (Exception $e) {
    print "An error occurred: " . $e->getMessage();
  }
}
=========================== */


/* Not working, cannot find how to get the permissionId
$service = new Google_Service_Drive($client);
updatePermission($service, $my_table, SERVICE_ACCOUNT_NAME , "writer");

function updatePermission($service, $fileId, $permissionId, $newRole) {
  try {
    // First retrieve the permission from the API.
    $permission = $service->permissions->get($fileId, $permissionId);
    $permission->setRole($newRole);
    return $service->permissions->update($fileId, $permissionId, $permission);
  } catch (Exception $e) {
    print "An error occurred: " . $e->getMessage();
  }
  return NULL;
}
*/


