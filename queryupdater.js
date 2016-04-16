/* Copyright (C) Andres Canelones 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Written by Andrés Canelones <andrescanelones10@gmail.com>, January 2016
 */

/*----- html strings repository (to keep code readable) -----*/

var sortby = 'date';
var order = 'DESC';

var fee_update = "<select class=\"update_select\" name=\"update_select\">\r\n<option value=\"not paid\">not paid<\/option>\r\n<option value=\"paid\">paid<\/option>\r\n<\/select>";

var status_update = "<select class=\"update_select\" name=\"update_select\">\r\n" +
					"<option value=\"admitted\">admitted<\/option>\r\n" +
					"<option value=\"denied\">denied<\/option>\r\n" +
					"<option value=\"in process\">in process<\/option>\r\n" +
					"<option value=\"not processed\">not processed<\/option>\r\n" +
					"<option value=\"pending documents\">pending documents<\/option>\r\n" +
					"<option value=\"pending TOEFL\">pending TOEFL<\/option>\r\n" +
					"<\/select>";

var processor_update;

var bulk_execute = "<button id=\"bulk_execute_button\">Go</button>";

var bulk_cancel = "<img src=\"assets/close.png\" id=\"bulk_cancel_button\"";

// Get processors list
function GetAdmissionProcessors() {
    $.ajax ({
        url:"assets/ajaxcatcher.php",
        dataType: 'json',
        type: 'POST',
        data: {func:'GetAdmissionProcessors'},
        success: function(result){
            processor_update = "<select class=\"update_select\" name=\"update_select\">\r\n";
            
            $.each(result, function(index, value){
                processor_update += "<option value=\""+ value +"\">"+ value + "<\/option>\r\n";
            });
            processor_update += "<\/select>";
        },
        error: function( jqxhr, textStatus, error ) {
            displayMessage("An unexpected error ocurred. Please reload the site.", "error_message");
            console.log(jqxhr);
            console.log(textStatus);
            console.log(error);
        }
    });
}


/*-----------------------------------------------------------*/

// display messages above
function displayMessage(message, type) {
    
    //removes any existing messages
    $("#warning_message").remove();
    $("#success_message").remove();
    $("#error_message").remove();
    
    $('body').prepend("<div id=\"" + type + "\">" + message + "<\/div>");

        setTimeout(function(){
        $('#' + type).css("opacity","1");
        },100);

        setTimeout(function(){
            $('#' + type).css("opacity","0");
        },4000);

        setTimeout(function(){
            $('#' + type).remove();
        },4200);
};

// Prompt messages
function displayPrompt(message, callback) {
	
	// Add prompt div to the DOM
	$('body').prepend("<div id=\"prompt\">\r\n<p>" + message + "<\/p>\r\n" +
	"<button value=\"1\" class=\"prompt_button\">Do It<\/button>\r\n" +
	"<button value=\"0\" class=\"prompt_button\">Cancel<\/button>\r\n<\/div>" +
	"<div id=\"prompt_bg\"></div>");
	
	var response; // Declare variable that will be returned
	
	$('.prompt_button').on('click', function() {
		response = Number($(this).val()); //store response from user
		$('#prompt, #prompt_bg').remove(); //Remove prompt from DOM
		callback(Boolean(response));
	});
};

