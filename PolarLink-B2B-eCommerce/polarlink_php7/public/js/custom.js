// This is for the selected ship to once the user clicks on any of the ship To address
 var _isDirty = false;
 var delFunc = '';
 var saveSuccess = '';
$(function(){

	$('[data-toggle="buttons"] > .btn > .radioButtonShipMethod').on('change', function() {
		changeDirtyFlag(true);
		});
	
	$('#order-header-form :input, #announcement-form :input').change(function(e){
		changeDirtyFlag(true);
		});
	// for the custom prompt popup when leave without save content for the order process steps
	$('.order-tabs li a').click(function(){
		var _this = $(this);
		 var url = $(this).attr("href");
	    	if(_isDirty){
	    		 bootbox.dialog({
				  message: "This page is asking you to confirm that you want to leave - data you have entered may not be saved?",
				  title: "Cancel",
				  buttons: {
				    success: {
				      label: "Continue",
				      className: "btn-success",
				      callback: function() {
				    	  changeDirtyFlag(false);
				    	  window.location.href = url;
					   return true;
					      }
				    },
				    main: {
				    	label: 'Go back to save my changes',
					      className: "btn-primary",
					      callback: function() {
					        // No action required
					      }
				    }
				  }
				});
	  	  
	        	return false; 
	        	
	    	 } else {
	    		 //return
	       	 // tabbing();
	         }
	    });
	
	
	
	
	


$('.shipToSelectable').click(
		function() {
			
			_isDirty = true;
			$('.shipToSelectable').removeClass('active');
			$(this).addClass('active');
			var idShipTo = $(this).attr('id');
			idShipToArray = idShipTo.split('_');
			var combinedId = idShipToArray['1'] + '_' + idShipToArray['2'];
			$('#selectedShipTo').html(
					'<div class="col-sm-3">' +
					$('#customerName_' + combinedId).html() + ' / ' + 
					$('#shipToNumber_' + combinedId).html() + '</div>' + 
					'<div class="col-sm-9">' + 
					$('#name_' + combinedId).html() + '<br />' + 
					$('#address_' + combinedId).html() + '<br />' + 
					$('#city_' + combinedId).html() + ', ' + 
					$('#state_' + combinedId).html() + ' - ' +
					$('#zip_' + combinedId).html()  + '</div>'
					);
			$('.selectedShipToButtons').show();
			var defaultShipTo = $('#defaultShippingMethod_'+idShipToArray['1'] + '_' + idShipToArray['2']).html();
			
			if(defaultShipTo != ''){
				$('input[name="shippingMethod"]').attr('checked',false);
				$('input[name="shippingMethod"][value="'+defaultShipTo+'"]').prop('checked',true);
				$('.method-list').children().removeClass('active');
				$('input[name="shippingMethod"][value="'+defaultShipTo+'"]').parent().addClass('active');
			}
			$('#shipToAddToSend').val(idShipToArray['1']);
			$('#shipToCustomerNumber').val($('#customerName_' + idShipToArray['1'] + '_' + idShipToArray['2']).text().trim());
		});


window.onbeforeunload = function(e) {
	if(_isDirty)
	return "There are unsaved changes on this page. Do you still want to continue";
	else
		return ;
	 
	};
	
});

function changeDirtyFlag(value){
	if(value != '' || value != 'undefined')
		_isDirty = value;
	else
	_isDirty = true;
}
// This is for the search in the selected shipTo address
function checkblank(){
	/* var searchShippingText = $('#searchShippingText').val().trim();
	if(searchShippingText == ''){
		alert('Please enter value to search');
		return false;
	} */
	return true;
}

function clearFilterCSRSearch(){
	$('#searchShippingText').val('');
	$('#search-csr-customer-form').submit();
}

// this is the function which is fired when the cancel button is pressed from the order steps pages, it checks whether there are unsaved changes or not

