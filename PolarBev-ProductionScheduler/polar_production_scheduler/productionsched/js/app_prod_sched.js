
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


function doSave() {

	// Convert the weekly schedule to JSON for submission to server
	document.prodSchedForm.jsonWeekly.value = JSON.stringify(objWeekly);
	$("#action").val('update');

	// turn off flag which triggers confirmation pop-up to leave page without saving changes
	boolChangedData = false;

	// Save date for daily schedule in hidden input field, for reload of screen
	$("#weekly_current_date").val(dailySchedDateLoaded);

	// Update lock on this work center.
	var script = 'updateUserLock.php';
	var data = {'facility' : reqFacility,
				'work_ctr' : reqWorkCtr
	};

	$.get(script, data, pageReloadCallBack);
	return false;
}

function doReload() {
	$("#action").val('reload');

	// Save date for daily schedule in hidden input field, for reload of screen
	$("#weekly_current_date").val(dailySchedDateLoaded);

	// Update lock on this work center.
	var script = 'updateUserLock.php';
	var data = {'facility' : reqFacility,
				'work_ctr' : reqWorkCtr
	};

	$.get(script, data, pageReloadCallBack);

	return false;
}

function doFirmToShop() {

	// Convert the daily schedule of firm orders to table
	// table will be sent to MRP system and converted to shop orders
	document.prodSchedForm.jsonDaily.value = JSON.stringify(objWeekly[dailySchedDateLoaded]);
	$("#action").val('firmToShop');

	// turn off flag which triggers confirmation pop-up to leave page without saving changes
	boolChangedData = false;

	// Save date for daily schedule in hidden input field, for reload of screen
	$("#weekly_current_date").val(dailySchedDateLoaded);

	// Update lock on this work center.
	var script = 'updateUserLock.php';
	var data = {'facility' : reqFacility,
				'work_ctr' : reqWorkCtr
	};


	$.get(script, data, pageReloadCallBack);

	return false;
}

function doStartOver() {

	$("#action").val('startOver');

	// Save date for daily schedule in hidden input field, for reload of screen
	$("#weekly_current_date").val(dailySchedDateLoaded);

	// Delete lock on this work center.
	var script = 'deleteWorkCtrLock.php';
	var data = {
		'facility' : reqFacility,
		'work_ctr' : reqWorkCtr
	};

	$.get(script, data, startOverCallBack);

	return false;
}

function startOverCallBack () {
	boolCloseSession = true;

	document.prodSchedForm.action = 'prodSchedSelect.php';
	document.prodSchedForm.submit();
}

function pageReloadCallBack () {
	boolCloseSession = true;

	document.prodSchedForm.action = 'prodSchedMaint.php';
	document.prodSchedForm.submit();
}


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