// Set event handlers for dynamically-generated data
function SetHandlers(row) {
	// cell updates
	row.find(".fee_cell").on("click", "span", function(){
		updateCell($(this), "fee_paid", fee_update);
	});
	
	row.find(".status_cell").on("click", "span", function(){
		updateCell($(this), "status", status_update);
	});
	
	row.find(".processor_cell").on("click", "span", function(){
		updateCell($(this), "processor", processor_update);
	});
    
    // bulk actions
	row.find('.bulk_select').on("change", function(){
		enableBulk();
	});
   
	// Download Application Package
    row.find(".actions_app_pack").on("click", function(e){
		e.preventDefault(); //prevent page reload on hidden form submission
        
        var downloadTarget = { EMPLID: $(this).closest("tr").attr('id'),
						   term: $(this).closest("tr").find(".term_cell").text(),
						   year: $(this).closest("tr").find(".year_cell").text()
					     };
        
		//hidden form to post data
        $(this).append('<form class=\"hidden\" name=\"download_request\" id=\"download_request\" method=\"post\" action=\"assets/ajaxcatcher.php\">' +
                       '\r\n<input class=\"hidden\" type=\"hidden\" name=\"EMPLID\" value=\"' + downloadTarget['EMPLID'] + '\">' + 
                       '\r\n<input class=\"hidden\" type=\"hidden\" name=\"term\" value=\"' + downloadTarget['term'] + '\">' +
                       '\r\n<input class=\"hidden\" type=\"hidden\" name=\"year\" value=\"' + downloadTarget['year'] + '\">' +
                       '\r\n<input class=\"hidden\" type=\"hidden\" name=\"func\" value=\"DownloadPackage\">' + 
                       '<\/form>');
        
        var form = $('#download_request');
        form.submit(); // submit hidden form
        
        setTimeout(function(){
            $('#download_request').remove(); // remove form from DOM
        },100);
       
	});
    
    // Add documents to application package
    row.find(".actions_transcripts").on("click", function(e){
       modifyPackage($(this).closest("tr")); 
    });
    
    // Delete application
    row.find(".actions_delete").on("click", function(e){
       DeleteApplication($(this).closest("tr")); 
    });
    
    // Display comments
    row.find(".EMPLID_cell").on("click", function(){
       DisplayComments($(this)); 
    });
    
};

// Main Ajax request function
function GetApplications (filter_, sortby, order_) {
	
	var filterData = JSON.stringify(filter_); //prepare filter data to POST
	
	$.ajax({
		url:"assets/ajaxcatcher.php",
		dataType: 'html',
		type: 'POST',
		data: {func:'GetApplications', filter:filterData, sort:sortby, order:order_},
		
		success: function (html) {
			$('#app_table .app_row, #app_table #app_details_row').remove();
			$('#app_table').append(html);
            
			//SetHandlers on all rows
            $(".app_row").each(function() {
                SetHandlers($(this));
            });		   
		},
		
		error: function() {
			displayMessage('Could not fetch applications. Please try again, or check your internet connection.','error_message');
		}
	});
};

// Function for updating values in applications
function UpdateApplication (updateTarget, column_, newVal_, callback) {
	
	var targetData = JSON.stringify(updateTarget);
	
	$.ajax({
		url:"assets/ajaxcatcher.php",
		dataType: 'html',
		type: 'POST',
		data: {func:'UpdateApplication', updateTarget:targetData, column:column_, newVal:newVal_},
        
        success: function(html){
            
			//insert updated row into table
            updateTarget['row'].before(html);
            var newRow = updateTarget['row'].prev();
            
            //if the row to be updated has been selected for bulk actions, retain the selection
            if (updateTarget['row'].find(".bulk_select").prop("checked") == true) 
            {               
                newRow.find(".bulk_select").prop("checked",true);
                updateTarget['row'].remove();
            }
            
            //copy other properties into the new row
            var rowAttributes = updateTarget['row'].prop("attributes");
            $.each(rowAttributes, function() {
                updateTarget['row'].prev().attr(this.name, this.value);
            });

            SetHandlers(newRow);            // SetHandlers on new row	
            updateTarget['row'].remove();   // remove old row at last		
			callback(true);                 // return to handler
        },
        
		error: function(){
			callback(false);
		}
	});
};

