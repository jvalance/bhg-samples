// ---------- GLOBAL VARIABLES ------------
/** objWeekly: (object)
 * This will initially hold the json response of orders by day, to load the
 * weekly orders bar chart. It will be updated by drag/drop actions, which will
 * move orders between days. These changes will be reflected visually in the
 * bar chart, but they will not be saved to the database until the save button is clicked.
*/
var objWeekly = new Object();

/** dayTotals: (object)
 * This will hold total quantities for each day. Properties identified by the date will be added
 * as each day is added to the grid.  i.e. dayTotals['2016-01-25'] = 54792;
 */
var dayTotals = new Object();

/** weeklyHeight: (integer)
 * This will hold the current height of the entire weekly div. This div's height will vary
 * based on how many orders are scheduled for the days displayed. The day with the greatest
 * order quantity will determine the height of the entire weekly div. As orders are moved
 * from one day to another, the height may change to accomodate increases/decreases in daily order qtys.
 */
var weeklyHeight = 0;

/**
 * boolChangedData: (boolean)
 * Changed to true when any data is changed on the screen.
 */
var boolChangedData = false;

/**
 * wcCapacity is the work center capacity in hours per day. It is used to determine
 * if any orders in the weekly schedule are over capacity. If so, these orders
 * will show in RED on the weekly schedule. This value is retrieved along with the
 * daily schedule in weeklyScheduleRtv.php
 */
var wcCapacity = 24.0;
var wcHrsPerShift = 8;
var wcNumberOfShifts = 3;
var wcDescription = '';

var basePos = 23; // vertical pos to start building order stack
var hoursToPixelMult = 25; // how many pixels of height for each hour

/**
 * State of daily schedule drop rows.
 * 'hide' = Do not show second row with details
 * 'show' = Show second row with details
 * This is used when reloading the schedule due to changes in the daily list.
 * If details were displayed, the daily list will redisplay with details showing.
 */
var dailyListDetailsState = 'hide';

/**
 * The date that is currently loaded into the daily shedule.
 */
var dailySchedDateLoaded;

/**
 * plannedOrderNum is used to assign a dummy order number to planned orders that are
 * dragged from the Planned Orders panel to the weekly schedule. It will be incremented
 * each time an order is dragged from planned to firm planned.
 */
var plannedOrderNum = 0;

//var simpleTipsObj = {};

// ------------ FUNCTIONS -----------------
function simple_tooltip(target_items, name){
	 $(target_items).each(function(i){
		 	var titleText = $(this).attr('title');
		 	titleText = '<center>' + titleText.replace(/@NL/g,'<br />') + '</center>';
			$("body").append("<div class='"+name+"' id='"+name+i+"'><p>"+titleText+"</p></div>");
			var my_tooltip = $("#"+name+i);

			$(this).removeAttr("title").mouseover(function(){
					my_tooltip.css({opacity:1, display:"none"}).show();
			}).mousemove(function(kmouse){
					my_tooltip.css({left:kmouse.pageX-250, top:kmouse.pageY-25});
			}).mouseout(function(){
					my_tooltip.hide();
			});
		});
}

function addWarningTip(target_items, name){
	$(target_items).each(function(i){
		var titleText = $(this).attr('title');
//		titleText = titleText.replace(/@NL/g,'<br />');
		$("body").append("<div class='"+name+"' id='"+name+i+"'>"+titleText+"</div>");
		var my_tooltip = $("#"+name+i);

		$(this).removeAttr("title").mouseover(function(){
			my_tooltip.css({opacity:1, display:"none"}).show();
		}).mousemove(function(kmouse){
			my_tooltip.css({left:kmouse.pageX+5, top:kmouse.pageY+5});
		}).mouseout(function(){
			my_tooltip.hide();
		});
	});
}

