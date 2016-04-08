<?php
    require('../wp-blog-header.php');
    get_header();

    session_start();
    // if EMPLID hasn't been set, kick user to application start
    if ( !isset($_SESSION['emplid']) )
    {
        header("location:http://panama.fsu.edu/apply/application-start/");
        exit(0);
    }
?>

<html>
    <head>
        <script src="form.js"></script>
    </head>
    <body>
        
    <style>
    
    h3 {
        line-height: 30px;
    }
     
    #success-container {
        width: 70%;
        margin: 20px auto;
        display: block;
        border: solid thin #ccc;
        border-radius: 5px;
        box-shadow: #f1f1f1 2px 2px 5px;
        padding: 20px;
        box-sizing: border-box;
    }
    
    body {
        font-family: roboto, arial;
    }
    
    #payment h2 {
        width: 100%;
        padding-bottom: 10px;
        border-bottom: solid thin #ccc;
    }
    
    .pay-option {
        display: table-cell;
        width: 25%;
        height: auto;
        padding: 20px;
        border: solid thin #ccc;
        border-right: none;
        text-align: center;
    }
    
    .pay-option p, i {
        font-size: 1em;
    }
    
    .pay-option:nth-child(-n+1) {
        border-radius: 0 5px 5px 0;
    }
    
    .pay-option:last-child {
        border-right: solid thin #ccc;
        border-radius: 0 5px 5px 0;
    }
    
    .pay-icon {
        max-width: 100px
    }

</style>
        
<div id="success-container">
    <h2>Successfully submitted application for <?php echo $_SESSION['fullname']; ?></h2>
    <strong style="font-size: 1.3em; line-height: 30px;">Your EMPLID is (use this code for reference and enquiries about your application): <?php echo $_SESSION['EMPLID']; ?></strong>
    <p>You will receive your application in PDF format at <?php echo htmlspecialchars($_SESSION['email']) ?> </p>

    <div id="payment">
        <h2>Paying your application fee</h2>
        <p>You need to cancel the non-refundable $100 application fee for the university to process your application. The application will not be processed without the fee, and under no circumstances will the University waive or postpone the application fee. Your application will expire within three weeks if we do not receive your payment.</p>
        <h3>We offer the following payment options:</h3>
        
        <div class="pay-option"><img class="pay-icon" src="http://panama.fsu.edu/wp-content/uploads/2015/05/earth.png" alt="" />
            <h3>Pay now via online</h3>
            <p>We accept Visa or Mastercard.</p>
            <a href="online-payment.php" class="btn"><p>Proceed with online payment</p></a>
        </div>
        
        <div id="phone" class="pay-option"><img class="pay-icon" src="http://panama.fsu.edu/wp-content/uploads/2015/05/phonecard.png" alt="" />
            <h3>By Telephone</h3>
            <p>Call +507 317-0367 ext. 222 (Cashier's Office) and provide
            your credit card information to process your payment.</p>
            <i>NOT a Toll-Free number</i>
        </div>
        
        <div class="pay-option"><img class="pay-icon" src="http://panama.fsu.edu/wp-content/uploads/2015/05/check.png" alt="" />
            <h3>By check</h3>
            <p><strong>Only Panamanian or U.S. banks.</strong> Make payable to "Fundación Florida State University - Panama"</p>
        </div>
        
        <div class="pay-option"><img class="pay-icon" src="../wp-content/uploads/2015/05/user.png" alt="" />
            <h3>Pay On-site</h3>
            <p>If you are in Panama City you can pay on-site at our campus with credit or CLAVE cards, checks or cash</p>
                &nbsp;
            <i>Address: City of Knowledge, Building #227, Clayton, Panama City.</i>
        </div>
        
    </div>
</div>
</body>
</html>