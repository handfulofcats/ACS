<?php
$captcha = filter_input(INPUT_POST, 'captchaResponse'); // get the captchaResponse parameter sent from our ajax
 
/* Check if captcha is filled */
    if (!$captcha) {
        http_response_code(401); // Return error code if there is no captcha
    }
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LcQug8TAAAAAOziRovZYH7dy8F6JUipRu9BFNuM&amp;amp;response=" . $captcha);
    if ($response . success == false) {
        echo json_encode(array("message" => "We could not verify that you are not a spambot. Please try again.", "type" => "error"));
    } else {
       echo json_encode(array("message" => "Success", "type" => "success"));
    }
?>