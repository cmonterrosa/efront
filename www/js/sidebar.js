var sidebar_width = 18;
function initSidebar(s_login)
{
	try {
		var is_ie;
		var detect = navigator.userAgent.toLowerCase();
		detect.indexOf("msie") > 0 ? is_ie = "true" : is_ie = "false";
		createCookie(s_login+'_sidebarMode','automatic',30);
		var value = readCookie(s_login+'_sidebar');

		if(value == 'hidden')
		{

			//top.document.getElementById('framesetId').cols = "50, *";
			var b_version=navigator.appVersion;
			var re  = new RegExp("MSIE 10\d*");
		    if (re.exec(b_version) != 'MSIE 10') {
		    	top.document.getElementById('framesetId').cols = ""+sidebar_width+", *";
		    }
			if (top.sideframe) {
				top.sideframe.document.body.style.paddingLeft = "20px";

				if (top.sideframe.document.getElementById('toggleSidebarImage').src) {
					top.sideframe.document.getElementById('toggleSidebarImage').src = 'themes/default/images/others/transparent.gif';
					top.sideframe.document.getElementById('toggleSidebarImage').addClassName('sprite16').addClassName('sprite16-navigate_right');
				}
				if (is_ie == "true") {
					top.sideframe.document.getElementById('toggleSidebarImage').style.position="absolute";
					top.sideframe.document.getElementById('toggleSidebarImage').style.left = "0px";
					top.sideframe.document.getElementById('toggleSidebarImage').style.top = "4px";          
				}
			}
		}
		else
		{
			var b_version=navigator.appVersion;
			var re  = new RegExp("MSIE 10\d*");
		    if (re.exec(b_version) != 'MSIE 10') {
		    	top.document.getElementById('framesetId').cols = top.global_sideframe_width + ", *";
		    }
			if (top.sideframe) {

				top.sideframe.document.body.style.paddingLeft = "0px";

				if(top.sideframe.document.getElementById('toggleSidebarImage').src) {
					top.sideframe.document.getElementById('toggleSidebarImage').src = 'themes/default/images/others/transparent.gif';
					Element.extend(top.sideframe.document.getElementById('toggleSidebarImage')).addClassName('sprite16').addClassName('sprite16-navigate_left');
				}


				if(is_ie == "true")
				{
					top.sideframe.document.getElementById('toggleSidebarImage').style.position="absolute";
					top.sideframe.document.getElementById('toggleSidebarImage').style.left = (top.global_sideframe_width - 16) + "px";
					top.sideframe.document.getElementById('toggleSidebarImage').style.top = "4px";            
				}
				//top.sideframe.document.getElementById('logoutImage').style.position="absolute";
				//top.sideframe.document.getElementById('logoutImage').style.left = "1000px";
				//top.sideframe.document.getElementById('logoutImage').style.top = "45px";
				//top.sideframe.document.getElementById('mainPageImage').style.position="absolute";
				//top.sideframe.document.getElementById('mainPageImage').style.left = "1000px";
				//top.sideframe.document.getElementById('mainPageImage').style.top = "25px";

			}
		}
	} catch (e) {sidebarExceptionHandler(e, 'initSidebar');}
}
function toggleSidebar(s_login)
{
	try {
		var is_ie;
		var detect = navigator.userAgent.toLowerCase();
		detect.indexOf("msie") > 0 ? is_ie = "true" : is_ie = "false";
		//var value = readCookie('sidebar');
		var value = readCookie(s_login+'_sidebar');

		if(value == 'hidden')
		{
			createCookie(s_login+'_sidebar','visible',30);
			var b_version=navigator.appVersion;
			var re  = new RegExp("MSIE 10\d*");
		    if (re.exec(b_version) != 'MSIE 10') {
		    	top.document.getElementById('framesetId').cols = top.global_sideframe_width + ", *";
		    }
			if (top.sideframe) {
				top.sideframe.document.body.style.paddingLeft = "0px";

				setArrowStatus('down');
				initArrows();
				top.sideframe.document.getElementById('toggleSidebarImage').src = 'themes/default/images/others/transparent.gif';
				top.sideframe.document.getElementById('toggleSidebarImage').addClassName('sprite16').addClassName('sprite16-navigate_left');
				if(is_ie == "true")
				{
					top.sideframe.document.getElementById('toggleSidebarImage').style.position="absolute";
					top.sideframe.document.getElementById('toggleSidebarImage').style.left = (top.global_sideframe_width - 16) + "px";
					top.sideframe.document.getElementById('toggleSidebarImage').style.top = "4px";

				}
				//top.sideframe.document.getElementById('logoutImage').style.position="absolute";
				//top.sideframe.document.getElementById('logoutImage').style.left = "1000px";
				//top.sideframe.document.getElementById('logoutImage').style.top = "45px";
				//top.sideframe.document.getElementById('mainPageImage').style.position="absolute";
				//top.sideframe.document.getElementById('mainPageImage').style.left = "1000px";
				//top.sideframe.document.getElementById('mainPageImage').style.top = "25px";

				//top.sideframe.document.getElementById('toggleSidebarImage').style= "position: absolute; left: 0px";
				//changeImage(top.sideframe.document.getElementById('toggleSidebarImage'));
				//changeImage(top.sideframe.document.getElementById('logoutImage'));
				//changeImage(top.sideframe.document.getElementById('mainPageImage'))

				var menus = top.sideframe.document.getElementById('menu').childElements().length - 1; 
				var i = 2;
				for (i = 2; i <= menus; i++) {
					if (top.sideframe.document.getElementById('menu'+i)) {
						top.sideframe.document.getElementById('menu'+i).style.visibility = "visible";
					}        
				}   
			}
		}
		else
		{

			createCookie(s_login+'_sidebar','hidden',30);
			var b_version=navigator.appVersion;
			var re  = new RegExp("MSIE 10\d*");
			// because of http://stackoverflow.com/questions/12249268/frameset-cols-ie10 (#3373)
		    if (re.exec(b_version) != 'MSIE 10') {
		    	top.document.getElementById('framesetId').cols = ""+sidebar_width+", *";
		    }
			if (top.sideframe) {
				top.sideframe.document.body.style.paddingLeft = "130px";

				top.sideframe.document.getElementById('toggleSidebarImage').src = 'themes/default/images/others/transparent.gif';
				top.sideframe.document.getElementById('toggleSidebarImage').addClassName('sprite16').addClassName('sprite16-navigate_right');

				if(is_ie == "true")
				{
					top.sideframe.document.getElementById('toggleSidebarImage').style.position="absolute";
					top.sideframe.document.getElementById('toggleSidebarImage').style.left = "0px";
					top.sideframe.document.getElementById('toggleSidebarImage').style.top = "4px";
				}
				//top.sideframe.document.getElementById('logoutImage').style.position="absolute";
				//top.sideframe.document.getElementById('logoutImage').style.left = "1px";
				//top.sideframe.document.getElementById('logoutImage').style.top = "45px";
				//top.sideframe.document.getElementById('mainPageImage').style.position="absolute";
				//top.sideframe.document.getElementById('mainPageImage').style.left = "1px";
				//top.sideframe.document.getElementById('mainPageImage').style.top = "25px";

				//top.sideframe.document.getElementById('toggleSidebarImage').style.position = "absolute";position: absolute; left: 0px";
				//changeImage(top.sideframe.document.getElementById('toggleSidebarImage'));
				//changeImage(top.sideframe.document.getElementById('logoutImage'));
				//changeImage(top.sideframe.document.getElementById('mainPageImage'));
				var menus = top.sideframe.document.getElementById('menu').childElements().length - 1; 
				var i = 2;
				for (i = 2; i <= menus; i++) {
					if (top.sideframe.document.getElementById('menu'+i)) {
						top.sideframe.document.getElementById('menu'+i).style.visibility = "hidden";
					}        
				}
			}   

		}
	} catch (e) {sidebarExceptionHandler(e, 'toggleSidebar');}	    
}
function checkToOpenSidebar(s_login)
{
	try {
		var value = readCookie(s_login+'_sidebarMode');

		if(value == 'automatic'){
			toggleSidebar(s_login);
		} 
	} catch (e) {sidebarExceptionHandler(e, 'checkToOpenSidebar');}
}
function show_user_box(user_str,user,send_msg,view_page,user_type,user_time, user_stats, user_profile, logout_user)
{
	try {
		href_str = '<a href = "'+translations['s_type']+'.php?ctg=messages&add=1&recipient='+user+'&popup=1" onclick = "eF_js_showDivPopup(event, \'\', \'\', \'user_table\');eF_js_showDivPopup(event, \'\', 2)" target = "POPUP_FRAME">'+send_msg+"</a>";

		if (translations['s_type'] == "administrator") {
			href_str += '<BR><a href = "'+translations['s_type']+'.php?ctg=statistics&option=user&sel_user='+user+'&popup=1" onclick = "eF_js_showDivPopup(event, \'\', \'\', \'user_table\');eF_js_showDivPopup(event, \'\', 2)" target = "POPUP_FRAME">'+user_stats+"</a>";
			href_str += '<BR><a href = "'+translations['s_type']+'.php?ctg=personal&user='+user+'&op=profile" onclick = "eF_js_showDivPopup(event, \'\', \'\', \'user_table\');">'+user_profile+"</a>";

			if (translations['s_login'] != user) { 
				href_str += '<BR><a href = "javascript:void(0);" onclick = "parameters = {method: \'get\'};ajaxRequest(this, \''+translations['s_type']+'.php?ctg=logout_user&user='+user+'\', parameters, function (el, transport) {eF_js_showDivPopup(event, \'\', \'\', \'user_table\'); if (top.sidebar) {top.sideframe.location.reload();} });">'+logout_user+"</a>";
			}
		}

		if (top.mainframe && top.mainframe.document.getElementById('popup_title')) {
			top.mainframe.document.getElementById('popup_title').innerHTML = user_str;
		}

		if (top.mainframe && top.mainframe.document.getElementById('user_box')) {
			top.mainframe.document.getElementById('user_box').innerHTML=user_time+"<hr class='bluethin'/>"+href_str;
		}
	} catch (e) {sidebarExceptionHandler(e, 'show_user_box');}
}

