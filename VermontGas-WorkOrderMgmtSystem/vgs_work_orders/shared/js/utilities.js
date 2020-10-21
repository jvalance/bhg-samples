/**
 * String Ends With 
 * Usage: if (String.endswith( suffix )) { ... } 
 */
String.prototype.endsWith = function(suffix) {
    return this.indexOf(suffix, this.length - suffix.length) !== -1;
};

function openPopUp(url, name, opts) {
	var jsOpen;
	if (opts) {
		jsOpen = window.open(url,name, opts); 
	} else {
		jsOpen = window.open(url,name); 
	}
	if (jsOpen) jsOpen.focus();
	return false;
}

/**
 * This will submit a form in a new pop-up window; handy for filtered listing screens which
 * might create a report or download that should use the same filters as the listing screen.
 *   
 * @param formObj A reference to the form object to be submitted.
 * @param action The URL to submit the form to.
 * @param windowName The name to give the new window. 
 * @param options Comma separated list of the attributes with which to open the window; used
 * 					on the js window.open() method.
 */
function submitFormToPopUp(formObj, action, windowName, options) {
	var winOpts = '';

	// If window options are not passed, set default values 
	if (options) {
		winOpts = options; 
	} else {
		winOpts = 'width=700,height=500,status=yes,resizable=yes,scrollbars=yes';
	}

	// Save the current attributes of the form object, to be restored after submit.
	var saveAction = formObj.action;
	var saveMethod = formObj.method;
	var saveTarget = formObj.target;
	var saveOnSubmit = formObj.onSubmit;

	// Set the form attributes for submitting as pop-up
	formObj.action = action;
	formObj.method = 'get';
	formObj.target = 'new';
	formObj.onSubmit = "window.open('', '" + windowName + "', '" + winOpts + "')";

	// Submit the form with target = popup
	formObj.submit();
	
	// Restore the original form attributes
	formObj.action = saveAction;
	formObj.method = saveMethod;
	formObj.target = saveTarget;
	formObj.onSubmit = saveOnSubmit;
}

// *******
// Ajax code to change dropdown lists on page
// *******

//Make ajax request to server using method="post"
function getDropDownList( ddCode, selectList ) {
	var script = 'cvGetDropDownList_Ajax.php';
	var postData = { ddCode: ddCode, select: selectList };
	//	alert(postData.ddCode + ', ' + postData.select);
	$.post(script, postData, callbackGetDropDownList, 'json');
	return false;
}
 
//This is called when Ajax response is received from server 
function callbackGetDropDownList( returnVals ) {
	//	alert(returnVals.selectList);
	
	var options = '';
	var selectID = '';
	if (returnVals.error) {
		//	alert('Ajax reponse was: ' + returnVals.error);
		return false;
	} else {
		for (key in returnVals) {
			// PHP should add an entry in the returned array named 'selectList'.
			// This is the ID of the <select> list to be updated with the list of returned options.
			if (key == 'selectList') selectID = returnVals[key];
			else {
				options += '<option value="' + key + '">' + returnVals[key] + '</option>\n';
			}
		}
		var selector = 'select#' + selectID; 
		$(selector).html(options);
	}
	//	alert(options);
}