// For updating data in application cells
function updateCell (cell, column, update_type) {
	
	//get row parameters for updating record
	var updateTarget = { 
						 row:	 cell.closest("tr"), //jquery selector for row to be updated, won't be used by dbfunctions.php
						 first_name: cell.closest("tr").find(".first_name_cell").text(),
						 last_name: cell.closest("tr").find(".last_name_cell").text(),
						 EMPLID: cell.closest("tr").attr('id'),
						 term: 	 cell.closest("tr").find(".term_cell").text(),
						 year: 	 cell.closest("tr").find(".year_cell").text()
					   };
	
	var oldVal = cell.text();					//store current value
	cell = cell.closest("td");					//move cell selector from span to table cell	   
	cell.empty().html(update_type);				//insert and focus update_select element to show update choices	(selects current choice to avoid accidental changes)
	
	var update_select = cell.find("select");	//contain update_select in variable for abstraction purposes
	
	//auto-focus and select current value to avoid accidental changes
	update_select.focus();
	$(update_select,"option").attr("selected","false");
	update_select.find('option[value="' + oldVal + '"]').attr("selected","selected");
	
	var newVal = update_select.val();			//copy value from update_select to newVal immediately
	
	//update newVal if update_select changes
	update_select.change(function(e) {
		newVal = $(this).val();
		update_select.blur(); //remove focus from update_select
	});
	
	//When update_select goes out of focus update data
	update_select.focusout(function(e) {

		if (newVal != oldVal)
		{
			var promptMessage = "Are you sure you want to change this? </br> The record of applicant <strong>" + 
			updateTarget['first_name'] + " " + updateTarget['last_name'] + "</strong> will be updated.";
			
			displayPrompt(promptMessage, function(response) {
				if (response == true)
				{ 
					UpdateApplication(updateTarget, column, newVal, function(response) {						
						if (response == true)
						{ displayMessage('Record for applicant \'' + updateTarget['first_name']  + " " + updateTarget['last_name'] + '\' succesfully updated.', 'success_message'); }
						
						else 
						{ 
                            update_select.closest("td").empty().html("<span>" + oldVal + "<img src=\"assets\/write.png\" class=\"edit-icon\" \/><\/span>");
                            displayMessage('There was an error updating this record. Try Again.','error_message'); 
                        }
						
					}); 
				}
				else 
				{ 
					update_select.closest("td").empty().html("<span>" + oldVal + "<img src=\"assets\/write.png\" class=\"edit-icon\" \/><\/span>"); 
				}
			});
        }
		else 
		{ $(this).closest("td").empty().html("<span>" + oldVal + "<img src=\"assets\/write.png\" class=\"edit-icon\" \/><\/span>"); }	
	});	
}; //end of updateCell

//Updates filters
function GetFilters() {
	
	var filter = { limit: $('#query_limit').val(),
				   status: $('#status_filter').val(),
				   term: $('#term_filter').val(),
				   year: $('#year_filter').val(), 
				   EMPLID: $('#EMPLID_filter').val(),
				   };
	
	return filter;
};

///BULK PROCESSING///-----------------------------------------------------/

// enabling and disabling bulk actions menu
function enableBulk() {
    
    function loop(callback) {
                
        var checked = false;
        
        $('.bulk_select').each(function() {		
            if ( $(this).prop("checked") == true ) //if there's at least one checkbox checked enable bulk actions
            {
                $("#bulk_action").prop("disabled",false);
                checked = true; //set callback response to true
                return false; //break out of each loop
            }
        });    
        callback(checked);  //go to callback   
    };
    
    loop(function(response){
       if (response == true)
       {
           return false; //do nothing, break out
       } 
       else
       {
           $("#bulk_action").find('option[value=""]').prop("selected",true); // Reset bulk actions dropdown
	       $("#bulk_action").prop("disabled",true);
	       $('#bulk_actions').find('.bulk_submenu').remove(); //remove any lingering submenus
		   return true;
       }
    });
};

