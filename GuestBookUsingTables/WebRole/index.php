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
require_once('GuestBookEntry.class.php');

/*
 * Configuration values
 * NOTE: By default this sample is set to run in local development storage
 * 	 if you wish to run the sample on Windows Azure you must change
 *	 DEV to false and set your account and keys appropriately
 */
define('DEV', true);
define('STORAGE_ACCOUNT', '<endpoint of your storage account>');
define('STORAGE_KEY', '<storage account key>');
define('TABLE_GUESTBOOK', 'guestbook');


// Setup the connection
if(DEV) {
    // Connect to local development storage
    $table = new Microsoft_WindowsAzure_Storage_Table();
} else {
    // Connect to Windows Azure Storage in the cloud
    $table = new Microsoft_WindowsAzure_Storage_Table(
                'table.core.windows.net',
                STORAGE_ACCOUNT,
                STORAGE_KEY
            );
}

// Ensure the table exists
$table->createTableIfNotExists(TABLE_GUESTBOOK);



// If the user submitted something put it into the table storage
// NOTE: Inputs are not cleaned for example purposes
if(isset($_POST['NameTextBox']) && isset($_POST['MessageTextBox'])) {
    $g = new GuestBookEntry();
    
    $g->GuestName = $_POST['NameTextBox'];
    $g->Message = $_POST['MessageTextBox'];
    
    $table->insertEntity(TABLE_GUESTBOOK, $g);
}

if(isset($_POST['Update'])) {
    echo "<b>UPDATE NOT YET IMPLEMENTED</b>";
}

// User wishes to delete something
if(isset($_GET['Delete']) && isset($_GET['key'])) {
    $g = $table->retrieveEntityById(TABLE_GUESTBOOK, $_GET['Delete'], $_GET['key']);
    $table->deleteEntity(TABLE_GUESTBOOK, $g);
}

// Get all the guest book entries for display
$entries = $table->retrieveEntities(TABLE_GUESTBOOK);


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
                Windows Azure GuestBook
            </h1>
        </div>
        <div class="inputSection">
        <form id="gbForm" action="/index.php" method="post" enctype="multipart/form-data">
                <dl>

                <dt>
                    <span id="NameLabel">Name:</span></dt>
                <dd>
                    <input type="text" 
                       id="NameTextBox"
                       name="NameTextBox" 
                       class="field"/>
                </dd>
                <dt>
                    <span id="MessageLabel">Message:</span>
                </dt>

                <dd>
                    <textarea name="MessageTextBox" rows="2" cols="20" id="MessageTextBox" class="field" ></textarea>         
                </dd>
                    
                <dt>Submit:</dt>
                <dd><input type="submit"/></dd>

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
                            echo "\n<tr>";
                            echo "\n\t<td><strong>" . $e->GuestName . "</strong><br/><a href=\"?Delete=".$e->getPartitionKey()."&key=".$e->getRowKey()."\">Delete</a></td>";
                            echo "\n\t<td>" . $e->Message . "</td>";
                            echo "\n</tr>";
                        }
                    ?>
                </table>
            </div><!-- update panel -->

            
        </div>
            
        </div> 
    </div>
    </body>
</html>