function getWindowSize() {
	try {	
		var myWidth = 0, myHeight = 0;
		if( typeof( window.innerWidth ) == 'number' ) {
			//Non-IE
			myWidth = window.innerWidth;
			myHeight = window.innerHeight;
		} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
			//IE 6+ in 'standards compliant mode'
			myWidth = document.documentElement.clientWidth;
			myHeight = document.documentElement.clientHeight;
		} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
			//IE 4 compatible
			myWidth = document.body.clientWidth;
			myHeight = document.body.clientHeight;
		}

		return [myWidth, myHeight];
		//window.alert( 'Width = ' + myWidth );
		//window.alert( 'Height = ' + myHeight );
	} catch (e) {sidebarExceptionHandler(e, 'getWindowSize');}
}

//Function to resize the Chat iframe
function resize_iframe()
{
	try {
		if (chatOptionIsEnabled) {
			//fixCurtains();
			if ($('listmenu'+menuCount)) {
				var height= $('listmenu'+menuCount).getHeight();//window.innerWidth;//Firefox
			} else {
				var height = 0;
			}

			var offset_to_subtract = onlyViewChat == 1 ? 50 : 72;
			if (browser == 'IE6' || browser == 'IE7') {
				offset_to_subtract = offset_to_subtract - 2;
			} else if (browser == 'Safari') {
				offset_to_subtract = offset_to_subtract + 4;
			} else if (browser == 'Chrome') {
				offset_to_subtract = offset_to_subtract + 22;
			}

			//resize the iframe according to the size of the
			//window (all these should be on the same line)
			var diff = height-offset_to_subtract;
			if ($('glu')) { 
				if (browser == 'IE6' || browser == 'IE7') {
					$('glu').height = diff;

				} else {
					$('glu').setStyle({'height': diff+'px'});
				}
			}
			
			//$('glu').up().setStyle({height: (height-offset_to_subtract) + 'px'});
			//test.document.getElementById("chat_content").style.height = parseInt(height-offset_to_subtract)+ "px";
			//if (typeof(test) == 'undefined') alert('a');
			if (typeof(test) != 'undefined' && test.document && test.document.getElementById("chat_content")) {
				if (browser == 'IE6' || browser == 'IE7') {
					test.document.getElementById("chat_content").height = diff+ "px";
				} else {
					test.document.getElementById("chat_content").style.height = diff+ "px";
				}   

			}
		}
	} catch (e) {sidebarExceptionHandler(e, 'resize_iframe');}
}