// Execute bulk actions
function executeBulkAction() {
	var promptMessage = "This will modify " + $('.bulk_select:checked:enabled').length + " record(s). <br/> Are you sure you want to do this?";
	
    displayPrompt(promptMessage, function(response){
	if (response == false)
		return false; //Do not perform bulk actions, otherwise carry on.
	else
	{
	var action = $('select#bulk_action').val();
	var updated = 0;
	var failed = 0;
	
	displayMessage("Updating Records...", "warning_message");
	
	$('.bulk_select:checked:enabled').each( function() {
		
		var updateTarget = { 
				row:	 $(this).closest("tr"), //jquery selector for row to be updated, won't be used by dbfunctions.php
				EMPLID:  $(this).closest("tr").attr('id'),
				term: 	 $(this).closest("tr").find(".term_cell").text(),
				year: 	 $(this).closest("tr").find(".year_cell").text()
		};
		
		var newVal, oldVal, cell;
		
		switch (action)
		{
		case "change_status":			
			newVal = $('.bulk_submenu .update_select').val(); // New value
			oldVal = updateTarget['row'].find("status_cell").text(); //old value
			cell = "status";					
			break;
			
		case "change_fee_status":
			newVal = $('.bulk_submenu .update_select').val(); // New value
			oldVal = updateTarget['row'].find("fee_cell").text(); //old value
			cell = "fee_paid";
			break;
			
		case "assign_processor":
			newVal = $('.bulk_submenu .update_select').val(); // New value
			oldVal = updateTarget['row'].find("processor_cell").text(); //old value
			cell = "processor";
			break;
			
		case "download_app_pack":
			break;
			
		default:
			return false; //break out
		}
		
		//perform bulk actions
		if (action == "change_status" || action == "change_fee_status" || action == "assign_processor" )
		{
			if (newVal != oldVal) //additional check to avoid unnecessary queries
			{
				UpdateApplication(updateTarget, cell, newVal, function(result) {				
					if (result == true)
					{ 	
						updated++; 
					}
					else
					{ 
						failed++; 
					}
				});
			}
			else { updated++; } //Count it as updated even though the value was the same
		}
		else if (action == "download_app_pack")
		{
			//nothing for now...
		}
	});
	
    //re-check everything if select all was set
    if ( ('#bulk_select_all').checked )
    {
        $('.bulk_select').prop("checked",true);
    }
    
    $('#bulk_action').find('option[value=""]').prop("selected",true); // Reset bulk actions dropdown
	$('#bulk_actions').find('.bulk_submenu').remove(); //remove any lingering submenus
    
	//Display messages after bulk processing is done (wait 2 seconds for ajax to finish)
	setTimeout( function(){
		//remove "updating records..." message	
		$("#warning_message").remove();
		
		//display result messages
		if (action == "change_status" || action == "change_fee_status")
		{
			if (updated > 0 && failed == 0) //success message
			{
				displayMessage(updated + " record(s) successfully updated.", "success_message");
			}
			else if (updated > 0 && failed > 0) //warning message
			{
				displayMessage(updated + " record(s) successfully updated. However, " + failed + " record(s) could not be updated.", "warning_message");
			}
			else if (updated == 0 && failed > 0) //error message
			{
				displayMessage("Records could not be updated. Please try again.", "error_message");
			} 
		}	
		else if (action == "assign_processor")
		{
			var newProcessor = $('#bulk_submenu .update_select').val(); //get name of new processor
			
			if (updated > 0 && failed == 0) //success message
			{
				displayMessage(updated + " record(s) have been assigned to " + newProcessor, "success_message");
			}
			else if (updated > 0 && failed > 0) //warning message
			{
				displayMessage(updated + " record(s) have been assigned to " + newProcessor + ". However, " + failed + "record(s) could not be updated.", "warning_message");
			}
			else if (updated == 0 && failed > 0) //error message
			{
				displayMessage("Records could not be updated. Please try again.", "error_message");
			} 
		}
	}, 2000);
	
	} //end of displayPrompt else
    }); //end of displayPrompt
	
}; // end of executeBulkAction()

//-------------------------------------------------------------------------/

///COMMENTS///-------------------------------------------------------------/

function GetComments(target, callback) {
    var targetData = JSON.stringify(target);
    
    $.ajax({
		url:"assets/ajaxcatcher.php",
		dataType: 'html',
		type: 'POST',
		data: {func:'DisplayComments', target_:targetData},
        success: function(html){          
            callback(true, html);
        },
        error: function(html){
            callback(false, html);
        }
    });
};

