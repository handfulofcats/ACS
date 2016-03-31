/* Copyright (C) Andres Canelones 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Andrés Canelones <andrescanelones10@gmail.com>, March 2016
 */

var role_update = "<select class=\"update_select\" name=\"update_select\">\r\n" +
					"<option value=\"admissions\">Admissions<\/option>\r\n" +
					"<option value=\"pdp\">PDP<\/option>\r\n" +
					"<option value=\"accounting\">Accounting<\/option>\r\n" +
					"<option value=\"housing\">Housing<\/option>\r\n" +
					"<option value=\"admin\">Admin<\/option>\r\n" +
					"<\/select>";

function updateRole (cell) {
	
	//get row parameters for updating record
	var updateTarget = { 
						 row:	 cell.closest("tr"), //jquery selector for row to be updated, won't be used by dbfunctions.php
                         name:  cell.closest("tr").find(".user_name").text(),
						 email: cell.closest("tr").find(".user_email").text()
					   };
	
	var oldVal = cell.text();					//store current value
	cell = cell.closest("td");					//move cell selector from span to table cell	   
	cell.empty().html(role_update);				//insert and focus update_select element to show update choices	(selects current choice to avoid accidental changes)
	
	var update_select = cell.find("select");	//contain update_select in variable for abstraction purposes
	
	//auto-focus and select current value to avoid accidental changes
	update_select.focus();
	$(update_select,"option").attr("selected","false");
	update_select.find('option[value="' + oldVal + '"]').attr("selected","selected");
	
	var newVal_ = update_select.val();			//copy value from update_select to newVal immediately
	
	//update newVal if update_select changes
	update_select.change(function(e) {
		newVal_ = $(this).val();
		update_select.blur(); //remove focus from update_select
	});
	
	//When update_select goes out of focus update data
	update_select.focusout(function(e) {

		if (newVal_ != oldVal)
		{
			var promptMessage = "Are you sure you want to change the role of " + 
			updateTarget['name'] + "?";
			
			displayPrompt(promptMessage, function(response) {
				if (response == true)
				{ 
                    
                    var targetData = JSON.stringify(updateTarget);
	
                    $.ajax({
                        url:"../assets/ajaxcatcher.php",
                        dataType: 'html',
                        type: 'POST',
                        data: {func:'UpdateRole', updateTarget:targetData, newVal:newVal_},
                        
                        success: function(html){
                            
                            //insert updated row into table
                            updateTarget['row'].before(html);
                            var newRow = updateTarget['row'].prev();

                            user_SetHandlers(newRow);            // SetHandlers on new row	
                            updateTarget['row'].remove();   // remove old row at last
                            displayMessage("User role for " + updateTarget['name'] + " updated.", "success_message");		
                        },
                        
                        error: function(){
                            update_select.closest("td").empty().html("<span>" + oldVal + "<img src=\"../assets\/write.png\" class=\"edit-icon\" \/><\/span>");
                            displayMessage('There was an error updating the role. Try Again.','error_message');
                        }
                    });

				}
				else 
				{ 
					update_select.closest("td").empty().html("<span>" + oldVal + "<img src=\"../assets\/write.png\" class=\"edit-icon\" \/><\/span>"); 
				}
			});
        }
		else 
		{ $(this).closest("td").empty().html("<span>" + oldVal + "<img src=\"../assets\/write.png\" class=\"edit-icon\" \/><\/span>"); }	
	});	
};

function user_SetHandlers(row)
{
    row.find(".user_role").on("click", "span", function(){
        updateRole($(this))
    });
    
    row.find(".actions_delete").on("click", function(e){
       DeleteUser($(this).closest("tr")); 
    });
    
}

