<?php

/* Copyright (C) Andres Canelones 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Andrés Canelones <andrescanelones10@gmail.com>, January 2016
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('../includes/dbfunctions.php');
require('../includes/user_auth.php');

$func = $_POST['func'];

if ($func === "GetApplications")
	{
		$sortby = $_POST['sort'];
		$order = $_POST['order'];
		$filter = json_decode(stripslashes($_POST['filter']),true);
		GetApplications($filter, $sortby, $order, $appdb);
	}
else if ($func === "UpdateApplication")
	{
		$updateTarget = json_decode(stripslashes($_POST['updateTarget']),true);
		$newVal = $_POST['newVal'];
		$column = $_POST['column'];
		UpdateApplication($updateTarget, $column, $newVal, $appdb);
	}
else if ($func === "DownloadPackage")
    {
        $downloadTarget = Array( 'EMPLID'   => $_POST['EMPLID'],
                                 'term'     => $_POST['term'],
                                 'year'     => $_POST['year'] 
                               );
    
        DownloadPackage($downloadTarget, $appdb);
    }
else if ($func === "DisplayComments")
    {
        $target = json_decode(stripslashes($_POST['target_']),true);
        DisplayComments($target, $appdb);
    }
else if ($func === "UpdateComments")
    {
        $target = json_decode(stripslashes($_POST['target_']),true);
        $comment = $_POST['comment'];
        UpdateComments($target, $comment, $appdb);
    }
else if ($func === "DeleteApplication")
    {
        $target = json_decode(stripslashes($_POST['target_']),true);
        DeleteApplication($target, $appdb);
    }
else if ($func === "changePassword")
    {
        $pass_data = json_decode(stripslashes($_POST['pass_data']),true);
        changePassword($pass_data, $appdb);
    }
else if ($func === "UpdateRole")
    {
        $updateTarget = json_decode(stripslashes($_POST['updateTarget']),true);
        $newVal = $_POST['newVal'];
        UpdateRole($updateTarget, $newVal, $appdb);
    }
else if ($func === "DeleteUser")
    {
        $target = json_decode(stripslashes($_POST['target_']),true);
        DeleteUser($target,$appdb);
    }
else if ($func === "GetAdmissionProcessors")
    {
        GetAdmissionProcessors($appdb);
    }
?>

	