function DisplayComments(cell) {
    
    // If row clicked is open, close and return
    if ( cell.closest("tr").attr("comments_open") == "true" )
    {
        cell.closest("tr").removeAttr("comments_open");
        cell.closest("tr").removeClass("comments_open");
        $("#app_details_row").remove();
        return 0;
    }
    
    // else, remove any currently open comments
    if ($("#app_details_row").length > 0) //if a comment is open
    {
        $(".app_row").removeAttr("comments_open"); //remove open attribute in all rows
        $(".app_row").removeClass("comments_open"); // remove highlight class
        $("#app_details_row").remove(); //remove any open comments row
    }
    
    // Get target info
    var target = { 
				EMPLID:  cell.closest("tr").attr('id'),
				term: 	 cell.closest("tr").find(".term_cell").text(),
				year: 	 cell.closest("tr").find(".year_cell").text()
		};
    
    GetComments(target, function(response, html){
        if (response == true)
        {
            cell.closest("tr").attr("comments_open", true); //set row as open
            cell.closest("tr").addClass("comments_open"); // highlight row with CSS
            cell.closest("tr").after('<tr id=\"app_details_row\" colspan=\"11\"></tr>'); //insert container row            
            
            setTimeout (function() {
                $("#app_details_row").css("height","310px");  // animation        
            }, 50);
            
            setTimeout (function() {
                $("#app_details_row").append(html);  // Insert retrieved data into DOM
                
                $('#edit_comments').on("click", function(){
	                EditComments($(this).closest("tr").prev());
                });
                
            }, 70);
            
            setTimeout (function() {
                $("#app_details").fadeIn(70).css("margin-top","5px"); // Fade in data
            }, 90);
        }
        else
        {
            displayMessage("Comments could not be retrieved right now.", "error_message");
        }
    });
};

function EditComments(row) {
    
    if ($("#app_details_row p").attr("id") == "app_nocomment") //if no comments
    {   
        // insert edit interface
        $("#app_details").append("<textarea id=\"comment_edit\"></textarea>" +
                                 "<button id=\"save_comment\">Save</button>" +
                                 "<button id=\"cancel_edit\">Cancel</button>");
    }   
    else if ($("#app_details_row p").attr("id") == "app_comment") //if there's a comment
    {
        var comment_text = $("#app_details_row p").text(); //store current comment
        
        // insert edit interface with current comments
        $("#app_details").append("<textarea id=\"comment_edit\"></textarea>" +
                                 "<button id=\"save_comment\">Save</button>" +
                                 "<button id=\"cancel_edit\">Cancel</button>");
                                 
        $("#comment_edit").val(comment_text);
    }
    else
    {
        return false; //break out
    }
    
    //remove comment paragraph, remove edit button
    $("#app_comment, #app_nocomment").remove();
    $("#edit_comments").remove();
    
    $("#save_comment, #cancel_edit").on("click", function(e){
            
        // Get target info
        var target = { 
				EMPLID:  row.attr('id'),
				term: 	 row.find(".term_cell").text(),
				year: 	 row.find(".year_cell").text()
		};
        
        if ($(this).attr("id") == "save_comment") // if 'Save' was clicked, save
        {            
            var newComment = $("#comment_edit").val();
            
            var targetData = JSON.stringify(target);
    
            $.ajax({
                url:"assets/ajaxcatcher.php",
                dataType: 'html',
                type: 'POST',
                data: {func:'UpdateComments', target_:targetData, comment:newComment},
                success: function(html){          
                    displayMessage("Comment saved.", "success_message");
                },
                error: function(html){
                    displayMessage("Comments could not be updated right now.", "error_message");
                }
            });
        }
        
        // Refresh comment row (small delay to give ajax time)
        setTimeout (function() {
            GetComments(target, function(response, html){
                if (response == true)
                {
                    $("#app_details_row").empty();             
                    $("#app_details_row").append(html);
                    
                    setTimeout (function() {
                        $("#app_details").fadeIn(70).css("margin-top","5px"); // Fade in data
                        
                        $('#edit_comments').on("click", function(){
                            EditComments($(this).closest("tr").prev());
                        });
                    }, 90);
                    
                    
                }
                else
                {
                    displayMessage("something went wrong.", "error_message");
                }           
            });        
        }, 50);           
    });
}; //end of EditComment

//-------------------------------------------------------------------------/

