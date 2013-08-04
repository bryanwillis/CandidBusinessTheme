<?php
/**
 * Sidebar
 * Displays one of the registered Widget Areas of the theme
 */
 
if ( !class_exists( 'avia_sc_team' ) ) 
{
	class avia_sc_team extends aviaShortcodeTemplate
	{
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['name']		= __('Team Member', 'avia_framework' );
				$this->config['tab']		= __('Content Elements', 'avia_framework' );
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-team.png";
				$this->config['order']		= 35;
				$this->config['target']		= 'avia-target-insert';
				$this->config['shortcode'] 	= 'av_team_member';
				$this->config['shortcode_nested'] = array('av_team_icon');
				$this->config['tooltip'] 	= __('Display a team members image with additional information', 'avia_framework' );
			}
			
			
			function extra_assets()
			{
				if(is_admin())
				{
					$ver = AviaBuilder::VERSION;
					wp_enqueue_script('avia_tab_toggle_js' , AviaBuilder::$path['assetsURL'].'js/avia-tab-toggle.js' , array('avia_modal_js'), $ver, TRUE );
				}
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
						
						array(	
									"name" 	=> __("Team Member Name", 'avia_framework' ),
									"desc" 	=> __("Name of the person", 'avia_framework' ) ,
									"id" 	=> "name",
									"std" 	=> "John Doe",
									"type" 	=> "input"),
									
						array(	
									"name" 	=> __("Team Member Job title", 'avia_framework' ),
									"desc" 	=> __("Job title of the person.", 'avia_framework' ) ,
									"id" 	=> "job",
									"std" 	=> "",
									"type" 	=> "input"),
									
						array(	
							"name" 	=> __("Team Member Image",'avia_framework' ),
							"desc" 	=> __("Either upload a new, or choose an existing image from your media library",'avia_framework' ),
							"id" 	=> "src",
							"type" 	=> "image",
							"title" => __("Insert Image",'avia_framework' ),
							"button" => __("Insert",'avia_framework' ),
							"std" 	=> ""),
						
							
						array(	
							"name" 	=> __("Team Member Description",'avia_framework' ),
							"desc" 	=> __("Enter a few words that describe the person",'avia_framework' ),
							"id" 	=> "description",
							"type" 	=> "textarea",
							"std" 	=> ""),
						
												
						array(	
							"name" => __("Add/Edit Social Service or Icon Links", 'avia_framework' ),
							"desc" => __("Bellow each Team Member you can add Icons that link to destinations like facebook page, twitter account etc.", 'avia_framework' ),
							"type" 			=> "modal_group", 
							"id" 			=> "content",
							"modal_title" 	=> __("Edit Icon Link", 'avia_framework' ),
							"std"			=> array(
							
													),
							
													
							'subelements' 	=> array(	
									
									array(	
									"name" 	=> __("Hover Text", 'avia_framework' ),
									"desc" 	=> __("Text that appears if you place your mouse above the Icon", 'avia_framework' ) ,
									"id" 	=> "title",
									"std" 	=> "Tab Title",
									"type" 	=> "input"),
									
									 array(	
									"name" 	=> __("Icon Link", 'avia_framework' ),
									"desc" 	=> __("Enter the URL of the Page you want to link to", 'avia_framework' ),
									"id" 	=> "link",
									"type" 	=> "input",
									"std" 	=> "http://"),
									
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
										"name" 	=> __("Tab Icon",'avia_framework' ),
										"desc" 	=> __("Select an icon for your tab title bellow",'avia_framework' ),
										"id" 	=> "icon",
										"type" 	=> "iconfont",
										"font"	=> "entypo-fontello",
										"folder"=> AviaBuilder::$path['assetsURL']."fonts/",
										"chars"	=> AviaBuilder::$path['pluginPath'].'assets/fonts/entypo-fontello-charmap.php',
										"std" 	=> "212",
										)
									)	           
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
				$templateNAME  	= $this->update_template("name", "{{name}}");
				$templateIMG 	= $this->update_template("src", "<img src='{{src}}' alt=''/>");
				$templateJob 	= $this->update_template("job", "{{job}}");
				
				$params['innerHtml'] = "";
				
				if(empty($params['args']['src']))
				{
					$params['innerHtml'].= "<div class='avia_image_container' {$templateIMG}>";
					$params['innerHtml'].= "	<img src='".$this->config['icon']."' title='".$this->config['name']."' alt='' />";
					$params['innerHtml'].= "	<div class='avia-element-label'>".$this->config['name']."</div>";
					$params['innerHtml'].= "</div>";
				}
				else
				{
					$params['innerHtml'].= "<div class='avia_image_container' {$templateIMG}><img src='".$params['args']['src']."' alt='' /></div>";
				}
				
				$params['innerHtml'].= "<div class='avia-element-name' {$templateNAME} >".$params['args']['name']."</div>";
				$params['innerHtml'] .= "	<span class='avia_job_container_inner' {$templateJob} >".$params['args']['job']."</span>";
				
				return $params;
			}
			
			
			/**
			 * Editor Sub Element - this function defines the visual appearance of an element that is displayed within a modal window and on click opens its own modal window
			 * Works in the same way as Editor Element
			 * @param array $params this array holds the default values for $content and $args. 
			 * @return $params the return array usually holds an innerHtml key that holds item specific markup.
			 */
			function editor_sub_element($params)
			{	
				$template  = $this->update_template("title", "{{title}}");
	
				$icon_el = $this->elements[4]['subelements'][3]; 
		
				$chars = $icon_el['chars'];
				if(!is_array($chars))
				{
					include($icon_el['chars']);
				}
				
				$display_char = isset($chars[($params['args']['icon'] - 1)]) ? $chars[($params['args']['icon'] - 1)] : $chars[0];
		
				$params['innerHtml']  = "";
				$params['innerHtml'] .= "<div class='avia_title_container'>";
				$params['innerHtml'] .= "	<span data-update_with='icon_fakeArg' class='avia_tab_icon avia-font-".$icon_el['font']."'>".$display_char."</span>";
				$params['innerHtml'] .= "	<span class='avia_title_container_inner' {$template} >".$params['args']['title']."</span>";
				$params['innerHtml'] .= "</div>";
				
				
				
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
				$atts =  shortcode_atts(array('name' => '', 
			                                 'src' => '', 
			                                 'description' => '',
			                                 'job' => '',
			                                 ), $atts);
				extract($atts);

				$socials = ShortcodeHelper::shortcode2array($content);
				
				$output  = "";
				$output .= "<div class='avia-team-member ".$meta['el_class']."'>";
				if($src)
				{
					$output.= "<div class='team-img-container'>";
					$output.= "<img class='avia_image avia_image_team' src='".$src."' alt=''  />";
					
					if(!empty($socials))
					{
						$icon_el = $this->elements[4]['subelements'][3]; 
			
						$chars = $icon_el['chars'];
						if(!is_array($chars))
						{
							include($icon_el['chars']);
						}
					
						$output .= "<div class='team-social'>";
						
							$output .= "<div class='team-social-inner'>";
							
							foreach($socials as $social)
							{
								//set defaults
								$social['attr'] =  shortcode_atts(array('link' => '',  'link_target' => '', 'icon' => '1','title' => '' ), $social['attr']);
								
								//build link for each social item
								$tooltip = $social['attr']['title'] ? 'data-avia-tooltip="'.$social['attr']['title'].'"' : "";
								$target  = $social['attr']['link_target'] ? "target='_blank'" : "";
								
								//apply special class in case its a link to a known social media service
								$social_class = $this->get_social_class($social['attr']['link']);
								
								
								$output.= "<a {$tooltip} {$target} class='{$social_class} avia-team-icon avia-font-".$icon_el['font']."' href='".$social['attr']['link']."'>";
								$output.= isset($chars[($social['attr']['icon'] - 1)]) ? $chars[($social['attr']['icon'] - 1)] : $chars[0];
								$output.= "</a>";
							}
						
							$output .= "</div>";
							
						$output .= "</div>";
					}
					$output .= "</div>";
					
				}
				
				if($name)
				{
					$output.= "<h3 class='team-member-name'>{$name}</h3>";
				}
				
				if($job)
				{
					$output.= "<div class='team-member-job-title'>{$job}</div>";
				}
				
				if($description)
				{
					$output.= "<div class='team-member-description'>".wpautop( ShortcodeHelper::avia_remove_autop($description) )."</div>";
				}
				
				
				
				$output .= "</div>";
				return $output;
			}
			
			function get_social_class($link)
			{
				$class = "";
				$services = array(
					'facebook',
					'youtube',
					'twitter',
					'pinterest',
					'tumblr',
					'flickr',
					'linkedin',
					'dribbble',
					'behance',
					'github',
					'vimeo',
					'plus.google',
					'myspace',
					'forrst',
					'skype'
				);
				
				foreach($services as $service)
				{
					if(strpos($link, $service) !== false) $class .= " ".str_replace('.','-',$service);
				}
				
				return $class;
			}
			
			
			
	
	}
}
