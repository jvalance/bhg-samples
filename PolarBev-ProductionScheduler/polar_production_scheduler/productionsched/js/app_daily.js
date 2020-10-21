$('document').ready(function(){
 	$('#dailylist tbody').sortable({
 	 	items: '.dailyRow',
//		helper: "clone",
// 	 	activate: function(event, ui) {
		helper: function(event, ui) {
			var draggedID = '#' + $(ui).attr('id');
			var itemNo = $(draggedID + ' td[itemno]').attr('itemno');
			var itemDesc = $(draggedID + ' td[itemdesc]').attr('itemdesc');
			var helperWidth = itemDesc.length;
			var quantity = $(draggedID + ' td[qty]').attr('qty');
			var dragText = 'Item#: ' + itemNo + '<br>' + itemDesc + '<br>' + 'Quantity = ' + quantity;
			var helperDiv = '<div id="dragDaily" class="dragPlanned" style="width: 250px; height: 50px">' + dragText + '</div>';
//			ui.helper.html(helperDiv);
//			return true;
			return $(helperDiv);
		},
		cursorAt:  { left: 85, top: 5 },
		appendTo : 'body',
	    scroll: true,
//	    delay: 1,
        update: function (event, ui) {
        	$('#dragDaily').remove(); // delete the helper div
	        var seq = 0;
	        var tempDailyObj = new Object();

	        $('#dailylist tbody tr td.seqno').each(function(){
	        	seq += 10;
	        	//alert($(this).attr('id') + ', seq = ' + seq);
				$(this).text(seq);
	        	var orderNum = $(this).attr('orderno');
	        	//alert('orderNum = ' + orderNum + '; seq = ' + seq);
	        	objWeekly[dailySchedDateLoaded][orderNum]['seqno'] = seq;
	        	tempDailyObj[orderNum] = objWeekly[dailySchedDateLoaded][orderNum];
		    });
	        objWeekly[dailySchedDateLoaded] = tempDailyObj;
	        buildWeeklyChart();
	    	// Allow save changes, disallow shop orders, and set changed data flag
	    	$("#saveButton").show();
	    	$("#toShopButton").hide();
	    	boolChangedData = true;

	    	// Refresh user lock timer.
	    	var script = 'updateUserLock.php';
	    	var data = {'facility' : reqFacility,
	    				'work_ctr' : reqWorkCtr
	    	};

	    	$.get(script, data);
	    }
	}).disableSelection();
});

function start_FF_Fix(event, ui) {
	//fix firefox position issue when dragging.
	if (navigator.userAgent.toLowerCase().match(/firefox/) && ui.helper !== undefined) {
		ui.helper.css('position', 'absolute').css('margin-top', $(window).scrollTop());
		//wire up event that changes the margin whenever the window scrolls.
		$(window).bind('scroll.sortableplaylist', function () {
        	ui.helper.css('position', 'absolute').css('margin-top', $(window).scrollTop());
		});
    }
}
function undo_FF_Fix( ui, event ) {
	//undo the firefox fix.
	if (navigator.userAgent.toLowerCase().match(/firefox/) && ui.offset !== undefined) {
		$(window).unbind('scroll.sortableplaylist');
		ui.helper.css('margin-top', 0);
	}
}

/** Add a "sortByProp" method to the Array class
 * This will allow us to sort the daily schedule orders
 * by the sequence number property.
 * @param p - property of the objects in the array by which to sort.
 * @returns 1 if obj1.property > obj2.property,
 * 		   -1 if obj1.property < obj2.property,
 *          0 if obj1.property == obj2.property.
 */
Array.prototype.sortByProp = function(property){
	return this.sort(function(obj1,obj2){
		return parseInt(obj1[property]) - parseInt(obj2[property]);
	});
};