function DeleteUser(row)
{
    // Get target info
    var target = { 
            name:    row.find(".user_name").text(),
            email: 	 row.find(".user_email").text()
    };
    
    displayPrompt("Are you sure you want to delete this user?" +
                  "<br/>" + target['name'] + 
                  "<br/> This cannot be undone.", 
                  function(response){
                      if(response == false)
                      {
                          return false;
                      }
                      else
                      {
                        var targetData = JSON.stringify(target);

                        $.ajax({
                                url:"../assets/ajaxcatcher.php",
                                type: 'POST',
                                dataType: 'html',
                                data: {func:'DeleteUser', target_:targetData},
                                success: function(html){
                                    row.remove();        
                                    displayMessage("The user was removed succesfully.", "success_message");                           
                                },
                                
                                error: function(){
                                    displayMessage("An unexpected error has ocurred. Try again later.", "error_message");
                                }
                        });

                      }
                  });    
                                           
};

$( document ).ready(function() {
    
    $(".user_row").each(function(){
        user_SetHandlers($(this));
    })
    
    /////////////////////////////
    //////user configuration/////
    /////////////////////////////-----------------------------------------------------------------//

    $("#userconfig_changepass").on('click', function(){
        
        var dialog = "<h1>Change password<\/h1>" +
        "<h3>Input current password:<\/h3>" +
        "\r\n<input id=\"passchange_curr_pass\" class=\"text_input\" name=\"curr_pass\" type=\"password\" \/>\r\n" +
        "<h3>Enter your new password:<\/h3>\r\n" +
        "<p class=\"description\">Passwords must be 8-70 characters in lenght<\/p>" +
        "<input  id=\"passchange_new_pass\" class=\"text_input\" name=\"new_pass\" type=\"password\" \/>\r\n" +
        "<h3>Confirm your new password:<\/h3>\r\n" +
        "<input  id=\"passchange_confirm_pass\" class=\"text_input\" name=\"confirm_pass\" type=\"password\" \/>";
        
        // Add prompt div to the DOM
        $('body').prepend("<div id=\"password_dialog\">\r\n<p>" + dialog + "<\/p>\r\n" +
        "<button value=\"1\" class=\"prompt_button\">Change Password<\/button>\r\n" +
        "<button value=\"0\" class=\"prompt_button\">Cancel<\/button>\r\n<\/div>" +
        "<div id=\"prompt_bg\"></div>");
        
        $(".prompt_button").on('click', function() {
            //if user proceeds with pass change
            if (Number($(this).val() == 1))
            {
                if ($("#passchange_curr_pass").val() == "" || $("#passchange_new_pass").val() == "" || $("#passchange_confirm_pass").val() == "")
                {
                    displayMessage("Please fill out all fields.", "error_message");
                    $("#passchange_curr_pass, #passchange_new_pass, #passchange_confirm_pass").css("outline","solid 2px red");
                    return false;
                }
                else
                {
                    var input_data = {
                        curr_pass: $("#passchange_curr_pass").val(),
                        new_pass:  $("#passchange_new_pass").val(),
                        confirm_pass: $("#passchange_confirm_pass").val()
                    };
                    console.log(input_data);
                    var change_data = JSON.stringify(input_data);
                    
                    $.ajax({
                        url:"../assets/ajaxcatcher.php",
                        dataType: "json",
                        type: 'POST',
                        data: {func:'changePassword', pass_data: change_data},
                        success: function(result){
                            if (result['type'] == "error_message")
                            {
                                //clear fields
                                $("#passchange_curr_pass, #passchange_new_pass, #passchange_confirm_pass").val("");
                                displayMessage(result['message'],result['type']); 
                            }
                            else if (result['type'] == "success_message")
                            {
                                $("#password_dialog, #prompt_bg").remove();
                                displayMessage(result['message'],result['type']); 
                            }
                                
                        },
                        error: function( jqxhr, textStatus, error ) {
                            displayMessage("An unexpected error ocurred. Please try again later.", "error_message");
                        }
                    });   
                    
                }
            }
            else
            {
                $("#password_dialog, #prompt_bg").remove();
                return false;
            }
            
        });
        
    }); //end of changePassword

    
    
});