function updateCoupon(el) {
	var url    = location.toString();
	url = url.split("#")[0];
	parameters = {coupon:$('coupon_bogus').value, ajax:'coupon', method: 'get'};
	ajaxRequest(el, url, parameters, onUpdateCoupon);					
}
function onUpdateCoupon(el, response) {
	try {
		$('coupon_code').value = $('coupon_bogus').value;
		$('total_price_string').update(response.evalJSON(true).price_string);	
		if ($('paypal_form')) { 
			if ($('paypal_form')['amount']) {
				$('paypal_form')['amount'].value = response.evalJSON(true).price;
			} else if ($('paypal_form')['a3']) {
				$('paypal_form')['a3'].value = response.evalJSON(true).price;
			}
			if ($('paypal_form')['item_number']) {
				$('paypal_form')['item_number'].value += response.evalJSON(true).id+',';
				//$('paypal_form')['item_number'].value = $('paypal_form')['item_number'].value.slice(0, -1); //Remove trailing ','
			}
		}
		if ($('coupon_bogus').value) {
			$('enter_coupon_link').update(translations['_COUPON'] + ': ' + $('coupon_bogus').value);
		} else {
			$('enter_coupon_link').update(translations['_CLICKTOENTERDISCOUNTCOUPON']);
		}
		if (response.evalJSON(true).price == 0) {
			$('free_registration_hidden').show();
			if ($('submit_checkout_paypal')) {
				$('submit_checkout_paypal').hide();
			} else if ($('submit_enroll')) {
				$('submit_enroll').hide();
			}
		}
		eF_js_showDivPopup();
	} catch (e) {}
}

function addToCart(el, id, type) {
	var url    = location.toString();
	url = url.split("#")[0];
	parameters = {fct:'addToCart', id:id, ajax:'cart', type:type, method: 'get'};
	if (type == 'credit') {
		ajaxRequest(el, url, parameters, function () {location=redirectLocation;}, false, false);		
	} else {
		ajaxRequest(el, url, parameters, onCartOperation, false, false);
	}
}		
function removeFromCart(el, id, type) {
	var url    = location.toString();
	url = url.split("#")[0];
	parameters = {fct:'removeFromCart', ajax:'cart', id:id, type:type, method: 'get'};
	ajaxRequest(el, url, parameters, onCartOperation);				
}		
//if ($('buy_credit_value').value) ajaxRequest(this, '{$smarty.server.PHP_SELF}?ctg=lessons&catalog=1&fct=addToCart&type=credit&id='+$('buy_credit_value').value, {ldelim}ajax:1{rdelim})
function removeAllFromCart(el) {
	var url    = location.toString();
	url = url.split("#")[0];
	parameters = {fct:'removeAllFromCart', ajax:'cart', method: 'get'};
	ajaxRequest(el, url, parameters, onCartOperation);				
}
function onRemoveAllFromCart(el, response) {
	$('cart').innerHTML = response;
}
function onCartOperation(el, response) {
	var re2         = new RegExp("<!--ajax:cart-->((.*[\n])*)<!--\/ajax:cart-->");	//Does not work with smarty {strip} tags!
    var tableText   = re2.exec(response);

	if (!tableText) {
        var re      = new RegExp("<!--ajax:cart-->((.*[\r\n\u2028\u2029])*)<!--\/ajax:cart-->");	//Does not work with smarty {strip} tags!
        tableText   = re.exec(response);
	}

    $('cart').innerHTML = tableText[1];
	if ($('cart').select('.cartElement').length == 0) {
		$('cart').ancestors().each(function(s) {if (s.hasClassName('block')) s.hide();})
	} else {
		$('cart').ancestors().each(function(s) {if (s.hasClassName('block')) s.show();})
	}
	//$('cart').innerHTML = response;
}

function paypalSubmit() {
	$('checkout_form').request();
	return false;
}



