<?php
 

if(!function_exists('avia_find_layersliders'))
{
	function avia_find_layersliders($names_only = false)
	{
		// Get WPDB Object
	    global $wpdb;
	 
	    // Table name
	    $table_name = $wpdb->prefix . "layerslider";
	 
	    // Get sliders
	    $sliders = $wpdb->get_results( "SELECT * FROM $table_name WHERE flag_hidden = '0' AND flag_deleted = '0' ORDER BY date_c ASC LIMIT 100" );
	 	
	 	if(empty($sliders)) return;
	 	
	 	if($names_only)
	 	{
	 		$new = array();
	 		foreach($sliders as $key => $item) 
		    {
		    	if(empty($item->name)) $item->name = __("(Unnamed Slider)","avia_framework");
		       $new[$item->name] = $item->id;
		    }
		    
		    return $new;
	 	}
	 	
	 	return $sliders;
	}
}


/********************************************************/
/*            Action to import sample slider  - modified version          */
/********************************************************/

if(!function_exists('avia_import_sample_slider'))
{
	function avia_import_sample_slider($path = "avia-samples/") {
	
		// Base64 encoded, serialized slider export code
		$sample_slider = json_decode(base64_decode(file_get_contents(dirname(__FILE__)."/LayerSlider/{$path}avia-samples.txt")), true);
	
		// Iterate over the sliders
		foreach($sample_slider as $sliderkey => $slider) {
	
			// Iterate over the layers
			foreach($sample_slider[$sliderkey]['layers'] as $layerkey => $layer) {
	
				// Change background images if any
				if(!empty($sample_slider[$sliderkey]['layers'][$layerkey]['properties']['background'])) {
					$sample_slider[$sliderkey]['layers'][$layerkey]['properties']['background'] = $GLOBALS['lsPluginPath'].$path.basename($layer['properties']['background']);
				}
	
				// Change thumbnail images if any
				if(!empty($sample_slider[$sliderkey]['layers'][$layerkey]['properties']['thumbnail'])) {
					$sample_slider[$sliderkey]['layers'][$layerkey]['properties']['thumbnail'] = $GLOBALS['lsPluginPath'].$path.basename($layer['properties']['thumbnail']);
				}
	
				// Iterate over the sublayers
				foreach($layer['sublayers'] as $sublayerkey => $sublayer) {
	
					// Only IMG sublayers
					if($sublayer['type'] == 'img') {
						$sample_slider[$sliderkey]['layers'][$layerkey]['sublayers'][$sublayerkey]['image'] = $GLOBALS['lsPluginPath'].$path.basename($sublayer['image']);
					}
				}
			}
		}
	
		// Get WPDB Object
		global $wpdb;
	
		// Table name
		$table_name = $wpdb->prefix . "layerslider";
	
		// Append duplicate
		foreach($sample_slider as $key => $val) {
	
			// Insert the duplicate
			$wpdb->query(
				$wpdb->prepare("INSERT INTO $table_name
									(name, data, date_c, date_m)
								VALUES (%s, %s, %d, %d)",
								$val['properties']['title'],
								json_encode($val),
								time(),
								time()
				)
			);
		}
	
	}
}







/**************************/
/* Include LayerSlider WP */
/**************************/
if(is_admin())
{	
	add_action('init', 'avia_include_layerslider' , 45 );
}
else
{	
	add_action('wp', 'avia_include_layerslider' , 45 );
}

function avia_include_layerslider()
{
	if(!is_admin() && !post_has_layerslider()) return;
	
	// Path for LayerSlider WP main PHP file
	$layerslider = get_template_directory() . '/config-layerslider/LayerSlider/layerslider.php';
	$themeNice	 = avia_backend_safe_string(THEMENAME);

	// Check if the file is available and the user didnt activate the layerslide plugin to prevent warnings
	if(file_exists($layerslider)) 
	{
		if(function_exists('layerslider')) //layerslider plugin is active
		{
			if(get_option("{$themeNice}_layerslider_activated", '0') == '0') 
			{
		        //import sample sliders
		 		avia_import_sample_slider();
		 		
		        // Save a flag that it is activated, so this won't run again
		        update_option("{$themeNice}_layerslider_activated", '1');
		    }
		}
		else //not active, use theme version instead
		{
		    // Include the file
		    include $layerslider;
		    
		    $GLOBALS['lsPluginPath'] 	= get_template_directory_uri() . '/config-layerslider/LayerSlider/';
		    $GLOBALS['lsAutoUpdateBox'] = false;
		 
		    // Activate the plugin if necessary
		    if(get_option("{$themeNice}_layerslider_activated", '0') == '0') {
		 
		        // Run activation script
		        layerslider_activation_scripts();
		        
		        //import sample sliders
		 		avia_import_sample_slider();
		 		
		        // Save a flag that it is activated, so this won't run again
		        update_option("{$themeNice}_layerslider_activated", '1');
		    }
	    }
	}
}

