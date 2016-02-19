<?php
include_once "../Custom/config.php";
session_start();
include_once "../examples/templates/base.php";
/************************************************
  Make an API request authenticated with a service account.
 ************************************************/
require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

if($client = login_client()) echo "\nLogged in OK\n";
else exit("Cannot login!");

$taxon = array("concept_id" => 173, "sciname" => "Gadus eli");

$service = new Google_Service_Fusiontables($client);
// list_tables($service); //exit;

// $table_info = create_fusion_table($service, $taxon);
// $tableID = $table_info->tableId; 

$tableID = "1tD7V7ZouZymwY7P0gHvBD8LWU_aWC7sXUASxMwX4";

// delete_table($service, $tableID); exit;


if($permission = update_permission($client, $tableID))
{
    echo "\nAction is permitted OK\n";
    $result = append_rows($service, $tableID);
    print_r($result);
}
else echo "\nAction not permitted!\n";

// append_rows($service, $tableID);
exit;

//=================================================================================

function append_rows($service, $tableID)
{
    /*
    $val = "{
                     'path': '/upload/fusiontables/v1/tables/' . $my_table . '/import',
                     'method': 'POST',
                     'params': {'uploadType': 'media'},
                     'headers' : {'Content-Type' : 'application/octet-stream'},
                     'body': 'cat1,9,\ncat2,18\n'
                   }";
    */
    $arr = array('uploadType'   => 'media', //possible values: "media" "multipart" "resumable"
                 'mimeType'     => 'application/octet-stream' ,
                 'delimiter'    => "\t",
                 'data'         => 'cat3' . "\t" . '11' . "\n" . 'cat5' . "\t" . '22'
                 // ,'isStrict'     => false
                 );

    $results = $service->table->importRows($tableID, $arr);
    print_r($results);
    exit;
    
}

function list_tables($service)
{
    $tables = $service->table->listTable();
    echo "\nNo. of tables: " . count($tables) . "\n";
    foreach ($tables as $table) echo "\n" . $table['name'] . " - " . $table['tableId'];
}

function delete_table($service, $tableID)
{
    $results = $service->table->delete($tableID);
    echo "\n--\n";
    print_r($results);
    echo "\n--\n";
    list_tables($service); //exit;
    
    exit;
}
function update_permission($client, $tableID)
{
    $permissionsService = new Google_Service_Drive($client);
    $permissionsService = $permissionsService->permissions;
    $permission = new Google_Service_Drive_Permission();
    // $permission = new Google_Service_Drive_Permissions_Resource();
    $permission->setRole('writer'); //Valid values are 'reader', 'commenter', 'writer', and 'owner'
    $permission->setType('anyone'); 
    $result = $permissionsService->create($tableID, $permission);
    return $result;
    /*
    Google_Service_Drive_Permission Object
    (   [internal_gapi_mappings:protected] => Array()
        [allowFileDiscovery] => 
        [displayName] => 
        [domain] => 
        [emailAddress] => 
        [id] => anyoneWithLink
        [kind] => drive#permission
        [photoLink] => 
        [role] => writer
        [type] => anyone
        [modelData:protected] => Array()
        [processed:protected] => Array()
    )
    */
}

function create_fusion_table($service, $taxon)
{
    $cols = '[{"kind": "fusiontables#column", "columnId": "1", "name": "catalogNumber", "type": "STRING"},
              {"kind": "fusiontables#column", "columnId": "2", "name": "latitude",      "type": "LOCATION"}]';
    $postBody = new Google_Service_Fusiontables_Table();
    $postBody->name         = $taxon['concept_id']; //"eli_tbl4";
    $postBody->isExportable = true;
    $postBody->columns      = json_decode($cols);
    $postBody->kind         = "fusiontables#table";
    $results = $service->table->insert($postBody, array());
    return $results;
    /*
    Google_Service_Fusiontables_Table Object
    (
        [collection_key:protected] => columns
        [internal_gapi_mappings:protected] => Array()
        [attribution] => 
        [attributionLink] => 
        [baseTableIds] => 
        [columnPropertiesJsonSchema] => 
        [columnsType:protected] => Google_Service_Fusiontables_Column
        [columnsDataType:protected] => array
        [description] => 
        [isExportable] => 1
        [kind] => fusiontables#table
        [name] => 173
        [sql] => 
        [tableId] => 1_ceoW2ndzIEcgBVz5-zOMiDIHWM-AVE-KPY4mJty
        [tablePropertiesJson] => 
        [tablePropertiesJsonSchema] => 
        [modelData:protected] => Array
            (
                [columns] => Array
                    (
                        [0] => Array
                            (
                                [kind] => fusiontables#column
                                [columnId] => 0
                                [name] => catalogNumber
                                [type] => STRING
                                [formatPattern] => NONE
                                [validateData] => 
                            )
                        [1] => Array
                            (
                                [kind] => fusiontables#column
                                [columnId] => 1
                                [name] => latitude
                                [type] => LOCATION
                                [formatPattern] => NONE
                                [validateData] => 
                            )
                    )
            )
        [processed:protected] => Array()
    )
    */
}

function login_client()
{
    $client_id              = YOUR_CLIENT_ID;       //Client ID
    $service_account_name   = SERVICE_ACCOUNT_NAME; //Email Address
    $key_file_location      = KEY_FILE_LOCATION;    //key.p12

    echo pageHeader("Service Account Access");
    if (strpos($client_id, "googleusercontent") == false || !strlen($service_account_name) || !strlen($key_file_location)) {
      echo missingServiceAccountDetailsWarning();
      exit;
    }

    $client = new Google_Client();
    $client->setApplicationName("Client_Library_Examples eli");
    $client->addScope("https://www.googleapis.com/auth/fusiontables", "https://www.googleapis.com/auth/fusiontables.readonly", "https://www.googleapis.com/auth/drive");

    /************************************************
      If we have an access token, we can carry on. Otherwise, we'll get one with the help of an assertion credential. In other examples the list of scopes was managed by the Client, but here
      we have to list them manually. We also supply the service account
     ************************************************/
    if (isset($_SESSION['service_token'])) $client->setAccessToken($_SESSION['service_token']);
    $key = file_get_contents($key_file_location);
    $cred = new Google_Auth_AssertionCredentials(
        $service_account_name,
        // array('https://www.googleapis.com/auth/fusiontables', 'https://www.googleapis.com/auth/fusiontables.readonly'),
        array('https://www.googleapis.com/auth/drive', 'https://www.googleapis.com/auth/drive.apps.readonly', 
              'https://www.googleapis.com/auth/drive.file',
              'https://www.googleapis.com/auth/fusiontables', 'https://www.googleapis.com/auth/fusiontables.readonly'),
        $key);
    $client->setAssertionCredentials($cred);
    if ($client->getAuth()->isAccessTokenExpired()) {$client->getAuth()->refreshTokenWithAssertion($cred);}
    $_SESSION['service_token'] = $client->getAccessToken();

    if($client->getAccessToken()) return $client;
    else return false;
    //************************************************
}

/* //working OK =========================== Updating permissions to Google files
$permissionsService = new Google_Service_Drive($client);
$permissionsService = $permissionsService->permissions;

$permission = new Google_Service_Drive_Permission();
// $permission = new Google_Service_Drive_Permissions_Resource();
$permission->setRole('reader'); 
$permission->setType('anyone'); 

$result = $permissionsService->create($my_table, $permission);
echo"<pre>";print_r($result);echo"</pre>";exit;
 =========================== */


/* //working OK  =========================== Showing File's metadata
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
