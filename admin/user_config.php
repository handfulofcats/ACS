<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('../includes/dbfunctions.php');
require('../includes/user_auth.php');
session_start();

// check if session has expired or something else is different;
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
        // check if name is changed 
        if ($_POST['name'] != $user['name'])
        {
            // truncate name to 300 characters
            $name = substr($_POST['name'], 0, 300);
            
            // check if username already exists
            $users = get_userList($appdb);
            if (in_array($name, $users))
            {
                throw new Exception("A user with that name already exists.");                  
            }                 
        }
        
        /*if ($_POST['email'] != $user['email'])
        {          
            //get email domain
            $domain = array_pop(explode('@', $_POST['email']));
            
            // Validate email address
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
            {
                throw new Exception("The provided e-mail address is not valid.");
            }
            
            //validate email domain
            if (!in_array($domain, $allowed_domains))
            {
                throw new Exception("Only fsu.edu emails are allowed.");
            }
            
            //check that user doesn't already exist
            $user_query = "SELECT * FROM users WHERE email = '".$_POST['email']."' LIMIT 1";    
            $result = $appdb->query($user_query); //execute query
            $user = $result->fetch_assoc();           
            if (!empty($user))
            {
                throw new Exception("A user with that email account already exists.");
            }
            $errors[] = "email changed " . $_POST['email'];
        }*/
        
        if($_POST['name'] != $user['name'])
        {
            // if everything's ok, update name
            // prepare mysqli statement
            if ( !$update_query = $appdb->prepare("UPDATE users SET name = ? WHERE (name = ? AND email = ?)") )
            {
                throw new Exception("Could not update your preferences. Error 01");
            }
            // bind parameters
            else if ( !$update_query->bind_param('sss', $_POST['name'], $user['name'], $user['email']) )
            {
                throw new Exception("Could not update your preferences. Error 02");
            }
            //execute query
            else if (!$update_query->execute())
            {
                throw new Exception("Could not update your preferences. Error 03");
            }
        }              
    }
    catch(Exception $e)
    {
        $errors[] = $e->getMessage();
    }
    //update user details
    $user = get_userInfo($appdb);
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
        <script src="admin.js"></script>
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
            <h2>USER SETTINGS FOR <?php echo htmlspecialchars(mb_strtoupper($user['name'], 'UTF-8')); ?></h2>
        </div> 
            <div class="settings_container">
                
                <div id="personal_settings">
                    <div class="setting_title">
                        <h2>Personal Settings</h2>
                    </div>
                    <div class="settings_area">
                        <form name="user_settings" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                            <h3>Name:</h3>
                            <input class="text_input" name="name" type="text" value="<?php echo htmlspecialchars($user['name']) ?>"/>
                            
                            <!--<h3>Email:</h3>
                            <p class="description">Has to be a fsu.edu email</p>
                            <input class="text_input" name="email" type="text" value="<?php //echo htmlspecialchars($user['email']) ?>"/>--> 
                            
                            <input type="submit" class="button" value="Save changes"/>                    
                        </form>
                    </div>             
                </div>
                <div id="security_settings">
                    <div class="setting_title">
                        <h2>Security Settings</h2>
                    </div>
                    <div class="settings_area">
                        <h3>Password</h3>
                        <button id="userconfig_changepass" class="button">Change password</button>
                    </div>
                </div>
                <div id="account_settings">
                    <div class="setting_title">
                        <h2>Account Settings</h2>
                    </div>
                    <div class="settings_area">
                        <h3>Delete account</h3>
                        <p class="description">All information and settings for this account will be lost. You will not be able to access ACS after this.</p>
                        <a href="delete_acc.php"><button class="button">Delete this account</button></a>
                    </div>
                </div>
                <?php
                    if( user_auth($appdb, array("admin","superadmin")) )
                    {
                        echo "<div id=\"account_settings\">
                                <div class=\"setting_title\">
                                    <h2>Admin Settings</h2>
                                </div>
                                <div class=\"settings_area\">
                                    <h3>Manage Users</h3>
                                    <p class=\"description\">Change roles, create and delete users</p>
                                    <a href=\"manage_users.php\"><button class=\"button\">Manage users</button></a>
                                </div>
                            </div>";
                    }
                ?>
            </div>
        
    </body>
</html>