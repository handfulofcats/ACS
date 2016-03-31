<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('../includes/dbfunctions.php');
require('../includes/user_auth.php');
session_start();

if( !user_auth($appdb, array("admin","superadmin")) )
{
    header("Location:../index.php");
    exit();
}

$user = get_userInfo($appdb);
$errors = array();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>Manage Users - FSU-Panama Internal Application Control System</title>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="../assets/style.css" />
		<script src="../assets/queryupdater.js"></script>
        <script src="admin.js"></script>
	</head>
    <body>
        <div id="title-bar">
            <a href="http://acs.fsupanama.org">
                <img src="../assets/fsu-seal.png" id="logo" />
                <h1 id="title">FSU-Panama Internal Application Control System</h1>
            </a>
            <?php getUserBox($appdb); ?>
		</div>
        <div class="title_bar">
            <h2>MANAGE USERS</h2>
        </div>
        <div class="settings_container">
            <table id="user_table">
                <tr>
                    <th>Name</th>
                    <th>E-mail</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                <?php
                    $users = get_userList($appdb);
                    foreach($users as $user)
                    {
                        parseUser($user);
                    }
                ?>
            </table>
            <a href="new_user.php"><button class="button" style="font-size:0.8em">Create new user</button></a>
        </div>
    </body>
</html>