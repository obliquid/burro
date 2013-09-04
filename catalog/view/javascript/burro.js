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

function renewHosting(date_start,date_end,server,order_hosting_id,product_id,domain,mailbox,registrant) { 
	//disable interface
	$('#renewButton'+order_hosting_id).hide();
	$('#renewLoader'+order_hosting_id).show();
	
	var quantity = 1;
	//console.log("provo a chiamare route=checkout/cart/add");
	$.ajax({
		url: 'index.php?route=checkout/cart/add',
		type: 'post',
		data: 'product_id=' + product_id + '&quantity=' + quantity + '&is_hosting=yes&hosting_renew_order_hosting_id=' + order_hosting_id,
		dataType: 'json', 
		success: function(json) {
			if (json['success']) {
				//console.log("CE L'HO FATTA!");
				//console.log("provo a chiamare route=burro/hosting/add");
				$.ajax({
					url: 'index.php?route=burro/hosting/add',
					type: 'post',
					data: { 
						'product_id' : product_id, 
						'is_hosting' : 'yes', 
						'hosting_domain_selected' : domain, 
						'hosting_mailbox_selected' : mailbox,
						'hosting_renew_order_hosting_id' : order_hosting_id,
						'hosting_renew_date_start' : date_start, 
						'hosting_renew_date_end' : date_end,
						'hosting_renew_server' : server,
						'hosting_registrant' : registrant
					},
					dataType: 'json',
					success: function(jsonHosting) {
						if (jsonHosting['error']) {
							alert("error storing hosting to sessions");
						} 
						if (jsonHosting['success']) {
							//console.log("CE L'HO FATTA ANCHE CON BURRO!");
							$('#renewLoader'+order_hosting_id).hide();
							//$('#update').submit(); //update cart
							alert("product added to cart!");
							window.location.href=window.location.href;
						}
					}
				});
			}
		}
	});
	
}
