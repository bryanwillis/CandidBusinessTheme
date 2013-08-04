(function($)
{
	"use strict";

	var av_key = "avia_builder_button";	// $this->button['id']

    tinymce.create('tinymce.plugins.'+av_key, 													
    {  
    	//init: register the modal open function to the tinymce editor
        init : function(editor, url) 
        {  
			editor.addCommand("openAviaModal", function (ui, params) 
			{
				var modal = new $.AviaModal(params);
				return false;
			});
        },  
        
        //create controlls
        createControl : function(current_key, controlManager) 
        {  
        	if (current_key != av_key) return null;

        		//create the button
        		var cur_plugin 	= this,
        			open_modal  = $.AviaModal.openInstance || [],
        			button 		= controlManager.createMenuButton(av_key,
	            	{  
	            		icons : false,
	                	title : avia_globals.sc[av_key].title,
	                	image : avia_globals.sc[av_key].image,
	            	}); 
	            
	            //add dropdown items to the button
	            button.onRenderMenu.add(function (button, menu) 
	            {
	            	var shortcode_array = avia_globals.sc[av_key].config,
		            		submenu = [];
	            	
	            	
	            	//dont create tab submenus if modal window is open //todo: build finer controll which tabs to include when
            		if(open_modal.length == 0)
            		{
		            	//get all tabs
		            	for(var i in shortcode_array)
		            	{
		            		submenu[shortcode_array[i].tab] = [];
		            	}
		            	
		            	//create sub menus
		            	for(var title in submenu)
		            	{
		            		if( title != 'undefined')
		            		{
			            		submenu[title] = menu.addMenu({
			                        title: title
			                    });
		            		}
		            	}
		            	
		            	menu.addSeparator();
	            	}
	            	
	            	
	            	//add items to sub menus. based on the config tinymce array add an instant insert or modal popup button
	            	for(var z in shortcode_array)
	            	{
	            		//only render subset of elements if modal window is open
	            		if(open_modal.length == 0 || !shortcode_array[z].tab)
	            		{
		            		//set a default
		            		shortcode_array[z].tinyMCE = shortcode_array[z].tinyMCE || {};
		            		
		            		var current_menu = shortcode_array[z].tab ? submenu[shortcode_array[z].tab] : menu;
		            	
		            		if(typeof shortcode_array[z].tinyMCE.instantInsert != 'undefined')
		            		{
		            			cur_plugin.instantInsert(current_menu, shortcode_array[z]);
		            		}
		            		else
		            		{
		            			cur_plugin.modalInsert(current_menu, shortcode_array[z]);
		            		}
	            		}
	            	}
	            	

	            });

            return button;  
        },  
        
        instantInsert: function (menu, shortcode) 
        {
            menu.add(
            {
                title: shortcode.tinyMCE.name || shortcode.name,
                onclick: function () 
                {
                    tinyMCE.activeEditor.execCommand("mceInsertContent", false, window.switchEditors.wpautop(shortcode.tinyMCE.instantInsert))
                }
            })
        },
        
        modalInsert: function (menu, shortcode) 
        {
            menu.add({
                title: shortcode.tinyMCE.name || shortcode.name,
                onclick: function () 
                {
                	var modalData = $.extend({}, {modal_class:'', before_save:'', }, shortcode.modal_data);
                		
                	if(typeof shortcode.modal_on_load != "undefined" && typeof shortcode.modal_on_load != "string") shortcode.modal_on_load = shortcode.modal_on_load.join(', ');
                	
                    tinyMCE.activeEditor.execCommand("openAviaModal", false, 
                    {
						modal_class: 		modalData.modal_class,
						before_save:  		modalData.before_save,
						shortcodehandler: 	shortcode.shortcode, 
						modal_title: 		shortcode.name, 
						modal_ajax_hook: 	shortcode.shortcode ,
                        scope: 				tinyMCE.activeEditor,
						ajax_param: 		{extract: true, shortcode: ""},
                        on_load: 			shortcode.modal_on_load,
						on_save: 			function(values)
						{
							if(typeof values != "string")
							{
								//cleanup values: remove aviaTB addtion to the arguments, cleanup fake args and prepare object for insertion by creating the shortcode string
								var new_key;
								for (var el_key in values)
								{
									if (values.hasOwnProperty(el_key)) 
									{
										if(el_key.indexOf('_fakeArg') !== -1)
										{
											delete values[el_key];
										}
										else
										{
											new_key = el_key.replace(/aviaTB/g,"");
											if(new_key != el_key)
											{
												values[new_key] = values[el_key];
												delete values[el_key];
											}
										}
									}
								}
								
								//if a specific template was passed return that one, otherwise use the default shortcode builder
								if(shortcode.tinyMCE.templateInsert)
								{
									for (var el_key in values)
									{
										shortcode.tinyMCE.templateInsert = shortcode.tinyMCE.templateInsert.replace("{{"+el_key+"}}", values[el_key]);
									}
									
									values = shortcode.tinyMCE.templateInsert;
								}
								else
								{
									values = window.switchEditors.wpautop( $.avia_builder.createShortcode( values, shortcode.shortcode ) );
								}
							}
			
							
							tinyMCE.activeEditor.execCommand("mceInsertContent", false, values)
						}
                    })
                }
            })
        },
        
        
        
    });  
    
    
    tinymce.PluginManager.add(av_key, tinymce.plugins[av_key]);
    
})(jQuery);	 
