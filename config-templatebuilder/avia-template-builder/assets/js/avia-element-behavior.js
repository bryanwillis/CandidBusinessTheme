(function($)
{
	"use strict";
	
	$.AviaElementBehavior = $.AviaElementBehavior || {};
	
	$(document).ready(function () 
	{
		// can be removed once ie7 and 8 are dead and mobile browsers understand the css pseudo selector :checked
    	$.AviaElementBehavior.image_radio();		
    	
    	// can be removed once all browser support css only tabs (:target support needed)			
    	$.AviaElementBehavior.tabs('.avia-tab-container'); 	
    	
    	//sets the input hidden field that contains the final icon value
    	$.AviaElementBehavior.icon_select();
    	
    	//allows to expand a meta box to fullscreen proportions
    	$.AviaElementBehavior.expand_metabox();
    	
    	//show/hide dependent elements
    	$.AviaElementBehavior.check_dependencies();
    	
    	//set another elements property
    	$.AviaElementBehavior.set_target_property(); 
    	
    	//fetch a php template and append it to an element
    	$.AviaElementBehavior.tmpl_fetcher();
    	
    	//functionallity that controlls the redo and undo buttons
    	$.AviaElementBehavior.redo_undo(); 
    	
    	//image insert functionality located in avia-media.js
    	$.AviaElementBehavior.wp_media_advanced(); 
    	
    	//save template functionality avia-template-saving.js
    	new $.AviaElementBehavior.wp_save_template(); 
        
        //default tooltips for various elements like shortcodes
    	new $.AviaTooltip({attach:'body'});
    	
    	 //tooltips for the help icon
    	new $.AviaTooltip({class: 'avia-help-tooltip', data: 'avia-help-tooltip', event:'click', position:'bottom', attach:'body'});
	});
	
	
	// since css only tabs are not fully working by now this script adds tab behavior to a tab container of choice
	$.AviaElementBehavior.tabs =  function(tab_container, mirror_container)
	{
		$(tab_container).each(function(i)
		{
			var active_tab = 0, id = "avia_post_"+ i + "_" + avia_globals.post_id, storage = false;
			
			if(typeof(Storage)!=="undefined")
			{
				storage		= true;
				active_tab  = sessionStorage[id] || 0;
			}
			
			var current = $(this), links = current.find('.avia-tab-title-container a'), tabs = current.find('.avia-tab'), currentLink;
				
				
				links.unbind('click').bind('click', function()
				{
					links.removeClass('active-tab');
					currentLink = $(this).addClass('active-tab');
					
					var index = links.index(currentLink);
					
					tabs.css({display:'none'}).filter(':eq(' + index + ')').css({display:'block'});
					if(storage) sessionStorage[id] = index;
					
					//mirror_container should be defined when the tab element is cloned for the fullscreen view
					if(typeof mirror_container != "undefined")
					{
						mirror_container.find('.avia-tab-title-container a').eq(index).trigger('click');
					}
					
					return false;
				});
				
				
				if(!links.filter('.active-tab').length)
				{
					links.filter(':eq('+active_tab+')').addClass('active-tab').trigger('click');
				}
				
				
		});
	}	
	
	
	// necessary for image radiobutton
	$.AviaElementBehavior.image_radio  = function()
	{
		$('.avia_scope input[type="radio"]:checked').parents('.avia_radio_wrap:eq(0)').addClass('avia_checked');
	
		$("body").on("click", ".avia_scope input[type='radio']", function(event)
		{
			var $parent = $(this).parents('.avia_radio_wrap:eq(0)');
			$parent.siblings('.avia_radio_wrap').removeClass('avia_checked').end().addClass('avia_checked');
		});
	}
	
	//adds functionallity to the font based icon selector. when an item is clicked it stores the item nr and if possible the item html code
	$.AviaElementBehavior.icon_select =  function()
	{
		$("body").on('click', '.avia-attach-element-select', function()
		{
			var clicked = $(this),
				parent  = clicked.parents('.avia-attach-element-container:eq(0)'),
				old 	= parent.find('.avia-active-element').removeClass('avia-active-element'),
				input	= parent.find('input[type=hidden]:eq(0)'),
				extra 	= parent.find('input[type=hidden]:eq(1)');
				
				clicked.addClass('avia-active-element');
				input.val(clicked.data('element-nr'));
				
				if(extra.length)
				{
					extra.val(clicked.html());
				}
				
				return false;
		});
	}
	
	//function that expands a post metabox to fullscreen proportions
	$.AviaElementBehavior.expand_metabox =  function()
	{
		var the_body			= $("body"), 
			already_expanded	= $('.avia-expanded').find('.avia-attach-expand'),
			update_button		= $('input#publish'), 
			preview_button		= $('a#post-preview'),
			whitescreen			= $('<div class="avia-expand-whitescreen"></div>').appendTo(the_body),
			clicked, parent, container, clone_tab, button_container;

		if(already_expanded.length)
		{
			clicked = already_expanded;
			parent = clicked.parents('.postbox:eq(0)');
			avia_open_expand();
		}


		the_body.on('click', '.avia-attach-expand', function()
		{
			clicked 	= $(this);
			parent  	= clicked.parents('.postbox:eq(0)');
				
			if(parent.is('.avia-expanded'))
			{	
				whitescreen.css({display:"block", opacity:0}).animate({opacity:1}, function()
				{
					avia_close_expand();
					whitescreen.animate({opacity:0}, function(){ whitescreen.css({display:"none"}) });
				});
				
			}
			else
			{
				whitescreen.css({display:"block", opacity:0}).animate({opacity:1}, function()
				{
					avia_open_expand();
					whitescreen.animate({opacity:0}, function(){ whitescreen.css({display:"none"}) });
				});
			}
				
			return false;	
		});
		
		function avia_close_expand()
		{
			parent.removeClass('avia-expanded');
			the_body.removeClass('avia-noscroll-box');
			if(container.length) container.remove();
		}
		
		function avia_open_expand()
		{
			parent.addClass('avia-expanded');
			the_body.addClass('avia-noscroll-box');
			clone_tab = parent.find('.avia-tab-container').clone(true);
			
			if(clone_tab.length)
			{
				//create the cloned tab controls with buttons
				button_container = $('<div class="avia-expanded-buttons"></div>').appendTo(clone_tab);
				preview_button.clone(true).appendTo(button_container).bind('click', function()
				{ 
					//hackish way to switch to the wordpress preview window
					 setTimeout( function(){ var wp_prev = window.open('', 'wp-preview', ''); wp_prev.focus(); },10);
				});
				
				update_button.clone(true).appendTo(button_container);
				clicked.clone(true).addClass('wp-core-ui button').appendTo(button_container);
				
				//create hidden input that tells wordpress which element to expand in case the save button was pressed
				$('<input type="hidden" name="avia-expanded-hidden" value="' + parent.attr('id') +'" />').appendTo(button_container);
				
				//append the cloned tabs controls to the container
				container = $('<div class="avia-fixed-controls"></div>').appendTo(parent);
				clone_tab.appendTo(container);
				
				//activate behavior
				$.AviaElementBehavior.tabs(clone_tab, $('.avia-tab-container:not(.avia-fixed-controls .avia-tab-container):first')); 
			}
		}
		
		
	}
	
	//dependency checker for select elements
	$.AviaElementBehavior.check_dependencies = function()
	{
		var the_body = $("body"), container = "";
	
		the_body.on('change', '.avia-style select, .avia-style radio, .avia-style input[type=checkbox], .avia-style input[type=hidden]', function()
		{
			var current 	= $(this), 
				scope	= current.parents('.avia-modal:eq(0)');
			
			if(!scope.length) scope = the_body;
			
			var id			= this.id.replace(/aviaTB/g,""),
				dependent	= scope.find('.avia-form-element-container[data-check-element="'+id+'"]'), 
				value1		= this.value,
				is_hidden	= current.parents('.avia-form-element-container:eq(0)').is('.avia-hidden');
				
				if(!dependent.length) return;
				
				dependent.each(function()
				{
					var current		= $(this), 
						check_data	= current.data(), 
						value2		= check_data.checkValue, 
						show		= false;
						
						if(!is_hidden)
						{
							switch(check_data.checkComparison)
							{
								case 'equals': 			if(value1 == value2) show = true; break;
								case 'not': 			if(value1 != value2) show = true; break;
								case 'is_larger': 		if(value1 >  value2) show = true; break;
								case 'is_smaller': 		if(value1 <  value2) show = true; break;
								case 'contains': 		if(value1.indexOf(value2) != -1) show = true; break;
								case 'doesnt_contain':  if(value1.indexOf(value2) == -1) show = true; break;
								case 'is_empty_or':  	if(value1 == "" || value1 == value2) show = true; break;
								case 'not_empty_and':  	if(value1 != "" && value1 != value2) show = true; break;
								
								
							}
						}
						
						if(show == true && current.is('.avia-hidden'))
						{
							current.css({display:'none'}).removeClass('avia-hidden').find('select, radio, input[type=checkbox]').trigger('change');
							current.slideDown(300);
						}
						else if(show == false  && !current.is('.avia-hidden'))
						{
							current.css({display:'block'}).addClass('avia-hidden').find('select, radio, input[type=checkbox]').trigger('change');
							current.slideUp(300);
						}
				});
		});
	}
	
	//target setter for elements
	$.AviaElementBehavior.set_target_property = function()
	{
		var the_body = $("body"), container = "";
	
		the_body.on('change', '.avia-style select, .avia-style radio, .avia-style input[type=checkbox]', function()
		{
			var current = $(this),
				wrapper = current.parents('.avia-form-element-container:eq(0)'),
				scope	= current.parents('.avia-modal:eq(0)'),
				data 	= wrapper.data(), 
				options = "";
			
			if(!data.targetElement) return;
			if(!scope.length) scope = the_body;
			if(current.is('select')) options = current.find('option').map(function(){return this.value}).get().join(" ")
			
			var target 		= the_body.find( data.targetElement ), 
				new_value 	= this.value;
				
				if(!target.length) return;
				
				target.each(function()
				{
					var current_target = $(this);
					
					switch(data.targetProperty)
					{
						case 'class': current_target.removeClass(options).addClass(new_value); break;
						case 'id': current_target.attr({'id': new_value}); break;
					}
				});
		});
		
		
		the_body.on('avia_modal_finished', function(event, window)
		{
			window.modal.find(".avia-attach-targeting select,.avia-attach-targeting radio,.avia-attach-targeting input[type=checkbox]").trigger('change');
		});
		
	}
	
	
	//template fetchter for elements
	$.AviaElementBehavior.tmpl_fetcher = function()
	{
		var the_body = $("body"), container = "";
	
		the_body.on('change', '.avia-attach-templating select, .avia-attach-templating radio, .avia-attach-templating input[type=checkbox]', function()
		{
			var current = $(this),
				wrapper = current.parents('.avia-form-element-container:eq(0)'),
				scope	= current.parents('.avia-modal:eq(0)'),
				target  = current.next('.template-container');
			
			if(!scope.length) scope = the_body;
			if(!target.length) return;
			
			var new_value 	= this.value,
				template	= $("#avia-tmpl-"+new_value);
				
				if(!template.length)
				{
					if(avia_globals.builderMode && avia_globals.builderMode == "debug")
					{
						avia_log('template snippet "#avia-tmpl-'+new_value+'" not defined','error');
   						avia_log('Make sure that the you have created the template and check the source code if its really available','help');
					}
					
					template = $('<div />');
				}
				
				target.html(template.html());
		});
		
		
		the_body.on('avia_modal_finished', function(event, window)
		{
			window.modal.find(".avia-attach-templating select,.avia-attach-templating radio,.avia-attach-templating input[type=checkbox]").trigger('change');
		});
		
		
	}
	
	
	//redo and undo buttons
	$.AviaElementBehavior.redo_undo =  function()
	{
		var el_storage = new $.AviaElementBehavior.history({
			monitor: "#aviaLayoutBuilder", 
			editor:	 "#_aviaLayoutBuilderCleanData",
			buttons: ".layout-builder-wrap .avia-controll-bar"
		});
	}
	
	
	
	
	
	
		
	
})(jQuery);	 
