function checkCancel(step){
	
//	if(_isDirty){
		bootbox.dialog({
			  message: "You are about to leave this page. Do you want to save this order to submit later?",
			  title: "Cancel",
			  buttons: {
			    success: {
			      label: "Save this Order",
			      className: "btn-success",
			      callback: function() {
			        // Need to call ajax here to save the current order
			    	  switch(step) {
			          // if the cancel is called from order shipping page
			          case 'shipping':
			        	  saveForm('order-shipping-method');
			        	  if(saveSuccess == true){
			        		  window.location = "/user/index";
			        	  } else{
			        		  alert('There is some error. Please try again!');
			        	  }
			        	  changeDirtyFlag(false);
			              break;
			          // if the cancel is called from order header page
			          case 'header':
			        	  if(checkOrderHeaderForm()){
			        		  saveForm('order-header-form');  
			        	  } else {
			        		  bootbox.hideAll();
			        		  return false;
			        	  }
			        	  
			        	  if(saveSuccess == true){
			        		  window.location = "/user/index";
			        	  } else{
			        		  alert('There is some error. Please try again!');
			        	  }
			        	  changeDirtyFlag(false);
			              break;
			  		// if the cancel is called from item search page
			          case 'search':
			        	  if($('#my-tab-content').find('form#updateOrderForm').length > 0){
			        	   saveForm('updateOrderForm');
			        	   if(saveSuccess == true){
			        		  
				        		  window.location = "/user/index";
				        	  } else{
				        		  alert('There is some error. Please try again!');
				        	  }
			        	  } else {
			        		  setMessage('success');
			        		  window.location = "/user/index";
			        	  }
			              break;
			          default:
			        	  setMessage('success');
	        		  window.location = "/user/index";
			              // do nothing here
			      }  
			    			  
			    	//  window.location = "/user/index";
			      }
			    },
			    danger: {
			      label: "Delete this Order",
			      className: "btn-danger",
			      callback: function() {
			    	// need to redirect to the home page 
			    	  deleteOrder();
			    	 
			    	  if(delFunc == '1'){
			    	  window.location = "/user/index";
			    	  } 
			    	  
			    	  else if(delFunc == ''){
			    		  
			    		  window.location = "/user/index";
			    	  }
			    	  else {
			    		  alert('There is some error. Please try again after some time.');
			    	  }
			    	  changeDirtyFlag(false);
			      }
			    },
			    main: {
			    	label: 'Go back to edit my Order',
				      className: "btn-primary",
				      callback: function() {
				        // No action required
				      }
			    }
			  }
			});
		
//	} else {
//		window.location = "/user/index";
//	}
}

//this is the function which is fired when a user selects to delete the order

function deleteOrder(){
	$('#loading-image').show();
	// sending the ajax request
    $.ajax({
        url: '/user/order-cancel',
        type: "POST",
        data:  {'action': 'CNCL'},
     //   dataType: "html",
        async: false,
        success: function(result) {
        	
        	/**
        	 * Remove OH_PLINK_ORDERNO
        	 */
        	
        	Cookies.remove('OH_PLINK_ORDERNO', '/');
        	
        	 delFunc = result.trim();  
        	 return false;
            // do nothing here
     
        },
        failure: function(errMsg) {
            alert(errMsg);
            return false;
        },
        complete: function (){
        	$('#loading-image').hide();
        }
    });
	
}

//this is the function which is used to set a message

function setMessage(type){
	if(type == ''){
		type = 'success';
	}
	$('#loading-image').show();
	// sending the ajax request
    $.ajax({
        url: '/user/set-message',
        type: "POST",
        data:  {'type': type},
     //   dataType: "html",
        async: false,
        success: function(result) {
        	 return true;
            // do nothing here
     
        },
        failure: function(errMsg) {
            alert(errMsg);
            return false;
        },
        complete: function (){
        	$('#loading-image').hide();
        }
    });
	
}

function saveForm(formId) {
	var me = $(this);
	
	if ( me.data('requestRunning') ) {
        return;
    }
	me.data('requestRunning', true);
	var url = $('#'+formId).attr('action');
	$('#loading-image').show();
    // sending the ajax request
    $.ajax({
        url: url,
        type: "POST",
        data:  $('#'+formId).serialize()+ "&sendOnlyResponse=1",
  //      dataType: "html",
        async: false,
        success: function(result) {
        	
        	if(result.success === true){
        		saveSuccess = true;
        		return true;
        	} else {
        		saveSuccess = false;
        		return false;
        	}
		},
        failure: function(errMsg) {
            alert(errMsg);
        },
        complete: function (){
        	$('#loading-image').hide();
        	changeDirtyFlag(false);
        	 me.data('requestRunning', false);
        }
    });
}

