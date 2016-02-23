<?php
include_once "../Custom/config.php";
session_start();
include_once "../examples/templates/base.php";
require_once realpath(dirname(__FILE__) . '/../src/Google/autoload.php');

if($client = login_client()) echo "\nLogged in OK\n";
else exit("Cannot login!");

$taxon = array("concept_id" => 1048643, "sciname" => "Phalacrocorax penicillatus");
$taxon = array("concept_id" => 206692, "sciname" => "Gadus morhua Linnaeus, 1758");

$service = new Google_Service_Fusiontables($client);
// list_tables($service); return;

// delete_table($service, "153xFKz6jTMlkWQF0eljeDkc2olJ0DbsUyILXwmie"); list_tables($service); return;

// /* //Updating templates...
$tableID = "1TspfLoWk5Vee6PHP78g09vwYtmNoeMIBgvt6Keiq";
$tableID = "1XqplhcfZgYPFel9FIT6T0S5WTclNPIElOH4IAAKq"; //Gadus morhua
$templateId = 8;
update_template($service, $tableID, $templateId);
return;
// */

/* //delete a list of tables
$ids = array("1onEZfLtSHdlElNP8EvAZrx-dsScJmotY1kSEk3UR", "1O3yqE1j-ryGDsfnFa7Q7aS3Mlk9HVsbYDoERZa7Y",
             "1aqzzOgYTvDBdOztZdVSZcKTsbm9fR--pe4WIcv9s", "1Knzolalnq4gJpZsQkv_Ybgk_NovBZBpEULHhi1EL");
foreach($ids as $tableID) delete_table($service, $tableID);
list_tables($service);
return;
*/


$table_info = create_fusion_table($service, $taxon);
$tableID = $table_info->tableId; 

insert_template($tableID, $service); //exit;

if($permission = update_permission($client, $tableID))
{
    echo "\nAction is permitted OK\n";
    append_rows($service, $tableID, $taxon);
}
else echo "\nAction not permitted!\n";

return; //terminate program
//=================================================================================
//=================================================================================
function prepare_data($taxon_concept_id)
{
    $txtFile = "../../eol_php_code/public/tmp/google_maps/fusion/" . $taxon_concept_id . ".txt";
    $file_array = file($txtFile);
    unset($file_array[0]); //remove first line, the headers
    return $file_array;
    // echo "\n" . implode("", $file_array) . "\n";
    // return implode("", $file_array);
    // file_put_contents("outfile.txt", implode("", $file_array));
}

function insert_template($tableID, $service)
{
    $postBody = new Google_Service_Fusiontables_Template();
    $postBody->body = get_template_body();
    $result = $service->template->insert($tableID, $postBody); //working OK
    
    // $result = $service->template->delete($tableID, 5);    //working OK
    // $result = $service->template->delete($tableID, 7);    //working OK
    $result = $service->template->listTemplate($tableID);
    print_r($result);
}

function append_rows($service, $tableID, $taxon)
{
    $rows = prepare_data($taxon['concept_id']);
    
    //append batches of 10k
    $i = 0; $partial = array(); //initialize
    foreach($rows as $row) //$row is String not array
    {
        if(number_of_cols($row) != 11) continue; //exclude row if total no. of cols is not 11, if not it will cause Fatal error "(400) Content has a different number of columns than the table".
        $row = str_replace('"', "'", $row);      //need to do this to avoid "Parsing failure. Quotation mark found in unquoted value"
        
        $partial[] = $row;
        $i++;
        if(($i % 10000) == 0)   //batches of 10K
        {
            echo "\n[$i]\n";
            insert_rows($partial, $tableID, $service);
            echo "...sleep 20 secs...\n"; sleep(20);
            $i = 0; $partial = array(); //initialize again...
        }
    }
    if($partial) insert_rows($partial, $tableID, $service); //insert last batch
}

function number_of_cols($row)
{
    $cols = explode("\t", $row);
    return count($cols);
}

