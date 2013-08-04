<?php
/*function that checks if the avia builder was already included*/
function avia_builder_plugin_enabled()
{
	if (class_exists( 'AviaBuilder' )) { return true; }
	return false;
}


//set the folder that contains the shortcodes
function add_shortcode_folder($paths)
{
	$paths = array(dirname(__FILE__) ."/avia-shortcodes/");
	return $paths;
}

add_filter('avia_load_shortcodes','add_shortcode_folder');



//set the folder that contains assets like js and imgs
function avia_builder_plugins_url($url)
{
	$url = get_template_directory_uri()."/config-templatebuilder/avia-template-builder/";
	return $url;
}


add_filter('avia_builder_plugins_url','avia_builder_plugins_url');




//check if the builder was included via plugin. if not include it now via theme
if(!avia_builder_plugin_enabled())
{
	require_once( dirname(__FILE__) . '/avia-template-builder/php/template-builder.class.php' );
	
	//define( 'AVIA_BUILDER_TEXTDOMAIN',  'avia_framework' );
	
	$builder = new AviaBuilder();
	
	//activates the builder safe mode. this hides the shortcodes that are built with the content builder from the default wordpress content editor. 
	//can also be set to "debug", to show shortcode content and extra shortcode container field
	$builder->setMode( 'safe' ); 
}