//The following functions are used to highlight the correct menu on page load or refresh

//Function to make a certain menu item appear as activated 
function changeTDcolor(id) {
	try {
		if (!id || typeof(window['active_id']) == "undefined") {
			return false;
		}


		if (active_id != id)
		{
			if(document.getElementById(active_id))
			{
				$(active_id).className = "menuOption";
			}

			if(document.getElementById(active_id+"_a"))
			{
				$(active_id+"_a").className = "menuOption"; //"menuLinkInactive";
			}
			active_id = id;

			
			if(document.getElementById(id)) {
				$(id).className = "selectedTopTitle";// rightAlign";

				if ( $(id).up() && $(id).up().up()) {
					document.move($(id).up().up());
				}
			}
			if(document.getElementById(id+"_a"))
			{

				$(active_id+"_a").className = "selectedTopTitle";
				if ( $(active_id+"_a").up() && $(active_id+"_a").up().up()) {
					document.move($(active_id+"_a").up().up());
				}
			}

		}
	} catch (e) {sidebarExceptionHandler(e, 'changeTDcolor');}
}

function hideLoadingDiv() {
	try {
		if ($('tabmenu')) {
			$('tabmenu').style.visibility = "visible";
		}
		if ($('menu')) {
			$('menu').style.visibility = "visible";
		}

		if ($('utility_images')) {
			$('utility_images').style.visibility = "visible";
		}

		if ($('loading_sidebar')) {
			$('loading_sidebar').setStyle({display:'none'});
		}
	} catch (e) {sidebarExceptionHandler(e, 'hideLoadingDiv');}
}

