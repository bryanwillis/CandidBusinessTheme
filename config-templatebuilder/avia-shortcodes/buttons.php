<?php
/**
 * Sidebar
 * Displays one of the registered Widget Areas of the theme
 */
 
if ( !class_exists( 'avia_sc_button' ) ) 
{
	class avia_sc_button extends aviaShortcodeTemplate
	{
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['name']		= __('Button', 'avia_framework' );
				$this->config['tab']		= __('Content Elements', 'avia_framework' );
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-button.png";
				$this->config['order']		= 85;
				$this->config['target']		= 'avia-target-insert';
				$this->config['shortcode'] 	= 'av_button';
				$this->config['tooltip'] 	= __('Creates a colored button', 'avia_framework' );

			}
		
			/**
			 * Popup Elements
			 *
			 * If this function is defined in a child class the element automatically gets an edit button, that, when pressed
			 * opens a modal window that allows to edit the element properties
			 *
			 * @return void
			 */
			function popup_elements()
			{
				$this->elements = array(
			
					array(	"name" 	=> __("Button Label", 'avia_framework' ),
							"desc" 	=> __("This is the text that appears on your button.", 'avia_framework' ),
				            "id" 	=> "label",
				            "type" 	=> "input",
				            "std" => "Click me"),
				    array(	
							"name" 	=> __("Button Link?", 'avia_framework' ),
							"desc" 	=> __("Where should your button link to?", 'avia_framework' ),
							"id" 	=> "link",
							"type" 	=> "linkpicker",
							"fetchTMPL"	=> true,
							"subtype" => array(	
												__('Set Manually', 'avia_framework' ) =>'manually',
												__('Single Entry', 'avia_framework' ) =>'single',
												__('Taxonomy Overview Page',  'avia_framework' )=>'taxonomy',
												),
							"std" 	=> ""),
							
					array(	
							"name" 	=> __("Open Link in new Window?", 'avia_framework' ),
							"desc" 	=> __("Select here if you want to open the linked page in a new window", 'avia_framework' ),
							"id" 	=> "link_target",
							"type" 	=> "select",
							"std" 	=> "",
							"subtype" => array(
								__('No, open in same window',  'avia_framework' ) =>'',
								__('Yes, open in new window',  'avia_framework' ) =>'_blank')),	
										
					array(	
							"name" 	=> __("Button Color", 'avia_framework' ),
							"desc" 	=> __("Choose a color for your button here", 'avia_framework' ),
							"id" 	=> "color",
							"type" 	=> "select",
							"std" 	=> "theme-color",
							"subtype" => array(	
												__('Theme Color', 'avia_framework' )=>'theme-color',
												__('Theme Color Subtle', 'avia_framework' )=>'theme-color-subtle',
												__('Blue', 'avia_framework' )=>'blue',
												__('Red',  'avia_framework' )=>'red',
												__('Green', 'avia_framework' )=>'green',
												__('Orange', 'avia_framework' )=>'orange',
												__('Aqua', 'avia_framework' )=>'aqua',
												__('Teal', 'avia_framework' )=>'teal',
												__('Purple', 'avia_framework' )=>'purple',
												__('Pink', 'avia_framework' )=>'pink',
												__('Silver', 'avia_framework' )=>'silver',
												__('Grey', 'avia_framework' )=>'grey',
												__('Black', 'avia_framework' )=>'black',
												__('Custom Color', 'avia_framework' )=>'custom',
												)),

							
					array(	
							"name" 	=> __("Custom Background Color", 'avia_framework' ),
							"desc" 	=> __("Select a custom background color for your Button here", 'avia_framework' ),
							"id" 	=> "custom_bg",
							"type" 	=> "colorpicker",
							"std" 	=> "#444444",
							"required" => array('color','equals','custom')
						),	
						
					array(	
							"name" 	=> __("Custom Font Color", 'avia_framework' ),
							"desc" 	=> __("Select a custom font color for your Button here", 'avia_framework' ),
							"id" 	=> "custom_font",
							"type" 	=> "colorpicker",
							"std" 	=> "#ffffff",
							"required" => array('color','equals','custom')
						),		
						
						
					array(	
							"name" 	=> __("Button Size", 'avia_framework' ),
							"desc" 	=> __("Choose the size of your button here", 'avia_framework' ),
							"id" 	=> "size",
							"type" 	=> "select",
							"std" 	=> "small",
							"subtype" => array(
								__('Small',   'avia_framework' ) =>'small',
								__('Medium',  'avia_framework' ) =>'medium',
								__('Large',   'avia_framework' ) =>'large',
							)),
							
					array(	
							"name" 	=> __("Button Position", 'avia_framework' ),
							"desc" 	=> __("Choose the alignment of your button here", 'avia_framework' ),
							"id" 	=> "position",
							"type" 	=> "select",
							"std" 	=> "center",
							"subtype" => array(
								__('Align Left',   'avia_framework' ) =>'left',
								__('Align Center',  'avia_framework' ) =>'center',
								__('Align Right',   'avia_framework' ) =>'right',
							)),		
					array(	
							"name" 	=> __("Button Icon", 'avia_framework' ),
							"desc" 	=> __("Should an icon be displayed at the left side of the button", 'avia_framework' ),
							"id" 	=> "icon_select",
							"type" 	=> "select",
							"std" 	=> "yes",
							"subtype" => array(
								__('No Icon',  'avia_framework' ) =>'no',
								__('Yes, display Icon',  'avia_framework' ) =>'yes')),	
					
					array(	
							"name" 	=> __("Button Icon",'avia_framework' ),
							"desc" 	=> __("Select an icon for your Button bellow",'avia_framework' ),
							"id" 	=> "icon",
							"type" 	=> "iconfont",
							"font"	=> "entypo-fontello",
							"folder"=> AviaBuilder::$path['assetsURL']."fonts/",
							"chars"	=> AviaBuilder::$path['pluginPath'].'assets/fonts/entypo-fontello-charmap.php',
							"std" 	=> "25",
							"required" => array('icon_select','equals','yes')
							)			
				);

			}
			
			
			
			/**
			 * Editor Element - this function defines the visual appearance of an element on the AviaBuilder Canvas
			 * Most common usage is to define some markup in the $params['innerHtml'] which is then inserted into the drag and drop container
			 * Less often used: $params['data'] to add data attributes, $params['class'] to modify the className
			 *
			 *
			 * @param array $params this array holds the default values for $content and $args. 
			 * @return $params the return array usually holds an innerHtml key that holds item specific markup.
			 */
			function editor_element($params)
			{
				$icon_el = end($this->elements); //last element is the icon container
				
				$chars = $icon_el['chars'];
				if(!is_array($chars))
				{
					include($icon_el['chars']);
				}
				
				$display_char = isset($chars[($params['args']['icon'] - 1)]) ? $chars[($params['args']['icon'] - 1)] : $chars[0];
				
				$inner  = "<div class='avia_button_box avia_hidden_bg_box avia_textblock avia_textblock_style'>";
				$inner .= "		<div ".$this->class_by_arguments('icon_select, color, size, position' ,$params['args']).">";
				$inner .= "			<span data-update_with='icon_fakeArg' class='avia_button_icon avia-font-".$icon_el['font']."'>".$display_char."</span>";
				$inner .= "			<span data-update_with='label' class='avia_iconbox_title' >".$params['args']['label']."</span>";
				$inner .= "		</div>";
				$inner .= "</div>";
				
				$params['innerHtml'] = $inner;
				$params['content'] = NULL;
				$params['class'] = "";
				
				return $params;
			}
			
			
			
			/**
			 * Frontend Shortcode Handler
			 *
			 * @param array $atts array of attributes
			 * @param string $content text within enclosing form of shortcode element 
			 * @param string $shortcodename the shortcode found, when == callback name
			 * @return string $output returns the modified html string 
			 */
			function shortcode_handler($atts, $content = "", $shortcodename = "", $meta = "")
			{
			   $atts =  shortcode_atts(array('label' => 'Click me', 
			                                 'link' => '', 
			                                 'link_target' => '',
			                                 'color' => 'theme-color',
			                                 'custom_bg' => '#444444',
			                                 'custom_font' => '#ffffff',
			                                 'size' => 'small',
			                                 'position' => 'center',
			                                 'icon_select' => 'yes',
			                                 'icon' => '1',
			                                 ), $atts);
			
			    $icon_el = end($this->elements); //last element is the icon container
				
				$chars = $icon_el['chars'];
				if(!is_array($chars))
				{
					include($icon_el['chars']);
				}
				
				$style = "";
				if($atts['color'] == "custom") 
				{
					$style .= "style='background-color:".$atts['custom_bg']."; border-color:".$atts['custom_bg']."; color:".$atts['custom_font']."; '";
				}
				
				$display_char = isset($chars[($atts['icon'] - 1)]) ? $chars[($atts['icon'] - 1)] : $chars[0];
			    $blank = $atts['link_target'] ? 'target="_blank" ' : "";
			    $link  = AviaHelper::get_url($atts['link']);
			    $link  = $link == "http://" ? "" : $link;
			    
			    $output  = "";
				$output .= "<a href='{$link}' class='avia-button ".$this->class_by_arguments('icon_select, color, size, position' , $atts, true)."' {$blank} {$style} >";
				$output .= "<span class='avia_button_icon avia-font-".$icon_el['font']."'>".$display_char."</span>";
				$output .= "<span class='avia_iconbox_title' >".$atts['label']."</span>";
				$output .= "</a>";
				
				$output =  "<div class='avia-button-wrap avia-button-".$atts['position']." ".$meta['el_class']."'>".$output."</div>";
				
				return $output;
			}
			
			
			
	
	}
}