$('document').ready(function() {
	$("#wcdetails").html('Weekly Schedule...');
	var initialHeight = (Math.ceil(wcCapacity * hoursToPixelMult) + basePos);
	$("#bottom_panels").height(initialHeight);
//alert('initialHeight = ' + initialHeight);
	$(".dayColumn").height(initialHeight);
	$("#weekly").height(initialHeight);

	// Set the function to call before leaving the page. Function checkNavOK() will
	// check for any user changes before leaving the page.
	window.onbeforeunload = checkNavOK;
	window.onunload = checkNavOK;

	$( "#weekly-legend" ).dialog({
		autoOpen: false,
		draggable: true,
		droppable: false,
		closeOnEscape: true,
		width: 375,
		position: { my: "center", at: "center top", of: "#daily" },
	});

	$( "#weekly-legend-button" ).click(function() {
		$( "#weekly-legend" ).dialog( "open" );
	});

	// weeklyScheduleRtv.php?facility=WO&from_date=20120801&to_date=20120812&work_ctr=10
	$("#backButton").show();
	$("#saveButton").hide();
//	$("#toShopButton").show();
	var script = 'weeklyScheduleRtv.php';
	var data = {'facility' : reqFacility,
				'weekly_from_date' : reqWeeklyFromDate,
				'work_ctr' : reqWorkCtr,
				'debug' : reqDebug
	};

	//alert(script + '\n' + data.facility);
	$.get(script, data, weeklyCallBack, 'json');

	return false;
});


$('document').ready(function(){

});

function checkNavOK() {
	// This function is bound to the window's onbeforeunload event handler.
	// If form inputs have been changed by the user, it will warn them to save changes first

	var script = 'deleteWorkCtrLock.php';

	var data = {
			'facility' : reqFacility,
			'work_ctr' : reqWorkCtr
	};

	if (boolChangedData == true) {
		return'Data on this page has been changed. ' +
			'If you leave this page without saving, your changes will be lost.' +
			'\n\nClick OK to leave WITHOUT saving changes.' +
			'\nClick Cancel to return to editing and save your changes.';
	} else if (boolCloseSession == false) {
	    jQuery.ajax({
	        url: script,
	        data: data,
	        async: false /* Browser closing will hang until server call completes. May cause problems. */
	    });


	// 		return'You currently have an open session on this work center. ' +
	// 			'If you leave this page without closing the session, ' +
	// 			'you will not be able to re-open it until the session lock expires.' +
	// 			'\n\nClick OK to leave WITHOUT closing the session.' +
	// 			'\nClick Cancel to return to editing and close the session.';
	}
}

function weeklyCallBack( response ) {
	//alert('in weeklyCallBack()');
	objWeekly = response;
	dailySchedDateLoaded = $("#weekly_current_date").val();
	buildWeeklyChart();
}