// Delete an application
function DeleteApplication(row) {
    
    // Get target info
    var target = { 
            name:    row.find(".first_name_cell").text() + " " + row.find(".last_name_cell").text(),
            EMPLID:  row.attr('id'),
            term: 	 row.find(".term_cell").text(),
            year: 	 row.find(".year_cell").text()
    };
    
    displayPrompt("Are you sure you want to delete this record?" +
                  "<br/>" + target['name'] + "-" + target['term'] + " " + target['year'], 
                  function(response){
                      if(response == false)
                      {
                          return false;
                      }
                      else
                      {
                        var targetData = JSON.stringify(target);

                        $.ajax({
                                url:"assets/ajaxcatcher.php",
                                dataType: 'html',
                                type: 'POST',
                                data: {func:'DeleteApplication', target_:targetData},
                                success: function(html){
                                    var filter = GetFilters();
	                                GetApplications(filter, sortby, order);          
                                    displayMessage("The application was successfully deleted", "success_message");                              
                                },
                                error: function(html){
                                    displayMessage("The application could not be deleted at this moment.", "error_message");
                                }
                        });

                      }
                  });    
                                           
}; // end of deleteApplication

///ADDING FILES TO APPLICATION PACKAGE
function modifyPackage(row) {
    
    var target = { 
            EMPLID:  row.attr('id'),
            term: 	 row.find(".term_cell").text(),
            year: 	 row.find(".year_cell").text()
    };
    
    //the dialog
    var dialog = "<h3>Add documents to the application package of " + row.find(".first_name_cell").text() + " " + row.find(".last_name_cell").text() + "</h3>" +
                 "<p class=\"description\">You can select multiple files. Only PDF and JPEG files are allowed. 1MB max size.</p>" +
                 "<form id=\"modifyPackage\" name=\"modifyPackage\" enctype=\"multipart/form-data\">" +
                 "<input type=\"file\" name=\"app_documents[]\" id=\"app_documents\" multiple/>" +
                 "<input type=\"hidden\" name=\"func\" value=\"modifyPackage\" />"
                 "</form>";
        
    
    // Add prompt div to the DOM
	$('body').prepend("<div id=\"modifyPackagePrompt\">\r\n" + dialog + "\r\n" +
    "<input type=\"submit\" value=\"Upload files\" class=\"prompt_button\"/>\r\n" +
	"<button id=\"modifyPackage_close\" class=\"prompt_button\">Cancel<\/button>\r\n<\/div>" +
	"<div id=\"prompt_bg\"></div>");
    
    $("#modifyPackage_close").on("click", function(){
        $("#modifyPackagePrompt, #prompt_bg").remove();
        return false;
    });
    
    var files;  // to store uploaded files
    
    // store the files when they're selected
    $("#app_documents").on("change", function(e){
        files = event.target.files;
    });
    
    //on submit
    $("#modifyPackage").on('submit', function(e) {
         
        e.stopPropagation();
        e.preventDefault();
        
        // prevent submission with no files
        if ($("#app_documents").val() == "")
        {
            displayMessage("You must select at least one file.","error_message");
            return false;
        }
        
        // display uploading files message
        $('body').prepend("<div id=\"warning_message\">Uploading files, please wait...<\/div>");
             
        setTimeout(function(){
        $("#warning_message").css("opacity","1");
        $("#warning_message").css("z-index","1");
        },100);
        
        $("#modifyPackage_close").prop("disabled",true); // disable cancel button
        
        // create FormData object and append selected files 
        var formData = new FormData();
        $.each(files, function(key, value){
            formData.append(key,value);
        })
        
        formData.append("func","modifyPackage");    // append func variable
        formData.append("target",JSON.stringify(target)); // append target data
        
        // AJAX request
        $.ajax({
            url: "assets/ajaxcatcher.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(json){
                $("#modifyPackagePrompt, #prompt_bg").remove(); // remove upload dialogue
                displayMessage(json["message"], json["type"]); // this will display an appropriate message depending on whether it failed or it succeded
            },
            error: function(jqxhr, textStatus, error){
                displayMessage("There was an unexpected error while trying to update the application package. Please try again.","error_message");
                console.log(error);
                console.log(jqxhr);
                console.log(textStatus);
            },
            
        });
     });
     
     
    
};


//-------------------------------------------------------------------------/