// this is the function called to save the form values when a user wants to save the form values when he clicks on the cancel order button
function processSaveForm(formId) {
	var me = $(this);
	
	if ( me.data('requestRunning') ) {
        return;
    }
	me.data('requestRunning', true);
	var url = $('#'+formId).attr('action');
	$('#loading-image').show();
    // sending the ajax request
    $.ajax({
        url: url,
        type: "POST",
        data:  $('#'+formId).serialize(),
        dataType: "html",
        async: false,
        success: function(result) {
			// checking the current tab url
        currentTab = $("ul#tabs li.active a").attr('href');
        switch(currentTab) {
        // if the current selected tab is brand/size
        case '#brand-size':
            $('.main-data-brand-size').hide();
            $('#ajax-data-brand-size').html(result).promise().done(function(){
                checkHeight('ajax-data-brand-size');
            });;
            break;
        // if the current selected tab is brand
        case '#brand':
        	$('.main-data-brand').hide();
            $('#ajax-data-brand').html(result).promise().done(function(){
                checkHeight('ajax-data-brand');
            });;
            break;
		// if the current selected tab is size
        case '#size':
        	$('.main-data-size').hide();
            $('#ajax-data-size').html(result).promise().done(function(){
                checkHeight('ajax-data-size');
            });;
            break;
        // if the current selected tab is item description
        case '#item-description':
        	$('.main-data-item-description').hide();
            $('#ajax-data-item-description').html(result).promise().done(function(){
                checkHeight('ajax-data-item-description');
            });;
        default:
            
            // do nothing here
    }  
        },
        failure: function(errMsg) {
            alert(errMsg);
        },
        complete: function (){
        	$('#loading-image').hide();
        	changeDirtyFlag(false);
        	 me.data('requestRunning', false);
        }
    });
}

//this is the function which is fired when the user clicks on save and continue after selecting a shipTo address

function saveShipTo(){
	
	
	var shipToCustomerNumber = $('#shipToCustomerNumber').val();
	var shipToAddToSend = $('#shipToAddToSend').val();
	
	if(shipToCustomerNumber.trim() != '' && shipToAddToSend.trim() != '')
		{
	
	$('#loading-image').show();
	$("#announcementsToDisplay").html('');
	
	// sending the ajax request to check announcements
    $.ajax({
    	url: '/user/get-announcement-ajax',
        type: "POST",
        data:  {'customerId': shipToCustomerNumber,'shipTo': shipToAddToSend },
      //  dataType: "json",
        async: false,
        success: function(result) {
			// checking the current tab url
        	$("#announcementsToDisplay").html(result.html);
        },
        failure: function(errMsg) {
            alert(errMsg);
        },
        complete: function (){
        	
        	$('#loading-image').hide();
        	// changeDirtyFlag(false);
        	// me.data('requestRunning', false);
        }
    });
    $('.selectedShipToText').css('margin-top', '50px');
    $('.dataForShipTos, .searchBoxForShipTo, .selectedShipToButtons').hide();
    $('.shippingMethodOrderShipping, .selectedDifferentShipToButtons').show();
		} else {
			alert('There seems to be an error. Please try again by selecting the shipTo.');
		}
}

function differentShipTo(){
	$('.selectedShipToText').css('margin-top', '0px');
	$('.dataForShipTos, .searchBoxForShipTo, .selectedShipToButtons').show();
	$('.shippingMethodOrderShipping, .selectedDifferentShipToButtons').hide();
}

function checkOrderHistorySearch(){ 
     
    // define date string to test
    var fromDate = document.getElementById('fromDate').value;
	var toDate = document.getElementById('toDate').value;
    // check date and print message 
	if(fromDate.trim() != ''){
	    if (!isDate(fromDate)) { 
	        bootbox.alert('Invalid date selected in from Date!\nValid format - mm/dd/yyyy'); 
	        document.getElementById('fromDate').focus();
	        return false;
	    }
	}
    // check date and print message
	if(toDate.trim() != ''){
	    if (!isDate(toDate)) { 
	    	bootbox.alert('Invalid date selected in to Date!\nValid format - mm/dd/yyyy'); 
	        document.getElementById('toDate').focus();
	       return false;
	    }
	}

    return true;
}