//Direction tree functions
function showAll() {
	$$('tr').each(function (tr) 	  {tr.id.match(/subtree/) ? tr.show() : null;});
	$$('table').each(function (table) {table.id.match(/direction_/) ? table.show() : null;});
	$$('img').each(function (img) {
		if (img.id.match('subtree_img') && !img.hasClassName('visible')) {
			setImageSrc(img, 16, 'navigate_up');
			img.addClassName('visible');
		}
	});
	$('catalog_hide_all').show();
	$('catalog_show_all').hide();
	setCookie('collapse_catalog', 0);
	setCookie('hidden_catalog_entries', '', -1);
}
function hideAll() {
	$$('tr').each(function (tr) 	  {tr.id.match(/subtree/) ? tr.hide() : null;});
	$$('img').each(function (img) {
		if (img.id.match('subtree_img') && img.hasClassName('visible')) {
			img.removeClassName('visible');
			setImageSrc(img, 16, 'navigate_down');
		}
	});
	$('catalog_hide_all').hide();
	$('catalog_show_all').show();
	setCookie('collapse_catalog', 1);
	setCookie('hidden_catalog_entries', '', -1);
}

function showHideDirections(el, ids, id, mode) {
	if (readCookie('hidden_catalog_entries')) {
		var hidden_catalog_entries = getCookie('hidden_catalog_entries').evalJSON(true);
	} else {
		var hidden_catalog_entries = new Array();
	}
	
	Element.extend(el);		//IE intialization
	if (mode == 'show') {
		el.up().up().nextSiblings().each(function(s) {s.show();});
		if (ids) {
			ids.split(',').each(function (s) { showHideDirections($('subtree_img'+id), $('subtree_children_'+s) ? $('subtree_children_'+s).innerHTML : '', s, 'show');});
			ids.split(',').each(function (s) { obj = $('direction_'+s); obj ? obj.show() : '';});
		}
		setImageSrc(el, 16, 'navigate_up');
		$('subtree_img'+id) ? $('subtree_img'+id).addClassName('visible') : '';
		var idx = hidden_catalog_entries.indexOf(id);
		if(idx!=-1) hidden_catalog_entries.splice(idx, 1);
	} else {
		el.up().up().nextSiblings().each(function(s) {s.hide();});
		if (ids) {
			ids.split(',').each(function (s) { showHideDirections($('subtree_img'+id), $('subtree_children_'+s) ? $('subtree_children_'+s).innerHTML : '', s, 'hide') });
			ids.split(',').each(function (s) { obj = $('direction_'+s); obj ? obj.hide() : '';});
		}
		setImageSrc(el, 16, 'navigate_down.png');
		$('subtree_img'+id) ? $('subtree_img'+id).removeClassName('visible') : '';
		hidden_catalog_entries.push(id);
	}
	setCookie('hidden_catalog_entries', Object.toJSON(hidden_catalog_entries.uniq()));
}
function showHideCourses(el, course) {
	Element.extend(el);
	if (el.hasClassName('visible')) {
		if (course) {
			course.hide();
		}
		setImageSrc(el, 16, 'navigate_down.png');
		el.removeClassName('visible');
	} else {
		if (course) {
			course.show();
		}
		setImageSrc(el, 16, 'navigate_up');
		el.addClassName('visible');
	}
}

function updateInformation2(el, id, type, from_course) {
	Element.extend(el);
	
	var url = 'ask_information.php';
	parameters = {method: 'get'};
	type == 'lesson' ? Object.extend(parameters, {lessons_ID:id}) : Object.extend(parameters, {courses_ID:id});
	
	ajaxRequest(el, url, parameters, onUpdateInformation2);					

}
function onUpdateInformation2(el, response) {
	alert(response);
}

function filterTree(el, url) {
	Element.extend(el);
	url.match(/\?/) ? url = url+'&' : url = url + '?';
	new Ajax.Request(url+'filter='+el.value+'&ajax=1', {
		method:'get',
		asynchronous:true,
		onSuccess: function (transport) {
			$('directions_tree').innerHTML = transport.responseText;
			showAll();
		}
	});
}

if (getCookie('hidden_catalog_entries') && getCookie('hidden_catalog_entries').evalJSON(true)) {
	var ids = getCookie('hidden_catalog_entries').evalJSON(true);
	ids.each(function (s) {showHideDirections($('subtree_img'+s), $('subtree_children_'+s) ? $('subtree_children_'+s).innerHTML : '', s, 'hide')})
}

document.observe("dom:loaded", function() {
	//console.log($('cart').select('.cartElement').length)
	if ($('cart') && $('cart').select('.cartElement').length == 0) {
		$('cart').ancestors().each(function(s) {if (s.hasClassName('block')) s.hide();})
	}
});
