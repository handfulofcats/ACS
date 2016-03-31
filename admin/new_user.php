<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require('../includes/dbfunctions.php');
    require('../includes/user_auth.php');   
    
    session_start();
    /*if( !user_auth($appdb, array("admin","superadmin")) )
    {
        header("Location:../index.php");
        exit();
    }*/
    
    // Some vars
    $allowed_domains = Array("fsu.edu","admin.fsu.edu","my.fsu.edu");
    $allowed_usertypes = Array('admissions', 'pdp', 'accounting', 'housing', 'admin', 'superadmin');
    
    $errors = array();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {      
        try 
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
                throw new Exception("An user with that email account already exists.");
            }           
            //check password length
            if (strlen($_POST['password']) < 8 || strlen($_POST['password'] > 70))
            {
                throw new Exception("Passwords must be 8-70 characters long");
            }
            //check user rights
            if (!in_array($_POST['user_type'], $allowed_usertypes))
            {
                throw new Exception("That is not a valid type of user");
            }
        
            // Generate salt and hash
            $salt = mcrypt_create_iv(22, MCRYPT_DEV_URANDOM);
            $salt = base64_encode($salt);
            $salt = str_replace('+', '.', $salt);
            $hash = crypt($_POST['password'], '$2y$10$'.$salt.'$');
            
            // prepare query and insert into database
            if (!$create_user = $appdb->prepare("INSERT INTO users 
            (name, email, hash, salt, role) VALUES (?, ?, ?, ?, ?)") )
            {
                throw new Exception("There was an error creating the user. Please try again.");
            }                    
            else if (!$create_user->bind_param('sssss', $_POST['name'], $_POST['email'], $hash, $salt, $_POST['user_type']))
            { 
                throw new Exception("There was an error creating the user. Please try again.2"); 
            }
            
        } //end of try
        catch(Exception $e) {
            $errors[] = $e->getMessage();
        }
        
        if (!$errors)
        {
            if (!$create_user->execute())
            {
                header("HTTP/1.0 500 Internal Server Error"); //This is a non-friendly failure
                exit();
            }
            echo "<div id=\"success_message\" style=\"z-index:1;\">User ".$_POST['name']." successfully created.</div>\n";           
        }
        
    } // end POST
    
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
		<title>Create a new user - FSU-Panama Internal Application Control System</title>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="../assets/style.css" />
		<script src="../assets/queryupdater.js" type="text/javascript"></script>
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
			<img src="../assets/fsu-seal.png" id="logo" />
			<h1 id="title">FSU-Panama Internal Application Control System</h1>
            <?php getUserBox($appdb); ?>
		</div>
        <div class="title_bar">
            <h2>CREATE A NEW USER</h2>
        </div>
        <div id="login_form">           
            <form name="new_user" autocomplete="off" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <table id="form_table">
                    <tbody>
                        <tr>
                            <td class="label_cell">
                                <label>Name:</label>
                                <p class="description">First and Last name</p>
                            </td>
                            <td><input class="text_input" name="name" type="text" placeholder="John Doe" autocomplete="off"  value="<?php echo isset($_POST['name']) && ($errors) ? htmlspecialchars($_POST['name']) : ''; ?>" required /></td>
                        </tr>
                        <tr>
                            <td class="label_cell">
                                <label>E-mail address:</label>
                                <p class="description">Has to be a fsu.edu email</p>
                            </td>
                            <td><input class="text_input" name="email" type="email" autofocus placeholder="example@fsu.edu" autocomplete="on"  value="<?php echo isset($_POST['email']) && ($errors) ? htmlspecialchars($_POST['email']) : ''; ?>" required /></td>
                        </tr>
                        <tr>
                            <td class="label_cell">
                                <label>Password:</label>
                                <p class="description">Must be 8-70 characters long.</p>
                            </td>
                            <td><input class="text_input" name="password" type="password" autocomplete="off" required /></td>
                        </tr>
                        <tr>
                            <td class="label_cell">
                                <label>User Role:</label>
                                <p class="description">Determines what rights and functionality the user has access to</p>
                            </td>
                            <td>
                                <select class="select_input" name="user_type" required>
                                    <option value="">Select...</option>
                                    <option value="admissions">Admissions</option>
                                    <option value="pdp">Professional Development Program</option>
                                    <option value="accounting">Accounting</option>
                                    <option value="housing">Housing</option>
                                    <option value="admin">Administrator</option>                                    
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><input class="button" type="submit" value="Create User" /></td>
                        </tr>  
                    </tbody>
                </table>           
            </form>
        </div>
    </body>
</html>