function initArrows() {
	try {
		var windowSize = getWindowSize();
		var windowHeight = parseInt(windowSize[1]);
		scrollHeight = parseInt(document.documentElement.scrollHeight);
		//alert("windowHeight = "+windowHeight+" scrollHeight = "+scrollHeight);


		//hideLoadingDiv();
	} catch (e) {sidebarExceptionHandler(e, 'initArrows');}
}
window.onresize = resizeFunction;

function setArrowStatus(status) {
	try {
		arrow_status = status;
		initArrows();
	} catch (e) {sidebarExceptionHandler(e, 'setArrowStatus');}
}


function miniFixCurtains() {
	try {
		var windowSize = getWindowSize();
		var windowHeight = parseInt(windowSize[1]);

		var menus = $('menu').childElements().length - 1; // we do not take "logout" into account
		var offset;

		var i = menus;
		for (i = menus; i > 0; i--) {

			offset = windowHeight - $('tabmenu').getHeight() - $('logout').getHeight() -1;
			for (k = 1; k <= i; k++) {
				offset -= ($('tabmenu'+k).getHeight()+1);
			}

			j = i + 1;
			// Check the next menus
			while ( $('menu'+j) ) {
				if ($('menu'+j).status =='down') {
					offset = offset - $('tabmenu'+j).getHeight() - 1;
				}
				j = j + 1;
			}

			if (offset > 0) {
				$('listmenu'+i).setStyle("height: " +(offset+1)+"px;");
			}
		}
	} catch (e) {sidebarExceptionHandler(e, 'miniFixCurtains');}
}

//Function to fix the height of the curtains used to hide underlying menus - the menus must follow an order menu = <menu1,menu2...,menuN,logout>
function fixCurtains() {
	try {
		var windowSize = getWindowSize();
		var windowHeight = parseInt(windowSize[1]);

		var menus = $('menu').childElements().length - 1; // we do not take "logout" into account
		var offset;

		var i = menus;
		for (i = menus; i > 0; i--) {

			offset = windowHeight - $('tabmenu').getHeight() - $('logout').getHeight() -1;
			for (k = 1; k <= i; k++) {
				offset -= ($('tabmenu'+k).getHeight()+1);
			}

			j = i + 1;
			// Check the next menus
			while ( $('menu'+j) ) {
				if ($('menu'+j).status =='down') {
					offset = offset - $('tabmenu'+j).getHeight() - 1;
				}
				j = j + 1;
			}

			if (offset > 0) {
				$('listmenu'+i).setStyle("height: " +(offset)+"px;");
			}
		}

		// Code to correct sizes of the iframe.
		resize_iframe();
	} catch (e) {sidebarExceptionHandler(e, 'fixCurtains');}
}

//Function called on resizing the window. Changes the position of the tabheaders and fixes the Curtains by calling fixCurtain
function resizeFunction() {
	try {
		var windowSize = getWindowSize();
		var windowHeight = parseInt(windowSize[1]);

		// Adjust the menu size
		if ($('menu')) {
			var menus = $('menu').childElements().length - 1; // we do not take "logout" into account
			var wholeMenuSize = windowHeight - $('tabmenu').getHeight();
			var i = 2;

			for (i = 2; i <= menus; i++) {
				if ($('menu'+i).status == "down") {
					wholeMenuSize = wholeMenuSize - $('menu'+i).getHeight();
				}
			}

			if (browser == 'IE6') {
				$('menu').setAttribute("height", (wholeMenuSize) + 'px');
			} else {
				if (wholeMenuSize > 0) {
					$('menu').setStyle({height: (wholeMenuSize) + 'px'});
				}
			}

			// Adjust logout position
			//$('logout').setStyle({marginTop: '0px'});
			var newTop = (windowHeight-$('logout').getHeight());
			$('logout').setStyle({top: (newTop)+'px'});

			var logoutLogout = $('logout').style.top.split("px");
			logoutLogout  = parseInt(logoutLogout[0]);

			var temp;

			// Adjust all other menus
			var i = 2;
			for (i = 2; i <= menus; i++) {
				if(Object.isUndefined($('menu'+i).status) || $('menu'+i).status == 'up') {
					newTop = $('tabmenu').getHeight();
					temp =(i-2) * $('tabmenu2').getHeight();
					temp += $('tabmenu'+i).getHeight();
					newTop = newTop + temp;
				} else {
					temp = (menus - i + 2);
					temp = temp * ($('tabmenu2').getHeight()+1);
					newTop = windowHeight - temp;
				}
				$('menu'+i).setStyle({marginTop: (0)+'px'});
				$('menu'+i).setStyle({top: (newTop)+'px'});
			}

			// Fix the curtains used to hide the menus
			fixCurtains();
		}
	} catch (e) {sidebarExceptionHandler(e, 'resizeFunction');}
}