function qsort(array, sortby, numeric) {
    
    // base condition
    if (array.length <= 1)
        return array;
        
    var pindex = Math.floor(Math.random() * (array.length - 0) + 0);    // pivot index
    var pivot = array[pindex];  // pivot value
    var left = [];  // left partition
    var right = []; // right partition
    array = array.slice(0,pindex).concat(array.slice(pindex + 1)); // we extract the pivot from the array
      
    for (var i = 0; i < array.length; ++i)
    {
        //get values to be compared
        var comp1 = $("#" + array[i]).find("." + sortby).text();
        var comp2 = $("#" + pivot).find("." + sortby).text();       
        
        // if comparison values are numeric
        if (numeric == true)
        {
            //cast values as Numbers
            comp1 = Number(comp1);
            comp2 = Number(comp2);
            
            if (comp1 < comp2)
            {
                left.push(array[i]); // it's a value < the pivot
            }
            else
            {
                right.push(array[i]); // // it's a value > the pivot
            }           
        }
        else 
        {
            //lesser
            if (comp1.localeCompare(comp2, 'es', {sensitivity: 'base'}) < 0) //localeCompare provides string comparison
            {
                left.push(array[i]);
            }
            //greater
            else if (comp1.localeCompare(comp2, 'es', {sensitivity: 'base'}) > 0)
            {
                right.push(array[i]);
            }
            //if strings are the same, sort by EMPLID
            else
            {
                if (array[i] < pivot)
                {
                    left.push(array[i])
                }
                else
                {
                    right.push(array[i]);
                }
            }
        }
    }
    
    console.log(array);
    return (qsort(left, sortby, order, numeric)).concat([pivot],qsort(right, sortby, order, numeric));
    
}