function checkOrderHeaderForm(){
	 // define date string to test
    var delivDate = document.getElementById('OhReqDelivDate').value;
    
    if(delivDate.trim() != ''){
	    if (!isDate(delivDate)) { 
	        bootbox.alert('Invalid date selected in requested delievery date - mm/dd/yyyy'); 
	        document.getElementById('OhReqDelivDate').focus();
	        return false;
	    } else {
	    	changeDirtyFlag(false);
	    	return true;
	    }
	} else {
    	changeDirtyFlag(false);
    	return true;
    }
}

function validateCsrAnnouncementSearchForm(){
	 // define date string to test
   var startDate = document.getElementById('start_date').value;
   var endDate = document.getElementById('end_date').value;
   if(startDate.trim() != '' || endDate.trim() != ''){
	    if (!isDate(startDate) && startDate.trim() != '') { 
	        bootbox.alert('Invalid date selected in start date - mm/dd/yyyy'); 
	        document.getElementById('start_date').focus();
	        return false;
	    } else if (!isDate(endDate) && endDate.trim() != '') { 
	        bootbox.alert('Invalid date selected in end date - mm/dd/yyyy'); 
	        document.getElementById('end_date').focus();
	        return false;
	    } else {	    	
	    	return true;
	    }
	} else {   	
   	return true;
   }
}

function validateCsrAnnouncementForm(){
	 // define date string to test
  var startDate = document.getElementById('start_date').value;
  var endDate = document.getElementById('end_date').value;
  var AnnouncementText = document.getElementById('announcement_text').value;
  if(startDate.trim() == '' || endDate.trim() == '' || AnnouncementText.trim() == ''){
	  bootbox.alert('Please fill in the required fields');
      return false;
  } 
  if(startDate.trim() != '' || endDate.trim() != ''){
	    if (!isDate(startDate) && startDate.trim() != '') { 
	        bootbox.alert('Invalid date selected in start date - mm/dd/yyyy'); 
	        document.getElementById('start_date').focus();
	        return false;
	    } else if (!isDate(endDate) && endDate.trim() != '') { 
	    	bootbox.alert('Invalid date selected in end date - mm/dd/yyyy'); 
	        document.getElementById('end_date').focus();
	        return false;
	    } else {	    	
	    	return true;
	    }
	} else {   	
  	return true;
  }
}

function validateCsrUserAddForm(){

	 // define the variables to check
	  var custGroup = $('#plu_cust_group').val();
	  var userId = $('#plu_user_id').val();
	  var firstName = $('#plu_first_name').val();
	  var lastName = $('#plu_last_name').val();
	  var password = $('#plu_password').val();
	  var confirmPassword = $('#plu_confirm_password').val();
	  var emailAddress = $('#plu_email_address').val();
	  var userType = $('input[name="PLU_USER_TYPE"]').is(':checked');
	  if(userType == true) {
			 var check_userType = $("input[name=PLU_USER_TYPE]:checked").val();
	  }
	  
	  if(userId.trim() == '' || firstName.trim() == '' || lastName.trim() == '' || password.trim() == '' || confirmPassword.trim()== '' || emailAddress.trim() == '' || userType == false){
		   bootbox.alert('Please fill all the required fields');
	      return false;
	  } 
	  
	  if(!isValidEmailAddress(emailAddress)){
		  bootbox.alert('Please provide a valid email address.');
	      return false;
		}
	  
	  if(password.trim() != confirmPassword.trim()){
		  bootbox.alert('Password and Confirm Password do not match.');
	      return false;
		} 
		  if(check_userType == 'admin' || check_userType == 'normal' || custGroup != ''){
			  $('#loading-image').show();
		  }
	  // $('#plu_cust_group, #plu_user_id').removeClass('input-error');
	  $('#plu_cust_group').removeClass('input-error');
	  $('.errorFromAjax').remove();
	  var ret = false;
	// sending the ajax request to check customer group and user id
	  
	  if(check_userType == 'admin' || check_userType == 'normal' || custGroup != ''){
	    $.ajax({
	    	url: '/user/check-customer-user',
	        type: "POST",
	        data:  {'customerId': custGroup,'userId': userId },
	      //  dataType: "json",
	        async: false,
	        success: function(result) {
	        	if(result.customerError.trim() == '' && result.userError.trim() == ''){
	        		 ret = true;
	        		
	        	} else {
	        		
	        		if(result.customerError.trim() != ''){
	        			
	        			$('#plu_cust_group').addClass('input-error').after('<ul class="errorFromAjax"><li>'+result.customerError.trim()+'</li></ul>');
	        		}
	        		if(result.userError.trim() != ''){
	        			$('#plu_user_id').addClass('input-error').after('<ul class="errorFromAjax"><li>'+result.userError.trim()+'</li></ul>');
	        		}
	        		
	        	}
				// checking the current tab url
	        },
	        failure: function(errMsg) {
	            alert(errMsg);
	        },
	        complete: function (){
	        	
	        	$('#loading-image').hide();
	        	// changeDirtyFlag(false);
	        	// me.data('requestRunning', false);
	        }
	    });
	    
	  } else {
		  ret = true;
	  }
	   return ret;
	  
	
}


