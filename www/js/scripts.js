(function($){

	var _show_modal = function(options) {
		
		var settings = $.extend( {
				'save': 'Save',
				'close': 'Close',
				'width': null,
				'height': null,
				'body': '',
				'header': 'Header',
				'id': false,
				'remote': false,
				'element' : null,
				'url': null
		    }, options);

				
		var modal = $('<div id="ef-modal" class="modal" style = "display:none">\
		  <div class="modal-header">\
		    <button type="button" class="close" data-dismiss="modal">&times;</button>\
		    <h3 id="ef-modal-label">'+settings.header+'</h3>\
		  </div>\
		  <div class="modal-body">\
		    <p>'+settings.body+'</p>\
		  </div>\
		</div>').appendTo('body');
		
		marginleft = (parseInt(modal.css('margin-left')) - (parseInt(settings.width)-parseInt(modal.css('width')))/2);	//By default, the modal is aligned absolutely in the screen, based on its hard-coded bootstrap width, 560px. Since we're resizing the modal, we need to recalculate the suitable offset (margin left)
		
		modal.css({'width':settings.width, 'height':settings.height, 'margin-left':marginleft}).modal({remote:settings.remote});

		if (settings.id) {
			
			$('#'+settings.id).appendTo('.modal-body').show();

			$('#ef-modal').on('hidden', function () {
				$('#'+settings.id).appendTo('body').hide();
				  $('#ef-modal').remove();
				});
				
		} else if (settings.remote === false) {
			$('.modal-body').css({'height':'100%', 'max-height':(parseInt(settings.height)-50)+'px'});
			if (settings.url == null) {
				if ($(settings.element).is('a')) {
					var el = $(settings.element);					//If it's a link, then use its href attribute
				} else {
					var el = $(settings.element).parents('a');		//Otherwise, use its parent element, assuming that it's (for example) an img within a link
				}			
				if (el.length == 0) {
					var el = $(settings.element).children('a');		//Otherwise, use its child element, assuming we clicked on a td that contains a link (like when in icon blocks)
				}
				if (el.length != 0) {
					var href = el.attr('href');
				}
			} else {
				var href = settings.url;
			}
			//console.log(href);
			$('<iframe src = "'+href+'" frameborder="no"></iframe>')
			.css({'width':$('#ef-modal').children('.modal-body').width(),'height':$('#ef-modal').children('.modal-body').height()-30})
			.appendTo('.modal-body');
			$('#ef-modal').on('hidden', function () {
				  $('#ef-modal').remove();
				});
		} 
		
	};
	
	/*
	 * These are the actual public methods available 
	 */
	var methods = {
			init: function(options) {
				
			},
			switchmode: function() {
				$.ajax({url:location.toString(), data:{'toggle_mode':true}}).done(function (data) {window.location.reload();});
			},
			modal: function(options) {	
				_show_modal(options);
				
				return this;
			}
	};
	
	$.fn.efront = function(method) {
		// Method calling logic
		if ( methods[method] ) {
			return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.tooltip' );
		}    
    };
    
})(jQuery);

//$.fn.Efront();
//console.log($.fn.Efront());