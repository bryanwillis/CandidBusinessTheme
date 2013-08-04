<?php

if ( !class_exists( 'avia_sc_layerslider' )) 
{
	class avia_sc_layerslider extends aviaShortcodeTemplate
	{		
			static $slide_count = 0;
			
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['name']		= __('Advanced Layerslider', 'avia_framework' );
				$this->config['tab']		= __('Media Elements', 'avia_framework' );
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-slideshow-layer.png";
				$this->config['order']		= 10;
				$this->config['target']		= 'avia-target-insert';
				$this->config['shortcode'] 	= 'av_layerslider';
				$this->config['tooltip'] 	= __('Display a Layerslider Slideshow', 'avia_framework' );
				$this->config['tinyMCE'] 	= array('disable' => "true");
				$this->config['drag-level'] = 1;
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
				//fetch all registered slides and save them to the slides array
				$slides = avia_find_layersliders(true);
				if(empty($params['args']['id'])) $params['args']['id'] = reset($slides);				
				
				$element = array(
					'subtype' => $slides, 
					'type'=>'select', 
					'std' => $params['args']['id'], 
					'class' => 'avia-recalc-shortcode',
					'data'	=> array('attr'=>'id')
				);
				
				$inner		 = "<img src='".$this->config['icon']."' title='".$this->config['name']."' />";
				
				
				if(empty($slides))
				{
					$inner.= "<div><a target='_blank' href='".admin_url( 'admin.php?page=layerslider' )."'>".__('No Layer Slider Found. Click here to create one','avia_framework' )."</a></div>";
				}
				else
				{
					$inner .= "<div class='avia-element-label'>".$this->config['name']."</div>";
					$inner .= AviaHtmlHelper::render_element($element);
					$inner .= "<a target='_blank' href='".admin_url( 'admin.php?page=layerslider' )."'>".__('Edit Layer Slider here','avia_framework' )."</a>";
				}
				
				
				$params['class'] = "av_sidebar";
				$params['content']	 = NULL;
				$params['innerHtml'] = $inner;
				
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
				$output  = "";
				
				$skipSecond = false;
				avia_sc_layerslider::$slide_count++;
				
				//check if we got a layerslider
				global $wpdb;
				
				// Table name
				$table_name = $wpdb->prefix . "layerslider";
				
				// Get slider
				$slider = $wpdb->get_row("SELECT * FROM $table_name
										WHERE id = ".(int)$atts['id']." AND flag_hidden = '0'
										AND flag_deleted = '0'
										ORDER BY date_c DESC LIMIT 1" , ARRAY_A);
										
										
				if(!empty($slider))
				{		
					$slides = json_decode($slider['data'], true);	
					$height = $slides['properties']['height'];	
					$width  = $slides['properties']['width'];	
					$responsive = $slides['properties']['responsive'];
					$responsiveunder = $slides['properties']['responsiveunder'];
					
					$params['style'] = " style='height: ".($height+1)."px;' ";
				}
				
				
				$params['class'] = "avia-layerslider main_color avia-shadow ".$meta['el_class'];
				$params['open_structure'] = false;
				if($meta['index'] == 0) $params['close'] = false;
				if($meta['index'] != 0) $params['class'] .= " slider-not-first";
				if($meta['index'] == 0 && get_post_meta(get_the_ID(), 'header', true) != "no") $params['class'] .= " slider-not-first";
				$params['id'] = "layer_slider_".( avia_sc_layerslider::$slide_count );
				
				
				$output .=  avia_new_section($params);
				$output .= layerslider_init($atts);
				$output .= "</div>"; //close section
				
				
				//if the next tag is a section dont create a new section from this shortcode
				if(!empty($meta['siblings']['next']['tag']) && in_array($meta['siblings']['next']['tag'],  array('av_layerslider', 'av_section' ,'av_slideshow_full')))
				{
				    $skipSecond = true;
				}

				//if there is no next element dont create a new section.
				if(empty($meta['siblings']['next']['tag']))
				{
				    $skipSecond = true;
				}
				
				if(empty($skipSecond)) {
				
				$output .= avia_new_section(array('close'=>false, 'id' => "after_layer_slider_".avia_sc_layerslider::$slide_count));
				
				}
				
				return $output;
			}
	
	}
}


if(!function_exists('post_has_layerslider'))
{
	function post_has_layerslider()
	{
		if(!is_singular()) return false;
	
		if(empty(ShortcodeHelper::$tree))
		{
			$id = @get_the_ID();
			
			if(!$id) return false;
			ShortcodeHelper::$tree = get_post_meta($id, '_avia_builder_shortcode_tree', true);
		}
		
		if(is_array(ShortcodeHelper::$tree))
		{
			foreach(ShortcodeHelper::$tree as $sc)
			{
				if($sc['tag'] == 'av_layerslider') return true;
			}
		}
		return false;
	}
}