<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('../includes/dbfunctions.php');
require('../includes/user_auth.php');
session_start();

if(check_session())
{
    session_regenerate_id();
}

$user = get_userInfo($appdb);
$errors = array();

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    try
    {
        if(!isset($_POST['confirmation']))
        {
            throw new Exception("Please confirm that you want to delete the account");
        }
        
        $user_query = "SELECT * FROM users WHERE email = '".htmlspecialchars($user['email'])."' LIMIT 1";     
        $result = $appdb->query($user_query); //execute query
        $userdata = $result->fetch_assoc(); 

        $user_hash = $userdata['hash'];                                     // get current hash
        $input_hash = passHash($_POST['password'], $userdata['salt']); // password submitted for comparison
        
        if ($user_hash != $input_hash)
        {
            throw new Exception("The password given is incorrect");
        }
        
        $delete_query = "DELETE FROM users WHERE email = '".htmlspecialchars($user['email'])."' LIMIT 1";
        if(!$appdb->query($delete_query))
        {
            throw new Exception ("An unexpected error occurred, please try again.");
        }
        
        header("Location:http://acs.fsupanama.org/login.php?logout=true");
        exit(0);
    }
    catch (Exception $e)
    {
        $errors[] = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>User Settings - FSU-Panama Internal Application Control System</title>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="../assets/style.css" />
		<script src="../assets/queryupdater.js"></script>
	</head>
    <body>
        <?php 
        if (isset($errors) && $errors)
        {
            foreach ($errors as $error)
            {
                echo "<div id=\"error_message\" style=\"z-index:1;\">".htmlspecialchars($error)."</div>\n";
            }
        }
        ?>
        <script>
            setTimeout(function(){
            $('#success_message, #error_message').css("opacity","1");
            },100);
            
            setTimeout(function(){
            $('#success_message, #error_message').css("opacity","0");
            },5000);

            setTimeout(function(){
                $('#success_message, #error_message').remove();
            },5200);
        </script>
        <div id="title-bar">
            <a href="http://acs.fsupanama.org">
                <img src="../assets/fsu-seal.png" id="logo" />
                <h1 id="title">FSU-Panama Internal Application Control System</h1>
            </a>
            <?php getUserBox($appdb); ?>
		</div>
        <div class="title_bar">
            <h2>DELETE THIS ACCOUNT</h2>
        </div>
        <div class="settings_container">
            <p class="description">Are you absolutely sure you want to do this? All personal settings will be lost, and it cannot be
                undone</p>
            <form name="delete_acc" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <h3 style="margin: 15px 0;">Please input your current password to proceed</h3>
                <input class="text_input" type="password" name="password" style="margin-bottom:20px;" />
                <input type="checkbox" name="confirmation" style="float:left;" />
                <p style="margin-bottom: 25px;">Yes I want to delete this account.</p>
                <input class="button" type="submit" value="Delete Account" style="font-size:0.9em"/>
            </form>
        </div> 
     </body>