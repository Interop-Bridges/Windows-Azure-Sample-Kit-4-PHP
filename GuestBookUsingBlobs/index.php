<?php
/*
Copyright 2011 Microsoft Corporation

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
*/

// Include the Windows Azure SDK for PHP objects
require_once('Microsoft/AutoLoader.php');

// Setup some constant configuration values
define('DEV', true);
define('STORAGE_ACCOUNT', '<endpoint of your storage account>');
define('STORAGE_KEY', '<storage account key>');
define('BLOB_GUESTBOOK', 'guestbook');


// Setup the connection
if(DEV) {
    // Connect to local development storage
    $blob = new Microsoft_WindowsAzure_Storage_Blob();
} else {
    // Connect to Windows Azure Storage in the cloud
    $blob = new Microsoft_WindowsAzure_Storage_Blob(
                'blob.core.windows.net',
                STORAGE_ACCOUNT,
                STORAGE_KEY
            );
}

// Ensure the blob container exists
$blob->createcontainerIfNotExists(BLOB_GUESTBOOK);

// Set ACL
$blob->setContainerAcl(BLOB_GUESTBOOK, Microsoft_WindowsAzure_Storage_Blob::ACL_PUBLIC_CONTAINER);

// If the user submitted something put it into the blob storage
// NOTE: Inputs are not cleaned for example purposes
if(isset($_POST['submit'])) {
    $image = $blob->putBlob(BLOB_GUESTBOOK, $_FILES['Image']['name'], $_FILES['Image']['tmp_name']);
    
    
    $table = new Microsoft_WindowsAzure_Storage_Table();
    $writer = new Microsoft_WindowsAzure_Log_Writer_WindowsAzure(
            $table, 'logThis');
    $logger = new Microsoft_Log($writer);
    //$logger->addWriter($writer);
    $logger->log($_FILES['Image']['name'] . " added", 1);
}

if(isset($_POST['Update'])) {
    echo "<b>UPDATE NOT YET IMPLEMENTED</b>";
}

// User wishes to delete something
if(isset($_GET['Delete'])) {
    $blob->deleteBlob(BLOB_GUESTBOOK, $_GET['Delete']);
}

// Get all the guest book entries for display
$entries = $blob->listBlobs(BLOB_GUESTBOOK);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>Windows Azure Guestbook</title>
    <link href="main.css" rel="stylesheet" type="text/css" />
   
</head>
<body>
     <div class="general">
        <div class="title">
            <h1>
                Windows Azure Picture GuestBook
            </h1>
        </div>
        <div class="inputSection">
        <form id="gbForm" action="/index.php" method="post" enctype="multipart/form-data">
                <dl>

                <dt>
                    <span id="NameLabel">Image:</span></dt>
                <dd>
                    <input type="file" 
                       id="Image"
                       name="Image" 
                       class="field"/>
                </dd>
                                    
                <dt>Submit:</dt>
                <dd><input type="submit" name="submit"/></dd>

            </dl>
            <div class="inputSignSection">
                <img src="sign.png" align="bottom" alt="Sign Guestbook" />
            </div>
            </form>
        </div>
        
                
        <div id="theResults">
        
            <div ID="UpdatePanel" >
                <table id="gbEntryTable" cellspacing="0" border="0" style="border-collapse:collapse;">
                    <?php
                        foreach($entries AS $e) {
                            echo "\n<ul>";
                            echo "\n\t<li><img src=\"".$e->Url."\" title=\"".$e->Name."\"><br/><a href=\"?Delete=".$e->Name."\">Delete</a></li>";
                            echo "\n</ul>";
                        }
                    ?>
                </table>
            </div><!-- update panel -->

            
        </div>
            
        </div> 
    </div>
    </body>
</html>