function isValidEmailAddress(emailAddress) {
    var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
    return pattern.test(emailAddress);
}

function isDate(ExpiryDate) { 
    var objDate,  // date object initialized from the ExpiryDate string 
        mSeconds, // ExpiryDate in milliseconds 
        day,      // day 
        month,    // month 
        year;     // year 
    // date length should be 10 characters (no more no less) 
    if (ExpiryDate.length !== 10) { 
        return false; 
    } 
    // third and sixth character should be '/' 
    if (ExpiryDate.substring(2, 3) !== '/' || ExpiryDate.substring(5, 6) !== '/') { 
        return false; 
    } 
    // extract month, day and year from the ExpiryDate (expected format is mm/dd/yyyy) 
    // subtraction will cast variables to integer implicitly (needed 
    // for !== comparing) 
    month = ExpiryDate.substring(0, 2) - 1; // because months in JS start from 0 
    day = ExpiryDate.substring(3, 5) - 0; 
    year = ExpiryDate.substring(6, 10) - 0; 
    // test year range 
    if (year < 1000 || year > 3000) { 
        return false; 
    } 
    // convert ExpiryDate to milliseconds 
    mSeconds = (new Date(year, month, day)).getTime(); 
    // initialize Date() object from calculated milliseconds 
    objDate = new Date(); 
    objDate.setTime(mSeconds); 
    // compare input date and parts from Date() object 
    // if difference exists then date isn't valid 
    if (objDate.getFullYear() !== year || 
        objDate.getMonth() !== month || 
        objDate.getDate() !== day) { 
        return false; 
    } 
    // otherwise return true 
    return true; 
}

function clearVal(fieldId){
	$('#'+fieldId).val('');
	$('#'+fieldId).siblings('span').remove();
}

function clearForm(oForm) {
    
	  var elements = oForm.elements; 
	    
	  oForm.reset();

	  for(i=0; i<elements.length; i++) {
	      
		field_type = elements[i].type.toLowerCase();
		
		switch(field_type) {
		
			case "text": 
			case "password": 
			case "textarea":
		        case "hidden":	
				
				elements[i].value = ""; 
				break;
	        
			case "radio":
			case "checkbox":
	  			if (elements[i].checked) {
	   				elements[i].checked = false; 
				}
				break;

			case "select-one":
			case "select-multi":
	            		elements[i].selectedIndex = -1;
				break;

			default: 
				break;
		}
	    }
	}

function checkDirty(){
	if(_isDirty){
	bootbox.dialog({
		  message: "You are about to leave this page with unsaved changes. If you Continue, you will lose your changes. <br /> <br />Are you sure you want to continue? ",
		  title: "Cancel",
		  buttons: {
		    success: {
		      label: "Continue",
		      className: "btn-success",
		      callback: function() {
		    	  changeDirtyFlag(false);
		        // Need to call ajax here to save the current order
		    	  window.location = "/user/csr-announcement-search";
		      }
		    },
		    main: {
		    	label: 'Cancel',
			      className: "btn-primary",
			      callback: function() {
			        // No action required
			      }
		    }
		  }
		});
	} else {
		
		 window.location = "/user/csr-announcement-search";
	}
	
}

