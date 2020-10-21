/**
 * Custom modulus function - to get around bugs discovered when using javascript built-in modulus operator (%)
 * @param a Numerator
 * @param n Denominator
 * @return {float} Remainder after dividing a by n. 
 */
function mod(a, n) {
    return a - (n * Math.floor(a/n));
}

/**
 * Taken from phpjs, javascript function to simulate PHP's strip_tags function.
   http://kevin.vanzonneveld.net
   http://phpjs.org/
 * @param {string} input containing HTML tags to be stripped
 * @param {string} allowed of tags that will not be stripped (see examples, below)
 * @return {string} The input string with all HTML tags stripped (except the allowed ones specified)
 * @example <pre>
 * 	strip_tags_js('<p>Kevin</p> <br /><b>van</b> <i>Zonneveld</i>', '<i><b>');
 *  returns: 'Kevin <b>van</b> <i>Zonneveld</i>'
 * </pre>
 */
function strip_tags_js(input, allowed) {
	// making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
	allowed = (((allowed || "") + "")
				.toLowerCase()
				.match(/<[a-z][a-z0-9]*>/g) || [])
				.join(''); 
						
	var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi, 
		commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
	return input.replace(commentsAndPhpTags, '').replace(
			tags,
			function($0, $1) {
				return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0
						: '';
			});
	/* Examples of strip_tags_js:
	example 1: strip_tags_js('<p>Kevin</p> <br /><b>van</b> <i>Zonneveld</i>', '<i><b>');
	returns 1: 'Kevin <b>van</b> <i>Zonneveld</i>'
	
	example 2: strip_tags_js('<p>Kevin <img src="someimage.png" onmouseover="someFunction()">van <i>Zonneveld</i></p>', '<p>');
	returns 2: '<p>Kevin van Zonneveld</p>'
		
	example 3: strip_tags_js("<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>", "<a>");
	returns 3: '<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>'
		
	example 4: strip_tags_js('1 < 5 5 > 1');
	returns 4: '1 < 5 5 > 1'
		
	example 5: strip_tags_js('1 <br/> 1');
	returns 5: '1  1'
		
	example 6: strip_tags_js('1 <br/> 1', '<br>');
	returns 6: '1  1'
	
	example 7: strip_tags_js('1 <br/> 1', '<br><br/>');
	returns 7: '1 <br/> 1'
	*/
}