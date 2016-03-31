<?php

/* Copyright (C) Andres Canelones 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Andrés Canelones <andrescanelones10@gmail.com>, January 2016
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

///////////////////////////
//////database login///////
///////////////////////////-------------------------------------------------------

$servername = "fsupanamaacs.db.10035968.hostedresource.com";
$username   = "fsupanamaacs";
$password   = "ePDfvJQk#nc@7!";
$dbname     = "fsupanamaacs";

// Create connection
$appdb = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($appdb->connect_error) {
	die("Connection failed: " . $appdb->connect_error);
}

$appdb->set_charset("utf8");

//---------------------------------------------------------------------------------

//---- DB columns and other sets (for reference and sanitizing) -------//

$valid_columns = array("EMPLID", "last_name", "first_name", "app_semester", "app_year", "date", "fee_paid", "status", "app_pack", "processor", "comments");
$valid_terms = array("Spring", "Summer A", "Summer B", "Fall");
$valid_status = array("admitted","denied","processing","not processed","pending documents","pending TOEFL");
$valid_fee_paid = array("paid", "not paid");

//---------------------------------------------------------------------//

function SanitizeTarget($target)
{
    global $valid_terms;
    
    //sanitize query
    try
    {
        //sanitize EMPLID
        if (!is_numeric($target['EMPLID']) || strlen($target['EMPLID']) > 9)
		{
			throw new Exception("Invalid EMPLID.");
		}
        //sanitize term
        else if ( !in_array($target['term'], $valid_terms) )
        {
            throw new Exception("invalid term.");
        }
        //sanitize year
        else if (!is_numeric($target['year']) || strlen($target['year']) > 4 || $target['year'] > 2999)
        {
            throw new Exception("Invalid year.");
        }
        //if everything's ok, return true
        else
        {
            return true;
        }
    }
    catch(Exception $e) {
		//echo $e->getMessage();
		header("HTTP/1.0 500 Internal Server Error");
        exit();
	}
}


function GetApplications($filter, $sort, $order, $conn)
{
	global $valid_columns, $valid_terms;
	
	//Sanitize limit
	try 
	{
		if (!is_numeric($filter['limit']) || $filter['limit'] > 200 || $filter['limit'] < 0)
		{
			throw new Exception("Invalid query. Showing 200 results");
		}
	}
	catch(Exception $e) {
		//echo $e->getMessage();
		$filter['limit'] = 200;
	}
	
	//Sanitize status
	try
	{
		switch($filter['status'])
		{
			case 0: //all applications
				$query = "SELECT * FROM applications WHERE (status = 'admitted' OR status = 'denied' 
				OR status = 'in process' OR status = 'not processed' OR status = 'pending documents'
				OR status = 'pending TOEFL') ";
				break;
				
			case 1: //only 'not_processed' applications
				$query = "SELECT * FROM applications WHERE status = 'admitted' ";
				break;
				
			case 2: //only 'processed' applications
				$query = "SELECT * FROM applications WHERE status = 'denied' ";
				break;
			
			case 3:
				$query = "SELECT * FROM applications WHERE status = 'in process' ";
				break;
			
			case 4:
				$query = "SELECT * FROM applications WHERE status = 'not processed' ";
				break;
			
			case 5:
				$query = "SELECT * FROM applications WHERE status = 'pending documents' ";
				break;
			
			case 6:
				$query = "SELECT * FROM applications WHERE status = 'pending TOEFL' ";
				break;
			
			default:
				throw new Exception("Invalid query. No filter.");
		}
	}
	catch(Exception $e) {
		//echo $e->getMessage();
		$query = "SELECT * FROM applications ";
	}
	
	//sanitize term
	try
	{
		if ($filter['term'] === "All")
		{ 
			//nothing to add to query 
		}
		else if (!in_array($filter['term'], $valid_terms))
		{
			throw new Exception("Invalid term. Showing all terms.");
		}
		else
		{
			$query .= "AND app_semester = '".$filter['term']."' ";
		}
	}
	catch(Exception $e) {
		//echo $e->getMessage();
		$filter['term'] = "All";
	}
	
	//sanitize year
	try 
	{
		if ($filter['year'] === "")
		{
			//nothing to add to query
		}
		else if (!is_numeric($filter['year']) || strlen($filter['year']) > 4 || $filter['year'] > 2999)
		{
			throw new Exception("Invalid query. Showing all years.");
		}
		else
		{
			$query .= "AND app_year = ".$filter['year']." ";
		}
	}
	catch(Exception $e) {
		//echo $e->getMessage();
		$filter['year'] = "";
	}
	
	try 
	{
		if ($filter['EMPLID'] === "")
		{
			//nothing to add to query
		}
		else if (!is_numeric($filter['EMPLID']) || strlen($filter['EMPLID']) > 9)
		{
			throw new Exception("Invalid EMPLID.");
		}
		else
		{
			$query .= "AND EMPLID = ".$filter['EMPLID']." ";
		}
	}
	catch(Exception $e) {
		//echo $e->getMessage();
		$filter['EMPLID'] = "";
	}
	
	//sanitize $sort
	try
	{
		if (!in_array($sort, $valid_columns))
		{
			throw new Exception("Invalid sort. Sorting by date.");
		}
	}
	catch(Exception $e) {
		//echo $e->getMessage();
		$sort = 'date';
	}
	
	//sanitize $order
	try
	{
		if($order != "ASC" && $order != "DESC")
		{
			throw new Exception("Invalid order. Ordering by Descending");
		}
	}
	catch(Exception $e) {
		//echo $e->getMessage();
		$order = "DESC";
	}
	
	$query .= "ORDER BY ".$sort." ".$order." LIMIT ".$filter['limit']; //complete query
	
	//echo $query;
	
	$result = $conn->query($query); //execute query
	
	//send data to parse
	while($row = $result->fetch_assoc())
	{
		ParseApplications($row);
	}
}

function ParseApplications($app)
{
	echo '<tr class="app_row" id="'.$app['EMPLID'].'">'
			.'<td class="bulk"><input type="checkbox" class="bulk_select" /></td>'
			.'<td class="EMPLID_cell"><p>'.$app['EMPLID'].'</p></td>'
			.'<td class="last_name_cell">'.$app['last_name'].'</td>'
			.'<td class="first_name_cell">'.$app['first_name'].'</td>'
			.'<td class="date_cell">'.$app['date'].'</td>'
			.'<td class="term_cell"><span>'.$app['app_semester'].'</span></td>'
			.'<td class="year_cell"><span>'.$app['app_year'].'</span></td>'
			.'<td class="fee_cell"><span>'.$app['fee_paid'].'<img src="assets/write.png" class="edit-icon" /></span></td>'
			.'<td class="status_cell"><span>'.$app['status'].'<img src="assets/write.png" class="edit-icon" /></span></td>'
			//.'<td class="app_pack_cell"><a href="#">Download</a></td>'
			.'<td class="processor_cell"><span>'.$app['processor'].'<img src="assets/write.png" class="edit-icon" /></span></td>'
            .'<td class="actions_cell">'
                .'<span class="actions_app_pack"><img src="assets/download2.png"  title="Download Application Package" class="action-icon" /></span>'
                .'<span class="actions_transcripts"><img src="assets/file-text.png" title="Add transcripts" class="action-icon" /></span>'
                .'<span class="actions_delete"><img src="assets/cross.png" title="Delete this application" class="action-icon" /></span>'
            .'</td>'
		.'</tr>';
}

function UpdateApplication ($updateTarget, $column, $newVal, $conn) 
{
	global $valid_columns, $valid_terms, $valid_status, $valid_fee_paid, $valid_processor;
    
	//sanitize query
    try
    {
        //sanitize column
        if ( !in_array($column, $valid_columns) )
        {
            throw new Exception("No such column");
        }
        //sanitize EMPLID
        else if (!is_numeric($updateTarget['EMPLID']) || strlen($updateTarget['EMPLID']) > 9)
		{
			throw new Exception("Invalid EMPLID.");
		}
        //sanitize term
        else if ( !in_array($updateTarget['term'], $valid_terms) )
        {
            throw new Exception("invalid term.");
        }
        //sanitize year
        else if (!is_numeric($updateTarget['year']) || strlen($updateTarget['year']) > 4 || $updateTarget['year'] > 2999)
        {
            throw new Exception("Invalid year.");
        }
        //if everything's ok, build and execute the query
        else
        {   
            // prepare mysqli statement
            if ( !$update_query = $conn->prepare("UPDATE applications SET ".$column." = ? 
            WHERE (EMPLID = ? AND app_semester = ? AND app_year = ? )") )
            {
                throw new Exception("prepare failed");
            }
            // bind parameters
            else if ( !$update_query->bind_param('ssss', $newVal, 
            $updateTarget['EMPLID'], $updateTarget['term'], $updateTarget['year']) )
            {
                throw new Exception("binding failed");
            }
            //execute query
            else if (!$update_query->execute())
            {
                throw new Exception("couldn't update");
            }
        }
    }
    catch(Exception $e) {
		//echo $e->getMessage();
		header("HTTP/1.0 500 Internal Server Error");
        exit();
	}
	
	// Retrieve updated row to refresh it on the table
	$refresh_query = "SELECT * FROM applications WHERE (EMPLID = ".$updateTarget['EMPLID']
											." AND app_semester = '".$updateTarget['term']
											."' AND app_year = ".$updateTarget['year']
											." ) LIMIT 1";
	
	$result = $conn->query($refresh_query); //execute query
	
	//send row to parse
	while($row = $result->fetch_assoc())
	{
		ParseApplications($row);
	}
}

function DownloadPackage ($downloadTarget, $conn)
{
    
    global $valid_terms;
    
    if (SanitizeTarget($downloadTarget))
    {
        $query = "SELECT * FROM applications WHERE 
            (EMPLID = ".$downloadTarget['EMPLID']
            ." AND app_semester = '".$downloadTarget['term']
            ."' AND app_year = ".$downloadTarget['year']
            ." ) LIMIT 1";
    }
    
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $file  = "../../dev2/application/".$row['app_pack'];

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

function DisplayComments ($target, $conn)
{
    
    if (SanitizeTarget($target))
    {
        $query = "SELECT * FROM applications WHERE 
        (EMPLID = ".$target['EMPLID']
        ." AND app_semester = '".$target['term']
        ."' AND app_year = ".$target['year']
        ." ) LIMIT 1";
    }
    
    
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    
    $comments = htmlspecialchars($row['comments']);
    
    echo '<td colspan="11">'
            .'<div id="app_details">'
                .'<h2>'.$row['first_name'].' '.$row['last_name'].'</h2>';
                
   
   if ($row['comments'] == "") //if no comments stored
   {
       echo '<p id="app_nocomment">No comments.</p>'
           .'<button id="edit_comments">Add comments</button>';
   }
   else
   {
       echo '<h3>Comments:</h3>'
           .'<p id="app_comment">'.nl2br($comments).'</p>'
           .'<button id="edit_comments">Edit comments</button>';
   }
   
   echo '</div></td>';
   
}

function UpdateComments($target, $comment, $conn) {
    
    if (SanitizeTarget($target))
    {
        try
        {
            // prepare mysqli statement
            if ( !$update_query = $conn->prepare("UPDATE applications SET comments = ?
            WHERE (EMPLID = ? AND app_semester = ? AND app_year = ? )") )
            {
                throw new Exception("prepare failed");
            }
            // bind parameters
            else if ( !$update_query->bind_param('ssss', $comment, $target['EMPLID'], $target['term'], $target['year']) )
            {
                throw new Exception("binding failed");
            }
            //execute query
            else if (!$update_query->execute())
            {
                throw new Exception("couldn't update");
            }
        }
        catch(Exception $e) 
        {
            //echo $e->getMessage();
            header("HTTP/1.0 500 Internal Server Error");
            exit();
	    }
    }
}

function DeleteApplication($target, $conn)
{
    if (SanitizeTarget($target))
    {
        $query = "DELETE FROM applications WHERE (EMPLID = ".$target['EMPLID']." AND app_semester = '".$target['term']."' AND app_year = ".$target['year']." ) LIMIT 1";
        
        echo $query;
                                                      
        $conn->query($query);
    }
}



?>
