<?php
/* Copyright (C) Andres Canelones 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Andrés Canelones <andrescanelones10@gmail.com>, January 2016
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require("dbfunctions.php");

$allowed_domains = Array("fsu.edu","admin.fsu.edu","my.fsu.edu");
$allowed_usertypes = Array('admissions', 'pdp', 'accounting', 'housing', 'admin', 'superadmin');

function check_session() {
    $url = $_SERVER['HTTP_HOST'];
    $fingerprint = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);    // get fingerprint
    $session_time = 60 * 120;   // allowed session time. 2 hours.
    
    // If a session already exists...
    if (isset($_SESSION['last_active']) && isset($_SESSION['fingerprint']))
    {
        // check if session has expired, fingerprint doesn't match or email is not valid
        if ( ($_SESSION['last_active'] < (time() - $session_time)) || ($_SESSION['fingerprint'] != $fingerprint) || !filter_var($_SESSION['user'], FILTER_VALIDATE_EMAIL))
        {
            // log user out
            header("Location:http://".$url."/login.php?logout=true");
            exit();
        }
        else
        {
            // renew the session
            $_SESSION['last_active'] = time();
            $_SESSION['fingerprint'] = $fingerprint;
            return true;
        }
    }
    // else send user to login; In reality, this should almost never occur.
    else
    {
        header("Location:http://".$url."/login.php");
        exit();
    }
}

function user_auth($conn, $user_role) {

    check_session();    //if there is a problem with the session, execution will stop, otherwise session will renovate
    
    $user_query = "SELECT * FROM users WHERE email = '".$_SESSION['user']."' LIMIT 1";     
    $result = $conn->query($user_query); //execute query
    $user = $result->fetch_assoc();   
    
    if (in_array($user['role'], $user_role))
    {
        return true;
    }
    else
    {
        return false;
    }
}

function getUserBox($conn) {
    $url = $_SERVER['HTTP_HOST'];
    $user_query = "SELECT * FROM users WHERE email = '".$_SESSION['user']."' LIMIT 1";     
    $result = $conn->query($user_query); //execute query
    $user = $result->fetch_assoc();   
    
    echo "<div id=\"user_box\">
            <span id=\"user_name\">".htmlspecialchars($user['name'])."</span>        
            <a href=\"http://".$url."/login.php?logout=true\"><img title=\"Log Out\" src=\"http://".$url."/assets/exit.png\" /></a>
            <a href=\"/admin/user_config.php\"><img id=\"settings\" title=\"Settings\" src=\"http://".$url."/assets/cog.png\" /></a>
        </div>";
}

function get_userInfo($conn)
{
        check_session();
        $user_query = "SELECT * FROM users WHERE email = '".$_SESSION['user']."' LIMIT 1";     
        $result = $conn->query($user_query); //execute query
        $user = $result->fetch_assoc(); 

        return array('name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']);           
}

function get_userList($conn)
{
    if (check_session() == true)
    {
        $user_query = "SELECT name, email, role FROM users";     
        $result = $conn->query($user_query); //execute query
        $users = array();
        while ($user = $result->fetch_assoc())
        {
            if ($user['role'] == 'superadmin')
            {
                //..do nothing..
            }
            else
            {
                $users[] = $user;               
            }
        }
        return $users;
    }
}

function GetAdmissionProcessors($conn)
{
    session_start();    //will only be called from ajax requests
    
    $user_query = "SELECT name FROM users WHERE (role = 'admissions' OR role = 'admin')";     
    $result = $conn->query($user_query); //execute query
    $users = array();
    
    while ($user = $result->fetch_assoc())
    {
       $users[] = $user['name'];
    }
    
    echo json_encode($users);   
}

function parseUser($user)
{
        echo "<tr class=\"user_row\">
                <td class=\"user_name\">".$user['name']."</td>
                <td class=\"user_email\">".$user['email']."</td>
                <td class=\"user_role\"><span>".$user['role']."<img src=\"../assets/write.png\" class=\"edit-icon\" /></span></td>
                <td class=\"user_actions\"><span class=\"actions_delete\"><img class=\"action-icon\" src=\"../assets/cross.png\"/></span></td>
              </tr>";
}

function UpdateRole ($updateTarget, $newVal, $conn) 
{
    session_start();
    //get array of user emails
    $users = get_userList($conn);
    $useremails = array();
    foreach($users as $user)
    {
        $useremails[] = $user['email'];
    }
    
    
	//sanitize query
    try
    {
        if (!in_array($updateTarget['email'], $useremails))
        {
            throw new Exception("That is not a valid user");
        }
        //if everything's ok, build and execute the query
        else
        {   
            // prepare mysqli statement
            if ( !$update_query = $conn->prepare("UPDATE users SET role = ? 
            WHERE email = ?") )
            {
                throw new Exception("prepare failed");
            }
            // bind parameters
            else if ( !$update_query->bind_param('ss', $newVal, $updateTarget['email']) )
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
	$refresh_query = "SELECT * FROM users WHERE email = '".htmlspecialchars($updateTarget['email'])."' LIMIT 1";
	echo $refresh_query;
	$result = $conn->query($refresh_query); //execute query
	echo var_dump($result);
	//send row to parse
	while($row = $result->fetch_assoc())
	{
		ParseUser($row);
	}
}

function DeleteUser($target, $conn)
{
    session_start();    //this function will always be called from a Ajax request, so we need to start the session
    
    //get array of user emails
    $users = get_userList($conn);
    $useremails = array();
    foreach($users as $user)
    {
        $useremails[] = $user['email'];
    }
    
    try
    {
        if (!in_array($target['email'], $useremails))
        {
            throw new Exception("This is not a valid user");
        }
        
        $delete_query = "DELETE FROM users WHERE email = '".htmlspecialchars($target['email'])."' LIMIT 1";
        if(!$conn->query($delete_query))
        {
            throw new Exception ("An unexpected error occurred, please try again.");
        }
    }
    catch (Exception $e)
    {
		header("HTTP/1.0 500 Internal Server Error");
        exit();        
    }
}

function ChangePassword($pass_data, $conn)
{
    session_start();
    $user_query = "SELECT * FROM users WHERE email = '".htmlspecialchars($_SESSION['user'])."' LIMIT 1";     
    $result = $conn->query($user_query); //execute query
    $user = $result->fetch_assoc(); 
    
    $user_hash = $user['hash'];                                     // get current hash
    $input_hash = passHash($pass_data['curr_pass'], $user['salt']); // password submitted password for comparison
    
    try 
    {
        if ($user_hash != $input_hash)
        {
            throw new Exception("The password given is incorrect", 0);
        }
        else if (strlen($pass_data['new_pass']) < 8 || strlen($pass_data['new_pass']) > 70)
        {
            throw new Exception("The new password length is invalid. Passwords must be between 8-70 characters", 1);
        }
        else if ($pass_data['new_pass'] != $pass_data['confirm_pass'])
        {
            throw new Exception("Make sure to write the new password correctly on the two fields", 2);
        }
    }
    catch(Exception $e)
    {
        $result = array("message" => $e->getMessage(), "type" => "error_message", "code" => $e->getCode());
        echo json_encode($result);
        return;
    }
    
    $new_pass = newPassHash($pass_data['new_pass']);
    
    try
    {
        // prepare mysqli statement
        if ( !$update_query = $conn->prepare("UPDATE users SET hash = ?, salt = ? WHERE email = ? ") )
        {
            throw new Exception("prepare failed");
        }
        // bind parameters
        else if ( !$update_query->bind_param('sss', $new_pass['hash'], $new_pass['salt'], $user['email']) )
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
     
     echo json_encode(array("message" => "The password has been changed correctly.", "type" => "success_message"));
    
}
// for hash comparisons
function passHash($password, $salt) {

    return crypt($password, '$2y$10$'.$salt.'$');
}

// for hashing new passwords
function newPassHash($password) {

    $salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
    $salt = base64_encode($salt);
    $salt = str_replace('+', '.', $salt);
    $hash = crypt($password, '$2y$10$'.$salt.'$');
    
    $pass_data = Array('hash' => $hash, 'salt' => $salt);
    return $pass_data;
}

?>