var lessonsName = "";
function hideAllLessonSpecific() {
	try {
		//$('loading_sidebar').setStyle({display:'block'});
		// The first menu is built, so as to have all lesson specific and general elements
		allMenuOptions = $('listmenu1').childElements();

		// Hide lesson specific options                                             
		lessonsSpecificOptions = new Array();
		for (i=0; i<allMenuOptions.length; i++) {
			if (allMenuOptions[i].getAttribute("name") == "lessonSpecific") { 
				lessonsSpecificOptions.push(allMenuOptions[i]);
			}
		}

		for (i=0; i<lessonsSpecificOptions.length; i++) {
			lessonsSpecificOptions[i].style.display = "none";
		}

		// Show lesson general options (lessons link and skillgap tests if they exist)
		lessonsGeneralOptions = new Array();
		for (i=0; i<allMenuOptions.length; i++) {
			if (allMenuOptions[i].getAttribute("name") == "lessonGeneral") { 
				lessonsGeneralOptions.push(allMenuOptions[i]);
			}
		}   

		for (i=0; i<lessonsGeneralOptions.length; i++) {
			lessonsGeneralOptions[i].style.display = "block";
		}

		// Change the name of the header
		lessonsName = $('tabmenu1').innerHTML;
		$('tabmenu1').innerHTML = translations['lessons'];
	} catch (e) {sidebarExceptionHandler(e, 'hideAllLessonSpecific');}
}


//The opposite function than hideAllLessonSpecific
function hideAllLessonGeneral() {
	try {
		if ($('listmenu1')) {
			allMenuOptions = $('listmenu1').childElements();

			// Show lesson general options (lessons link and skillgap tests if they exist)
			lessonsGeneralOptions = new Array();
			for (i=0; i<allMenuOptions.length; i++) {
				if (allMenuOptions[i].getAttribute("name") == "lessonGeneral") { 
					lessonsGeneralOptions.push(allMenuOptions[i]);
				}
			}

			for (i=0; i<lessonsGeneralOptions.length; i++) {
				lessonsGeneralOptions[i].style.display = "none";
			}

			lessonsSpecificOptions = new Array();
			for (i=0; i<allMenuOptions.length; i++) {
				if (allMenuOptions[i].getAttribute("name") == "lessonSpecific") { 
					lessonsSpecificOptions.push(allMenuOptions[i]);
				}
			}

			for (i=0; i<lessonsSpecificOptions.length; i++) {
				lessonsSpecificOptions[i].style.display = "block";
			}           

			if (lessonsName != "") {
				$('tabmenu1').innerHTML = lessonsName;
			}
		}
	} catch (e) {sidebarExceptionHandler(e, 'hideAllLessonGeneral');}
}


var lock = 0;   // used to avoid overlapping effects
//Function to move menus up and down
document.move = function(element) {
	try {
		if (!lock) {
			lock = 1;
			setActiveMenu = element.id.substr(4);   //get the # of the menu

			// Move element down        
			if (Object.isUndefined(element.status) || element.status == 'up') {

				//Calculating from the last element to the current one
				var newPos = element.nextSiblings().last().positionedOffset().top - element.positionedOffset().top - (element.nextSiblings().last().getHeight() * (element.nextSiblings().length));

				for (var i = 0; i < element.nextSiblings().length - 1; i++) {
					elementNext = element.next(i);
					if (Object.isUndefined(elementNext.status) || elementNext.status == 'up') {
						newPos = elementNext.nextSiblings().last().positionedOffset().top - elementNext.positionedOffset().top - (elementNext.nextSiblings().last().getHeight() * elementNext.nextSiblings().length);
						elementNext.status = 'down';
						$('list'+elementNext.id).hide();
						//elementNext.down(1).hide();
						effect = new Effect.MoveUpDown(elementNext, newPos);
					}
				}

				if (element.id == 'menu1') {
					setTimeout(function(){$('listmenu1').show();}, 200);
				} else {
					setTimeout(function(){$('list'+element.id).show();}, 200); //element.down(1).show();
				}
			} else {
				// Move element up
				for (var i = element.previousSiblings().length - 1; i>=0; i--) {
					elementPrevious = element.previous(i); 
					if (elementPrevious.status == 'down') {

						newPos = -(elementPrevious.positionedOffset().top - elementPrevious.previousSiblings().last().positionedOffset().top);
						size = elementPrevious.previousSiblings().length;
						for (var j = 1; j <= size; j++) {
							newPos += $('tabmenu'+j).getHeight();
						}


						//newPos += $('tabmenu1').getHeight() - 1;
						elementPrevious.status = 'up';
						$('list'+elementPrevious.id).hide();
						//elementPrevious.down(1).hide();
						effect = new Effect.MoveUpDown(elementPrevious, newPos);

					}
				}
				//alert(element.previousSiblings().last().id + ' edw ' + element.getDimensions().height + ' element ID ' + element.id);
				//alert(element.positionedOffset().top + ' <= element.top , last.top => ' +  element.previousSiblings().last().positionedOffset().top);
				var newPos = -(element.positionedOffset().top - element.previousSiblings().last().positionedOffset().top);

				size = element.previousSiblings().length;
				for (j = 1; j <= size; j++) {
					newPos += $('tabmenu'+j).getHeight();
				}
				//newPos += $('tabmenu1').getHeight() - 1;

				element.status = 'up';

				effect = new Effect.MoveUpDown(element, newPos);

				setTimeout(function(){$('listmenu1').hide();$('list'+element.id).show();}, 250);

			}

			setTimeout(function(){fixCurtains();}, 250);
			setTimeout(function(){lock = 0;},250);
		}
	} catch (e) {sidebarExceptionHandler(e, 'document.move');}
}