function buildWeeklyChart() {

	// Capture current window scrolling position so we can reposition
	// in same spot after repainting screen.
	var scrollPosition = $(window).scrollTop();

	// Remove all days from weekly div
	$("#weekly div.dayColumn").remove();
	$(".tooltip").remove();

	//var initialHeight = (Math.ceil(wcCapacity * hoursToPixelMult) + basePos);
	$("#bottom_panels").height(basePos);
	$(".dayColumn").height(basePos);
	$("#weekly").height(basePos);
	//alert('In buildWeeklyChart(); dailySchedDateLoaded = ' + dailySchedDateLoaded + '; #weekly_current_date = ' + $("#weekly_current_date").val());

	var i = 1;
	for (var oDate in objWeekly) {
		if (oDate == 'workcenter') {
			// Retrieve work center details from the master weekly object
			workcenter = objWeekly[oDate];
			wcNumberOfShifts = workcenter['numShifts'];
			wcHrsPerShift = workcenter['hrsPerShift'];
			wcCapacity = wcNumberOfShifts * wcHrsPerShift;
			wcDescription = workcenter['description'];
			wcNum = workcenter['wcNum'];
			var wcDetailText =
				'Weekly Schedule - Line#: <b>' + wcNum + ' - ' + wcDescription
				+ '</b>; &nbsp;&nbsp;Capacity: <b>' + wcCapacity
				+ ' hrs</b>; &nbsp;&nbsp;No. of shifts: <b>' + wcNumberOfShifts
				+ '</b>; &nbsp;&nbsp;Hrs/shift: <b>' + wcHrsPerShift + '</b>';
			$("#wcdetails").html(wcDetailText);
		} else {
			if (dailySchedDateLoaded == '') dailySchedDateLoaded = oDate;
			var schedDate = Date.parse(oDate);
			//alert('oDate = ' + oDate); // + '; schedDate = ' + schedDate.toShortString());
			var orders = objWeekly[oDate];
			addDayToWeek(i++, orders, oDate);
		}
	}
	// Get tallest day and set height of entire weekly div
	weeklyHgt = setWeeklyHeight( );

//	alert('in buildWeeklyChart(), B4 loadDailySchedule(): dailySchedDateLoaded = ' + dailySchedDateLoaded);
	loadDailySchedule();

	// Reposition to previous window scrolling position after repainting screen.
	$(window).scrollTop(scrollPosition );


	// Add tooltips for each orderBox div, showing order details based on div's title attribute
	simple_tooltip(".orderBox","tooltip");

	// When clicking on an order in the weekly schedule, if that order's day is loaded in the
	// daily panel, permanently highlight the same order in the daily panel using orange background.
	$(".orderBox").click(function(){
		var clickedOrderDate = $(this).attr('date');
		//alert('clickedOrderDate = ' + clickedOrderDate + '; dailySchedDateLoaded = ' + dailySchedDateLoaded);
		if (clickedOrderDate == dailySchedDateLoaded) {
			$('#dailylist tbody tr').each(function(){
				$(this).removeClass('dailyRowSelected');
			});
			var clickedOrderNo = $(this).attr('orderno');
			var clickedID = '#dailylist tbody tr#daily_' + clickedOrderNo;
			//alert('clickedID = ' + clickedID);
			$(clickedID).removeClass('dailyRowHover');
			$(clickedID).addClass('dailyRowSelected');
		}
	});

	// When hovering over an order in the weekly schedule, if that order's day is loaded in the daily
	// panel, temporarily highlight the same order in the daily panel using light blue background.
	// This creates a visual connection between the order in the weekly and daily panels on mouse over.
	$(".orderBox").mouseover(function(){
		var moOrderDate = $(this).attr('date');
		if (moOrderDate == dailySchedDateLoaded) {
			//alert('moOrderDate = ' + moOrderDate + '; dailySchedDateLoaded = ' + dailySchedDateLoaded);
			var moOrderNo = $(this).attr('orderno');
			var moID = '#dailylist tbody tr#daily_' + moOrderNo;
			//alert('Over moID = ' + moID);
			$(moID).addClass('dailyRowHover');
		}
	});
	$(".orderBox").mouseout(function(){
		var moOrderDate = $(this).attr('date');
		//alert('moOrderDate = ' + moOrderDate + '; dailySchedDateLoaded = ' + dailySchedDateLoaded);
		if (moOrderDate == dailySchedDateLoaded) {
			var moOrderNo = $(this).attr('orderno');
			var moID = '#dailylist tbody tr#daily_' + moOrderNo;
			//alert('Out moID = ' + moID);
			$(moID).removeClass('dailyRowHover');
		}
	});

	// When hovering over a row in the daily panel, highlight it with light blue background.
	$("#dailylist tbody tr.dailyRow").mouseover(function(){$(this).addClass('dailyRowHover');});
	$("#dailylist tbody tr.dailyRow").mouseout(function(){$(this).removeClass('dailyRowHover')});

	// When clicking on an order in the daily panel, permanently highlight the row using orange background.
	$("#dailylist tbody tr.dailyRow").click(function(){
		$('#dailylist tbody tr.dailyRow').each(function(){
			$(this).removeClass('dailyRowSelected');
		});
		$(this).removeClass('dailyRowHover');
		$(this).addClass('dailyRowSelected');
	});

	// Set highlight on mouseover day column header (.date) and footer (.dailyLink)
	$(".date, .dailyLink").mouseover(function() {
		$(this).addClass("dailyLink_hover");
	}).mouseout(function(){
		$(this).removeClass("dailyLink_hover");
	});

	// Set drag/drop behavior on the weekly schedule columns (.dayColumn)
	$( ".dayColumn" ).droppable({
		hoverClass: "week-day-drop-hover",
		drop: handleOrderDrop
	});

	$("#load_weekly").hide();
	return;
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

	var caption = 'Daily Orders for ' + daily_caption_date;
	$("#dailyCaption").html(caption);

	var dailyTotalQty = dayTotals[dailySchedDateLoaded];
	var dailyTotalQtyText = 'Total quantity for day = ' + dailyTotalQty;
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
				'<td class="center ' + itemRescheduleClass + '" >' + itemno + '</td>' +
				'<td class="left">' + itemdesc + '</td>' +
				'<td class="right"><span id="qtyTD_' + orderNum + '">' + qtyInputTag + '</span></td>' +
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
	if (hasAnyFirm) {
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

function dataChangeUpdates () {
	// Perform various housekeeping functions when any data is changed.

	// Refresh the display
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

function handleOrderDrop( event, ui ) {
	var toDate = $(this).attr('id');

//	var attrStr = '';
//	for (attr in ui.draggable.attr) {
//		attrStr += attr + ' = ' + ui.draggable[attr] + '\n\n';
//	}
//	alert(attrStr);

	var draggedId = ui.draggable.attr('id');
	if (draggedId > 0) {
		// Dragging from Planned Orders to Weekly Schedule
		movePlannedToWeekly(draggedId, toDate);
		return;
	}

	if (draggedId.indexOf("daily") >= 0) {
		// Dragging from Daily Schedule to another day
		var fromDate = dailySchedDateLoaded;
		var draggedOrderNum = $('#'+draggedId).attr('roworderno');
	} else {
		// Dragging from weekly to weekly
		var fromDate = ui.draggable.attr('date');
		var draggedOrderNum = ui.draggable.attr('orderno');
	}

	var toDate = $(this).attr('id');
	//alert('In handleOrderDrop() - id = ' + draggedId + '; draggedOrd# = ' + draggedOrderNum);
	if (fromDate != toDate) { // Prevent dropping on same day
		moveOrder(draggedOrderNum, fromDate, toDate);
	}
}

function getHighestSeqNo( date ) {
	var highestSeqNo = 0;
	for (var order in objWeekly[date]) {
		if (objWeekly[date][order]['seqno'] > highestSeqNo ) {
			highestSeqNo = objWeekly[date][order]['seqno'];
		}
	}
	return highestSeqNo;
}

function movePlannedToWeekly( draggedId, toDate ) {
	var draggedID = '#'+draggedId;
	seqNo = parseInt(getHighestSeqNo( toDate )) + 10;

	var itemNo = $(draggedID + ' td[aria-describedby="plannedList_ITEM_NUMBER"]').html();
	var itemDesc = $(draggedID + ' td[aria-describedby="plannedList_ITEM_DESC"]').html();
	var quantity = $(draggedID + ' td[aria-describedby="plannedList_PLAN_QTY"]').html();
	var origDate = $(draggedID + ' td[aria-describedby="plannedList_DUE_DATE"]').html();
	var hours = $(draggedID + ' td[aria-describedby="plannedList_HOURS"]').html();
	var firmOrderNo = 'Firm' + ++plannedOrderNum;

	var altRoutingsURL = 'getOrderAltRoutings.php?itemNo=' + itemNo + '&facility=' + reqFacility + '&work_ctr=' + reqWorkCtr;
	$.get(altRoutingsURL, function(respAltRoutings) {
		var firmJSON = '{' +
				'"orderno" : "' + firmOrderNo + '",' +
				'"qty" : "' + quantity + '",' +
				'"hours" : "' + hours + '",' +
				'"seqno" : "' + seqNo + '",' +
				'"type" : "F",' +
				'"hoursAlpha" : " @ ' + hours + ' Hrs",' +
				'"itemno" : "' + itemNo + '",' +
				'"itemdesc" : "' + itemDesc + '",' +
				'"duedate" : "' + toDate + '",' +
				'"dateYMD" : "' + toDate + '",' +
				'"originalDateYMD" : "' + origDate + '", ' +
				'"lotSize" : "' + respAltRoutings.lotSize + '", ' +
				'"incrOrderQty" : "' + respAltRoutings.incrOrderQty + '", ' +
				'"altRoutes" : ' + JSON.stringify(respAltRoutings.altRoutes) + ', ' +
				'"hasAltRoutes" : ' +  respAltRoutings.hasAltRoutes + ', ' +
				'"route" : "' + reqWorkCtr + '" ' +
		'}';
		//alert(firmJSON);

		objWeekly[toDate][firmOrderNo] = JSON.parse(firmJSON);

		$(draggedID).remove();
		dailySchedDateLoaded = toDate;

	   	dataChangeUpdates();
	}, "json");

}

/*
*/
function moveOrder(draggedOrderNum, fromDate, toDate) {
	//alert('moveOrder("' + draggedOrderNum + '", "' + fromDate + '", "' + toDate + '") ');

	// Copy the order object from old date to new date.
	var orderJSON = JSON.stringify(objWeekly[fromDate][draggedOrderNum]);
	objWeekly[toDate][draggedOrderNum] = JSON.parse(orderJSON);
	// Change the date field values in the Order object just copied
	objWeekly[toDate][draggedOrderNum]['dateYMD'] = toDate;
	toDateLong = $.datepicker.formatDate('M dd, yy', Date.parse(toDate)); // format long date string
	objWeekly[toDate][draggedOrderNum]['duedate'] = toDateLong;

	// Remove the order from the original date object
	delete objWeekly[fromDate][draggedOrderNum];

   	dataChangeUpdates();
}


function setWeeklyHeight( ) {
	var highestDay = 0;
	for (var oDate in objWeekly) {
		var orders = objWeekly[oDate];
		var dayHeight = getDayHeight(orders);
		if (dayHeight > highestDay) {
			highestDay = dayHeight;
		}
	}
	var divHeight = $("#weekly").height();
	//alert('divHeight = ' + divHeight + '; day height = ' + highestDay + '; capacity = ' + wcCapacity*hoursToPixelMult);

	if (highestDay > divHeight) {
		highestDay += 5;
		weeklyHeight = highestDay; // weeklyHeight is global var to manage changes in daily order qtys
	}

	//alert('weeklyHeight: ' + weeklyHeight);
	if (weeklyHeight < (Math.ceil(wcCapacity*hoursToPixelMult)+basePos)) {
		// Height of weekly grid should be at least enough to accomodate the work center's capacity,
		// even if no orders found for the 12 day period.
		weeklyHeight = (Math.ceil(wcCapacity*hoursToPixelMult)+basePos) + 15;
	}

	weeklyHeight = addDailyLinksToChart(weeklyHeight);

	$("#bottom_panels").height(weeklyHeight);
	$(".dayColumn").height(weeklyHeight);
	$("#weekly").height(weeklyHeight);

	return weeklyHeight;
}

function addDailyLinksToChart(weeklyHeight) {

	var linkBottom = weeklyHeight + 9;
	for (var oDate in objWeekly) {
		if (oDate != 'workcenter') {
			var schedDate = Date.parse(oDate);
			var strDate = (schedDate.toString('MM')) + '/' + schedDate.toString('dd');
			var strFullDate = $.datepicker.formatDate('DD, M d, yy', schedDate);
			var strDayOfWk = $.datepicker.formatDate('D', schedDate);
			var dayTotal = dayTotals[oDate];
			var dailyClass = 'dailyLink';
			if (strDayOfWk == 'Sat' || strDayOfWk == 'Sun') dailyClass += ' weekendColHdr';
			var dateDiv = '<div class="dailyLink" style="bottom: ' + linkBottom + 'px;" date="'
				+ oDate + '" title="' + strFullDate + '\nTotal: ' + dayTotal + '\nClick to load in daily schedule.">' + strDayOfWk + '<br/>' + strDate
				+ '</div>';
			dayDiv = "#"+oDate;
			$(dayDiv).prepend(dateDiv);
		}
	}

	$("div.dailyLink, div.date").click(function() {
		dailySchedDateLoaded = $(this).attr('date');
		buildWeeklyChart();
	});

	var dateLoaded = "#"+dailySchedDateLoaded;
	$(dateLoaded).addClass('dateLoaded');

	return linkBottom + basePos + 15;

}

function getDayHeight( orders ) {
	var bottom = basePos;
	for (var orderNum in orders) {
		qty = orders[orderNum].qty;
		//alert("Order#: " + orderno + "\nQty: " + qty + "\nHeight: " + height + "\nbottom: " + bottom);

		// Get hours for this order and add them to the total hours for the day
		orderHours = parseFloat(orders[orderNum].hours);

		// var height = Math.round(qty / 100);
		var height = Math.round(orderHours * hoursToPixelMult);

		bottom = bottom + height;
	}
	return bottom;
}

function addDayToWeek( day, orders, oDate ) {

	var schedDate = Date.parse(oDate);
	var strDate = (schedDate.toString('MM')) + '/' + schedDate.toString('dd');
	var strDayOfWk = $.datepicker.formatDate('D', schedDate);
	var columnClass = 'dayColumn';
	if (strDayOfWk == 'Sat' || strDayOfWk == 'Sun') columnClass += ' weekendCol';
	var dayDivHTML = '<div id="' + oDate + '" class="' + columnClass + '"></div>';
	$('#weekly').append(dayDivHTML);
	var dayDiv = "#"+oDate;

	var dateDiv = '<div class="dailyLink" style="bottom: 0;" date="' + oDate
		+ '" title="Click to load daily schedule">' + strDate + '</div>';
	$(dayDiv).prepend(dateDiv);

	var bottom = basePos;
	var daysHours = 0.0;
	overCapacity = false;

	// Put daily orders into an array that we can sort by seqno
	var arrDailyOrders = new Array();
	var ordCnt = 0;
	for (var orderNum in orders) {
		arrDailyOrders[ordCnt++] = orders[orderNum];
	}
	arrDailyOrders.sortByProp('seqno');

	dayTotal = 0;

	for (var ordIdx = 0; ordIdx < ordCnt; ordIdx++) {
//	for (var orderNum in orders) {

		var orderType = arrDailyOrders[ordIdx].type;

		// If order is currently in process changing from firm to shop, set type to "FS"
		var inProcFlag = arrDailyOrders[ordIdx].inProcFlag;
		if (inProcFlag == 'Y') {
			orderType = 'FS';
		}

		// Skip order if type is not Shop or Firm or FS
		if (orderType != 'S' && orderType != 'F' && orderType != 'FS') {
			continue;
		}

		var route = arrDailyOrders[ordIdx].route;
		// Skip order if route has been changed to a different work center
		if (route != reqWorkCtr) {
			continue;
		}

		var orderno = arrDailyOrders[ordIdx].orderno;
		var qty = arrDailyOrders[ordIdx].qty;
		dayTotal += parseFloat(qty); // accumulate total quantity for the day

		// Get hours for this order and add them to the total hours for the day
		var orderHours = parseFloat(arrDailyOrders[ordIdx].hours);
		daysHours += orderHours;

//		var height = Math.round(qty / 100);
		var height = Math.round(orderHours * hoursToPixelMult);
		var orderId = 'ord'+orderno;
		//alert("Order#: " + orderno + "\nQty: " + qty + "\nHeight: " + height + "\nbottom: " + bottom);

		// Char strings '@NL' will be changed to <br> tags when rendering in function simple_tooltip().
		// The title attribute of each order div will be converted into a tooltip on hover.
		var toolTipText =
			'Order#: ' + orderno +
			'@NL' + arrDailyOrders[ordIdx].itemdesc +
			'@NLQuantity: ' + qty + arrDailyOrders[ordIdx].hoursAlpha +
			'@NLDue: ' + arrDailyOrders[ordIdx].duedate;

		var ordReschedule = arrDailyOrders[ordIdx].reschedule;
		if (ordReschedule != '' && ordReschedule != 'undefined')
			toolTipText += '@NLReschedule: ' + ordReschedule;

		var due_resched = '';
		if (ordReschedule != '' && ordReschedule != 'undefined') {
		    var obj_reschedule_date = new Date(arrDailyOrders[ordIdx].reschedule);
		    var obj_due_date = new Date(arrDailyOrders[ordIdx].duedate);
		    if (obj_reschedule_date < obj_due_date) due_resched = '-expedite';
		    if (obj_reschedule_date > obj_due_date) due_resched = '-deexpedite';
		}

		if (daysHours > wcCapacity) {
			overCapacity = true;
		}

		var boxClass = ' ';
		switch (orderType) {
		case 'S':
			boxClass += 'shop';
			break;
		case 'F':
			boxClass += 'firm';
			break;
		case 'FS':
			boxClass += 'firmToShop';
			break;
		default:
			break;
		}

		if (due_resched != '') {
			boxClass += boxClass + due_resched;
		}


//		.firm { background-color: green; }
//		.shop { background-color: lightgreen; }
//
//		.firm-expedite { background-color: #DB0704; }
//		.shop-expedite { background-color: #E58080; }
//
//		.firm-deexpedite { background-color: #FDCF0B; }
//		.shop-deexpedite { background-color: #FFFDA0; }


//		if (ordReschedule != '') {
//			boxClass += 'resched-';
//		}
//
//		if (overCapacity) {
//			boxClass += 'over-cap';
//		} else {
//			boxClass += 'under-cap';
//		}

		var orderDiv = '<div class="orderBox ' + boxClass +
							'" title="' + toolTipText +
							'" id="' + orderId +
							'" style="height: ' + height + 'px; ' +
								'position:absolute; ' +
								'bottom: ' + bottom + 'px; ' +
							'" date="' + arrDailyOrders[ordIdx].dateYMD +
							'" orderno="' + orderno + '">' +
						'</div>';
		$(dayDiv).prepend(orderDiv);

		// Make each order box draggable (if not in process firm -> shop)
		if (orderType != 'FS') {
			$( "#"+orderId ).draggable({
				snap: ".dayColumn",
				snapTolerance: 10,
				helper: 'clone',
				containment: "#weekly",
				opacity: 0.35
			});
		}

		// Add current box height to bottom position for next order
		bottom = bottom + height;
	}

	// Store the daily totals in dayTotals object
	dayTotals[oDate] = dayTotal;

	//if (!overCapacity) alert(strDate + ": hrs = " + daysHours + "; capacity = " + wcCapacity);

	// Show horizontal line indicating max daily capacity
//	var capacityHR = '<hr width="100%" style="height: 1px; border: 0; color: #f00; background-color: #f00; ' +
//			'position:absolute; bottom: ' + (Math.ceil(wcCapacity*hoursToPixelMult)+basePos) + 'px;" />';

	capacityHR = getShiftHR( wcCapacity, 'red' );
	$(dayDiv).append(capacityHR);

	if (wcNumberOfShifts == 3) {
		hr1stShift = getShiftHR( wcHrsPerShift, 'orange' );
		$(dayDiv).append(hr1stShift);
		hr2ndShift = getShiftHR( 2*wcHrsPerShift, 'orange' );
		$(dayDiv).append(hr2ndShift);
	}

	if (wcNumberOfShifts == 2) {
		hr1stShift = getShiftHR( wcHrsPerShift, 'orange' );
		$(dayDiv).append(hr1stShift);
	}

	return bottom;
}

function getShiftHR( hours, color ) {
	var hrTag = '<hr width="100%" style="height: 1px; border: 0; color: ' + color
				+ '; background-color: ' + color + '; '
				+ 'position:absolute; bottom: ' + (Math.ceil(hours * hoursToPixelMult) + basePos - 5) + 'px;" />';
	return hrTag;
}