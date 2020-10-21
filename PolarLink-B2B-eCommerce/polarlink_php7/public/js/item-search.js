// this is the variable current tab to check where the user is currently
var currentTab = $("ul#tabs li.active a").attr('href');

$(document).ready(function(){
	
// to make the active class on the selected tab
    $('#accordion a').click(function(){
    	$('#accordion a').removeClass('active');
			$(this).addClass('active');
        });

// on click on any of the tab at the top changing the values of the selected rows and hiding the show items button    
    $("#tabs li a").click(function(){
    	var _this = $(this);
          if(_isDirty){
        	/*if(confirm("This page is asking you to confirm that you want to leave - data you have entered may not be saved")) {
        		tabbing();
				return true;
                }
        	  */
        	  bootbox.dialog({
  			  message: "This page is asking you to confirm that you want to leave - data you have entered may not be saved?",
  			  title: "Cancel",
  			  buttons: {
  			    success: {
  			      label: "Continue",
  			      className: "btn-success",
  			      callback: function() {
  				      tabbing();
  				    _this.trigger('click');
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
        	  tabbing();
          }
    });  
    
    function tabbing() {
        $('#showItemsButtonSubmit').hide();
       	$('.brandSizeSelectableRow, .brandSelectableRow, .sizeSelectableRow, .brandSizeSelectableRow a').removeClass('active');
       	$('#showItemsButtonSubmit').hide();
       	$('#ajax-data-brand-size, #ajax-data-brand, #ajax-data-size, #ajax-data-item-description').html('');
       	$('.main-data-brand-size, .main-data-brand, .main-data-size, .main-data-item-description').show();
       	$('#CustomTextOnTabs').hide();
       	changeDirtyFlag(false);
    };
    

    
 // on click on any of the tab at the top, hiding the show items button
    $('.panel a').click(function(){
    	$('#showItemsButtonSubmit').hide();
    });
    
});

/* The function to search the items based on the input vales selected
 * function name - processForm
 * params - formId - this is id of the form on which we want to send the ajax and get the result 
 */
function processForm(formId) {
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
        	 me.data('requestRunning', false);
        }
    });
}


function showNewSearch_chk(){
        if(_isDirty){
      	/* if(confirm("This page is asking you to confirm that you want to leave - data you have entered may not be saved")) {
      		showNewSearch();
              }
          	return false; */
        	
        	bootbox.dialog({
  			  message: "This page is asking you to confirm that you want to leave - data you have entered may not be saved?",
  			  title: "Cancel",
  			  buttons: {
  			    success: {
  			      label: "Continue",
  			      className: "btn-success",
  			      callback: function() {
  				        changeDirtyFlag(false);
  			    	  showNewSearch();
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
        	showNewSearch()
        }
  }
/* function showNewSearch()
 *  this is the function which is used to show the current search form again. This is called from the new search button from the search results
 *  
 */
function showNewSearch(){
	// checking the current tab url
	var currentTabNew = $("ul#tabs li.active a").attr('href');
	$('.brandSizeSelectableRow, .brandSelectableRow, .sizeSelectableRow, .brandSizeSelectableRow a').removeClass('active');
  	 $('#showItemsButtonSubmit').hide();
  	changeDirtyFlag(false);
    switch(currentTabNew) {
 // if the current selected tab is brand/size
    case '#brand-size':
        $('.main-data-brand-size').show();
        $('#ajax-data-brand-size').html('');
        
        return false;
        break;
     // if the current selected tab is brand
    case '#brand':
    	$('.main-data-brand').show();
        $('#ajax-data-brand').html('');
        return false;
        break;
     // if the current selected tab is size
    case '#size':
    	$('.main-data-size').show();
        $('#ajax-data-size').html('');
        return false;
        break;
     // if the current selected tab is item description
    case '#item-description':
    	$('.main-data-item-description').show();
        $('#ajax-data-item-description').html('');
        return false;
        break;
    default:
        
    	return false;
        // do nothing here
    }   

}

/* 
 * function checkBlank - This is called when the user clicks on search button from the item Description Tab
 * Purpose - to check whether there is an input value or not and then send ajax 
*/
function checkBlank(){
	
	var itemFilterValue = $('#itemFilter').val().trim();
	
	if(itemFilterValue != ''){
		$('#itemBrand').val('');
		$('#itemBrandNameToDisplay').val('');
	   	$('#itemSize').val('');
	   	$('#itemSizeToDisplay').val('');
		var upperCaseFilterValue = itemFilterValue.toUpperCase();
		$('#itemFilter').val(upperCaseFilterValue);
		processForm('item-search-form');
	}
	return false;
}

/* 
 * function checkBlank - This is called when the user clicks on search button from the search results
 * Purpose - to check whether there is an input value or not and then send ajax again
*/
function checkBlankAjax(){
	
	var itemFilterValue = $('#itemFilterAjax').val().trim();
	
	if(itemFilterValue != ''){
		var upperCaseFilterValue = itemFilterValue.toUpperCase();
		$('#itemFilterAjax').val(upperCaseFilterValue);
		processForm('item-search-form-ajax');
	} else{
		bootbox.alert('Please input some value to search');
		$('#itemFilterAjax').focus();
	}
	return false;
}

/*
 *  function checkHeight - This is the function to check the height of a div and make it scrollable
 *  basically this is used after placing the ajax contents in the div
 */
function checkHeight(divId){
	var heightTopUl = '0';
	var heightTopFilter = '0';
	var itemsMatchingSelection = $('#'+divId+' .order-heading.margin-none').height();

	var heightBottomButtons = $('#'+divId+' .buttons').height();
	heightBottomButtons += 24;
	
	

	if($('#'+divId).find('ul.total-amount').length  > 0){
		heightTopUl = $('#'+divId+' ul.total-amount').height();
		heightTopUl += 38;
		
	}
	if($('#'+divId).find('div.filterby-product-description').length  > 0){
		heightTopFilter = $('#'+divId+' div.filterby-product-description').height();
		heightTopFilter += 14; 
		
	}
	var heightInnerContainer = $('#'+divId+' .table-responsive').height();
	var heightTotalDisplayedMust = 	parseInt(heightTopUl) + parseInt(heightTopFilter) + parseInt(itemsMatchingSelection) + parseInt(heightBottomButtons) ;
	var heightOuterContainer =  $('#'+divId).height();
	var remainingHeight =  610-parseInt(heightTotalDisplayedMust) - 60;
	if(heightOuterContainer > 610){
		$('#'+divId+' .table-scroll').css('max-height', remainingHeight);
	}
	
}

/*
 *  function changeSaveStatus - This is the function to change the status of the save to 1, ie, make it updatable once there is any change in the input box 
 */

function changeSaveStatus(obj){
	var attrId = $(obj).attr('id');
	//var changeTabFlag;
	var attrIdArray = attrId.split('_');
	$('#status_'+attrIdArray['1']).val('1');
	// this is to change the dirty flag here
	changeDirtyFlag(true);
	//changeTabFlag(true);
 //console.log(_isTab);
}

function isOdd(num) { return num % 2;}

/* The function to search the items based on the input values selected in the ajax results to resend the ajax results
 * function name - processUpdateOrderForm
 * params - formId - this is id of the form on which we want to send the ajax and get the result 
 */
function processUpdateOrderForm(obj) {
	var url = $(obj).attr('action');
	var form_id = $(obj).attr('id');
	
	$('#loading-image').show();
	var sendAjax = true;
	// here we are checking the value of uom if it is 12 than giving the validations that the value of quantity can not be odd
	$("#updateOrderForm").find('table tr td').each(function(){
		var _parent = $(this).closest('tr');
		  var _thisTrId = $(_parent).attr('id');
		  var _sttatus = $("#status_"+_thisTrId+"").val();
		
	if(isNaN( $('#quantity_'+_thisTrId).val().trim())){
			bootbox.alert('Please input numeric quantities only');
			$('#quantity_'+_thisTrId).focus();
			$('#loading-image').hide();
			sendAjax = false;
			return false;
	  }
	});
	$("#updateOrderForm").find('table tr td[value_of_uom="12"]').each(function(){
		  var _parent = $(this).closest('tr');
		  var _thisTrId = $(_parent).attr('id');
		  var _sttatus = $("#status_"+_thisTrId+"").val();
		 
		  
		  if(isOdd($('#quantity_'+_thisTrId).val().trim()) == true && _sttatus == '1'){
			  alert('Quantity must be an even number for 12 packs');
				$('#quantity_'+_thisTrId).focus();
				$('#loading-image').hide();
				sendAjax = false;
				return false;
		  }
		  
		  
		  
		});
	
	if(!(sendAjax)){
		$('#loading-image').hide();
		return false;
	}
	sendAjax = false;
	// sending the ajax request
	$.ajax({
        url: url,
        type: "POST",
        data: $('#'+form_id).serialize(),
      //  dataType: "json",
        async: true,
        success: function(result) {
               $('#updateOrderAjax').html(result.html);
             
               setTimeout(function(){
	               $("#updateOrderForm").find("table tr").each(function(){
		       			var _this = $(this);
		       			var _thisId = $(this).attr('id');
		       			var _found = $("#updateOrderAjax").find(".table-responsive table tr."+_thisId+"");
		       			var _sttatus = $("#status_"+_thisId+"").val();
		       			if( $(_found).length > 0 ){
		       				if($(_this).hasClass("bolderTr") === false){
		       					$(_this).addClass("bolderTr");
		       				}
		       				if(_sttatus == '1'){
		       					$(_this).find("td[copy_to='ext_price']").html($(_found).find("td[copy_from='ext_price']").html());
		       					$(_this).find("td[copy_to='pallet_qty']").html($(_found).find("td[copy_from='pallet_qty']").html());
		       					$(_this).find("td[copy_to='case_qty']").html($(_found).find("td[copy_from='case_qty']").html());
		       					$(_this).find("td[copy_to='unit_qty']").html($(_found).find("td[copy_from='unit_qty']").html());
		       				}
		       			}else{
		       				$(_this).removeClass("bolderTr");
		       				$(_this).find("td[copy_to='ext_price']").html("-");
		       				$(_this).find("td[copy_to='pallet_qty']").html("-");
		       				$(_this).find("td[copy_to='case_qty']").html("-");
		       				$(_this).find("td[copy_to='unit_qty']").html("-");
		       			}
		       		})
               }, 1000);  
               
               
               bootbox.alert(result.successmsg, function() { });
               setTimeout(function(){
            	   bootbox.hideAll();
        	   }, 6000);
            return false;
        },
        failure: function(errMsg) {
            alert(errMsg);
        },
        complete: function (){
        	 checkKeyUp();
        	$('#loading-image').hide();
        	sendAjax = true;
        	changeDirtyFlag(false);
        }
    });
    return false;
}



/*
 *  function clearFilterItemAjax - This is the function to clear the filter value in the ajax results 
 */


function clearFilterItemAjax_chk(){
    if(_isDirty){
  	/*if(confirm("This page is asking you to confirm that you want to leave - data you have entered may not be saved")) {
  		clearFilterItemAjax();
          }
      	return false;
      	*/
    	
    	bootbox.dialog({
			  message: "This page is asking you to confirm that you want to leave - data you have entered may not be saved?",
			  title: "Cancel",
			  buttons: {
			    success: {
			      label: "Continue",
			      className: "btn-success",
			      callback: function() {
				        changeDirtyFlag(false);
				        clearFilterItemAjax();
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
    	clearFilterItemAjax()
    }
}

function clearFilterItemAjax(){
	$('#itemFilterAjax').val('');
	processForm('item-search-form-ajax');
}

// this is the function called when a user clicks on new search or on any new element for searching the items
function removeActiveFromTabs(){
	$('.brandSizeSelectableRow, .brandSelectableRow, .sizeSelectableRow, .brandSizeSelectableRow a, .brandSelectableRow a, .sizeSelectableRow a').removeClass('active');
}

// function called on item search page when a user clicks on the brand/size tab on the listing
// to change the active class and change the display values and the form values accordingly in the brandsize tab
function brandSizeSelectableRow(obj){
	removeActiveFromTabs();
	var _parentTd = $(obj).closest('td');
	var itemSize = $(_parentTd).attr('id');
	var itemSizeToDisplay = $(_parentTd).attr('rel');
	var _parentTr = $(obj).closest('tr');
	var itemBrand = $(_parentTr).attr('id');
	var itemBrandNameToDisplay = $(_parentTr).attr('rel');
	$('#itemBrand').val(itemBrand);
	$('#itemBrandNameToDisplay').val(itemBrandNameToDisplay);
	$('#itemSize').val(itemSize);
	$('#itemSizeToDisplay').val(itemSizeToDisplay);
	$('#itemFilter').val('');
	$(_parentTd).addClass('active');
	processForm('item-search-form');
	return false;
// 	$('#showItemsButtonSubmit').show().focus();
    }


//function called on item search page when a user clicks on the brand tab on the listing
//to change the active class and change the display values and the form values accordingly in the brand tab
function brandSelectableRow(obj){
	//alert(obj);
	removeActiveFromTabs();
	var _parentTd = $(obj).closest('td');
	var itemBrand = $(_parentTd).attr('id');
	var itemBrandNameToDisplay = $(_parentTd).attr('rel');
	$('#itemBrand').val(itemBrand);
	$('#itemBrandNameToDisplay').val(itemBrandNameToDisplay);
	$('#itemSize').val('');
	$('#itemSizeToDisplay').val('');
	$('#itemFilter').val('');
	$(_parentTd).addClass('active');
	processForm('item-search-form');
	return false;
//	$('#showItemsButtonSubmit').show().focus();
 }


//function called on item search page when a user clicks on the size tab on the listing
//to change the active class and change the display values and the form values accordingly in the size tab
function sizeSelectableRow(obj){
	removeActiveFromTabs();
	var _parentTd = $(obj).closest('td');
	var itemSize = $(_parentTd).attr('id');
	var itemSizeToDisplay = $(_parentTd).attr('rel');
	$('#itemBrand').val('');
	$('#itemBrandNameToDisplay').val('');
	$('#itemSize').val(itemSize);
	$('#itemSizeToDisplay').val(itemSizeToDisplay);
	$('#itemFilter').val('');
	$(_parentTd).addClass('active');
	processForm('item-search-form');
	return false;
//	$('#showItemsButtonSubmit').show().focus();
}

// function to remove the order from the current order
function removeItemFromOrder(ItemId){
	if((ItemId != '')){
		var _found = $("#updateOrderAjax").find(".table-responsive table tr."+ItemId+"");
		var itemDesc = $(_found).find("td[desc='desc']").html();
		var uom = $(_found).find("td[uom='uom']").html();
		bootbox.confirm("Are you sure, you want to delete this item?" +
				"<br />Item Number:" + ItemId +
				"<br />Description:" + itemDesc, function(result) {
			if(result == true){
				$('#loading-image').show();
				// sending the ajax request
				$.ajax({
			        url: '/user/delete-item',
			        type: "POST",
			        data: {'itemId':ItemId, 'uom' : uom},
			      //  dataType: "json",
			        async: true,
			        success: function(resultAjax) {
			        	resultAjaxOutput = resultAjax.output.trim();
			        	// 
			        	// if the item is deleted from the order successfully
			        	if(resultAjaxOutput == '1'){
			        		setTimeout(function(){
					               		var _this = $("#updateOrderForm").find("table tr#"+ItemId);
						       				$("#updateOrderAjax").html(resultAjax.html);
						       			if($(_this).length > 0){
						       				$("#status_"+ItemId+"").val('1');
						       				$("#quantity_"+ItemId+"").val('');
						       				$(_this).removeClass("bolderTr");
						       				$(_this).find("td[copy_to='ext_price']").html("-");
						       				$(_this).find("td[copy_to='pallet_qty']").html("-");
						       				$(_this).find("td[copy_to='case_qty']").html("-");
						       				$(_this).find("td[copy_to='unit_qty']").html("-");;
						       			}
				               }, 1000);
			        		bootbox.alert({ message: 'Item Deleted Successfully', 
			        			    callback: function(){ 
			        			    	checkKeyUp();
			        			    }
			        		});
			        		
				               setTimeout(function(){
				            	   bootbox.hideAll();
				            	   checkKeyUp();
				        	   }, 5000);
			        		
			        	} else {
			        		// if the item could not be deleted from the order successfully
			        		bootbox.alert({ message: 'Item Could not be Deleted, Please Try again!', 
			        			    callback: function(){ 
			        			    	checkKeyUp();
			        			    }
			        		});
				               setTimeout(function(){
				            	   bootbox.hideAll();
				            	   checkKeyUp();
				        	   }, 5000);
			        	}
			               
			            return false;
			        },
			        failure: function(errMsg) {
			            alert(errMsg);
			             checkKeyUp(); 
			        },
			        complete: function (){
			        	$('#loading-image').hide();
			        	 checkKeyUp(); 
			        }
			    });
			}
			  
			}); 
	}
}

//function called on item substitute page when a user clicks on the brand/size tab on the listing
//to change the active class and change the display values and the form values accordingly in the brandsize tab
function substituteBrandSizeSelectableRow(obj){
	removeActiveFromTabs();
	var _parentTd = $(obj).closest('td');
	var itemSize = $(_parentTd).attr('id');
	var itemSizeToDisplay = $(_parentTd).attr('rel');
	var _parentTr = $(obj).closest('tr');
	var itemBrand = $(_parentTr).attr('id');
	var itemBrandNameToDisplay = $(_parentTr).attr('rel');
	$('#itemBrand').val(itemBrand);
	$('#itemBrandNameToDisplay').val(itemBrandNameToDisplay);
	$('#itemSize').val(itemSize);
	$('#itemSizeToDisplay').val(itemSizeToDisplay);
	$('#itemFilter').val('');
	$(_parentTd).addClass('active');
	processForm('item-substitute-form');
	return false;
//	$('#showItemsButtonSubmit').show().focus();
 }


//function called on item substitute page when a user clicks on the brand tab on the listing
//to change the active class and change the display values and the form values accordingly in the brand tab
function substituteBrandSelectableRow(obj){
	removeActiveFromTabs();
	var _parentTd = $(obj).closest('td');
	var itemBrand = $(_parentTd).attr('id');
	var itemBrandNameToDisplay = $(_parentTd).attr('rel');
	$('#itemBrand').val(itemBrand);
	$('#itemBrandNameToDisplay').val(itemBrandNameToDisplay);
	$('#itemSize').val('');
	$('#itemSizeToDisplay').val('');
	$('#itemFilter').val('');
	$(_parentTd).addClass('active');
	processForm('item-substitute-form');
	return false;
//	$('#showItemsButtonSubmit').show().focus();
}


//function called on item substitute page when a user clicks on the size tab on the listing
//to change the active class and change the display values and the form values accordingly in the size tab
function substituteSizeSelectableRow(obj){
	removeActiveFromTabs();
	var _parentTd = $(obj).closest('td');
	var itemSize = $(_parentTd).attr('id');
	var itemSizeToDisplay = $(_parentTd).attr('rel');
	$('#itemBrand').val('');
	$('#itemBrandNameToDisplay').val('');
	$('#itemSize').val(itemSize);
	$('#itemSizeToDisplay').val(itemSizeToDisplay);
	$('#itemFilter').val('');
	$(_parentTd).addClass('active');
	processForm('item-substitute-form');
	return false;
//	$('#showItemsButtonSubmit').show().focus();
}

function checkMoreSubtitutes(){
	var currentSubstitutes = $('#currentSubstitutes').html();
	var maxSubstitutes = $('#maximumSubstitutes').html();
	if(currentSubstitutes < maxSubstitutes){
		return true;
	} else {
		return false;
	}
}

// function changeSubstituteStatus to send ajax and update the status of the current item in substitute form
function changeSubstituteStatus(obj){
	
	var ItemId = $(obj).val();
	var action = '';
	if($(obj).prop( "checked" )){
	action = 'ADD';
	} else {
		action = 'RMV';
	}
	
	var checkMore = checkMoreSubtitutes();
	if(checkMore == false && action == 'ADD'){
		$(obj).prop( "checked", false );
		bootbox.alert('You have already the maximum number of substitutes items in your order. Please remove some and try again!');
		return false;
	}
	$('#loading-image').show();
	// sending the ajax request
	$.ajax({
        url: '/user/update-substitutes',
        type: "POST",
        data: {'itemId':ItemId, 'action' : action},
      //  dataType: "json",
        async: true,
        success: function(resultAjax) {
        	if(resultAjax.result == '1'){
        		$('#ajax-data-substitutes').html(resultAjax.html);
        		if(action == 'ADD'){
        			$(obj).closest('tr').addClass('bolderTr');
        		} else {
        			$(obj).closest('tr').removeClass('bolderTr');
        		}
        	}
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

/*
 *  function checkBlankSubstituteAjax - This is the function to check the value of filter on the substitute page
 */
function checkBlankSubstituteAjax(){
	
var itemFilterValue = $('#itemSubstituteFilterAjax').val().trim();
	
	if(itemFilterValue != ''){
		var upperCaseFilterValue = itemFilterValue.toUpperCase();
		$('#itemSubstituteFilterAjax').val(upperCaseFilterValue);
		processForm('item-substitute-form-ajax');
	} else{
		bootbox.alert('Please input some value to search');
		$('#itemFilterAjax').focus();
	}
	return false;
}


/*
 *  function clearFilterItemSubstituteAjax - This is the function to clear the filter value in the ajax results on the substitutes page
 */

function clearFilterItemSubstituteAjax(){
	$('#itemSubstituteFilterAjax').val('');
	processForm('item-substitute-form-ajax');
}

function checkReviewSubstitutes(redirect){
	
	if(redirect === "undefined" || redirect == null) redirect='';
	
	var currentSubstitutes = $('#currentSubstitutes').html();
	var minSubstitutes = $('#minimumSubstitutes').html();
	var maxSubstitutes = $('#maximumSubstitutes').html();
	if(currentSubstitutes <= maxSubstitutes && currentSubstitutes >= minSubstitutes && redirect ==''){
		window.location = '/user/review-order';
	} else if(currentSubstitutes <= maxSubstitutes && currentSubstitutes >= minSubstitutes && redirect !=''){
		
		window.location = '/user/review-order/'+redirect;
	}
	
	else if(currentSubstitutes < minSubstitutes){
		bootbox.alert('Please select minimum '+minSubstitutes+' substitutes to move on to the next step.');
	} else {
		alert('There is some error. Please try again later');
	}
}


function checkMinimumItemsSearch(sendUser){
	
	
	var currentItems = $('#totalItemsNumber').html().trim();
	if(currentItems != '0'){
		
		//alert('kailash');
		
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
	} else {
		//alert('Please select minimum 1 item to add to the current order.');
		bootbox.alert("Please select minimum 1 item to add to the current order.");
	} 
	
}

/* 
 * function checkBlank - This is called when the user clicks on search button from the item Description Tab
 * Purpose - to check whether there is an input value or not and then send ajax 
*/
function checkBlankSubstitutes(){
	
	var itemFilterValue = $('#itemFilter').val().trim();
	
	if(itemFilterValue != ''){
		$('#itemBrand').val('');
		$('#itemBrandNameToDisplay').val('');
	   	$('#itemSize').val('');
	   	$('#itemSizeToDisplay').val('');
		var upperCaseFilterValue = itemFilterValue.toUpperCase();
		$('#itemFilter').val(upperCaseFilterValue);
		processForm('item-substitute-form');
	}
	return false;
}


//function to remove the order from the substitute tab
function removeItemFromSubstitute(ItemId){
	if((ItemId != '')){
		var _found = $("#ajax-data-substitutes").find(".table-responsive table tr."+ItemId+"");
		var itemDesc = $(_found).find("td[desc='desc']").html();
		var action = 'RMV';
		bootbox.confirm("Are you sure, you want to Remove substitute item?" +
				"<br />Item Number:" + ItemId +
				"<br />Description:" + itemDesc, function(result) {
			if(result == true){
				$('#loading-image').show();
				// sending the ajax request
				$.ajax({
					url: '/user/update-substitutes',
			        type: "POST",
			        data: {'itemId':ItemId, 'action' : action},
			      //  dataType: "json",
			        async: true,
			        success: function(resultAjax) {
			        	$('#ajax-data-substitutes').html(resultAjax.html);
			        	var _foundInForm = $("#item-update-substitute-form-ajax").find(".table-responsive table tr#"+ItemId+"");
			        	if(_foundInForm.length > 0){
			        		$("#"+ItemId).removeClass('bolderTr');
			        		$("#"+ItemId).find('input[type="checkbox"]').prop('checked', false);
			        	}
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
// function performed on click of the pencil icon to make the quantity field editable
// params - ItemId
function makeEditable(ItemId){
	if($('.'+ItemId).find('span.formShowHide').length > 0){
	$('.'+ItemId).find('span.formShowHide').show();
	$('.'+ItemId).find('span.textQuantityShowHide').hide();
	$('.'+ItemId).find('form input[name="quantity"]').focus();
	} else {
		alert('There is some error. Please try again later.');
	}
	return false;
}

function cancelUpdateOrderInlineForm(obj){
$(obj).closest("form")[0].reset();
$(obj).closest(".formShowHide").hide();
$(obj).closest("td").find("span").first().show();
return false;
	
}

function submitUpdateOrderInlineForm(obj){
	var formObj = $(obj).closest("form");
	processUpdateOrderInlineForm(formObj);
}

function isInt(n) {
	   return n % 1 === 0;
	}
function processUpdateOrderInlineForm(form){
	 var url = form.attr('action');
	 var quantityField = $(form).find('input[name="quantity"]');
	 var ItemId = $(form).find('input[name="item_number"]').val().trim();
	 var quantityValue = parseFloat(quantityField.val().trim());
	 
	if(isNaN(quantityValue) || quantityValue === 0 || !isInt(quantityValue)){
		alert('Please input numeric quantities only');
		return false;
	} else {
	
		$('#loading-image').show();
		// sending the ajax request
		$.ajax({
			url: url,
	       type: "POST",
	       data: form.serialize(),
	     //  dataType: "json",
	       async: true,
	       success: function(resultAjax) {
	       	$('#updateOrderAjax').html(resultAjax.html);

	       	setTimeout(function(){
	       	var _foundInForm = $("#updateOrderForm").find(".table-responsive table tr#"+ItemId+"");
	       	if(_foundInForm.length > 0){
	       		$(_foundInForm).find("td[copy_to='ext_price']").html($('.'+ItemId).find("td[copy_from='ext_price']").html());
					$(_foundInForm).find("td[copy_to='pallet_qty']").html($('.'+ItemId).find("td[copy_from='pallet_qty']").html());
					$(_foundInForm).find("td[copy_to='case_qty']").html($('.'+ItemId).find("td[copy_from='case_qty']").html());
					$(_foundInForm).find("td[copy_to='unit_qty']").html($('.'+ItemId).find("td[copy_from='unit_qty']").html());
					$("#quantity_"+ItemId).val(quantityValue);
	       	}
           }, 1000);  
	           return false;
	       },
	       failure: function(errMsg) {
	           alert(errMsg);
	       },
	       complete: function (){
	       	$('#loading-image').hide();
	       	 checkKeyUp();
	       }
	   });
		
	}
	
	return false;
	
}



