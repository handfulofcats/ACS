<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require('includes/dbfunctions.php');   
    
    session_start();
    $session_time = 60 * 120;
    $fingerprint = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
    
    //check if user has been logged out by the system, if so, clear session
    if (isset($_GET['logout']) && $_GET['logout'] == 'true')
    {
        $errors = Array();
        $errors[] = "You've been logged out.";
        setcookie(session_name(), '', time()-3600, '/');
        session_destroy();
    }   
    // check if user is already logged in  
    else if(    (isset($_SESSION['last_active']) && $_SESSION['last_active'] > (time() - $session_time))
             && (isset($_SESSION['fingerprint']) && $_SESSION['fingerprint'] == $fingerprint)
           )
    {
        //allow user into ACS
        header("Location:index.php");
        exit();
    }   
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $errors = Array();
        
        try
        {               
            // Validate email address
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
            {
                throw new Exception ("The provided e-mail address is not valid.");
            }

            $user_query = "SELECT * FROM users WHERE email = '".$_POST['email']."' LIMIT 1";     
            $result = $appdb->query($user_query); //execute query
            $user = $result->fetch_assoc();            

            if (empty($user))
            {
                throw new Exception ("The user or password is incorrect");
            }
            else
            {
                $pass_input = crypt($_POST['password'], '$2y$10$'.$user['salt'].'$'); //hashes given password with salt
                
                if (strcmp($user['hash'], $pass_input) != 0)
                {
                    throw new Exception ("The user or password is incorrect");
                }                
            }
        }
        catch(Exception $e)
        {
            $errors[] = $e->getMessage();
        }
        
        if(!$errors)
        {
            // Open session and store login properties
            $_SESSION['user'] = $_POST['email'];
            $_SESSION['last_active'] = time();
            $_SESSION['fingerprint'] = md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
            
            //allow user into ACS
            header("Location:index.php");
            exit();
        }
        
    }
    
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
		<title>Log In - FSU-Panama Internal Application Control System</title>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
		<link rel="stylesheet" type="text/css" href="assets/style.css" />
		<script src="assets/queryupdater.js"></script>
    </head>
    <body>
        <?php 
        if (isset($errors) && $errors)
        {
            foreach ($errors as $error)
            {
                echo "<div id=\"warning_message\" style=\"z-index:1;\">".htmlspecialchars($error)."</div>\n";
            }
        }
        ?>
        <script>
            setTimeout(function(){
            $('#success_message, #warning_message').css("opacity","1");
            },100);
            
            setTimeout(function(){
            $('#success_message, #warning_message').css("opacity","0");
            },5000);

            setTimeout(function(){
                $('#success_message, #warning_message').remove();
            },5200);
        </script>
        <div id="title-bar">
			<img src="assets/fsu-seal.png" id="logo" />
			<h1 id="title">FSU-Panama Internal Application Control System</h1>
		</div>
        <div class="title_bar">
            <h2>LOGIN USING YOUR FSU.EDU EMAIL</h2>
        </div>
        <div id="login_form">           
            <form name="log-in" autocomplete="on" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <table id="form_table">
                    <tbody>
                        <tr>
                            <td class="label_cell"><label>E-mail address:</label></td>
                            <td><input class="text_input" name="email" type="email" autofocus placeholder="example@fsu.edu" autocomplete="on"  value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required /></td>
                        </tr>
                        <tr>
                            <td class="label_cell"><label>Password:</label></td>
                            <td><input class="text_input" name="password" type="password" autocomplete="off" required /></td>
                        </tr>
                        <tr>
                            <td><input class="button" type="submit" value="Log In" /></td>
                        </tr>  
                    </tbody>
                </table>           
            </form>
        </div>
    </body>
</html>