$(document).ready(function() {
	
	
});

function sanitizeDomain(domain,trim) { 
	if ( !trim ) trim = false;
	//console.log("sanitizeDomain");
	var allowed = [
		"a",
		"b",
		"c",
		"d",
		"e",
		"f",
		"g",
		"h",
		"i",
		"j",
		"k",
		"l",
		"m",
		"n",
		"o",
		"p",
		"q",
		"r",
		"s",
		"t",
		"u",
		"v",
		"w",
		"x",
		"y",
		"z",
		"0",
		"1",
		"2",
		"3",
		"4",
		"5",
		"6",
		"7",
		"8",
		"9",
		"-"
	];
	var sanitizedDomain = "";
	domain = domain.toLowerCase();
	for ( var i = 0; i < domain.length; i++ ) {
		if ( allowed.indexOf( domain.charAt(i) ) > -1 ) {
			sanitizedDomain = sanitizedDomain + domain.charAt(i);
		}
	}
	if ( trim ) {
		while ( sanitizedDomain.charAt( 0 ) == '-' ) { sanitizedDomain = sanitizedDomain.substr(1); }
		while ( sanitizedDomain.charAt( sanitizedDomain.length - 1 ) == '-' ) { sanitizedDomain = sanitizedDomain.substr(0,sanitizedDomain.length - 1); } 
	}
	if ( sanitizedDomain.length > 63 ) {
		sanitizedDomain = sanitizedDomain.substr(0,63);
	} 

	return sanitizedDomain;
	
} 

function validateEmail(email) { 
  // http://stackoverflow.com/a/46181/11236
  
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}