function loadDailySchedule() {
	$("#dailylist tbody tr").remove();


//	$daily_caption_date = HTML_FormHelper::convertDateFormat(dailySchedDateLoaded, 'Y-m-d', 'M d, Y');
	var date_daily = $.datepicker.parseDate( 'yy-mm-dd', dailySchedDateLoaded );
	var daily_caption_date = $.datepicker.formatDate('DD, M d, yy', date_daily); // 'Y-m-d', 'M d, Y');

	var caption = daily_caption_date;
//	var caption = 'Daily Orders for ' + daily_caption_date;
	$("#dailyCaption").html(caption);

	var dailyTotalQty = dayTotals[dailySchedDateLoaded];
	var dailyTotalQtyText = 'Total qty = ' + dailyTotalQty;
	$("#dailyTotal").html(dailyTotalQtyText);

	var dailyOrders = objWeekly[dailySchedDateLoaded];
	var idx = 0;

	// Reset the expand/collapse icon in the daily header
	$('#dailyHeadExpImg').attr('src', 'images/expand2.gif');

	// Put daily orders into an array that we can sort by seqno
	var arrDailyOrders = new Array();
	var ordCnt = 0;
	for (var orderNum in dailyOrders) {
		arrDailyOrders[ordCnt++] = dailyOrders[orderNum];
	}
	// alert('after for loop - number of orders = ' + arrDailyOrders.length);

	// Sort orders by sequence number
	arrDailyOrders.sortByProp('seqno');

	// Loop through orders array in seqno order and add table rows for each order
	for (var ordIdx = 0; ordIdx < ordCnt; ordIdx++) {
		var seqno = arrDailyOrders[ordIdx].seqno;
		var itemno = arrDailyOrders[ordIdx].itemno;
		var itemdesc = arrDailyOrders[ordIdx].itemdesc;
		var qty = arrDailyOrders[ordIdx].qty;
		var hours = arrDailyOrders[ordIdx].hours;
		var type = arrDailyOrders[ordIdx].type;
		var orderNum = arrDailyOrders[ordIdx].orderno;
		var reschedule = arrDailyOrders[ordIdx].reschedule;
		var duedate = arrDailyOrders[ordIdx].duedate;
		var lotSize = arrDailyOrders[ordIdx].lotSize;
		var incrOrderQty = arrDailyOrders[ordIdx].incrOrderQty;
		var hasAltRoutes = arrDailyOrders[ordIdx].hasAltRoutes;
		var altRoutes = arrDailyOrders[ordIdx].altRoutes;
		var currRoute = arrDailyOrders[ordIdx].route;
		var inProcFlag = arrDailyOrders[ordIdx].inProcFlag;

//		var temp = JSON.stringify(arrDailyOrders[ordIdx]);
//		alert('arrDailyOrders[ordIdx] = ' + temp);

		// If order is currently in process changing from firm to shop, show type as "FS"
		if (inProcFlag == 'Y') {
			type = 'FS';
		}

		idx += 10;

		var hiddenTypeInputTag = '<input type="hidden" name="TYPE['
			+ orderNum +
			']" id="TYPE_' + orderNum +
			'" value="' + type +
			'" valueLoaded="' + type +
			'" size="1" />';

		var qtyInputTag = '';
		var removeImgTag = '';
		var rowClass = '';
		var hasAnyFirm = '';

		switch (type) {
		case 'F':
			var classAttr = 'DailyQtyInput';
			// Test to see if quantity ordered is multiple of incremental order quantity
			// Call custom modulus function, and round result to 4 fixed decimal places
		   	var remainder = Number((mod(qty, incrOrderQty)).toFixed(4));
			if (remainder >= 1.0) {
				classAttr += ' warning';
			}

			qtyInputTag = '<input type="text" name="QTY[' + orderNum + ']" ' +
				'id="QTY_' + orderNum + '" ' +
				'class="' + classAttr + '" ' +
				'value="' + qty + '" ' +
				'orderno="' + orderNum + '" ' +
				'qtyLoaded="' + qty + '" ' +
				'hrsLoaded="' + hours + '" ' +
				'style="text-align: right" ' +
				'onclick="this.focus();" ' +
				'onChange="changeQty(this.id)" size="7" />';
			removeImgTag = '&nbsp;&nbsp;<img height="13" width="13" align="middle" ' +
				'src="images/gray_x1.gif" title="Click to make PLANNED" class="changeOrderType" ' +
				'onclick="changeType(\'' + orderNum + '\')" />';
			rowClass = 'firm';
			break;
		case 'FS':
			qtyInputTag = qty;
			removeImgTag = '';
			rowClass = 'shop';
			break;
		case 'P':
			qtyInputTag = qty;
			removeImgTag = '&nbsp;&nbsp;<img height="13" width="13" align="middle" ' +
				'src="images/blue_plus.png" title="Click to make FIRM" class="changeOrderType" ' +
				'onclick="changeType(\'' + orderNum + '\')" />';
			rowClass = 'planned';
			break;
		case 'S':
			qtyInputTag = qty;
			removeImgTag = '';
			rowClass = 'shop';
			break;
		default:
			break;
		}

		// Gray out order if changed to different work center
		if (rowClass != 'planned' && currRoute != reqWorkCtr) {
			rowClass = 'planned';
		}

		// If order has rechedule date, show item # in red font
//		var itemRescheduleClass = reschedule != '' ? ' reschedule' : '';

		var itemRescheduleClass = 'duedate';
		if (reschedule != '' && reschedule != 'undefined') {
		    var obj_reschedule_date = new Date(reschedule);
		    var obj_due_date = new Date(duedate);
		    if (obj_reschedule_date < obj_due_date) itemRescheduleClass = ' expedite ';
		    if (obj_reschedule_date > obj_due_date) itemRescheduleClass = ' deexpedite ';
		}
		//rowClass += due_resched;

		// If order is in process firm to shop, show type in red font

		var expandImg = '<img src="images/expand2.gif" id="expImg_' + orderNum +  '" />';
		var dailyRow =
			'<tr id="daily_' + orderNum +
			'" rowOrderNo="' + orderNum +
			'" ondblclick="toggleDailyDtl(\'' + orderNum + '\')' +
			'" class="dailyRow ' + rowClass +
			'" class="ui-widget-content jqgrow ui-row-ltr ui-draggable">' +
				'<td class="expandIcon center" id="expandDaily_' + orderNum + '" >' + expandImg + '</td>' +
				'<td class="seqno right" orderno="' + orderNum + '" >' + seqno + '</td>' +
				'<td itemno="' + itemno + '" class="center ' + itemRescheduleClass + '" >' + itemno + '</td>' +
				'<td itemdesc="' + itemdesc + '" class="left">' + itemdesc + '</td>' +
				'<td qty="' + qty + '" class="right"><span id="qtyTD_' + orderNum + '">' + qtyInputTag + '</span></td>' +
				'<td class="center">' + orderNum + '</td>' +
				'<td class="center order-type-' + type +
					'">' + type + removeImgTag + hiddenTypeInputTag + '</td>' +
			'</tr>';
		$("#dailylist tbody").append(dailyRow);

		if (type == 'F') {
			checkIncrOrderQty ( '#QTY_' + orderNum );
			hasAnyFirm = true;
		}

		var selectAltRoutes = '';
		if (hasAltRoutes && type != 'FS') {
			selectAltRoutes = '<div style="float:left; display: inline">' +
				'Alt Work Ctr: <select name="altRoute[' + orderNum + ']" ' +
					'id="altRoute_' + orderNum + '" ' +
					'orderNum="' + orderNum + '" ' +
					'onclick="this.focus();" ' +
					'onChange="changeRoute(this.id)">';
			// add current work center as selected route
			selectAltRoutes += '<option selected value="' + reqWorkCtr + '">' + reqWorkCtr + '</option>';

			for (route in altRoutes) {
				selectAltRoutes += '<option value="' + altRoutes[route] + '">' + altRoutes[route] + '</option>';
			}
			selectAltRoutes += '</select>&nbsp;</div>';
		}

		if (reschedule != '' && reschedule != 'undefined') {
			var rescheduleSpan = '<span class="right">Reschedule: </span>' +
				'<span class="left reschedule"> ' + reschedule + ' </span>' +
				'&nbsp;&nbsp;';
		} else {
			var rescheduleSpan = '';
		}

		var dropContent = selectAltRoutes + '<div style="float:right; display: inline">' +
			rescheduleSpan +
			'<span class="right">Lot Size: </span>' +
			'<span class="left" style="color: navy"> ' + lotSize + ' </span>' +
			'&nbsp;&nbsp;' +
			'<span class="right">Incr order qty: </span>' +
			'<span class="left" style="color: navy"> ' + incrOrderQty + ' </span></div>';
		var dailyDropRow =
			'<tr id="dailyDrop_' + orderNum +
			'" rowOrderNo="' + orderNum +
			'" ondblclick="toggleDailyDtl(\'' + orderNum + '\')"' +
			'" class="dailyDrop"' +
			 ' class="ui-widget-content jqgrow ui-row-ltr ui-draggable">' +
				'<td colspan="2">&nbsp;</td><td colspan="20" ' +
					'style="border: 1px solid silver; border-bottom:0; background-color: #EAF4FD; vertical-align:middle; padding-top:3px">'
			 		+ dropContent
			 	+ '</td>' +
			'</tr>';
		$("#dailylist tbody").append(dailyDropRow);

		// Set currently selected route in drop down list
		if (hasAltRoutes) {
			$('#altRoute_' + orderNum).val(currRoute);
		}
	}

	// Show firm_to_shop button only if any firm orders in current day
	if (hasAnyFirm && !boolChangedData) {
		$("#toShopButton").show();
	} else {
		$("#toShopButton").hide();
	}


	$(".warningTooltip").remove();
	addWarningTip('.warning',"warningTooltip");

	// On enter in qty field, trigger update of weekly grid by blurring
	$('.DailyQtyInput').bind('keyup', function(event) {
 	    if(event.which==13){ // Enter key = 13
 	    	$(this).blur();
	    }
	});

	// If daily list was previously expanded, then show it expanded again.
	toggleAllDailyDtls(dailyListDetailsState);

	$('#daily').show(); // Daily panel is intially hidden


}