Effect.MoveUpDown = function(element, offset) {
	try {
		element = $(element);
		var oldStyle = {opacity: element.getInlineOpacity() };
		return new Effect.Parallel(
				[ new Effect.Move(element, {x: 0, y: offset, sync: true }),
				  new Effect.Opacity(element, { sync: true, to: 0.5 }) ],
				  Object.extend(
						  { duration: 0.25,
							  beforeSetup: function(effect) {
							  //effect.effects[0].element.makePositioned();
						  },
						  afterFinishInternal: function(effect) {
							  effect.effects[0].element.setStyle(oldStyle);
						  }
						  }, arguments[1] || { }));
	} catch (e) {sidebarExceptionHandler(e, 'Effect.MoveUpDown');}
};


var initUpperTabHeight;
var photoHeight;
function fixUpperMenu() {
	try {
		var windowSize = getWindowSize();
		var windowHeight = parseInt(windowSize[1]);
		if ($('tabmenu')) {
			initUpperTabHeight = $('tabmenu').getHeight();
		}
		if ($('topPhoto')) {
			photoHeight = $('topPhoto').getHeight();
		}
		if (browser == 'IE6') {
			if ($('tabmenu')) {
				var tempSize = windowHeight - $('tabmenu').getHeight();
				if ($('menu')) {
					$('menu').setAttribute("height", (tempSize) + 'px');
				}
			}
		}

		if ($('menu')) {
			$('menu').setStyle({height: (windowHeight - $('tabmenu').getHeight()) + 'px'});
		}


		if ($('logout')) {
			//tempHeight = $('logout').getHeight();

			$('logout').setStyle({top: (windowHeight-26)+'px'});

			// The following code is used to move the logout button to the bottom of the page
			$('logout').status = "down";


			// The following code is used to set the active menu appearing and the rest to their correct positions
			var i = 2;
			var offset =$('logout').style.top.split("px");
			offset = offset[0];
		} else {
			offset = windowHeight - 26;
		}

		if ($('menu')) {
			var menus = $('menu').childElements().length - 1; // we do not take "logout" into account
			if (setActiveMenu != 0) {
				var active_menu = setActiveMenu;

			} else {
				var active_menu = activeMenu;
			}

			if (active_menu == 1) {
				// All menu tabs go down, except the top (which is stable)
				for (i = menus; i >= 2; i--) {
					//alert(i);
					if ($('tabmenu'+i) && $('menu'+i)) {
						offset = offset - $('tabmenu' + i).getHeight() - 1;
						$('menu'+i).setStyle({top: (offset)+'px'});
						$('menu'+i).status = 'down';
						$('listmenu'+i).hide();

					}
				}
			} else {
				// Only menu tabs after the active menu go down, the rest remain up
				for (i = menus; i > active_menu; i--) {
					if ($('tabmenu'+i) && $('menu'+i)) {
						offset = offset - $('tabmenu' + i).getHeight() - 1;
						$('menu'+i).setStyle({top: (offset)+'px'});
						$('menu'+i).status = 'down';
						$('listmenu'+i).hide();
					}
				}

				offset =initUpperTabHeight;

				for (i = 2; i <= active_menu; i++) {
					if ($('tabmenu'+(i-1)) && $('menu'+i)) {
						offset = offset + $('tabmenu' + (i-1)).getHeight();
						$('menu'+i).setStyle({top: (offset)+'px'});
						$('menu'+i).status = 'up';
					}
				}
			}
		}

		hideLoadingDiv();
	} catch (e) {sidebarExceptionHandler(e, 'fixUpperMenu');}
}