function insert_rows($data, $tableID, $service)
{
    $data = implode("", $data); //convert array to string;
    $arr = array('uploadType'   => 'media', //possible values: "media" "multipart" "resumable", but only 'media' works for our purpose
                 'mimeType'     => 'application/octet-stream' ,
                 'delimiter'    => "\t",
                 'data'         => $data    //sample data: 'cat3' . "\t" . '11' . "\n" . 'cat5' . "\t" . '22'
                 ,'isStrict'    => true    //false
                 );
    if($result = $service->table->importRows($tableID, $arr)) echo "\nNo. of rows received: " . $result->numRowsReceived . "\n";
}

function list_tables($service)
{
    $tables = $service->table->listTable();
    echo "\nNo. of tables: " . count($tables) . "\n";
    foreach ($tables as $table)
    {
        echo "\n" . $table['name'] . " - " . $table['tableId'] . " = " . total_records($table['tableId'], $service);
    }
}

function total_records($tableID, $service)
{
    $result = $service->query->sql("SELECT count('*') FROM $tableID");  //working OK
    // echo "<pre>";print_r($result);echo "</pre>";
    return $result->rows[0][0];
}
function delete_table($service, $tableID)
{
    $result = $service->table->delete($tableID);
    echo "\n--";
    echo "\n[$result]";
    echo "\n--\n";
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
              {"kind": "fusiontables#column", "columnId": "2", "name": "sciname",       "type": "STRING"},
              {"kind": "fusiontables#column", "columnId": "2", "name": "publisher",     "type": "STRING"},
              {"kind": "fusiontables#column", "columnId": "2", "name": "publisher_id",  "type": "STRING"},
              {"kind": "fusiontables#column", "columnId": "2", "name": "dataset",       "type": "STRING"},
              {"kind": "fusiontables#column", "columnId": "2", "name": "dataset_id",    "type": "STRING"},
              {"kind": "fusiontables#column", "columnId": "2", "name": "gbifID",        "type": "STRING"},
              {"kind": "fusiontables#column", "columnId": "2", "name": "recordedBy",    "type": "STRING"},
              {"kind": "fusiontables#column", "columnId": "2", "name": "identifiedBy",  "type": "STRING"},
              {"kind": "fusiontables#column", "columnId": "2", "name": "pic_url",       "type": "STRING"},
              {"kind": "fusiontables#column", "columnId": "2", "name": "location",      "type": "LOCATION"}
              ]';
    $postBody = new Google_Service_Fusiontables_Table();
    $postBody->name         = $taxon['concept_id']; //"eli_tbl4";
    $postBody->isExportable = true;
    $postBody->columns      = json_decode($cols);
    $postBody->kind         = "fusiontables#table";
    $results = $service->table->insert($postBody, array());
    echo "\nNew table created OK: " . $results->tableId . "\n";
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

function update_template($service, $tableID, $templateId)
{
    $result = $service->template->listTemplate($tableID);
    print_r($result);

    $postBody = new Google_Service_Fusiontables_Template();
    $postBody->body = get_template_body();

    $result = $service->template->update($tableID, $templateId, $postBody); //working OK
    print_r($result);
}

function get_template_body()
{
    return '{template .contents}
    <div class="googft-info-window" style="{if $data.value.pic_url}height: 300px;{/if} overflow-y: auto">
    <h3>{$data.value.sciname}</h3>
    {if $data.value.pic_url}<img src="{$data.value.pic_url}" style="vertical-align: top; height: 15px"/>{/if}
    <b>Catalog number:</b>  {$data.value.catalogNumber}<br/>
    <b>Source portal:</b>   <a href="http://www.gbif.org/occurrence/{$data.value.gbifID}" target="_blank">GBIF</a><br/>
    <b>Publisher:</b>       <a href="http://www.gbif.org/publisher/{$data.value.publisher_id}" target="_blank">{$data.value.publisher}</a><br/>
    <b>Dataset:</b>         <a href="http://www.gbif.org/dataset/{$data.value.dataset_id}" target="_blank">{$data.value.dataset}</a><br/>
    {if $data.value.recordedBy}<b>Recorded by:</b>      {$data.value.recordedBy}<br/>{/if}
    {if $data.value.identifiedBy}<b>Identified by:</b>  {$data.value.identifiedBy}<br/>{/if}
    </div>
    {/template}';
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