function toggleDailyDtl(orderNum) {
	imgID = '#expImg_' + orderNum;
	dropId = '#dailyDrop_' + orderNum;
	if ($(imgID).attr('src').indexOf('expand') > -1) {
		$(imgID).attr('src', 'images/collapse2.gif');
		//$(dropId).css('display', '');
		$(dropId).show();
	} else {
		$(imgID).attr('src', 'images/expand2.gif');
		$(dropId).hide();
	}
}

function toggleAllDailyDtlsAuto() {
	hdrImgID = '#dailyHeadExpImg';
	if ($(hdrImgID).attr('src').indexOf('expand') > -1) {
		$(hdrImgID).attr('src','images/collapse2.gif');
		dailyListDetailsState = 'show';
	} else {
		$(hdrImgID).attr('src', 'images/expand2.gif');
		dailyListDetailsState = 'hide';
	}

	toggleAllDailyDtls( dailyListDetailsState );
}

function toggleAllDailyDtls( state ) {
	$('.dailyRow').each(function() {
		orderNum = $(this).attr('roworderno');
		imgId = '#expImg_' + orderNum;
		thisImg = $(imgId);
		dropId = '#dailyDrop_' + orderNum;
		if (state == 'show') {
			thisImg.attr('src', 'images/collapse2.gif');
			$(dropId).show();
		} else {
			thisImg.attr('src', 'images/expand2.gif');
			$(dropId).hide();
		}
	});
}