//Start of document
$( document ).ready(function() {

//SetHandlers on all rows for the first time
$(".app_row").each(function() {
    SetHandlers($(this));
});

//filter applications

$("#query_limit, #status_filter, #term_filter, #year_filter, #EMPLID_filter").change(function(e){
	
	var filter = GetFilters();
	GetApplications(filter, sortby, order);
	
});

// reset filters
$("#reset_filter").click(function(e){
	
	$("#status_filter","option").attr("selected","false");
	$('#status_filter').find('option[value="0"]').attr("selected","selected");
	
	$("#term_filter","option").attr("selected","false");
	$('#term_filter').find('option[value="All"]').attr("selected","selected");

	$('#year_filter').val('');
	$('#EMPLID_filter').val('');
	
	var filter = GetFilters();
	GetApplications(filter, sortby, order);
});

//change sort order

/*$(".sort_button").click(function(e){	

	var filter = GetFilters();
	
	//If current order is DESC
	if ( $(this).attr("sort") == "DESC" )
	{
		sortby = $(this).attr("id");
		order = "ASC";
		$(this).attr("sort","ASC");
		
		$(".sort-icon").css("display","none");
		$(".asc", $(this)).css("display","inline");
	}
	//if current order is ASC
	else if ( $(this).attr("sort") == "ASC" )
	{
		sortby = $(this).attr('id');
		order = "DESC";
		$(this).attr("sort","DESC");
		
		$(".sort-icon").css("display","none");
		$(".sort-icon.desc", $(this)).css("display","inline");
	}
	//if not being sorted by this column, set it to DESC
	else if ( $(this).attr("sort") == "" )
	{
		sortby = $(this).attr('id');
		order = "DESC";
		$(this).attr("sort","DESC");
		
		$(".sort-icon").css("display","none");
		$(".sort-icon.desc", $(this)).css("display","inline");
	}
	
	GetApplications(filter, sortby, order);
});*/

$(".sort_button").click(function(){
   
   var sortby = $(this).attr('id');
   var order;
   var reverse = false; // to check if user just wants to reverse sort order
   
   //If current order is DESC
   if ( $(this).attr("sort") == "DESC" )
   {
       reverse = true;
       order = "ASC";
	   $(this).attr("sort","ASC");
       
       $(".sort-icon").css("display","none");
	   $(".asc", $(this)).css("display","inline");
   }
   //if current order is ASC
   else if ( $(this).attr("sort") == "ASC" )
   {      
       reverse = true;
       order = "DESC";
	   $(this).attr("sort","DESC");
       
       $(".sort-icon").css("display","none");
	   $(".sort-icon.desc", $(this)).css("display","inline");
   }
   //if not being sorted by this column, set it to DESC
   else if ( $(this).attr("sort") == "" )
   {
       order = "DESC";
	   $(this).attr("sort","DESC");
       
       $(".sort-icon").css("display","none");
	   $(".sort-icon.desc", $(this)).css("display","inline");
   }
   
   var rows = [];   //array to store row IDs
   
   $(".app_row").each(function(){
       rows.push($(this).attr('id')); //push all row IDs into array
   })
   
   console.log("rows:" + rows);
   
   if (reverse == true)
   {
       $.each(rows, function(index, value){
        $("#head_row").after($("#" + value)); 
      });
      return true; // exit event handler
   }
   
   console.log(rows);
   var sortedArray;
   switch(sortby) {
       case "EMPLID":
            sortedArray = qsort(rows,"EMPLID_cell", true);
            console.log(sortedArray);
            break;
       case "last_name":
            sortedArray = qsort(rows, "last_name_cell", false);
            break;
       case "first_name":
            sortedArray = qsort(rows, "first_name_cell", false);
            break;
       case "date":
            sortedArray = qsort(rows, "date_cell", false);
            break;
       case "app_semester":
            sortedArray = qsort(rows, "term_cell", false);
            break;
       case "app_year":
            sortedArray = qsort(rows, "year_cell", true);
            break;
       case "fee_paid":
            sortedArray = qsort(rows, "fee_cell", false);
            break;
       case "status":
            sortedArray = qsort(rows, "status_cell", false);
            break;
       case "processor":
            sortedArray = qsort(rows, "processor_cell", false);
            break;
       default:
            break;
   }
   
   console.log("final array: " + sortedArray);
   
   if (order == "ASC")
   {
      $.each(sortedArray, function(index, value){
        $("#head_row").after($("#" + value)); 
      });
   }
   else
   {
      $.each(sortedArray.reverse(), function(index, value){
        $("#head_row").after($("#" + value)); 
      });
   }

    
});


////////////////////
//Bulk processing///
////////////////////------------------------------------------------------//

//selecting all rows for bulk actions
$('#bulk_select_all').change(function(e) {
	
	if ( this.checked )
	{
		$('.bulk_select').prop("checked",true);
	}	
	else 
	{
		$('.bulk_select').prop("checked",false);
	}
	
	enableBulk();
	
});

// Sub-menus for bulk actions
$('#bulk_action').on("change", function() {
	
    var action = $('#bulk_action').val();
    
	 $('#bulk_actions').find('.bulk_submenu').remove(); //remove any lingering submenus
	
	switch (action)
	{
		case 'change_status':
			$('#bulk_actions').append("<div class=\"bulk_submenu\"><p>Change to:</p>" + status_update + bulk_execute + bulk_cancel + "</div>");
			break;
		
		case 'change_fee_status':
			$('#bulk_actions').append("<div class=\"bulk_submenu\"><p>Change to:</p>" + fee_update + bulk_execute + bulk_cancel + "</div>");
			break;
			
		case 'assign_processor':
			$('#bulk_actions').append("<div class=\"bulk_submenu\">" + processor_update + bulk_execute + bulk_cancel + "</div>");
			break;
			
		case 'download_app_pack':
			$('#bulk_actions').append("<div class=\"bulk_submenu\">" + bulk_execute + bulk_cancel + "</div>");
			break;
			
		default:
			break;
	}
	
    $('#bulk_execute_button').on("click", function(){
	   executeBulkAction();
    });
    
	// submenu close button
	$('#bulk_cancel_button').on('click', function(e) {
	
		$('#bulk_action').find('option[value=""]').prop("selected",true); // Reset bulk actions dropdown
		$('#bulk_actions').find('.bulk_submenu').remove(); //remove any lingering submenus
	});
	
});

//----------------------------------------------------------------------------//


}); //end of document.ready

