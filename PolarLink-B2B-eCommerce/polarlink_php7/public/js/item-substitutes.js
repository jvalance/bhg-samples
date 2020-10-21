// this is the variable current tab to check where the user is currently
var currentTab = $("ul#tabs li.active a").attr('href');

$(document).ready(function(){
	
// to make the active class on the selected tab
    $('#accordion a').click(function(){
    	$('#accordion a').removeClass('active');
			$(this).addClass('active');
        });
// to change the active class and change the display values and the form values accordingly in the brandsize tab
//    $('.brandSizeSelectableRow').click(function(e){
//    	$('.brandSizeSelectableRow, .brandSelectableRow, .sizeSelectableRow').removeClass('active');
//    	var itemSize = $(this).attr('id');
//    	var itemSizeToDisplay = $(this).attr('rel');
//    	var itemBrand = $(this).parent().attr('id');
//    	var itemBrandNameToDisplay = $(this).parent().attr('rel');
//    	$('#itemBrand').val(itemBrand);
//    	$('#itemBrandNameToDisplay').val(itemBrandNameToDisplay);
//    	$('#itemSize').val(itemSize);
//    	$('#itemSizeToDisplay').val(itemSizeToDisplay);
//    	$('#itemFilter').val('');
//    	$(this).addClass('active');
//    	processForm('item-search-form');
//   // 	$('#showItemsButtonSubmit').show().focus();
//        });
    
    
//  to change the active class and change the display values and the form values accordingly in the brand tab

//    $('.brandSelectableRow').click(function(){
//	    $('.brandSizeSelectableRow, .brandSelectableRow, .sizeSelectableRow').removeClass('active');
//	   	var itemBrand = $(this).attr('id');
//	   	var itemBrandNameToDisplay = $(this).attr('rel');
//	   	$('#itemBrand').val(itemBrand);
//	   	$('#itemBrandNameToDisplay').val(itemBrandNameToDisplay);
//		$('#itemSize').val('');
//		$('#itemSizeToDisplay').val('');
//		$('#itemFilter').val('');
//		$(this).addClass('active');
//		processForm('item-search-form');
//   // 	$('#showItemsButtonSubmit').show().focus();
//   });
    
//  to change the active class and change the display values and the form values accordingly in the size tab
//   $('.sizeSelectableRow').click(function(){
//	    $('.brandSizeSelectableRow, .brandSelectableRow, .sizeSelectableRow, .brandSizeSelectableRow a').removeClass('active');
//	  	var itemSize = $(this).attr('id');
//	  	var itemSizeToDisplay = $(this).attr('rel');
//	  	$('#itemBrand').val('');
//	  	$('#itemBrandNameToDisplay').val('');
//	   	$('#itemSize').val(itemSize);
//	   	$('#itemSizeToDisplay').val(itemSizeToDisplay);
//	   	$('#itemFilter').val('');
//	   	$(this).addClass('active');
//	   	processForm('item-search-form');
//    // 	$('#showItemsButtonSubmit').show().focus();
//  });

 // on click on any of the tab at the top changing the values of the selected rows and hiding the show items button    
    $("#tabs li a").click(function(){
          if(_isDirty){
        	if(confirm("This page is asking you to confirm that you want to leave - data you have entered may not be saved")) {
        		tabbing();
				return true;
                }
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

/* function showNewSearch()
 *  this is the function which is used to show the current search form again. This is called from the new search button from the search results
 *  
 */
function showNewSearch(){
	// checking the current tab url
	var currentTabNew = $("ul#tabs li.active a").attr('href');
	$('.brandSizeSelectableRow, .brandSelectableRow, .sizeSelectableRow, .brandSizeSelectableRow a').removeClass('active');
  	 $('#showItemsButtonSubmit').hide();
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
		processForm('item-substitute-form');
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
		alert('Please input some value to search');
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
	var attrIdArray = attrId.split('_');
	$('#status_'+attrIdArray['1']).val('1');
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
        	$('#loading-image').hide();
        	sendAjax = true;
        }
    });
    return false;
}


/*
 *  function clearFilterItemAjax - This is the function to clear the filter value in the ajax results 
 */

function clearFilterItemAjax(){
	$('#itemFilterAjax').val('');
	processForm('item-search-form-ajax');
}

// this is the function called when a user clicks on new search or on any new element for searching the items
function removeActiveFromTabs(){
	$('.brandSizeSelectableRow, .brandSelectableRow, .sizeSelectableRow, .brandSizeSelectableRow a, .brandSelectableRow a, .sizeSelectableRow a').removeClass('active');
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
			        		bootbox.alert('Item Deleted Successfully', function() { });
				               setTimeout(function(){
				            	   bootbox.hideAll();
				        	   }, 5000);
			        		
			        	} else {
			        		// if the item could not be deleted from the order successfully
			        		bootbox.alert('Item Could not be Deleted, Please Try again!', function() { });
				               setTimeout(function(){
				            	   bootbox.hideAll();
				        	   }, 5000);
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