//Function used to hide and show the upper part of the sidebar
document.myhide = function() {
	try {
		var element = $('tabmenu');
		var tempHeight;
		// Adjust the height of the top frame
		if (element.status == 'hidden') {
			element.status = 'visible';
			$('topPhoto').show();
		} else {
			element.status = 'hidden';
			$('topPhoto').hide();
			tempHeight = initUpperTabHeight-photoHeight;
		}

		var menus = $('menu').childElements().length - 1; // we do not take "logout" into account
		var offset, menuTopOffset;

		// Adjust all other menus
		var i =2;
		for (i = 2; i <= menus; i++) {
			if (Object.isUndefined($('menu'+i).status) || $('menu'+i).status == 'up') {
				menuTopOffset = $('menu'+i).style.top.split("px");
				//eeeedw
				// SWSTI SOLUTION: offset = $('tabmenu').getHeight() + (i-1) * $('tabmenu1').getHeight();
				offset = $('tabmenu').getHeight() + (i-2) * $('tabmenu2').getHeight();
				offset += $('tabmenu1').getHeight();
				$('menu'+i).setStyle({top: (offset)+'px'});
			}
		}

		// Adjust logout menu
		var windowSize = getWindowSize();
		var windowHeight = parseInt(windowSize[1]);
		$('logout').setStyle({top: (windowHeight-$('logout').getHeight())+'px'});

		fixCurtains();
	} catch (e) {sidebarExceptionHandler(e, 'document.myhide');}
};

function checkSidebarMode(s_login) {
	try {
		var value = readCookie(s_login+'_sidebar');
		var valueMode = readCookie(s_login+'_sidebarMode');
		var unit = top.mainframe.location.toString().match('view_unit');

		if(unit=='view_unit' && value == 'hidden') {
			createCookie(s_login+'_sidebarMode','automatic',30);
		} else {
			createCookie(s_login+'_sidebarMode','manual',30);
		}
	} catch (e) {sidebarExceptionHandler(e, 'checkSidebarMode');}
}

//Functions to change user status
var __initStatus;
var __noChangeEscape = 0;
function showStatusChange() {
	try {
		__initStatus = $('inputStatusText').value;
		$('statusText').style.display = 'none';
		$('inputStatusText').style.display = 'block';
		$('inputStatusText').focus();
	} catch (e) {sidebarExceptionHandler(e, 'showStatusChange');}
}

function changeStatus() {
	try {
		if (__initStatus != $('inputStatusText').value) {
			if (translations['s_type'] == "administrator") {
				var url = translations['servername']+"administrator.php?ctg=personal&postAjaxRequest=1&setStatus=" + $('inputStatusText').value;
			} else {
				var url = translations['servername']+translations['s_type']+".php?ctg=personal&postAjaxRequest=1&setStatus=" + $('inputStatusText').value;
			}

			$('inputStatusText').style.display = 'none';
			$('statusTextProgressImg').show();

			new Ajax.Request(url, {
				method:'get',
				asynchronous:true,
				onSuccess: function (transport) {

				$('statusTextProgressImg').hide().src = 'themes/default/images/others/transparent.gif';
				$('statusTextProgressImg').addClassName('sprite16').addClassName('sprite16-success');

				new Effect.Appear($('statusTextProgressImg'));

				window.setTimeout('Effect.Fade("statusTextProgressImg")', 2000);
				window.setTimeout("$('statusTextProgressImg').writeAttribute('src', 'themes/default/images/others/progress1.gif')", 3500);

				if ($('inputStatusText').value != '') {
					$('statusText').innerHTML = $('inputStatusText').value;

					if (top.mainframe.document.getElementById('statusText')) {
						top.mainframe.document.getElementById('statusText').innerHTML = "\"<i>" + $('inputStatusText').value + "</i>\"";
						top.mainframe.document.getElementById('inputStatusText').value = $('inputStatusText').value;
					}
				} else {
					$('statusText').innerHTML = translations['clicktochange'];


					if (top.mainframe.document.getElementById('statusText')) {
						top.mainframe.document.getElementById('statusText').innerHTML = translations['clicktochange'];
						top.mainframe.document.getElementById('inputStatusText').value = "";
					}
				}

				window.setTimeout("$('statusText').style.display = 'block';",3500);
			}
			});


		} else {
			$('inputStatusText').style.display="none";
			$('statusText').style.display = 'block';
		}
		__noChangeEscape = 0;
	} catch (e) {sidebarExceptionHandler(e, 'changeStatus');}
}


function checkIfEnter(event) {
	try {
		//event.keyCode;

		if (event.keyCode == Event.KEY_RETURN) {
			$('inputStatusText').blur();
		} else if (event.keyCode == 27) {           // Escape
			__noChangeEscape = 1;
			$('inputStatusText').value = __initStatus;
			$('inputStatusText').blur();
			$('inputStatusText').style.display="none";
			$('statusText').style.display = 'block';
		}
	} catch (e) {sidebarExceptionHandler(e, 'checkIfEnter');}
}