function checkIncrOrderQty ( id ) {
   	var orderNum = $(id).attr('orderno');
   	var inpQty = parseFloat($.trim($(id).val()));
   	var incrOrdQty = parseFloat(objWeekly[dailySchedDateLoaded][orderNum]['incrOrderQty']);
   	var lotSize = parseFloat(objWeekly[dailySchedDateLoaded][orderNum]['lotSize']);

	var message = '';
	// Call custom modulus function, and round result to 4 fixed decimal places
   	var remainder = Number((mod(inpQty, incrOrdQty)).toFixed(4));

   	var diffObject = calcDiffFromIncrOrdQty( inpQty, incrOrdQty );
   	var diffFromIncrOrd = diffObject.diff;

   	// If the difference between the qty entered and nearest multiple of incremental
   	// order qty is more than 1, flag as warning.
   	if (diffFromIncrOrd > 1.0) {
		message = '<span style="color:red">WARNING: The quantity entered (' + inpQty + ') for order# ' +
				orderNum + '\nis not a multiple of the incremental order quantity.</span><ul>'
				+ '\n<li>Incremental order qty = ' + incrOrdQty + '</li>'
				+ '\n<li>Nearest whole multiple = ' + diffObject.nearestMultiple + '</li>'
				+ '\n<li>Nearest incremental order = ' + diffObject.nearestIncrOrder  + '</li>'
				+ '\n<li>Difference = ' + diffObject.diff + '</li></ul>';

		$(id).addClass('warning');
//		var tipId = '#qtyTD_' + orderNum;
		$(id).attr('title', message);
//		simpleTipsObj[id] = message;
	} else {
		$(id).removeClass('warning');
		$(id).removeAttr('title');
	}

	return message;
}
/**
 * This is used to calculate the difference between the input quantity and the nearest
 * whole multiple of the incremental order quantity - If this difference is greater than
 * one, we will display a warning message for this order.
 * @param inputQty integer The quantity entered on the daily schedule for this order.
 * @param incrementalOrderQty float The incremental order quantity for this item.
 */
