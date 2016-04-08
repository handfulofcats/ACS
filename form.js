$( document ).ready(function() {
    
    var clicks0 = 0;
    //alert(clicks0);
    
    $("#add-sec").click(function(){
        
        switch(clicks0++) {
                case 0:
                $("#sec1").css("display","block");
                $("#sec1 :input").prop("required",true);
                $("#sec1 .degree-input").prop("required",false);
                //alert(clicks0);
                $(this).prop("disabled",true);
                break;
        }

    });
    
    $("#sec1-close").click(function() {
            clicks0 = 0;
            $("#sec1").css("display","none");
            $("#sec1 :input").prop("required",false);
            $("#add-sec").prop("disabled",false);
        });
    
    var clicks1 = 0;
    
    $("#add-postsec").click(function(){
        
        switch(clicks1++) {
                case 0:
                $("#postsec1").css("display","block");
                //$("#postsec1 :input").prop("required",true);
                //alert(clicks1);
                break;
                
                case 1:
                $("#postsec2").css("display","block");
                //$("#postsec2 :input").prop("required",true);
                //alert(clicks1);
                break;
                
                case 2:
                $("#postsec3").css("display","block");
                //$("#postsec3 :input").prop("required",true);
                $(this).prop("disabled",true);
                //alert(clicks1); 
                break;
        }

    });
    
    $(".postsec-close").click(function(){
        switch(clicks1--) {
            case 3:
            $("#postsec3").css("display","none");
            //$("#postsec3 :input").prop("required",false);
            //alert(clicks1);
            $("#add-postsec").prop("disabled",false);
            break;
            
            case 2:
            $("#postsec2").css("display","none");
            //$("#postsec2 :input").prop("required",false);
            //alert(clicks1);
            $("#add-postsec").prop("disabled",false);
            break;
            
            case 1:
            $("#postsec1").css("display","none");
            //$("#postsec1 :input").prop("required",false);
            //alert(clicks1);
            $("#add-postsec").prop("disabled",false);
            break;
        }
    });
        
    
    var clicks2 = 0;
    
    $("#add-employment-node").click(function(){
        
        switch(clicks2++) {
                
            case 0:
            $("#employment-node-1").css("display","block");
            break;
                
            case 1:
            $("#employment-node-2").css("display","block");
            break;
                
            case 2:
            $("#employment-node-3").css("display","block");
            $(this).prop("disabled",true); 
            break;
        }
    });
    
    
    $("input[name='ant-1']").change(function(e ){
        
        if($(this).val() == 'yes') {

            $("#written-1").css("display","block");
            $("#antecedent-2").css("margin-top","55px");

        } else {

            $("#written-1").css("display","none");
            $("#antecedent-2").css("margin-top","10px");

    }

});
    
    $("input[name='ant-2']").change(function(e){
        
    if($(this).val() == 'yes') {
        
            $("#written-2").css("display","block");
            $("#antecedent-3").css("margin-top","55px");

        } else {

            $("#written-2").css("display","none");
            $("#antecedent-3").css("margin-top","10px");

    }
});
    
    $("input[name='ant-3']").change(function(e){
        
    if($(this).val() == 'yes') {
        
            $("#written-3").css("display","block");
            $("#ant-disclaimer").css("margin-top","55px");

        } else {

            $("#written-3").css("display","none");
            $("#ant-disclaimer").css("margin-top","10px");

    }
});

$("input[name='agree']").change(function(e){
        
    if( $(this).is(':checked') ) {
        
        $("input[type='submit']").prop("disabled",false); 
    }
    else {
        $("input[type='submit']").prop("disabled",true);
    }

});
     
// RECAPTCHA

$("#application_submit").on("click", function (e) {
    e.preventDefault();
    /* Check if the captcha is complete */ 
    if ($("#g-recaptcha-response").val()) {
        
        $.ajax({
            type: 'POST',
            url: "captcha.php", 
            dataType: 'json',
            async: true,
            data: {
                captchaResponse: $("#g-recaptcha-response").val()
            },
            success: function (response) {
                if(response["type"] == "success")
                {
                    //console.log(response.message);
                    $("#applyform").submit();                   
                }
                else
                {
                    alert(response.message);
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {               
                alert("We could not verify that you are not a spambot. Please try again");
            }
        });
    } 
    else 
    {
        e.preventDefault();
        alert("Please fill in the CAPTCHA");
    }
});

});