function setActiveId(ctg, op, tab, type, module_menu, stats_options, user_type) {
	try {
		if (ctg == "personal" && tab == "file_record") {

			changeTDcolor('file_manager');

		} else if (ctg == "control_panel" && user_type != "administrator") {

			changeTDcolor('lesson_main');

		} else if (ctg == "content" && type == "theory") {

			changeTDcolor('theory');

		} else if (ctg == "tests") {

			changeTDcolor('tests');

		} else if (ctg == "projects") {

			changeTDcolor('exercises');

		} else if (ctg == "glossary") {

			changeTDcolor('glossary');

		} else if (ctg == 'content' && op == 'file_manager') {

			changeTDcolor('file_manager');

		} else if (ctg == 'statistics') {
			changeTDcolor('statistics_' + stats_options);
//			} else if (ctg == 'users' && $smarty.session.employee_type == $smarty.const._SUPERVISOR}
//			changeTDcolor('employees');

		} else if (ctg == "module_hcd") {
			if (op == "reports") {
				changeTDcolor('search_employee');
			} else if (op != "") {
				changeTDcolor(op);
			} else {
				changeTDcolor('hcd_control_panel');
			}
		} else if (ctg == 'social') {
			if (op == 'people') {
				changeTDcolor('people');

			} 
			/*
        else if (op == 'timeline') {
            {if isset($smarty.get.lessons_ID)}
                changeTDcolor('timeline');
            {else}
                changeTDcolor('system_timeline');
            {/if}
        {/if}
			 */
		} else if (ctg == 'module') {
			changeTDcolor(module_menu); //{$T_MODULE_HIGHLIGHT}
		} else {
			changeTDcolor(ctg);
		}
	} catch (e) {sidebarExceptionHandler(e, 'setActiveId');}
}   


function setMenuPositions() {
	try {
		// Get window size
		var windowSize = getWindowSize();
		var windowHeight = parseInt(windowSize[1]);

		if (!usingHorizontalInterface) {
			// Adjust logout position
			var logoutHeight = $('logout').getHeight();
			var newTop = (windowHeight - logoutHeight);
			$('logout').setStyle({top: (newTop)+'px'});
			var logoutLogout = $('logout').style.top.split("px");
			logoutLogout  = parseInt(logoutLogout[0]);

			// Cache tabmenu heights
			var tabmenuHeight = $('tabmenu').getHeight();
			var tabmenuHeight_other = $('tabmenu2').getHeight();

			// Set menu size
			var wholeMenuSize = windowHeight - tabmenuHeight;
			if (browser == 'IE6') {
				$('menu').setAttribute("height", (wholeMenuSize) + 'px');
			} else {
				if (wholeMenuSize > 0) {
					$('menu').setStyle({height: (wholeMenuSize) + 'px'});
				}
			}

			// Initialize menu positions
			var menus = $('menu').childElements().length - 1;

			// Find which menus are up and which are down
			if(document.getElementById(active_id+"_a")) {
				$(active_id+"_a").className = "selectedTopTitle";

				// Set all previous elements to up status and all following to down
				var limitElementId = $(active_id+"_a").up().up().id;                    
			} else {
				var limitElementId = "menu1";
			}

			var status = "up";
			var shouldHide = false;
			for (i = 1; i<= menus; i++) {
				$('menu'+i).status = status;
				if (shouldHide) {
					$('listmenu'+i).hide(); 
				}

				if ($('menu'+i).id == limitElementId) {
					status = "down";
					shouldHide = true;
				}
			}

			// Set menu positions
			for (i = 2; i <= menus; i++) {
				if($('menu'+i).status == 'up') {
					newTop = tabmenuHeight;
					temp =(i-2) * tabmenuHeight_other;
					temp += $('tabmenu'+i).getHeight();
					newTop = newTop + temp;
				} else {
					temp = (menus - i + 2);
					temp = temp * (tabmenuHeight_other+1);
					newTop = windowHeight - temp;
				}
				$('menu'+i).setStyle({marginTop: (0)+'px'});
				$('menu'+i).setStyle({top: (newTop)+'px'});
			}

			// MinifixCurtains
			var i = menus;
			for (i = menus; i > 0; i--) {

				offset = windowHeight - tabmenuHeight - logoutHeight - 1;
				for (k = 1; k <= i; k++) {
					offset -= ($('tabmenu'+k).getHeight()+1);
				}

				j = i + 1;
				// Check the next menus
				while ( $('menu'+j) ) {
					if ($('menu'+j).status =='down') {
						offset = offset - $('tabmenu'+j).getHeight() - 1;
					}
					j = j + 1;
				}

				if (offset > 0) {
					$('listmenu'+i).setStyle("height: " +(offset+1)+"px;");
				}
			}


			// Set overflow style       
			for (i = 1 ; i <= menus; i++) {
				$('listmenu'+i).setStyle({overflowY: 'auto'});
			}


			// Fix the curtains used to hide the menus
			fixCurtains();   

			// Hide the loading bar, just after all menu item movements
			hideLoadingDiv();
		}
	} catch (e) {sidebarExceptionHandler(e, 'setMenuPositions');}
}

function sidebarExceptionHandler(e, fnc) {
	//alert(e);
	//alert(fnc);
}


if ($('current_location') && top.mainframe) {  // in order to evaluate current_location for search box when sidebar is refreshed
	$('current_location').value = top.mainframe.location.toString();
}     
