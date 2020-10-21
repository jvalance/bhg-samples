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


function weeklyCallBack( response ) {
	//alert('in weeklyCallBack()');
	objWeekly = response;
	dailySchedDateLoaded = $("#weekly_current_date").val();
	buildWeeklyChart();
}

function buildWeeklyChart() {
	$('#dragDaily').remove(); // delete the helper div for dragging from the daily list

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
				'<span style="font-size: 14px; color: white; font-family: verdana; font-weight: normal">Weekly Schedule-Work Ctr#: <b>'
				+ wcNum
//				+ ' - ' + wcDescription
				+ '</b>; &nbsp;&nbsp;Capacity: <b>' + wcCapacity
				+ ' hrs</b>; &nbsp;&nbsp;No. of shifts: <b>' + wcNumberOfShifts
				+ '</b>; &nbsp;&nbsp;Hrs/shift: <b>' + wcHrsPerShift + '</b></span>';
			wcDetailText +=
				'<button id="weekly-legend-button" type="button" onclick="$( \'#weekly-legend\' ).dialog( \'open\' );" ' +
					' class="green_button">Color Legend</button>';
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
		$(this).addClass('orderBoxHover');
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
		$(this).removeClass('orderBoxHover');
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

	if (boolChangedData) $("#toShopButton").hide();

	return;
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

		var ordReschedule = arrDailyOrders[ordIdx].reschedule;

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
		var oTypeText = ' ';

		switch (orderType) {
		case 'S':
			boxClass += 'shop';
			oTypeText = 'Shop Order' + due_resched;
			break;
		case 'F':
			boxClass += 'firm';
			oTypeText = 'Firm Planned Order' + due_resched;
			break;
		case 'FS':
			boxClass += 'firmToShop';
			oTypeText = 'Converting Firm to Shop' + due_resched;
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


// <span class="' + boxClass + '">  '</span>' +
		// Char strings '@NL' will be changed to <br> tags when rendering in function simple_tooltip().
		// The title attribute of each order div will be converted into a tooltip on hover.
		var toolTipText =
			'Order#: ' + orderno +
			'@NL' + oTypeText +
			'@NL' + arrDailyOrders[ordIdx].itemdesc +
			'@NLQuantity: ' + qty + arrDailyOrders[ordIdx].hoursAlpha +
			'@NLDue: ' + arrDailyOrders[ordIdx].duedate;

		if (ordReschedule != '' && ordReschedule != 'undefined')
			toolTipText += '@NLReschedule: ' + ordReschedule;

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

