<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    require('includes/generatepdf.php');
    require('includes/dbconnect.php');
    require('includes/emailApp.php');
    require('../wp-blog-header.php');
    get_header();
    
    session_start();
    
    global $appdb; //gets database connection variable
    
    
    /////////////////////////////////////
    ///////arrays and other values///////
    /////////////////////////////////////-----------------------------------------//
    
    // function for deleting directories
    function Delete($path)
    {
        if (is_dir($path) === true)
        {
            $files = array_diff(scandir($path), array('.', '..'));

            foreach ($files as $file)
            {
                Delete(realpath($path) . '/' . $file);
            }

            return rmdir($path);
        }

        else if (is_file($path) === true)
        {
            return unlink($path);
        }

        return false;
    }
    
    $valid_gender = array("Male", "Female");
    
    $valid_yesno = array("yes","no"); //hurr
    
    $valid_terms = array("Spring", "Summer A", "Summer B", "Fall");
    
    $valid_months = array("00","01","02","03","04","05","06","07","08","09","10","11","12");
    
    $races = array("","native","black","pacific","asian","white");
    
    $countries = array("Afghanistan", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua and Barbuda", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia and Herzegowina", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, the Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia (Hrvatska)", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "France Metropolitan", "French Guiana", "French Polynesia", "French Southern Territories", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard and Mc Donald Islands", "Holy See (Vatican City State)", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, Democratic People's Republic of", "Korea, Republic of", "Kuwait", "Kyrgyzstan", "Lao, People's Democratic Republic", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macau", "Macedonia, The Former Yugoslav Republic of", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "Mexico", "Micronesia, Federated States of", "Moldova, Republic of", "Monaco", "Mongolia", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Seychelles", "Sierra Leone", "Singapore", "Slovakia (Slovak Republic)", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia and the South Sandwich Islands", "Spain", "Sri Lanka", "St. Helena", "St. Pierre and Miquelon", "Sudan", "Suriname", "Svalbard and Jan Mayen Islands", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan, Province of China", "Tajikistan", "Tanzania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Turks and Caicos Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (U.S.)", "Wallis and Futuna Islands", "Western Sahara", "Yemen", "Yugoslavia", "Zambia", "Zimbabwe");
    
    //-------------------------------------
    
    // if EMPLID hasn't been set, kick user to application start
    if ( !isset($_SESSION['emplid']) )
    {
        header("location:http://panama.fsu.edu/apply/application-start/");
        exit(0);
    }
    
    //get EMPLID
    $emplid = $_SESSION['emplid'];
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        
        if (isset($errors))
        {
            echo "unsetting errors";
            unset($errors);
        }
        
        $errors = array();
        
        ///////////////////////////////////////////
        ////data sanitizing and validation/////////
        ///////////////////////////////////////////-----------------------------------//
        
        // Input type groupings:
        
            // Month inputs
            $month_inputs = Array(
            $_POST['b-month'], 
            $_POST['sec0-from-month'], $_POST['sec0-to-month'], $_POST['sec0-degree-month'], 
            $_POST['sec1-from-month'], $_POST['sec1-to-month'], $_POST['sec1-degree-month'],
            $_POST['postsec0-from-month'], $_POST['postsec0-to-month'], $_POST['postsec0-degree-month'],
            $_POST['postsec1-from-month'], $_POST['postsec1-to-month'], $_POST['postsec1-degree-month'],
            $_POST['postsec2-from-month'], $_POST['postsec2-to-month'], $_POST['postsec2-degree-month'],
            $_POST['postsec3-from-month'], $_POST['postsec3-to-month'], $_POST['postsec3-degree-month'],
            $_POST['employment0-from-month'], $_POST['employment0-to-month'],
            $_POST['employment1-from-month'], $_POST['employment1-to-month'],
            $_POST['employment2-from-month'], $_POST['employment2-to-month'],
            $_POST['employment3-from-month'], $_POST['employment3-to-month'] );
            
            // Yes/No radio buttons
            $yesno_inputs = Array(
            $_POST['latino'], $_POST['applied-before'], $_POST['enrolled-before'], $_POST['enrolled-currently'],
            $_POST['ant-1'], $_POST['ant-2'], $_POST['ant-3']
            );
            
            // Country inputs
            $country_inputs = Array(
            $_POST['country'], $_POST['m-country'], $_POST['b-country'], $_POST['citizenship'],
            $_POST['sec0-country'], $_POST['sec1-country'], $_POST['postsec0-country'], $_POST['postsec1-country'],
            $_POST['postsec2-country'], $_POST['postsec3-country']
            );
            
            // Year inputs
            $year_inputs = Array(
            $_POST['b-year'], $_POST['sec0-from-year'], $_POST['sec0-to-year'], $_POST['sec0-degree-year'], 
            $_POST['sec1-from-year'], $_POST['sec1-to-year'], $_POST['sec1-degree-year'],
            $_POST['postsec0-from-year'], $_POST['postsec0-to-year'], $_POST['postsec0-degree-year'],
            $_POST['postsec1-from-year'], $_POST['postsec1-to-year'], $_POST['postsec1-degree-year'],
            $_POST['postsec2-from-year'], $_POST['postsec2-to-year'], $_POST['postsec2-degree-year'],
            $_POST['postsec3-from-year'], $_POST['postsec3-to-year'], $_POST['postsec3-degree-year'], 
            $_POST['employment0-from-year'], $_POST['employment0-to-year'], 
            $_POST['employment1-from-year'], $_POST['employment1-to-year'], 
            $_POST['employment2-from-year'], $_POST['employment2-to-year'], 
            $_POST['employment3-from-year'], $_POST['employment3-to-year'] );
        
        // Truncate really long strings to 500 characters
        
        /*foreach ($_POST as $key => $value)
        {
            if (is_string($value) && strlen($value) > 500)
            {
                $value = substr($value, 0, 500);
            }
        }*/

        //checking yes/no radio buttons
        foreach($yesno_inputs as $input)
        {
            if (isset($input) && $input != "")
            {
                if (!in_array($input, $valid_yesno))
                {
                    $errors[] = "Invalid input. Please try again.\n";
                }
            }
        }
        
        // Validate gender
        if ( !in_array($_POST['gender'], $valid_gender) )
        {
            $errors[] = "This is not a valid gender option.\n";
        }
        
        // Validate year
        foreach ($year_inputs as $input)
        {
            if ($input != "" && (!is_numeric($input) || strlen($input) > 4 || $input > 2999))
            {
                $errors[] = "invalid year.\n";
                break;
            }
        }
        
        // Validate months
        foreach ($month_inputs as $input)
        {
            if ($input != "" && !in_array($input, $valid_months))
            {
                $errors[] = "invalid month.\n";
                break;
            }
        }
        
        // Validate countries
        foreach ($country_inputs as $input)
        {
            if ($input != "" && !in_array($input, $countries))
            {
                $errors[] = "Not a valid country.\n";
            }
        }
        
        // Validate day of birth
        if (!is_numeric($_POST['b-day']) || $_POST['b-day'] > 31)
        {
            $errors[] = "Not a valid day of birth.\n";
        }
        
        // Validate email address
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
        {
            $errors[] = "The provided e-mail address is not valid.\n";
        }
        
        // Validate phone number
        if (!is_numeric($_POST['t-acode']) || strlen($_POST['t-acode']) > 4 || !is_numeric($_POST['t-number']))
        {
            $errors[] = "Please correct the provided telephone number.";
        }
        
        // Validate Application Term 
        if ( !in_array($_POST['application-term'], $valid_terms) )
        {
            $errors[] = "The selected term is not valid. Please try again.\n";
        }
        
        // Validate Application Year
        if (!is_numeric($_POST['application-year']) || strlen($_POST['application-year']) > 4 || $_POST['application-year'] < date("Y") || $_POST['application-year'] > 2999)
        {
            $errors[] = "The year indicated is not valid. Please Indicate a valid year.\n";
        }
            
        // Validate race
        if (!empty($_POST['race']))
        {
            foreach ($_POST['race'] as $val)
            {
                if (!in_array($val, $races))
                {
                    $errors[] = "not a valid race value.\n";
                    break;
                }
            }
        }    
        
        ////////////////////////////
        ////transcript procesing////
        ////////////////////////////----------------------------------------------//
        
        //generate directory for application in server
        $upload_folder = "applications/".$emplid; 
        if ( !is_dir($upload_folder) ) {
            mkdir($upload_folder, 0755, TRUE);
        }
        
        //create zip file
        $zip = new ZipArchive;
        $zipname = $upload_folder."/".$emplid.".zip";
        $res = $zip->open( $zipname, ZipArchive::CREATE );
        
        //check each file      
        try
        {
            $correct_files = array();   // array for containing files that pass checks
            
            foreach ($_FILES as $file)
            {
                $file_size = $file['size']/1024;                // size in KBs
                $maxsize = 1000;
                $fileinfo = pathinfo($file_name);               // get file extension    
                            
                if ($file['name'] == "")
                {
                    //do nothing
                }
                // check file extension
                else if ($file['type'] != "application/pdf")
                {
                    throw new Exception("The file \"" . $file['name'] . "\" is not a .pdf file\n");
                }               
                // check file size
                else if ($file_size > $maxsize)
                {
                    throw new Exception("The file \"" . $file['name'] . "\" exceeds file size limit\n");
                }
                else
                {
                    $upload_path = $upload_folder."/".$file['name'];   // build the upload path
                    $tmp_path = $file["tmp_name"];                  // temporary path
                    
                    // upload file
                    if(is_uploaded_file($tmp_path))
                    {
                        if(!copy($tmp_path,$upload_path))
                        {
                            throw new Exception("There was an error while copying the uploaded files. Please try again.\n");
                        }
                    }
                    
                    // add file to zip
                    if ($res === TRUE)
                    {
                        $zip->addFile($upload_path, "transcripts/".$file['name']);
                    }                         
                    else
                    {
                        throw new Exception("There was an error while copying the uploaded files. Please try again.\n"); 
                    }
                    
                    $correct_files[] = $upload_path; //for future removal
                }               
            }           
        }
        catch(Exception $e)
        {
            $errors[] = $e->getMessage();
        }
        
        // attachment name
        $filename = $emplid.".pdf";
        $filepath = $upload_folder."/".$filename;
        //$download = "http://escribemeuncuento.info/application/applications/".$emplid."/".$filename;
        $attachname = $emplid.".zip";
        
        //generate application PDF and add to ZIP
        generatePDF($filepath, $emplid);
        $zip->addFile($filepath, "application/".$filename);
        $zip->close(); //closes .ZIP file
        
        //delete leftover transcript files
        foreach ($correct_files as $file)
        {  
            // safety measures
            if(empty($file) || $file == "." || $file == ".." || !file_exists($file))
            {
                break;
            }
            else
            {
                unlink($file); // file is removed
            }
        }
        
        //-----------Processing ends here----------------------------------------------//
                
        
        ///////////////////////////
        //////Generate e-mail//////
        ///////////////////////////---------------------------------------------------//
        $to = "aec12e@my.fsu.edu"; 
        $from = "onlinereg@fsupanama.org"; 
        $subject = "[NEW ONLINE APPLICATION] EMPLID ".$emplid." ".$_POST['last_name']." ".$_POST['first_name']; 
        $message = "<p>New online application received</p>";
        
        // a random hash will be necessary to send mixed content
        $separator = md5(time());
        
        // carriage return type (we use a PHP end of line constant)
        $eol = PHP_EOL;   
        
        // parse .zip data to attach
        if ( ($attachFile = fopen($zipname, "r")) ) {
            $data = fread( $attachFile, filesize($zipname) );
            fclose($attachFile);  
        }   
        else {
            $errors[] = "There was an error processing your application. Please try again.";
        }
        
        // encode data
        $data = chunk_split(base64_encode($data));
        
        // main header
        $headers  = "From: ".$from.$eol;
        $headers .= "MIME-Version: 1.0".$eol; 
        $headers .= "Content-Type: multipart/mixed; boundary=\"".$separator."\"";
        
        // body
        $body = "--".$separator.$eol;
        $body .= "Content-Transfer-Encoding: 7bit".$eol.$eol;
        $body .= "".$eol;
        
        // message
        $body .= "--".$separator.$eol;
        $body .= "Content-Type: text/html; charset=\"iso-8859-1\"".$eol;
        $body .= "Content-Transfer-Encoding: 8bit".$eol.$eol;
        $body .= $message.$eol;
        
        // attachment
        $body .= "--".$separator.$eol;
        $body .= "Content-Type: application/octet-stream; name=\"$attachname\"".$eol; 
        $body .= "Content-Transfer-Encoding: base64".$eol;
        $body .= "Content-Disposition: attachment".$eol.$eol;
        $body .= $data.$eol;
        $body .= "--".$separator."--";
        
        //-------------------e-mail ends here----------------------------------------------//
        
        ////////////////////////////////
        //////Online payment setup//////
        ////////////////////////////////---------------------------------------------------// 
        
        $_SESSION['fullname'] = $_POST['first_name'] . " " . $_POST['middle_name'] . " " . $_POST['last_name'];
        $_SESSION['download'] = $filepath;
        $_SESSION['email']    = $_POST['email']; 
        $_SESSION['service']  = "Undergraduate Application to FSU-Panama";
        $_SESSION['price']    = "1.12";
        $_SESSION['EMPLID']   = $emplid;
        
        //--------------------------------------------------------------------------------//
        
        ///////////////////////////
        //////insert into db///////
        ///////////////////////////-------------------------------------------------------//            
        
        $date = date("Y-m-d");
        $fee_paid = "not paid";
        $status = "not processed";
        $app_pack = "applications/".$emplid."/".$emplid.".zip";
        $app_semester = $_POST['application-term']; // already sanitized
        $app_year = $_POST['application-year']; // already sanitized
        
        try { 
            if (!$insert_app = $appdb->prepare("INSERT INTO applications 
            (EMPLID, last_name, first_name, date, app_semester, app_year, fee_paid, status, app_pack) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)") )
            {
                throw new Exception("There was an error submitting your application. Please try again.");
            }        
            
            else if (!$insert_app->bind_param('sssssssss', $emplid, $_POST['last_name'], $_POST['first_name'], 
            $date, $app_semester, $app_year, $fee_paid, $status, $app_pack))
            { 
                throw new Exception("There was an error submitting your application. Please try again."); 
            }
        }
        catch(Exception $e) {
            $errors[] = $e->getMessage();
        }
    
        //--------------------------------------------------------------------------------//   
        
        ///////////////////////////////
        //////Application result///////
        ///////////////////////////////--------------------------------------------------// 
        
        if (!$errors)
        {
            // send message
            mail($to, $subject, $body, $headers);
            
            //send applicant an email
            sendAppEmail($_POST['email'], $emplid, $filepath, $_POST['first_name'], $_POST['application-term'], $_POST['application-year']);
            
            // Insert in Database
            if(!$insert_app->execute())
            {
                header("HTTP/1.0 500 Internal Server Error"); //This is a non-friendly failure
                exit();
            }
            
            // Close Database connection
            $appdb->close();
            
            // Send to application success page
            header("Location: http://panama.fsu.edu/application/application_result.php");
            exit(0);
        }
        
        // if there are errors, clean-up for new attempt.
        else
        {
            // safety measures
            if(empty($upload_folder) || $upload_folder == "." || $upload_folder == ".." || !is_dir($upload_folder))
            {
                // do nothing.
            }
            else
            {
               // Remove previously created Application folder
               Delete($upload_folder); 
            }  
        }
        
        //--------------------------------------------------------------------------------//
            
        } //end of $_POST mode processing
        
        
        
?>

<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="form.js"></script>
</head>
<body>
    <div id="title-bar">
			<h1 id="title">Apply to Florida State University - Panama</h1>
            <h2>Undergraduate Application</h2>
    </div>
    <form name="applyform" id="applyform" autocomplete="on" method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    <?php if (isset($errors) && $errors)
        {
            foreach ($errors as $error)
            {
                echo "<p class=\"error-box\">".htmlspecialchars($error)."</p>\n";
            }
            echo "<br />";
        }
     ?>
        <h4>All fields marked with a '*' are required</h4>
        <div id="name-address">
            <h2>1. Name and Address</h2>
            <p class="app-label">Last (family) Name: *</p>
            <input type="text"  name="last_name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
            
            <p class="app-label">First (given) Name: *</p>
            <input type="text" name="first_name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
            
            <p class="app-label">Middle Name:</p>
            <input type="text" name="middle_name" value="<?php echo isset($_POST['middle_name']) ? htmlspecialchars($_POST['middle_name']) : ''; ?>">
            
            <p class="app-label">Other names that may appear on your credentials:</p>
            <input type="text" name="other_name" value="<?php echo isset($_POST['other_name']) ? htmlspecialchars($_POST['other_name']) : ''; ?>">
                <br />
                <br />
            <p class="app-label">Permanent Address: *</p>
            <input type="text" name="home_address" value="<?php echo isset($_POST['home_address']) ? htmlspecialchars($_POST['home_address']) : ''; ?>" required>
                <br />
            <p class="app-label">Street or Box Number: *</p>
            <input type="text" name="street" value="<?php echo isset($_POST['street']) ? htmlspecialchars($_POST['street']) : ''; ?>" required>
                <br />
            <p class="app-label">City: *</p>
            <input type="text" name="city" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>" required>
                <br />
            <p class="app-label">Province or State: *</p>
            <input type="text" name="state" value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>" required>
                <br />
            <p class="app-label">Postal code (Leave blank if none):</p>
            <input type="text" name="postal_code" value="<?php echo isset($_POST['postal_code']) ? htmlspecialchars($_POST['postal_code']) : ''; ?>" >
                <br />
            <p class="app-label">Country: *</p>
            <select name="country" required>
                <option value="">Country...</option>
                <?php 
                    foreach($countries as $country)
                    {
                        echo "<option value=\"".htmlspecialchars($country)."\"";
                        echo $_POST['country'] == $country ? "selected>" : '>';
                        echo htmlspecialchars($country)."</option>"."\n";
                    }
                ?>
            </select>
                <br />
            <p class="app-label">E-mail address: *</p>
            <input type="email" name="email" placeholder="me@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                <br />
            <h3>Current Mailing Address (if different from above, optional):</h3>
            <p class="app-label">Number and Street or Box Number</p>
            <input type="text" name="m-street" value="<?php echo isset($_POST['m-street']) ? htmlspecialchars($_POST['m-street']) : ''; ?>" >
                <br />
            <p class="app-label">City</p>
            <input type="text" name="m-city" value="<?php echo isset($_POST['m-city']) ? htmlspecialchars($_POST['m-city']) : ''; ?>" >
                <br />
            <p class="app-label">Province or State</p>
            <input type="text" name="m-state" value="<?php echo isset($_POST['m-state']) ? htmlspecialchars($_POST['m-state']) : ''; ?>" >
                <br />
            <p class="app-label">Postal Code</p>
            <input type="text" name="m-postal_code" value="<?php echo isset($_POST['m-postal_code']) ? htmlspecialchars($_POST['m-postal_code']) : ''; ?>" >
                <br />
            <p class="app-label">Country</p>
            <select name="m-country">
                <option value="">Country...</option>
                <?php 
                    foreach($countries as $country)
                    {
                        echo "<option value=\"".htmlspecialchars($country)."\"";
                        echo $_POST['m-country'] == $country ? "selected>" : '>';
                        echo htmlspecialchars($country)."</option>"."\n";
                    }
                ?>
            </select>
                <br />
            <h4>Telephone Number (for international numbers, do not add '+' or '00' in the area code) *</h4>
            <p class="app-label" style="margin:0; width:75px; clear:none;">Area Code:</p>
            <p class="app-label" style="margin:0; clear:none;">Number:</p>
            <br />
            <input type="text" maxlength="4" name="t-acode" style="width: 50px;
                                                    float: left;
                                                    margin-top: 10px;
                                                    margin-right: 10px;" value="<?php echo isset($_POST['t-acode']) ? htmlspecialchars($_POST['t-acode']) : ''; ?>" required>
            <input type="text" name="t-number" style="width: 250px;
                                                        margin-top: 10px;
                                                        margin-right: 10px;" value="<?php echo isset($_POST['t-number']) ? htmlspecialchars($_POST['t-number']) : ''; ?>" required>
            <h4>Fax Number (optional)</h4>
            <p class="app-label" style="margin:0; width:75px; clear:none;">Area Code:</p>
            <p class="app-label" style="margin:0; clear:none;">Number:</p>
            <br />
            <input type="text" maxlength="4" name="f-acode" style="width: 50px;
                                                    float: left;
                                                    margin-top: 10px;
                                                    margin-right: 10px;"
                                                    value="<?php echo isset($_POST['f-acode']) ? htmlspecialchars($_POST['f-acode']) : ''; ?>" >
            <input type="text" name="f-number" style="width: 250px;
                                                        margin-top: 10px;
                                                        margin-right: 10px;"
                                                        value="<?php echo isset($_POST['f-number']) ? htmlspecialchars($_POST['f-number']) : ''; ?>" >
        </div>
        <div id="personal-data">
        <h2>2. Personal Data</h2>
        <p class="app-label" style="width:90px;">Gender: *</p>
        <input type="radio" name="gender" value="Male" <?php echo $_POST['gender'] == "Male" ? "checked" : ''; ?> required><p class="app-radio-label">Male</p>
        <input type="radio" name="gender" value="Female" <?php echo $_POST['gender'] == "Female" ? "checked" : ''; ?> required><p class="app-radio-label">Female</p>
        <br />
        <h4>Date of Birth: *</h4>
        <p class="app-label" style="width: 40%; margin: 0; clear:none;">Month:</p>
        <p class="app-label" style="width: 70px; margin: 0; clear:none;">Day:</p>  
        <p class="app-label" style="width: 70px; margin: 0; clear:none;">Year:</p>
            <br/>
        <select name="b-month" style="margin-top: 10px; margin-bottom: 0; width: 40%;" required>
            <option value="" <?php echo ($_POST['b-month'] == "") ? "selected" : ''; ?> >Select...</option>
            <option value="01" <?php echo ($_POST['b-month'] == "01") ? "selected" : ''; ?>>January</option>
            <option value="02" <?php echo ($_POST['b-month'] == "02") ? "selected" : ''; ?>>February</option>
            <option value="03" <?php echo ($_POST['b-month'] == "03") ? "selected" : ''; ?>>March</option>
            <option value="04" <?php echo ($_POST['b-month'] == "04") ? "selected" : ''; ?>>April</option>
            <option value="05" <?php echo ($_POST['b-month'] == "05") ? "selected" : ''; ?>>May</option>
            <option value="06" <?php echo ($_POST['b-month'] == "06") ? "selected" : ''; ?>>June</option>
            <option value="07" <?php echo ($_POST['b-month'] == "07") ? "selected" : ''; ?>>July</option>
            <option value="08" <?php echo ($_POST['b-month'] == "08") ? "selected" : ''; ?>>August</option>
            <option value="09" <?php echo ($_POST['b-month'] == "09") ? "selected" : ''; ?>>September</option>
            <option value="10" <?php echo ($_POST['b-month'] == "10") ? "selected" : ''; ?>>October</option>
            <option value="11" <?php echo ($_POST['b-month'] == "11") ? "selected" : ''; ?>>November</option>
            <option value="12" <?php echo ($_POST['b-month'] == "12") ? "selected" : ''; ?>>December</option>
        </select>
        <input type="text" name="b-day" maxlenght="2" style="width: 50px; margin-top: 10px;" value="<?php echo isset($_POST['b-day']) ? htmlspecialchars($_POST['b-day']) : ''; ?>" required>
        <input type="text" name="b-year" maxlenght="4" style="width: 70px; margin-top: 10px;" value="<?php echo isset($_POST['b-year']) ? htmlspecialchars($_POST['b-year']) : ''; ?>" required>
		<h4>Are you Hispanic/Latino? *</h4>
		<input type="radio" name="latino" value="yes" <?php echo ($_POST['latino'] == "yes") ? "checked" : ''; ?> ><p class="app-radio-label">Yes</p>
		<input type="radio" name="latino" value="no" <?php echo ($_POST['latino'] == "no") ? "checked" : ''; ?>><p class="app-radio-label">No</p>
			<br />
		<h4>How would you describe yourself?</h4>
        <input type="checkbox" name="race[]" value="native"><p class="app-radio-label">American Indian / Alaska Native</p>
		<input type="checkbox" name="race[]" value="asian"><p class="app-radio-label">Asian</p>
        <input type="checkbox" name="race[]" value="black"><p class="app-radio-label">Black / African American</p>
        <input type="checkbox" name="race[]" value="pacific"><p class="app-radio-label">Native Hawaiian / Other Pacific Island</p>
        <input type="checkbox" name="race[]" value="white"><p class="app-radio-label">White</p>
        <br/>
		<br />
        <h4>City and Country of Birth</h4>
        <p class="app-label">City *</p><input type="text" name="b-city"  value="<?php echo isset($_POST['b-city']) ? htmlspecialchars($_POST['b-city']) : ''; ?>" required>
        <p class="app-label">Country *</p>
        <select name="b-country" required>
            <option value="">Country...</option>
            <?php 
                foreach($countries as $country)
                {
                    echo "<option value=\"".htmlspecialchars($country)."\"";
                    echo $_POST['b-country'] == $country ? "selected>" : '>';
                    echo htmlspecialchars($country)."</option>"."\n";
                }
            ?>
        </select>
        <br/>
        <p class="app-label">Country of Citizenship *</p>
        <select name="citizenship" required>
            <option value="">Country...</option>
            <?php 
                foreach($countries as $country)
                {
                    echo "<option value=\"".htmlspecialchars($country)."\"";
                    echo $_POST['citizenship'] == $country ? "selected>" : '>';
                    echo htmlspecialchars($country)."</option>"."\n";
                }
            ?>
        </select>
        <br />
        <p class="app-label">Native Language *</p>
        <input type="text" name="language" value="<?php echo isset($_POST['language']) ? htmlspecialchars($_POST['language']) : ''; ?>" required>
        </div>
        
        <div id="enrollment">
        <h2>3. Enrollment Objectives</h2>
        <p class="app-label">Have you previously submitted an application to FSU *</p>
        <input type="radio" name="applied-before" value="yes" <?php echo $_POST['applied-before'] == "yes" ? "checked" : ''; ?>  required><p class="app-radio-label">Yes</p>
        <input type="radio" name="applied-before" value="no" <?php echo $_POST['applied-before'] == "no" ? "checked" : ''; ?> required><p class="app-radio-label">No</p>
        <br/>
        <p class="app-label">Did you enroll? *</p>
        <input type="radio" name="enrolled-before" value="yes" <?php echo $_POST['enrolled-before'] == "yes" ? "checked" : ''; ?> required><p class="app-radio-label">Yes</p>
        <input type="radio" name="enrolled-before" value="no" <?php echo $_POST['enrolled-before'] == "no" ? "checked" : ''; ?> required><p class="app-radio-label">No</p>
        <br />
        <p class="app-label">Are you currently enrolled? *</p>
        <input type="radio" name="enrolled-currently" value="yes" <?php echo $_POST['enrolled-currently'] == "yes" ? "checked" : ''; ?> required><p class="app-radio-label">Yes</p>
        <input type="radio" name="enrolled-currently" value="no"<?php echo $_POST['enrolled-currently'] == "no" ? "checked" : ''; ?>  required><p class="app-radio-label">No</p>
        <br/>
        <p class="app-label">For which term, in which year, do you seek admission? *</p>
        <select name="application-term" style="float: left; width: 100px; margin-right: 15px; margin-top: 0.3px;" required>
            <option value="" <?php echo $_POST['application-term'] == "" ? "selected" : ''; ?> >Select...</option>
            <option value="Fall" <?php echo $_POST['application-term'] == "Fall" ? "selected" : ''; ?>>Fall</option>
            <option value="Spring" <?php echo $_POST['application-term'] == "Spring" ? "selected" : ''; ?>>Spring</option>
            <option value="Summer A" <?php echo $_POST['application-term'] == "Summer A" ? "selected" : ''; ?>>Summer A</option>
            <option value="Summer B" <?php echo $_POST['application-term'] == "Summer B" ? "selected" : ''; ?>>Summer B</option>
        </select>
        <p style="float: left; padding-top: 5px; margin-right: 3px;">Year *</p><input type="text" name="application-year" maxlength="4" style="float:left; width:40px;" value="<?php echo isset($_POST['application-year']) ? htmlspecialchars($_POST['application-year']) : ''; ?>" required>
        <h3>This application is for enrollment as: *</h3>
            <input type="radio" name="enroll-as" value="freshman" <?php echo $_POST['enroll-as'] == "freshman" ? "checked" : ''; ?> required><p class="app-enroll-as">First-Time-In-College Freshman</p>
            <input type="radio" name="enroll-as" value="transfer" <?php echo $_POST['enroll-as'] == "transfer" ? "checked" : ''; ?> required><p class="app-enroll-as">Undergraduate Transfer</p>
            <input type="radio" name="enroll-as" value="bachelor" <?php echo $_POST['enroll-as'] == "bachelor" ? "checked" : ''; ?> required><p class="app-enroll-as">Second Bachelor's Degree</p>
        <br/>
        <h4>Proposed Academic Program (Consult <a href="http://www.academic-guide.fsu.edu" target="_blank">www.academic-guide.fsu.edu</a> for a full list of Academic Programs offered by Florida State University)</h4>
        <p class="app-label">Academic Program (leve blank if undecided):</p><input type="text" name="program" value="<?php echo isset($_POST['program']) ? htmlspecialchars($_POST['program']) : ''; ?>" >
        <br />
        </div>
        
        <div id="ed-background">
            <h2>4.Educational Background</h2>
            <p class="section-text">List in chronological order all secondary and postsecondary schools, colleges, universities, or other institutions you have attended or are currently attending. Please do not abbreviate. Official academic records, in the native language and English, must be submitted from each institution attended. To speed up the processing of your application, you can upload scans of these records, however, you must submit original physical copies, either through courier or when you come to our campus.
You must include schools even if you did not complete a term. Include FSU if you attended previously. For multi-campus institutions, indicate a specific campus. Failure to list all institutions could result in your application being denied or your admission being rescinded.
Original transcripts must be submitted to the admission and records office before the semester starts.</p>
            <h3>Secondary Schools</h3>
            <div id="sec0" class="sec">
                <p class="app-label-2">Name: *</p><input type="text" name="sec0-name" style="float:left; margin-right:15px;" value="<?php echo isset($_POST['sec0-name']) ? htmlspecialchars($_POST['sec0-name']) : ''; ?>" required>
                <p class="app-label-2">City: *</p><input type="text" name="sec0-city" style="float:left; margin-right:15px;" value="<?php echo isset($_POST['sec0-city']) ? htmlspecialchars($_POST['sec0-city']) : ''; ?>" required>
                <p class="app-label-2">State/Province: *</p><input type="text" name="sec0-state" style="float:left; margin-right:15px;" value="<?php echo isset($_POST['sec0-state']) ? htmlspecialchars($_POST['sec0-state']) : ''; ?>" required>
                <p class="app-label-2">Country: *</p>
                <select name="sec0-country" required>
                <option value="">Country...</option>
                    <?php 
                        foreach($countries as $country)
                        {
                            echo "<option value=\"".htmlspecialchars($country)."\"";
                            echo $_POST['sec0-country'] == $country ? "selected>" : '>';
                            echo htmlspecialchars($country)."</option>"."\n";
                        }
                    ?>
                </select>
                <br />
                <h4 style="margin-bottom:20px;">Date of Attendance:</h4>
                <div class="sec-from">
                    <p class="app-label">From: *</p>
                    <select name="sec0-from-month" style="clear: both; float: left;" required>
                        <option value="" <?php echo $_POST['sec0-from-month'] == "" ? "selected" : ''; ?> >Select...</option>
                        <option value="01" <?php echo $_POST['sec0-from-month'] == "01" ? "selected" : ''; ?>>January</option>
                        <option value="02" <?php echo $_POST['sec0-from-month'] == "02" ? "selected" : ''; ?>>February</option>
                        <option value="03" <?php echo $_POST['sec0-from-month'] == "03" ? "selected" : ''; ?>>March</option>
                        <option value="04" <?php echo $_POST['sec0-from-month'] == "04" ? "selected" : ''; ?>>April</option>
                        <option value="05" <?php echo $_POST['sec0-from-month'] == "05" ? "selected" : ''; ?>>May</option>
                        <option value="06" <?php echo $_POST['sec0-from-month'] == "06" ? "selected" : ''; ?>>June</option>
                        <option value="07" <?php echo $_POST['sec0-from-month'] == "07" ? "selected" : ''; ?>>July</option>
                        <option value="08" <?php echo $_POST['sec0-from-month'] == "08" ? "selected" : ''; ?>>August</option>
                        <option value="09" <?php echo $_POST['sec0-from-month'] == "09" ? "selected" : ''; ?>>September</option>
                        <option value="10" <?php echo $_POST['sec0-from-month'] == "10" ? "selected" : ''; ?>>October</option>
                        <option value="11" <?php echo $_POST['sec0-from-month'] == "11" ? "selected" : ''; ?>>November</option>
                        <option value="12" <?php echo $_POST['sec0-from-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2" margin-right:5px;">Year: *</p>
                    <input type="text" name="sec0-from-year" maxlength="4" style="width:40px;" value="<?php echo isset($_POST['sec0-from-year']) ? htmlspecialchars($_POST['sec0-from-year']) : ''; ?>" required>
                </div>
                <div class="sec-to">
                    <p class="app-label">To: *</p>
                    <select name="sec0-to-month" style="clear: both; float: left;" required>
                        <option value="" <?php echo $_POST['sec0-to-month'] == "" ? "selected" : ''; ?> >Select...</option>
                        <option value="01" <?php echo $_POST['sec0-to-month'] == "01" ? "selected" : ''; ?>>January</option>
                        <option value="02" <?php echo $_POST['sec0-to-month'] == "02" ? "selected" : ''; ?>>February</option>
                        <option value="03" <?php echo $_POST['sec0-to-month'] == "03" ? "selected" : ''; ?>>March</option>
                        <option value="04" <?php echo $_POST['sec0-to-month'] == "04" ? "selected" : ''; ?>>April</option>
                        <option value="05" <?php echo $_POST['sec0-to-month'] == "05" ? "selected" : ''; ?>>May</option>
                        <option value="06" <?php echo $_POST['sec0-to-month'] == "06" ? "selected" : ''; ?>>June</option>
                        <option value="07" <?php echo $_POST['sec0-to-month'] == "07" ? "selected" : ''; ?>>July</option>
                        <option value="08" <?php echo $_POST['sec0-to-month'] == "08" ? "selected" : ''; ?>>August</option>
                        <option value="09" <?php echo $_POST['sec0-to-month'] == "09" ? "selected" : ''; ?>>September</option>
                        <option value="10" <?php echo $_POST['sec0-to-month'] == "10" ? "selected" : ''; ?>>October</option>
                        <option value="11" <?php echo $_POST['sec0-to-month'] == "11" ? "selected" : ''; ?>>November</option>
                        <option value="12" <?php echo $_POST['sec0-to-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2" style=" margin-right:5px;">Year: *</p>
                    <input type="text" name="sec0-to-year" maxlength="4" style="width:40px;" value="<?php echo isset($_POST['sec0-to-year']) ? htmlspecialchars($_POST['sec0-to-year']) : ''; ?>" required>
                </div>
                <br />
                <h4 style="margin-bottom:30px;">Certificates, degrees, and/or diplomas earned/expected.Use exact title, i.e., Diplom, Matrise, etc.</h4>
                <p class="app-label-2">Degree name (leave blank if none):</p>
                <input type="text" name="sec0-degree" style="width:300px;" value="<?php echo isset($_POST['sec0-degree']) ? htmlspecialchars($_POST['sec0-degree']) : ''; ?>" >
                <p class="app-label-2">Date issued or to be issued:</p>
                <select name="sec0-degree-month" >
                    <option value="" <?php echo $_POST['sec0-degree-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['sec0-degree-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['sec0-degree-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['sec0-degree-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['sec0-degree-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['sec0-degree-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['sec0-degree-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['sec0-degree-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['sec0-degree-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['sec0-degree-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['sec0-degree-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['sec0-degree-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['sec0-degree-month'] == "12" ? "selected" : ''; ?>>December</option>
                </select>
                <p class="app-label-2" >Year:</p><input type="text" name="sec0-degree-year" maxlength="4" style="width:40px;" value="<?php echo isset($_POST['sec0-degree-year']) ? htmlspecialchars($_POST['sec0-degree-year']) : ''; ?>">
                <br />
                <h4>Upload transcripts (only .PDF files, must not exceed 1MB in size) *</h4>
                <input type="file" name="transcripts1" accept=".pdf" value="<?php echo isset($_POST['transcripts1']) ? htmlspecialchars($_POST['transcripts1']) : ''; ?>" required>
            </div>
            
            <div id="sec1" class="sec postsec-extra">
                <span id="sec1-close" class="close" style="width:12px"><img src="close.png" /></span>
                <h3>2nd School</h3>
                <p class="app-label-2">Name:</p><input type="text" name="sec1-name" style="float:left; margin-right:15px;" value="<?php echo isset($_POST['sec1-name']) ? htmlspecialchars($_POST['sec1-name']) : ''; ?>" >
                <p class="app-label-2">City:</p><input type="text" name="sec1-city" style="float:left; margin-right:15px;" value="<?php echo isset($_POST['sec1-city']) ? htmlspecialchars($_POST['sec1-city']) : ''; ?>" >
                <p class="app-label-2">State/Province:</p><input type="text" name="sec1-state" style="float:left; margin-right:15px;" value="<?php echo isset($_POST['sec1-state']) ? htmlspecialchars($_POST['sec1-state']) : ''; ?>" >
                <p class="app-label-2">Country:</p>
                <select name="sec1-country">
                <option value="">Country...</option>
                    <?php 
                        foreach($countries as $country)
                        {
                            echo "<option value=\"".htmlspecialchars($country)."\"";
                            echo $_POST['sec1-country'] == $country ? "selected>" : '>';
                            echo htmlspecialchars($country)."</option>"."\n";
                        }
                    ?>
                </select>
                <br />
                <h4 style="margin-bottom:20px;">Date of Attendance:</h4>
                <div class="sec-from">
                    <p class="app-label">From:</p>
                    <select name="sec1-from-month" style="clear: both; float: left;" >
                        <option value="" <?php echo $_POST['sec1-from-month'] == "" ? "selected" : ''; ?> >Select...</option>
                        <option value="01" <?php echo $_POST['sec1-from-month'] == "01" ? "selected" : ''; ?>>January</option>
                        <option value="02" <?php echo $_POST['sec1-from-month'] == "02" ? "selected" : ''; ?>>February</option>
                        <option value="03" <?php echo $_POST['sec1-from-month'] == "03" ? "selected" : ''; ?>>March</option>
                        <option value="04" <?php echo $_POST['sec1-from-month'] == "04" ? "selected" : ''; ?>>April</option>
                        <option value="05" <?php echo $_POST['sec1-from-month'] == "05" ? "selected" : ''; ?>>May</option>
                        <option value="06" <?php echo $_POST['sec1-from-month'] == "06" ? "selected" : ''; ?>>June</option>
                        <option value="07" <?php echo $_POST['sec1-from-month'] == "07" ? "selected" : ''; ?>>July</option>
                        <option value="08" <?php echo $_POST['sec1-from-month'] == "08" ? "selected" : ''; ?>>August</option>
                        <option value="09" <?php echo $_POST['sec1-from-month'] == "09" ? "selected" : ''; ?>>September</option>
                        <option value="10" <?php echo $_POST['sec1-from-month'] == "10" ? "selected" : ''; ?>>October</option>
                        <option value="11" <?php echo $_POST['sec1-from-month'] == "11" ? "selected" : ''; ?>>November</option>
                        <option value="12" <?php echo $_POST['sec1-from-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2" style="margin-right:5px;">Year:</p>
                    <input type="text" name="sec1-from-year" maxlength="4" style="width:40px;" value="<?php echo isset($_POST['sec1-from-year']) ? htmlspecialchars($_POST['sec1-from-year']) : ''; ?>" >
                </div>
                <div class="sec-to">
                    <p class="app-label">To:</p>
                    <select name="sec1-to-month" style="clear: both; float: left;" >
                        <option value="" <?php echo $_POST['sec1-to-month'] == "" ? "selected" : ''; ?> >Select...</option>
                        <option value="01" <?php echo $_POST['sec1-to-month'] == "01" ? "selected" : ''; ?>>January</option>
                        <option value="02" <?php echo $_POST['sec1-to-month'] == "02" ? "selected" : ''; ?>>February</option>
                        <option value="03" <?php echo $_POST['sec1-to-month'] == "03" ? "selected" : ''; ?>>March</option>
                        <option value="04" <?php echo $_POST['sec1-to-month'] == "04" ? "selected" : ''; ?>>April</option>
                        <option value="05" <?php echo $_POST['sec1-to-month'] == "05" ? "selected" : ''; ?>>May</option>
                        <option value="06" <?php echo $_POST['sec1-to-month'] == "06" ? "selected" : ''; ?>>June</option>
                        <option value="07" <?php echo $_POST['sec1-to-month'] == "07" ? "selected" : ''; ?>>July</option>
                        <option value="08" <?php echo $_POST['sec1-to-month'] == "08" ? "selected" : ''; ?>>August</option>
                        <option value="09" <?php echo $_POST['sec1-to-month'] == "09" ? "selected" : ''; ?>>September</option>
                        <option value="10" <?php echo $_POST['sec1-to-month'] == "10" ? "selected" : ''; ?>>October</option>
                        <option value="11" <?php echo $_POST['sec1-to-month'] == "11" ? "selected" : ''; ?>>November</option>
                        <option value="12" <?php echo $_POST['sec1-to-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2" style="margin-right:5px;">Year:</p>
                    <input type="text" name="sec1-to-year" maxlength="4" style="width:40px;" value="<?php echo isset($_POST['sec1-to-year']) ? htmlspecialchars($_POST['sec1-to-year']) : ''; ?>" >
                </div>
                <br />
                <h4 style="margin-bottom:30px;">Certificates, degrees, and/or diplomas earned/expected.Use exact title, i.e., Diplom, Matrise, etc.</h4>
                <p class="app-label-2">Degree name:</p>
                <input type="text" name="sec1-degree" style="width:300px;" class="degree-input" value="<?php echo isset($_POST['sec1-degree']) ? htmlspecialchars($_POST['sec1-degree']) : ''; ?>" >
                <p class="app-label-2">Date issued or to be issued:</p>
                <select name="sec1-degree-month" class="degree-input">
                    <option value="" <?php echo $_POST['sec1-degree-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['sec1-degree-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['sec1-degree-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['sec1-degree-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['sec1-degree-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['sec1-degree-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['sec1-degree-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['sec1-degree-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['sec1-degree-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['sec1-degree-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['sec1-degree-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['sec1-degree-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['sec1-degree-month'] == "12" ? "selected" : ''; ?>>December</option>
                </select>
                <p class="app-label-2">Year:</p><input type="text" name="sec1-degree-year" class="degree-input" maxlength="4" style="width:40px;" value="<?php echo isset($_POST['sec1-degree-year']) ? htmlspecialchars($_POST['sec1-degree-year']) : ''; ?>">
                <br />
                <h4>Upload transcripts (only .PDF files, must not exceed 1MB in size)</h4>
                <input type="file" name="transcripts2" accept=".pdf" value="<?php echo isset($_POST['transcripts2']) ? htmlspecialchars($_POST['transcripts2']) : ''; ?>" >
            </div>
            
            <input type="button" id="add-sec" name="add-sec" value="Add 2nd School">
            <br />
            <h3>Postsecondary schools, colleges, universities, and/or other institutions (if applicable)</h3>
            <p style="margin-bottom: 30px;">Original transcripts must be submitted to the admission and records office before the deadline. If the University is a non-U.S. College or University a course – by – course evaluation is required.</p>
            <div id="postsec0" class="postsec">
                <p class="app-label-2">Name:</p><input type="text" name="postsec0-name" style="float:left; margin-right:15px;" value="<?php echo isset($_POST['postsec0-name']) ? htmlspecialchars($_POST['postsec0-name']) : ''; ?>" >
                <p class="app-label-2">City:</p><input type="text" name="postsec0-city" style="float:left; margin-right:15px;" value="<?php echo isset($_POST['postsec0-city']) ? htmlspecialchars($_POST['postsec0-city']) : ''; ?>" >
                <p class="app-label-2">State/Province:</p><input type="text" name="postsec0-state" style="float:left; margin-right:15px;" value="<?php echo isset($_POST['postsec0-state']) ? htmlspecialchars($_POST['postsec0-state']) : ''; ?>" >
                <p class="app-label-2">Country:</p>
                <select name="postsec0-country">
                    <option value="">Country...</option>
                    <?php 
                        foreach($countries as $country)
                        {
                            echo "<option value=\"".htmlspecialchars($country)."\"";
                            echo $_POST['postsec0-country'] == $country ? "selected>" : '>';
                            echo htmlspecialchars($country)."</option>"."\n";
                        }
                    ?>
                </select>
                <br />
                <h4 style="margin-bottom:20px;">Date of Attendance:</h4>
                <div class="postsec-from">
                    <p class="app-label">From:</p>
                    <select name="postsec0-from-month" style="clear: both; float: left;">
                    <option value="" <?php echo $_POST['postsec0-from-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['postsec0-from-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['postsec0-from-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['postsec0-from-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['postsec0-from-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['postsec0-from-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['postsec0-from-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['postsec0-from-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['postsec0-from-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['postsec0-from-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['postsec0-from-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['postsec0-from-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['postsec0-from-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2" style="margin-right:5px;">Year:</p>
                    <input type="text" name="postsec0-from-year" maxlength="4" style="width:40px;" value="<?php echo isset($_POST['postsec0-from-year']) ? htmlspecialchars($_POST['postsec0-from-year']) : ''; ?>" >
                </div>
                <div class="postsec-to">
                    <p class="app-label">To:</p>
                    <select name="postsec0-to-month" style="clear: both; float: left;">
                    <option value="" <?php echo $_POST['postsec0-to-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['postsec0-to-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['postsec0-to-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['postsec0-to-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['postsec0-to-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['postsec0-to-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['postsec0-to-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['postsec0-to-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['postsec0-to-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['postsec0-to-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['postsec0-to-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['postsec0-to-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['postsec0-to-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2" style="margin-right:5px;">Year:</p>
                    <input type="text" name="postsec0-to-year" maxlength="4" style="width:40px;" value="<?php echo isset($_POST['postsec0-to-year']) ? htmlspecialchars($_POST['postsec0-to-year']) : ''; ?>" >
                </div>
                <br />
                <h4 style="margin-bottom:30px;">Certificates, degrees, and/or diplomas earned/expected.Use exact title, i.e., Diplom, Matrise, etc.</h4>
                <p class="app-label-2">Degree name:</p>
                <input type="text" name="postsec0-degree" style="width:300px;" value="<?php echo isset($_POST['postsec0-degree']) ? htmlspecialchars($_POST['postsec0-degree']) : ''; ?>">
                <p class="app-label-2">Date Issued:</p>
                <select name="postsec0-degree-month">
                    <option value="" <?php echo $_POST['postsec0-degree-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['postsec0-degree-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['postsec0-degree-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['postsec0-degree-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['postsec0-degree-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['postsec0-degree-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['postsec0-degree-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['postsec0-degree-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['postsec0-degree-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['postsec0-degree-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['postsec0-degree-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['postsec0-degree-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['postsec0-degree-month'] == "12" ? "selected" : ''; ?>>December</option>
                </select>
                <p class="app-label-2">Year:</p><input type="text" name="postsec0-degree-year" maxlength="4" style="width:40px;" value="<?php echo isset($_POST['postsec0-degree-year']) ? htmlspecialchars($_POST['postsec0-degree-year']) : ''; ?>">
                <br />
                <h4>Upload transcripts (only .PDF files, must not exceed 1MB in size)</h4>
                <input type="file" name="transcripts3" accept=".pdf" />
            </div>
            <div id="postsec1" class="postsec-extra" >
                <span class="close postsec-close" style="width:12px"><img src="close.png" /></span>
                <h3>2nd School</h3>
                <p class="app-label-2">Name:</p><input type="text" name="postsec1-name" value="<?php echo isset($_POST['postsec1-name']) ? htmlspecialchars($_POST['postsec1-name']) : ''; ?>" style="float:left; margin-right:15px;" >
                <p class="app-label-2">City:</p><input type="text" name="postsec1-city" value="<?php echo isset($_POST['postsec1-city']) ? htmlspecialchars($_POST['postsec1-city']) : ''; ?>" style="float:left; margin-right:15px;">
                <p class="app-label-2">State:</p><input type="text" name="postsec1-state" value="<?php echo isset($_POST['postsec1-state']) ? htmlspecialchars($_POST['postsec1-state']) : ''; ?>" style="float:left; margin-right:15px;">
                <p class="app-label-2">Country:</p>
                <select name="postsec1-country">
                    <option value="">Country...</option>
                    <?php 
                        foreach($countries as $country)
                        {
                            echo "<option value=\"".htmlspecialchars($country)."\"";
                            echo $_POST['postsec1-country'] == $country ? "selected>" : '>';
                            echo htmlspecialchars($country)."</option>"."\n";
                        }
                    ?>
                </select>
                <br />
                <h4 style="margin-bottom:20px;">Date of Attendance:</h4>
                <div class="postsec-from">
                    <p class="app-label">From:</p>
                    <select name="postsec1-from-month" style="clear: both; float: left;">
                    <option value="" <?php echo $_POST['postsec1-from-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['postsec1-from-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['postsec1-from-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['postsec1-from-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['postsec1-from-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['postsec1-from-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['postsec1-from-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['postsec1-from-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['postsec1-from-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['postsec1-from-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['postsec1-from-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['postsec1-from-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['postsec1-from-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2" style="margin-right:5px;">Year:</p>
                    <input type="text" name="postsec1-from-year" maxlength="4" value="<?php echo isset($_POST['postsec1-from-year']) ? htmlspecialchars($_POST['postsec1-from-year']) : ''; ?>" style="width:40px;">
                </div>
                <div class="postsec-to">
                    <p class="app-label">To:</p>
                    <select name="postsec1-to-month" style="clear: both; float: left;">
                    <option value="" <?php echo $_POST['postsec1-to-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['postsec1-to-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['postsec1-to-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['postsec1-to-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['postsec1-to-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['postsec1-to-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['postsec1-to-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['postsec1-to-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['postsec1-to-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['postsec1-to-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['postsec1-to-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['postsec1-to-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['postsec1-to-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2" style="margin-right:5px;">Year:</p>
                    <input type="text" name="postsec1-to-year" maxlength="4" value="<?php echo isset($_POST['postsec1-to-year']) ? htmlspecialchars($_POST['postsec1-to-year']) : ''; ?>" style="width:40px;">
                </div>
                <br />
                <h4 style="margin-bottom:30px;">Certificates, degrees, and/or diplomas earned/expected.Use exact title, i.e., Diplom, Matrise, etc.</h4>
                <p class="app-label-2">Degree name:</p>
                <input type="text" name="postsec1-degree" value="<?php echo isset($_POST['postsec1-degree']) ? htmlspecialchars($_POST['postsec1-degree']) : ''; ?>" style="width:300px;">
                <p class="app-label-2">Date Issued:</p>
                <select name="postsec1-degree-month">
                    <option value="" <?php echo $_POST['postsec1-degree-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['postsec1-degree-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['postsec1-degree-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['postsec1-degree-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['postsec1-degree-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['postsec1-degree-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['postsec1-degree-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['postsec1-degree-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['postsec1-degree-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['postsec1-degree-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['postsec1-degree-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['postsec1-degree-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['postsec1-degree-month'] == "12" ? "selected" : ''; ?>>December</option>
                </select>
                <p class="app-label-2">Year:</p><input type="text" name="postsec1-degree-year" maxlength="4" value="<?php echo isset($_POST['postsec1-degree-year']) ? htmlspecialchars($_POST['postsec1-degree-year']) : ''; ?>" style="width:40px;">
                <br />
                <h4>Upload transcripts (only .PDF files, must not exceed 1MB in size)</h4>
                <input type="file" name="transcripts4" accept=".pdf" />
            </div>
            <div id="postsec2" class="postsec-extra" >
                <span class="close postsec-close" style="width:12px"><img src="close.png" /></span>
                <h3>3rd School</h3>
                <p class="app-label-2">Name:</p><input type="text" name="postsec2-name" value="<?php echo isset($_POST['postsec2-name']) ? htmlspecialchars($_POST['postsec1-degree-year']) : ''; ?>" style="float:left; margin-right:15px;">
                <p class="app-label-2">City:</p><input type="text" name="postsec2-city" value="<?php echo isset($_POST['postsec2-city']) ? htmlspecialchars($_POST['postsec1-degree-year']) : ''; ?>" style="float:left; margin-right:15px;">
                <p class="app-label-2">State:</p><input type="text" name="postsec2-state" value="<?php echo isset($_POST['postsec2-state']) ? htmlspecialchars($_POST['postsec1-degree-year']) : ''; ?>" style="float:left; margin-right:15px;">
                <p class="app-label-2">Country:</p>
                <select name="postsec2-country">
                    <option value="">Country...</option>
                    <?php 
                        foreach($countries as $country)
                        {
                            echo "<option value=\"".htmlspecialchars($country)."\"";
                            echo $_POST['postsec2-country'] == $country ? "selected>" : '>';
                            echo htmlspecialchars($country)."</option>"."\n";
                        }
                    ?>
                </select>
                
                <br />
                <h4 style="margin-bottom:20px;">Date of Attendance:</h4>
                <div class="postsec-from">
                    <p class="app-label">From:</p>
                    <select name="postsec2-from-month" style="clear: both; float: left;">
                    <option value="" <?php echo $_POST['postsec2-from-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['postsec2-from-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['postsec2-from-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['postsec2-from-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['postsec2-from-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['postsec2-from-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['postsec2-from-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['postsec2-from-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['postsec2-from-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['postsec2-from-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['postsec2-from-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['postsec2-from-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['postsec2-from-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2" style="margin-right:5px;">Year:</p>
                    <input type="text" name="postsec2-from-year" maxlength="4" value="<?php echo isset($_POST['postsec2-from-year']) ? htmlspecialchars($_POST['postsec2-from-year']) : ''; ?>" style="width:40px;">
                </div>
                <div class="postsec-to">
                    <p class="app-label">To:</p>
                    <select name="postsec2-to-month" style="clear: both; float: left;">
                    <option value="" <?php echo $_POST['postsec2-to-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['postsec2-to-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['postsec2-to-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['postsec2-to-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['postsec2-to-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['postsec2-to-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['postsec2-to-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['postsec2-to-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['postsec2-to-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['postsec2-to-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['postsec2-to-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['postsec2-to-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['postsec2-to-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2" style="margin-right:5px;">Year:</p>
                    <input type="text" name="postsec2-to-year" maxlength="4" value="<?php echo isset($_POST['postsec2-from-year']) ? htmlspecialchars($_POST['postsec2-from-year']) : ''; ?>" style="width:40px;">
                </div>
                <br />
                <h4 style="margin-bottom:30px;">Certificates, degrees, and/or diplomas earned/expected.Use exact title, i.e., Diplom, Matrise, etc.</h4>
                <p class="app-label-2">Degree name:</p>
                <input type="text" name="postsec2-degree" style="width:300px;">
                <p class="app-label-2">Date Issued:</p>
                <select name="postsec2-degree-month">
                    <option value="" <?php echo $_POST['postsec2-degree-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['postsec2-degree-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['postsec2-degree-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['postsec2-degree-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['postsec2-degree-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['postsec2-degree-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['postsec2-degree-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['postsec2-degree-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['postsec2-degree-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['postsec2-degree-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['postsec2-degree-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['postsec2-degree-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['postsec2-degree-month'] == "12" ? "selected" : ''; ?>>December</option>
                </select>
                <p class="app-label-2">Year:</p><input type="text" name="postsec2-degree-year" maxlength="4" style="width:40px;" value="<?php echo isset($_POST['postsec2-degree-year']) ? htmlspecialchars($_POST['postsec2-degree-year']) : ''; ?>">
                <br />
                <h4>Upload transcripts (only .PDF files, must not exceed 1MB in size)</h4>
                <input type="file" name="transcripts5" accept=".pdf" />
            </div>
            <div id="postsec3" class="postsec-extra" >
                <span class="close postsec-close" style="width:12px"><img src="close.png" /></span>
                <h3>4th School</h3>
                <p class="app-label-2">Name:</p><input type="text" name="postsec3-name" value="<?php echo isset($_POST['postsec3-name']) ? htmlspecialchars($_POST['postsec3-name']) : ''; ?>" style="float:left; margin-right:15px;">
                <p class="app-label-2">City:</p><input type="text" name="postsec3-city" value="<?php echo isset($_POST['postsec3-city']) ? htmlspecialchars($_POST['postsec3-city']) : ''; ?>" style="float:left; margin-right:15px;">
                <p class="app-label-2">State:</p><input type="text" name="postsec3-state" value="<?php echo isset($_POST['postsec3-state']) ? htmlspecialchars($_POST['postsec3-state']) : ''; ?>" style="float:left; margin-right:15px;">
                <p class="app-label-2">Country:</p>
                <select name="postsec3-country">
                    <option value="">Country...</option>
                    <?php 
                        foreach($countries as $country)
                        {
                            echo "<option value=\"".htmlspecialchars($country)."\"";
                            echo $_POST['postsec3-country'] == $country ? "selected>" : '>';
                            echo htmlspecialchars($country)."</option>"."\n";
                        }
                    ?>
                </select>
                <br />
                <h4 style="margin-bottom:20px;">Date of Attendance:</h4>
                <div class="postsec-from">
                    <p class="app-label">From:</p>
                    <select name="postsec3-from-month" style="clear: both; float: left;">
                    <option value="" <?php echo $_POST['postsec3-from-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['postsec3-from-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['postsec3-from-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['postsec3-from-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['postsec3-from-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['postsec3-from-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['postsec3-from-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['postsec3-from-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['postsec3-from-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['postsec3-from-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['postsec3-from-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['postsec3-from-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['postsec3-from-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2" style="margin-right:5px;">Year:</p>
                    <input type="text" name="postsec3-from-year" maxlength="4" value="<?php echo isset($_POST['postsec3-from-year']) ? htmlspecialchars($_POST['postsec3-from-year']) : ''; ?>" style="width:40px;">
                </div>
                <div class="postsec-to">
                    <p class="app-label">To:</p>
                    <select name="postsec3-to-month" style="clear: both; float: left;">
                    <option value="" <?php echo $_POST['postsec3-to-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['postsec3-to-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['postsec3-to-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['postsec3-to-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['postsec3-to-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['postsec3-to-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['postsec3-to-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['postsec3-to-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['postsec3-to-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['postsec3-to-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['postsec3-to-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['postsec3-to-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['postsec3-to-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2" style="font-size:1.2em; margin-right:5px;">Year:</p>
                    <input type="text" name="postsec3-to-year" maxlength="4" value="<?php echo isset($_POST['postsec3-to-year']) ? htmlspecialchars($_POST['postsec3-to-year']) : ''; ?>" style="width:40px;">
                </div>
                <br />
                <h4 style="margin-bottom:30px;">Certificates, degrees, and/or diplomas earned/expected.Use exact title, i.e., Diplom, Matrise, etc.</h4>
                <p class="app-label-2">Degree name:</p>
                <input type="text" name="postsec3-degree" value="<?php echo isset($_POST['postsec3-degree']) ? htmlspecialchars($_POST['postsec3-degree']) : ''; ?>" style="width:300px;">
                <p class="app-label-2">Date Issued:</p>
                <select name="postsec3-degree-month">
                    <option value="" <?php echo $_POST['postsec3-degree-month'] == "" ? "selected" : ''; ?> >Select...</option>
                    <option value="01" <?php echo $_POST['postsec3-degree-month'] == "01" ? "selected" : ''; ?>>January</option>
                    <option value="02" <?php echo $_POST['postsec3-degree-month'] == "02" ? "selected" : ''; ?>>February</option>
                    <option value="03" <?php echo $_POST['postsec3-degree-month'] == "03" ? "selected" : ''; ?>>March</option>
                    <option value="04" <?php echo $_POST['postsec3-degree-month'] == "04" ? "selected" : ''; ?>>April</option>
                    <option value="05" <?php echo $_POST['postsec3-degree-month'] == "05" ? "selected" : ''; ?>>May</option>
                    <option value="06" <?php echo $_POST['postsec3-degree-month'] == "06" ? "selected" : ''; ?>>June</option>
                    <option value="07" <?php echo $_POST['postsec3-degree-month'] == "07" ? "selected" : ''; ?>>July</option>
                    <option value="08" <?php echo $_POST['postsec3-degree-month'] == "08" ? "selected" : ''; ?>>August</option>
                    <option value="09" <?php echo $_POST['postsec3-degree-month'] == "09" ? "selected" : ''; ?>>September</option>
                    <option value="10" <?php echo $_POST['postsec3-degree-month'] == "10" ? "selected" : ''; ?>>October</option>
                    <option value="11" <?php echo $_POST['postsec3-degree-month'] == "11" ? "selected" : ''; ?>>November</option>
                    <option value="12" <?php echo $_POST['postsec3-degree-month'] == "12" ? "selected" : ''; ?>>December</option>
                </select>
                <p class="app-label-2">Year:</p><input type="text" name="postsec3-degree-year" maxlength="4" style="width:40px;" value="<?php echo isset($_POST['postsec3-degree-year']) ? htmlspecialchars($_POST['postsec3-degree-year']) : ''; ?>">
                <br />
                <h4>Upload transcripts (only .PDF files, must not exceed 1MB in size)</h4>
                <input type="file" name="transcripts6" accept=".pdf" />
            </div>
            <input type="button" id="add-postsec" name="add-postsec" value="Add Another School">
            <br />
        </div>
        <div id="employment">
            <h2>5. Employment/Activity Information</h2>
            <p class="section-text">If there has been a period of time, other than summer vacations, when you were not enrolled in school, please explain what you were doing (i.e., work, military, travel). Use a separate sheet, if necessary. If you are not currently enrolled in school, include your present activities. (optional)</p>
            <br/>
            <div id="employment-node-0" class="employment-node">
                <p class="app-label">Employment / Activity</p>
                <input type="text" name="employment0" value="<?php echo isset($_POST['employment0']) ? htmlspecialchars($_POST['employment0']) : ''; ?>">
                <p class="app-label employment-location">Location</p>
                <input type="text" name="employment0-location" value="<?php echo isset($_POST['employment0-location']) ? htmlspecialchars($_POST['employment0-location']) : ''; ?>">
                <br />
                <div class="employment-from">
                    <p class="app-label-2">From:</p>
                    <select name="employment0-from-month" style="margin-top: 0.3px;">
                        <option value="" <?php echo $_POST['employment0-from-month'] == "" ? "selected" : ''; ?> >Select...</option>
                        <option value="01" <?php echo $_POST['employment0-from-month'] == "01" ? "selected" : ''; ?>>January</option>
                        <option value="02" <?php echo $_POST['employment0-from-month'] == "02" ? "selected" : ''; ?>>February</option>
                        <option value="03" <?php echo $_POST['employment0-from-month'] == "03" ? "selected" : ''; ?>>March</option>
                        <option value="04" <?php echo $_POST['employment0-from-month'] == "04" ? "selected" : ''; ?>>April</option>
                        <option value="05" <?php echo $_POST['employment0-from-month'] == "05" ? "selected" : ''; ?>>May</option>
                        <option value="06" <?php echo $_POST['employment0-from-month'] == "06" ? "selected" : ''; ?>>June</option>
                        <option value="07" <?php echo $_POST['employment0-from-month'] == "07" ? "selected" : ''; ?>>July</option>
                        <option value="08" <?php echo $_POST['employment0-from-month'] == "08" ? "selected" : ''; ?>>August</option>
                        <option value="09" <?php echo $_POST['employment0-from-month'] == "09" ? "selected" : ''; ?>>September</option>
                        <option value="10" <?php echo $_POST['employment0-from-month'] == "10" ? "selected" : ''; ?>>October</option>
                        <option value="11" <?php echo $_POST['employment0-from-month'] == "11" ? "selected" : ''; ?>>November</option>
                        <option value="12" <?php echo $_POST['employment0-from-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2">Year:</p>
                    <input type="text" name="employment0-from-year" value="<?php echo isset($_POST['employment0-from-year']) ? htmlspecialchars($_POST['employment0-from-year']) : ''; ?>" maxlength="4" style="width:40px;">
                </div>
                <div class="employment-to">
                    <p class="app-label-2">To:</p>
                    <select name="employment0-to-month" style="margin-top: 0.3px;">
                        <option value="" <?php echo $_POST['employment0-to-month'] == "" ? "selected" : ''; ?> >Select...</option>
                        <option value="01" <?php echo $_POST['employment0-to-month'] == "01" ? "selected" : ''; ?>>January</option>
                        <option value="02" <?php echo $_POST['employment0-to-month'] == "02" ? "selected" : ''; ?>>February</option>
                        <option value="03" <?php echo $_POST['employment0-to-month'] == "03" ? "selected" : ''; ?>>March</option>
                        <option value="04" <?php echo $_POST['employment0-to-month'] == "04" ? "selected" : ''; ?>>April</option>
                        <option value="05" <?php echo $_POST['employment0-to-month'] == "05" ? "selected" : ''; ?>>May</option>
                        <option value="06" <?php echo $_POST['employment0-to-month'] == "06" ? "selected" : ''; ?>>June</option>
                        <option value="07" <?php echo $_POST['employment0-to-month'] == "07" ? "selected" : ''; ?>>July</option>
                        <option value="08" <?php echo $_POST['employment0-to-month'] == "08" ? "selected" : ''; ?>>August</option>
                        <option value="09" <?php echo $_POST['employment0-to-month'] == "09" ? "selected" : ''; ?>>September</option>
                        <option value="10" <?php echo $_POST['employment0-to-month'] == "10" ? "selected" : ''; ?>>October</option>
                        <option value="11" <?php echo $_POST['employment0-to-month'] == "11" ? "selected" : ''; ?>>November</option>
                        <option value="12" <?php echo $_POST['employment0-to-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2">Year:</p>
                    <input type="text" name="employment0-to-year" value="<?php echo isset($_POST['employment0-to-year']) ? htmlspecialchars($_POST['employment0-to-year']) : ''; ?>" maxlength="4" style="width:40px;">                   
                </div>
            </div>
            <div id="employment-node-1" class="employment-node-extra">
                <p class="app-label">Employment / Activity</p>
                <input type="text" name="employment1" value="<?php echo isset($_POST['employment1']) ? htmlspecialchars($_POST['employment1']) : ''; ?>">
                <p class="app-label employment-location">Location</p>
                <input type="text" name="employment1-location" value="<?php echo isset($_POST['employment1-location']) ? htmlspecialchars($_POST['employment1-location']) : ''; ?>">
                <br />
                <div class="employment-from">
                    <p class="app-label-2">From:</p>
                    <select name="employment1-from-month" style="margin-top: 0.3px;">
                        <option value="" <?php echo $_POST['employment1-from-month'] == "" ? "selected" : ''; ?> >Select...</option>
                        <option value="01" <?php echo $_POST['employment1-from-month'] == "01" ? "selected" : ''; ?>>January</option>
                        <option value="02" <?php echo $_POST['employment1-from-month'] == "02" ? "selected" : ''; ?>>February</option>
                        <option value="03" <?php echo $_POST['employment1-from-month'] == "03" ? "selected" : ''; ?>>March</option>
                        <option value="04" <?php echo $_POST['employment1-from-month'] == "04" ? "selected" : ''; ?>>April</option>
                        <option value="05" <?php echo $_POST['employment1-from-month'] == "05" ? "selected" : ''; ?>>May</option>
                        <option value="06" <?php echo $_POST['employment1-from-month'] == "06" ? "selected" : ''; ?>>June</option>
                        <option value="07" <?php echo $_POST['employment1-from-month'] == "07" ? "selected" : ''; ?>>July</option>
                        <option value="08" <?php echo $_POST['employment1-from-month'] == "08" ? "selected" : ''; ?>>August</option>
                        <option value="09" <?php echo $_POST['employment1-from-month'] == "09" ? "selected" : ''; ?>>September</option>
                        <option value="10" <?php echo $_POST['employment1-from-month'] == "10" ? "selected" : ''; ?>>October</option>
                        <option value="11" <?php echo $_POST['employment1-from-month'] == "11" ? "selected" : ''; ?>>November</option>
                        <option value="12" <?php echo $_POST['employment1-from-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2">Year:</p>
                    <input type="text" name="employment1-from-year" value="<?php echo isset($_POST['employment1-from-year']) ? htmlspecialchars($_POST['employment1-from-year']) : ''; ?>" maxlength="4" style="width:40px;">
                </div>
                <div class="employment-to">
                    <p class="app-label-2">To:</p>
                    <select name="employment1-to-month" style="margin-top: 0.3px;">
                        <option value="" <?php echo $_POST['employment1-to-month'] == "" ? "selected" : ''; ?> >Select...</option>
                        <option value="01" <?php echo $_POST['employment1-to-month'] == "01" ? "selected" : ''; ?>>January</option>
                        <option value="02" <?php echo $_POST['employment1-to-month'] == "02" ? "selected" : ''; ?>>February</option>
                        <option value="03" <?php echo $_POST['employment1-to-month'] == "03" ? "selected" : ''; ?>>March</option>
                        <option value="04" <?php echo $_POST['employment1-to-month'] == "04" ? "selected" : ''; ?>>April</option>
                        <option value="05" <?php echo $_POST['employment1-to-month'] == "05" ? "selected" : ''; ?>>May</option>
                        <option value="06" <?php echo $_POST['employment1-to-month'] == "06" ? "selected" : ''; ?>>June</option>
                        <option value="07" <?php echo $_POST['employment1-to-month'] == "07" ? "selected" : ''; ?>>July</option>
                        <option value="08" <?php echo $_POST['employment1-to-month'] == "08" ? "selected" : ''; ?>>August</option>
                        <option value="09" <?php echo $_POST['employment1-to-month'] == "09" ? "selected" : ''; ?>>September</option>
                        <option value="10" <?php echo $_POST['employment1-to-month'] == "10" ? "selected" : ''; ?>>October</option>
                        <option value="11" <?php echo $_POST['employment1-to-month'] == "11" ? "selected" : ''; ?>>November</option>
                        <option value="12" <?php echo $_POST['employment1-to-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2">Year:</p>
                    <input type="text" name="employment1-to-year" value="<?php echo isset($_POST['employment1-to-year']) ? htmlspecialchars($_POST['employment1-to-year']) : ''; ?>" maxlength="4" style="width:40px;">                   
                </div>
            </div>
            <div id="employment-node-2" class="employment-node-extra">
                <p class="app-label">Employment / Activity</p>
                <input type="text" name="employment2" value="<?php echo isset($_POST['employment2']) ? htmlspecialchars($_POST['employment2']) : ''; ?>">
                <p class="app-label employment-location">Location</p>
                <input type="text" name="employment2-location" value="<?php echo isset($_POST['employment2-location']) ? htmlspecialchars($_POST['employment2-location']) : ''; ?>">
                <br />
                <div class="employment-from">
                    <p class="app-label-2">From:</p>
                    <select name="employment2-from-month" style="margin-top: 0.3px;">
                        <option value="" <?php echo $_POST['employment2-from-month'] == "" ? "selected" : ''; ?> >Select...</option>
                        <option value="01" <?php echo $_POST['employment2-from-month'] == "01" ? "selected" : ''; ?>>January</option>
                        <option value="02" <?php echo $_POST['employment2-from-month'] == "02" ? "selected" : ''; ?>>February</option>
                        <option value="03" <?php echo $_POST['employment2-from-month'] == "03" ? "selected" : ''; ?>>March</option>
                        <option value="04" <?php echo $_POST['employment2-from-month'] == "04" ? "selected" : ''; ?>>April</option>
                        <option value="05" <?php echo $_POST['employment2-from-month'] == "05" ? "selected" : ''; ?>>May</option>
                        <option value="06" <?php echo $_POST['employment2-from-month'] == "06" ? "selected" : ''; ?>>June</option>
                        <option value="07" <?php echo $_POST['employment2-from-month'] == "07" ? "selected" : ''; ?>>July</option>
                        <option value="08" <?php echo $_POST['employment2-from-month'] == "08" ? "selected" : ''; ?>>August</option>
                        <option value="09" <?php echo $_POST['employment2-from-month'] == "09" ? "selected" : ''; ?>>September</option>
                        <option value="10" <?php echo $_POST['employment2-from-month'] == "10" ? "selected" : ''; ?>>October</option>
                        <option value="11" <?php echo $_POST['employment2-from-month'] == "11" ? "selected" : ''; ?>>November</option>
                        <option value="12" <?php echo $_POST['employment2-from-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2">Year:</p>
                    <input type="text" name="employment2-from-year" value="<?php echo isset($_POST['employment2-from-year']) ? htmlspecialchars($_POST['employment2-from-year']) : ''; ?>" maxlength="4" style="width:40px;">
                </div>
                <div class="employment-to">
                    <p class="app-label-2">To:</p>
                    <select name="employment2-to-month" style="margin-top: 0.3px;">
                        <option value="" <?php echo $_POST['employment2-to-month'] == "" ? "selected" : ''; ?> >Select...</option>
                        <option value="01" <?php echo $_POST['employment2-to-month'] == "01" ? "selected" : ''; ?>>January</option>
                        <option value="02" <?php echo $_POST['employment2-to-month'] == "02" ? "selected" : ''; ?>>February</option>
                        <option value="03" <?php echo $_POST['employment2-to-month'] == "03" ? "selected" : ''; ?>>March</option>
                        <option value="04" <?php echo $_POST['employment2-to-month'] == "04" ? "selected" : ''; ?>>April</option>
                        <option value="05" <?php echo $_POST['employment2-to-month'] == "05" ? "selected" : ''; ?>>May</option>
                        <option value="06" <?php echo $_POST['employment2-to-month'] == "06" ? "selected" : ''; ?>>June</option>
                        <option value="07" <?php echo $_POST['employment2-to-month'] == "07" ? "selected" : ''; ?>>July</option>
                        <option value="08" <?php echo $_POST['employment2-to-month'] == "08" ? "selected" : ''; ?>>August</option>
                        <option value="09" <?php echo $_POST['employment2-to-month'] == "09" ? "selected" : ''; ?>>September</option>
                        <option value="10" <?php echo $_POST['employment2-to-month'] == "10" ? "selected" : ''; ?>>October</option>
                        <option value="11" <?php echo $_POST['employment2-to-month'] == "11" ? "selected" : ''; ?>>November</option>
                        <option value="12" <?php echo $_POST['employment2-to-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2">Year:</p>
                    <input type="text" name="employment2-to-year" value="<?php echo isset($_POST['employment2-to-year']) ? htmlspecialchars($_POST['employment2-to-year']) : ''; ?>" maxlength="4" style="width:40px;">                   
                </div>
            </div>
            <div id="employment-node-3" class="employment-node-extra">
                <p class="app-label">Employment / Activity</p>
                <input type="text" name="employment3" value="<?php echo isset($_POST['employment3']) ? htmlspecialchars($_POST['employment3']) : ''; ?>">
                <p class="app-label employment-location">Location</p>
                <input type="text" name="employment3-location" value="<?php echo isset($_POST['employment3-location']) ? htmlspecialchars($_POST['employment3-location']) : ''; ?>">
                <br />
                <div class="employment-from">
                    <p class="app-label-2">From:</p>
                    <select name="employment3-from-month" style="margin-top: 0.3px;">
                        <option value="" <?php echo $_POST['employment3-from-month'] == "" ? "selected" : ''; ?> >Select...</option>
                        <option value="01" <?php echo $_POST['employment3-from-month'] == "01" ? "selected" : ''; ?>>January</option>
                        <option value="02" <?php echo $_POST['employment3-from-month'] == "02" ? "selected" : ''; ?>>February</option>
                        <option value="03" <?php echo $_POST['employment3-from-month'] == "03" ? "selected" : ''; ?>>March</option>
                        <option value="04" <?php echo $_POST['employment3-from-month'] == "04" ? "selected" : ''; ?>>April</option>
                        <option value="05" <?php echo $_POST['employment3-from-month'] == "05" ? "selected" : ''; ?>>May</option>
                        <option value="06" <?php echo $_POST['employment3-from-month'] == "06" ? "selected" : ''; ?>>June</option>
                        <option value="07" <?php echo $_POST['employment3-from-month'] == "07" ? "selected" : ''; ?>>July</option>
                        <option value="08" <?php echo $_POST['employment3-from-month'] == "08" ? "selected" : ''; ?>>August</option>
                        <option value="09" <?php echo $_POST['employment3-from-month'] == "09" ? "selected" : ''; ?>>September</option>
                        <option value="10" <?php echo $_POST['employment3-from-month'] == "10" ? "selected" : ''; ?>>October</option>
                        <option value="11" <?php echo $_POST['employment3-from-month'] == "11" ? "selected" : ''; ?>>November</option>
                        <option value="12" <?php echo $_POST['employment3-from-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2">Year:</p>
                    <input type="text" name="employment3-from-year" value="<?php echo isset($_POST['employment3-from-year']) ? htmlspecialchars($_POST['employment3-from-year']) : ''; ?>" maxlength="4" style="width:40px;">
                </div>
                <div class="employment-to">
                    <p class="app-label-2">To:</p>
                    <select name="employment3-to-month" style="margin-top: 0.3px;">
                    <option value="" <?php echo $_POST['employment3-from-month'] == "" ? "selected" : ''; ?> >Select...</option>
                        <option value="01" <?php echo $_POST['employment3-to-month'] == "01" ? "selected" : ''; ?>>January</option>
                        <option value="02" <?php echo $_POST['employment3-to-month'] == "02" ? "selected" : ''; ?>>February</option>
                        <option value="03" <?php echo $_POST['employment3-to-month'] == "03" ? "selected" : ''; ?>>March</option>
                        <option value="04" <?php echo $_POST['employment3-to-month'] == "04" ? "selected" : ''; ?>>April</option>
                        <option value="05" <?php echo $_POST['employment3-to-month'] == "05" ? "selected" : ''; ?>>May</option>
                        <option value="06" <?php echo $_POST['employment3-to-month'] == "06" ? "selected" : ''; ?>>June</option>
                        <option value="07" <?php echo $_POST['employment3-to-month'] == "07" ? "selected" : ''; ?>>July</option>
                        <option value="08" <?php echo $_POST['employment3-to-month'] == "08" ? "selected" : ''; ?>>August</option>
                        <option value="09" <?php echo $_POST['employment3-to-month'] == "09" ? "selected" : ''; ?>>September</option>
                        <option value="10" <?php echo $_POST['employment3-to-month'] == "10" ? "selected" : ''; ?>>October</option>
                        <option value="11" <?php echo $_POST['employment3-to-month'] == "11" ? "selected" : ''; ?>>November</option>
                        <option value="12" <?php echo $_POST['employment3-to-month'] == "12" ? "selected" : ''; ?>>December</option>
                    </select>
                    <p class="app-label-2">Year:</p>
                    <input type="text" name="employment3-to-year" value="<?php echo isset($_POST['employment3-to-year']) ? htmlspecialchars($_POST['employment3-to-year']) : ''; ?>" maxlength="4" style="width:40px;">                   
                </div>
            </div>
            <input type="button" name="add-employment-node" id="add-employment-node" value="Add another">
        </div>
        <div id="tests">
            <h2>6. Standardized Test Information</h2>
            <p class="section-text">Please list all dates on which you took, or are planning to take, the following tests. Include score results, if known, and attach copies if available.
Official scores must be sent directly to Florida State University by the testing agency. Our codes: IBT: 5203; SAT: 5219; ACT: 0734
            <br/>
                <span>If English is not your native language, and you have not studied full-time in an English-speaking country for at least one academic year, a
TOEFL score is required.
                </span>
            </p>
            <h4>TOEFL:</h4>
            <p class="app-label-2">Date:</p><input type="date" name="toefl-date" value="<?php echo isset($_POST['toefl-date']) ? htmlspecialchars($_POST['toefl-date']) : ''; ?>" >
            <p class="app-label-2">L</p><input type="text" name="toefl-L" value="<?php echo isset($_POST['toefl-L']) ? htmlspecialchars($_POST['toefl-L']) : ''; ?>" >
            <p class="app-label-2">S</p><input type="text" name="toefl-R" value="<?php echo isset($_POST['toefl-R']) ? htmlspecialchars($_POST['toefl-R']) : ''; ?>" >
            <p class="app-label-2">V</p><input type="text" name="toefl-S" value="<?php echo isset($_POST['toefl-S']) ? htmlspecialchars($_POST['toefl-S']) : ''; ?>" >
            <p class="app-label-2">TWE</p><input type="text" name="toefl-TWE" value="<?php echo isset($_POST['toefl-TWE']) ? htmlspecialchars($_POST['toefl-TWE']) : ''; ?>" >
            <p class="app-label-2">Total</p><input type="text" name="toefl-Total" value="<?php echo isset($_POST['toefl-Total']) ? htmlspecialchars($_POST['toefl-Total']) : ''; ?>" >
            <p class="app-label-2 upload-scores">Upload Scores (Optional. File must not exceed 1MB. PDF files only.)</p><input type="file" name="transcripts7" accept=".pdf" />
            <hr/>
            <h4>IB TOEFL:</h4>
            <p class="app-label-2">Date:</p><input type="date" name="ibtoefl-date" value="<?php echo isset($_POST['ibtoefl-date']) ? htmlspecialchars($_POST['ibtoefl-date']) : ''; ?>">
            <p class="app-label-2">L</p><input type="text" name="ibtoefl-L" value="<?php echo isset($_POST['ibtoefl-L']) ? htmlspecialchars($_POST['ibtoefl-L']) : ''; ?>">
            <p class="app-label-2">R</p><input type="text" name="ibtoefl-R" value="<?php echo isset($_POST['ibtoefl-R']) ? htmlspecialchars($_POST['ibtoefl-R']) : ''; ?>">
            <p class="app-label-2">S</p><input type="text" name="ibtoefl-S" value="<?php echo isset($_POST['ibtoefl-S']) ? htmlspecialchars($_POST['ibtoefl-S']) : ''; ?>">
            <p class="app-label-2">W</p><input type="text" name="ibtoefl-W" value="<?php echo isset($_POST['ibtoefl-W']) ? htmlspecialchars($_POST['ibtoefl-W']) : ''; ?>">
            <p class="app-label-2">Total</p><input type="text" name="ibtoefl-Total" value="<?php echo isset($_POST['ibtoefl-Total']) ? htmlspecialchars($_POST['ibtoefl-Total']) : ''; ?>">
            <p class="app-label-2 upload-scores">Upload Scores (Optional. File must not exceed 1MB. PDF files only.)</p><input type="file" name="transcripts8" accept=".pdf" />
            <hr/>
            <h4>SAT:</h4>
            <p class="app-label-2">Date:</p><input type="date" name="SAT-date" value="<?php echo isset($_POST['SAT-date']) ? htmlspecialchars($_POST['SAT-date']) : ''; ?>" >
            <p class="app-label-2">CR</p><input type="text" name="SAT-CR" value="<?php echo isset($_POST['SAT-CR']) ? htmlspecialchars($_POST['SAT-CR']) : ''; ?>" >
            <p class="app-label-2">MA</p><input type="text" name="SAT-MA" value="<?php echo isset($_POST['SAT-MA']) ? htmlspecialchars($_POST['SAT-MA']) : ''; ?>" >
            <p class="app-label-2">Total (CR + MA)</p><input type="text" name="SAT-total" value="<?php echo isset($_POST['SAT-total']) ? htmlspecialchars($_POST['SAT-total']) : ''; ?>" >
            <p class="app-label-2">WR</p><input type="text" name="SAT-WR" value="<?php echo isset($_POST['SAT-WR']) ? htmlspecialchars($_POST['SAT-WR']) : ''; ?>" >
            <p class="app-label-2">ES</p><input type="text" name="SAT-ES" value="<?php echo isset($_POST['SAT-ES']) ? htmlspecialchars($_POST['SAT-ES']) : ''; ?>" >
            <p class="app-label-2 upload-scores">Upload Scores (Optional. File must not exceed 1MB. PDF files only.)</p><input type="file" name="transcripts8" accept=".pdf" />
            <hr/>
            <h4>ACT:</h4>
            <p class="app-label-2">Date:</p><input type="date" name="ACT-date" value="<?php echo isset($_POST['ACT-date']) ? htmlspecialchars($_POST['ACT-date']) : ''; ?>" >
            <p class="app-label-2">EN</p><input type="text" name="ACT-EN" value="<?php echo isset($_POST['ACT-EN']) ? htmlspecialchars($_POST['ACT-EN']) : ''; ?>" >
            <p class="app-label-2">MA</p><input type="text" name="ACT-MA" value="<?php echo isset($_POST['ACT-MA']) ? htmlspecialchars($_POST['ACT-MA']) : ''; ?>" >
            <p class="app-label-2">RE</p><input type="text" name="ACT-RE" value="<?php echo isset($_POST['ACT-RE']) ? htmlspecialchars($_POST['ACT-RE']) : ''; ?>" >
            <p class="app-label-2">SR</p><input type="text" name="ACT-SR" value="<?php echo isset($_POST['ACT-SR']) ? htmlspecialchars($_POST['ACT-SR']) : ''; ?>" >
            <p class="app-label-2">Comp</p><input type="text" name="ACT-Composite" value="<?php echo isset($_POST['ACT-Composite']) ? htmlspecialchars($_POST['ACT-Composite']) : ''; ?>" >
            <p class="app-label-2">E/W</p><input type="text" name="ACT-EW" value="<?php echo isset($_POST['ACT-EW']) ? htmlspecialchars($_POST['ACT-EW']) : ''; ?>" >
            <p class="app-label-2">WR</p><input type="text" name="ACT-WR" value="<?php echo isset($_POST['ACT-WR']) ? htmlspecialchars($_POST['ACT-WR']) : ''; ?>" >
            <p class="app-label-2 upload-scores">Upload Scores (Optional. File must not exceed 1MB. PDF files only.)</p><input type="file" name="transcripts8" accept=".pdf" />
        </div>
        <div id="antecedents">
            <h2>7.Antecedents and Final Agreement</h2>
            <h3>Failure to answer these questions will result in a delay in processing your application</h3>
            <p class="section-text" id="antecedent-1">Are you currently, or have you ever been, charged with or subject to disciplinary action for scholastic or any other
type of behavioral misconduct at any educational institution? You do not need to disclose academic dismissal,
suspension, or probation for poor grades. However, you will be required to furnish FSU with a written explanation of
the event(s) if there was academic misconduct (such as plagiarism or cheating) or behavioral misconduct, and tell us
what you have learned from your past action(s).</p>
            <input type="radio" name="ant-1" id="ant-1-yes" value="yes"  <?php echo $_POST['ant-1'] == "yes" ? "checked" : ''; ?> required><p class="app-radio-label">Yes</p>
            <input type="radio" name="ant-1" id="ant-1-no" value="no"  <?php echo $_POST['ant-1'] == "no" ? "checked" : ''; ?> required><p class="app-radio-label">No</p>
            <div class="written-statement-node" id="written-1">
                <br />
                <h4>Tell us what you have learned from your past action(s):</h4>
                <textarea cols="10" rows="6" name="statement1" class="written-statement" form="applyform" >
                    <?php echo isset($_POST['statement1']) ? htmlspecialchars($_POST['statement1']) : ''; ?>
                </textarea>
            </div>
            <br />
            <p class="section-text" id="antecedent-2">Have you ever been charged with a violation of the law which resulted in, or if still pending could result in,
probation, community service, a jail sentence, or the revocation or suspension of your driver’s license (including
traffic violations which resulted in a fine of $200 or more)? You will be required to furnish FSU with a list of all
violations, and include a statement telling us what you have learned from your past action(s).</p>
            <input type="radio" name="ant-2" id="ant-2-yes" value="yes" <?php echo $_POST['ant-2'] == "yes" ? "checked" : ''; ?> required><p class="app-radio-label">Yes</p>
            <input type="radio" name="ant-2" id="ant-2-no" value="no" <?php echo $_POST['ant-2'] == "no" ? "checked" : ''; ?> required><p class="app-radio-label">No</p>
            <div class="written-statement-node" id="written-2">
                <br />
                <h4>Tell us what you have learned from your past action(s):</h4>
                <textarea cols="10" rows="6" name="statement2" class="written-statement" >
                    <?php echo isset($_POST['statement2']) ? htmlspecialchars($_POST['statement2']) : ''; ?>
                </textarea>
            </div>
            <br />
            <p class="section-text" id="antecedent-3">Have you ever been charged with a felony (even if adjudication was withheld)? You will be required to furnish
FSU with a copy of your criminal background history from each state in which the violation(s) occurred. If the
violation(s) occurred in Florida, the criminal background history can be emailed to the Office of Admissions at
admsofficer@admin.fsu.edu from the Florida Department of Law Enforcement (www.fdle.state.fl.us). You will
also be required to furnish a statement telling us what you have learned from your past action(s).</p>
            <input type="radio" name="ant-3" id="ant-3-yes" value="yes" <?php echo $_POST['ant-3'] == "yes" ? "checked" : ''; ?> required><p class="app-radio-label">Yes</p>
            <input type="radio" name="ant-3" id="ant-3-no" value="no" <?php echo $_POST['ant-3'] == "no" ? "checked" : ''; ?> required><p class="app-radio-label">No</p>
            <div class="written-statement-node" id="written-3">
                <br />
                <h4>Tell us what you have learned from your past action(s):</h4>
                <textarea cols="10" rows="6" name="statement3" class="written-statement">
                    <?php echo isset($_POST['statement3']) ? htmlspecialchars($_POST['statement3']) : ''; ?>
                </textarea>
            </div>
            <br />
            <p class="section-text" id="ant-disclaimer">If your answer to any of the above questions is “yes,” the University reserves the right to request additional information. If your records
have been expunged pursuant to applicable law, you are not required to answer yes to these questions. If you are unsure whether
you should answer yes, we strongly suggest you answer yes and fully disclose all incidents. By doing so, you can avoid any risk of
disciplinary action or revocation of an offer of admission.</p>
            <br />
            <h3>Important: You must read and accept the following section in order to complete your application to FSU.</h3>
            <p class="section-text">I understand that this application is for admission to Florida State University and is valid only for the term indicated on the application.<br />
I also understand and agree that I will be bound by the University’s regulations concerning application deadline dates and admission
requirements. I further agree to the release of all transcripts and test scores to this institution, including test score reports that this institution may request.<br />
I certify that the information given in this application is complete and accurate and I understand that to make false or fraudulent statements within this application or Certification of Financial Responsibility Form may result in disciplinary action, denial of admission, and<br />
invalidation of credits or degrees earned. If admitted, I agree to abide by the policies of the Florida Board of Education and the rules and regulations of Florida State University. Should any of the information I have given change prior to my enrollment at the University, I shall immediately notify the Office of Admissions.<br />
I understand that the one hundred dollar ($100.00) payment that must accompany this application is a nonrefundable fee, and that this application and all supporting materials become the property of
Florida State University. No items will be returned to the applicant or forwarded to other institutions or third
parties.</p>
           <input type="checkbox" name="agree" style="float: left; margin-right: 10px;"><p class="app-radio-label" style="margin-top: 3px;
margin-bottom: 20px;">I agree to the terms described above</p>
        <h3>Please solve the CAPTCHA to continue:</h3>
        <input type="submit" value="submit" style="float:left; clear:both; margin-top:50px;" disabled>
        </div>
    </form>
</body>