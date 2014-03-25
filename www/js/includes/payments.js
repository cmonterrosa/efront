function createCouponCode(el) {
	Element.extend(el);
	parameters = {'create_coupon':1, method: 'get'};
	var url    = location.toString();
	ajaxRequest(el, url, parameters, onCreateCouponCode);		
}
function onCreateCouponCode(el, response) {	
	$('coupon_code').value = response.evalJSON(true).coupon;
}
function editBalance(el, login) {
	Element.extend(el);
	parameters = {balance: el.previous().value, login:login, method: 'get'};
	var url    = location.toString();
	ajaxRequest(el, url, parameters, onEditBalance);	
}
function onEditBalance(el, response) {
	el.up().hide();
	if (response.isJSON()) {
		el.up().next().down().update(response.evalJSON(true).price);
	}
	el.up().next().show();
}

if ($('autocomplete_users')) { 
	new Ajax.Autocompleter("autocomplete", 
						   "autocomplete_users", 
						   "ask.php?ask_type=users", {paramName: "preffix", 
													indicator : "busy"}); 
}