function stringToUpper(obj){
	var valueObject = $(obj).val();
	$(obj).val(valueObject.trim().toUpperCase());
	return false;
 }



//this is the function which is fired when the cancel button is pressed from the customer page, it checks whether there are unsaved changes or not

function checkCancelCustomerForm(){
	if(_isDirty){
		bootbox.dialog({
			  message: "You are about to leave this page. Do you want to continue?",
			  title: "Cancel",
			  buttons: {
			    success: {
			      label: "Continue",
			      className: "btn-success",
			      callback: function() {
				        changeDirtyFlag(false);
			    	  window.location = "/user/csr-customer-list";
				      }
			    },
			    main: {
			    	label: 'Go back to save my changes',
				      className: "btn-primary",
				      callback: function() {
				        // No action required
				      }
			    }
			  }
			});
		
	} else {
		window.location = "/user/csr-customer-list";
	}
}

//this is the function which is fired when the cancel button is pressed from the user page, it checks whether there are unsaved changes or not

function checkCancelUserForm(){
	
	if(_isDirty){
		bootbox.dialog({
			  message: "You are about to leave this page. Do you want to continue?",
			  title: "Cancel",
			  buttons: {
			    success: {
			      label: "Continue",
			      className: "btn-success",
			      callback: function() {
				        changeDirtyFlag(false);
			    	  window.location = "/user/csr-user-list";
				      }
			    },
			    main: {
			    	label: 'Go back to save my changes',
				      className: "btn-primary",
				      callback: function() {
				        // No action required
				      }
			    }
			  }
			});
		
	} else {
		window.location = "/user/csr-user-list";
	}
}

// this is the function to add the active class on the user type radio button
function addActiveClass(changeFlag){
	$('input[name="PLU_USER_TYPE"]').parent().removeClass('active');
	$('input[name="PLU_USER_TYPE"]:checked').parent().addClass('active');
	
	var check_userType = $("input[name=PLU_USER_TYPE]:checked").val();
	
	if(check_userType == 'csr'){
		
		$('.astrik').empty();
	} else {
		
		$('.astrik').html('*');
	}
	
	if(changeFlag == 1)
	changeDirtyFlag(true);
	return false;
}

//function to remove the order from the substitute tab
function removeItemOrderAttachment(PLAT_ATTACH_NO,PLAT_ORDER_NO,FILE_NAME_ORIGINAL,ths){
	
	if((PLAT_ATTACH_NO != '')){
		bootbox.confirm("Are you sure, you want to Remove current order file?" +
				"<br />File Name: " + FILE_NAME_ORIGINAL +
				"<br />Current Order Number: " + PLAT_ORDER_NO, function(result) {
			
			
			if(result == true){
				$('#loading-image').show();
				// sending the ajax request
				$.ajax({
					url: '/user/delete-order-attachment-file',
			        type: "POST",
			        data: {'PLAT_ATTACH_NO':PLAT_ATTACH_NO, 'PLAT_ORDER_NO' : PLAT_ORDER_NO},
			      //  dataType: "json",
			        async: true,
			        success: function(resultAjax) {
			        	
			        	$('#loading-image').hide();
			        	//alert();
			        	if(resultAjax.html==="success"){
			        	$(ths).parent('td').parent('tr').remove();
			        	}
			        	//$('#loading-image').hide();
			            return false;
			        },
			        failure: function(errMsg) {
			            alert(errMsg);
			        },
			        complete: function (){
			        	$('#loading-image').hide();
			        }
			    });
			}
			  
			}); 
	}
}





function listingPages(sendUser){
	
	 if(_isDirty){
     	bootbox.dialog({
			  message: "This page is asking you to confirm that you want to leave - data you have entered may not be saved?",
			  title: "Cancel",
			  buttons: {
			    success: {
			      label: "Continue",
			      className: "btn-success",
			      callback: function() {
				        changeDirtyFlag(false);
				    //  checkMinimumItemsSearch(loc);
				      window.location = '/user/'+sendUser;
				      }
			    },
			    main: {
			    	label: 'Go back to save my changes',
				      className: "btn-primary",
				      callback: function() {
				        // No action required
				      }
			    }
			  }
			});
     } else {
     	
     	  window.location = '/user/'+sendUser;
     }
}