function calcDiffFromIncrOrdQty( inputQty, incrementalOrderQty ) {
	var quotient = inputQty / incrementalOrderQty;
   	var intQuotient = Math.floor(quotient); // extract integer portion of the quotient
   	var decQuotient = quotient - Math.floor(quotient); // extract decimal portion of the quotient
   	var diff = 0;
   	var nearestIncrOrder = 0;

	if (intQuotient < 1) {
		// If qty entered is less than incr ord qty, quotient will be less than one.
		intQuotient = 1;
   		nearestIncrOrder = incrementalOrderQty * intQuotient ;
   		diff = nearestIncrOrder - inputQty; // subtract higher number from lower
	} else if (decQuotient >= 0.5) {
   	   	// If the fractional part is >= half, then the nearest multiple of
   		// incremental order quantity is greater than the qty input. So add 1 to
   		// the integer part of the quotient and multiply by incremental order qty
   		// to get the nearest multiple of the incremental order qty.
   		intQuotient++;
   		nearestIncrOrder = incrementalOrderQty * intQuotient;
   		diff = nearestIncrOrder - inputQty; // subtract higher number from lower
   	} else {
   	   	// If the fractional part is < half, then the nearest multiple of
   		// incremental order quantity is less than the qty input. So just use
   		// the integer part of the quotient and multiply by incremental order qty
   		// to get the nearest multiple of the incremental order qty.
   		nearestIncrOrder = incrementalOrderQty * intQuotient ;
   		diff = inputQty - nearestIncrOrder; // subtract higher number from lower
   	}
   	// Return the difference (if it is > 1, then a warning msg will be displayed)
	retObj = {'nearestMultiple' : intQuotient, 'nearestIncrOrder' : nearestIncrOrder, 'diff' : diff};
   	return retObj;

}


function changeQty( id ) {
	id = '#' + id;
   	var orderNum = $(id).attr('orderno');
   	var prevQty = objWeekly[dailySchedDateLoaded][orderNum]['qty'];
   	var incrOrdQty = objWeekly[dailySchedDateLoaded][orderNum]['incrOrderQty'];
   	var lotSize = objWeekly[dailySchedDateLoaded][orderNum]['lotSize'];

   	var newQty = $.trim($(id).val());

   	// Test for valid numeric input
	var re = /[^\d]/;
	if(re.test(newQty)){
		alert('The quantity entered (' + newQty + ') for order# ' +
				orderNum + ' is not a valid integer value.');
		$(id).val(prevQty);
		return false;
	}

	var message = checkIncrOrderQty(id);
	if (message != '') {
//		alert('B4 replace: ' + message);
//		message = message.replace(/<li>/g,'\n');
//		alert('B4 strip: ' + message);
		message = strip_tags_js(message, '');
		alert(message);
	}

   	var prevQty = objWeekly[dailySchedDateLoaded][orderNum]['qty'];
   	var prevHrs = objWeekly[dailySchedDateLoaded][orderNum]['hours'];
   	var factor = newQty / prevQty;
   	var newHrs = (prevHrs * factor).toFixed(3);
//   	alert('orderNum = ' + orderNum +
//   			'\n Prev Qty: ' + prevQty + '; New Qty: ' + newQty +
//   			'\n Factor: ' + factor +
//			'\n Prev Hrs: ' + prevHrs + '; New Hrs: ' + newHrs );
   	objWeekly[dailySchedDateLoaded][orderNum]['qty'] = newQty;
   	objWeekly[dailySchedDateLoaded][orderNum]['hours'] = newHrs;
   	objWeekly[dailySchedDateLoaded][orderNum]['hoursAlpha'] = ' @ ' + newHrs + ' Hrs';
   	dataChangeUpdates();
}

function changeType( orderNum ) {
	typeArray = new Array();
	typeArray['F'] = 'FIRM';
	typeArray['P'] = 'PLANNED';

	var id = '#TYPE_' + orderNum;
	var currType = $(id).val();
	if (currType == 'P') {
		newType = 'F';
	} else {
		newType = 'P';
	}

	if (confirm('Change order #' + orderNum + ' from '
				+ typeArray[currType] + ' to ' + typeArray[newType] + '?'))
	{
	   	objWeekly[dailySchedDateLoaded][orderNum]['type'] = newType;
	   	$(id).val(newType);
	   	dataChangeUpdates();
	}

}

function changeRoute( id ) {
	id = '#' + id;
   	var orderNum = $(id).attr('orderNum');

   	var selectedRoute = $(id).val();
	objWeekly[dailySchedDateLoaded][orderNum]['route'] = selectedRoute;
   	dataChangeUpdates();
}

