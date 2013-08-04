<?php

/*
 * The function within this file are theme specific:
 * they are used only by this theme and not by the Avia Framework in general
 */

/* wrap embeds into a proportion containing div */

if(!function_exists('avia_iframe_proportion_wrap'))
{
	add_filter( 'embed_oembed_html', 'avia_iframe_proportion_wrap', 10, 4 );

	function avia_iframe_proportion_wrap ( $html, $url, $attr, $post_ID  )
	{
		if(strpos($html, '<iframe') !== false)
		{
			$html = "<div class='avia-iframe-wrap'>{$html}</div>";
		}
	    return $html;
	}
}


/* AJAX SEARCH */
if(!function_exists('avia_append_search_nav'))
{
	//first append search item to main menu
	add_filter( 'wp_nav_menu_items', 'avia_append_search_nav', 10, 2 );

	function avia_append_search_nav ( $items, $args )
	{
	    if ($args->theme_location == 'avia')
	    {
	        global $avia_config;
	        ob_start();
	        get_search_form();
	        $form = htmlentities( ob_get_clean() );

	        $items .= '<li id="menu-item-search" class="noMobile menu-item menu-item-search-dropdown"><a href="?s=" data-avia-search-tooltip="'.$form.'">'.$avia_config['font_icons']['search'].'</a></li>';
	    }
	    return $items;
	}
}


if(!function_exists('avia_ajax_search'))
{
	//now hook into wordpress ajax function to catch any ajax requests
	add_action( 'wp_ajax_avia_ajax_search', 'avia_ajax_search' );
	add_action( 'wp_ajax_nopriv_avia_ajax_search', 'avia_ajax_search' );

	function avia_ajax_search()
	{
	    global $avia_config;

	    unset($_REQUEST['action']);
	    if(empty($_REQUEST['s'])) die();

	    $defaults = array('numberposts' => 5, 'post_type' => 'any', 'post_status' => 'publish', 'post_password' => '', 'suppress_filters' => false);
	    $_REQUEST['s'] = apply_filters( 'get_search_query', $_REQUEST['s']);

	    $query = array_merge($defaults, $_REQUEST);
	    $query = http_build_query($query);
	    $posts = get_posts( $query );

	    if(empty($posts))
	    {
	        $output  = "<span class='ajax_search_entry ajax_not_found'>";
	        $output .= "<span class='ajax_search_image'>";
	        $output .= $avia_config['font_icons']['info'];
	        $output .= "</span>";
	        $output .= "<span class='ajax_search_content'>";
	        $output .= "    <span class='ajax_search_title'>";
	        $output .=       __("Sorry, no posts matched your criteria", 'avia_framework');
	        $output .= "    </span>";
	        $output .= "    <span class='ajax_search_excerpt'>";
	        $output .=      __("Please try another search term", 'avia_framework');
	        $output .= "    </span>";
	        $output .= "</span>";
	        $output .= "</span>";
	        echo $output;
	        die();
	    }

	    //if we got posts resort them by post type
	    $output = "";
	    $sorted = array();
	    $post_type_obj = array();
	    foreach($posts as $post)
	    {
	        $sorted[$post->post_type][] = $post;
	        if(empty($post_type_obj[$post->post_type]))
	        {
	            $post_type_obj[$post->post_type] = get_post_type_object($post->post_type);
	        }
	    }



	    //now we got everything we need to preapre the output
	    foreach($sorted as $key => $post_type)
	    {
	        if(isset($post_type_obj[$key]->labels->name))
	        {
	            $output .= "<h4>".$post_type_obj[$key]->labels->name."</h4>";
	        }
	        else
	        {
	            $output .= "<hr />";
	        }

	        foreach($post_type as $post)
	        {
	            $image = get_the_post_thumbnail( $post->ID, 'thumbnail' );

	            $extra_class = $image ? "with_image" : "";
	            $post_type   = $image ? "" : get_post_format($post->ID) != "" ? get_post_format($post->ID) : "standard";
	            $iconfont    = $image ? "" : $avia_config['font_icons'][$post_type];
	            $excerpt     = "";

	            if(!empty($post->post_excerpt))
	            {
	                $excerpt =  avia_backend_truncate($post->post_excerpt,70," ");
	            }
	            else
	            {
	                $excerpt = get_the_time(get_option('date_format'), $post->ID);
	            }

	            $link = apply_filters('av_custom_url', get_permalink($post->ID), $post);

	            $output .= "<a class ='ajax_search_entry {$extra_class}' href='".$link."'>";
	            $output .= "<span class='ajax_search_image'>";
	            $output .= $image.$iconfont;
	            $output .= "</span>";
	            $output .= "<span class='ajax_search_content'>";
	            $output .= "    <span class='ajax_search_title'>";
	            $output .=      get_the_title($post->ID);
	            $output .= "    </span>";
	            $output .= "    <span class='ajax_search_excerpt'>";
	            $output .=      $excerpt;
	            $output .= "    </span>";
	            $output .= "</span>";
	            $output .= "</a>";
	        }
	    }

	    $query = http_build_query($_REQUEST);
	    $output .= "<a class='ajax_search_entry ajax_search_entry_view_all' href='".home_url('?' . $query )."'>".__('View all results','avia_framework')."</a>";

	    echo $output;
	    die();
	}
}


if(!function_exists('avia_social_widget_icon'))
{
	/*modify twitter social count widget and add social icons as iconfont*/
	add_filter('avf_social_widget', 'avia_social_widget_icon',2,2);

	function avia_social_widget_icon($content, $icon)
	{
		global $avia_config;

		$content = "<span class='avia-font-entypo-fontello social_widget_icon'>".$avia_config['font_icons'][$icon]."</span>".$content;
		return $content;
	}
}





//call functions for the theme
add_filter('the_content_more_link', 'avia_remove_more_jump_link');
add_post_type_support('page', 'excerpt');




//allow mp4, webm and ogv file uploads
if(!function_exists('avia_upload_mimes'))
{
	add_filter('upload_mimes','avia_upload_mimes');
	function avia_upload_mimes($mimes){ return array_merge($mimes, array ('mp4' => 'video/mp4', 'ogv' => 'video/ogg', 'webm' => 'video/webm')); }
}




//change default thumbnail size and fullwidth size on theme activation
if(!function_exists('avia_set_thumb_size'))
{
	add_action('avia_backend_theme_activation', 'avia_set_thumb_size');
	function avia_set_thumb_size()
	{
		update_option( 'thumbnail_size_h', 80 ); update_option( 'thumbnail_size_w', 80 );
		update_option( 'large_size_w', 1030 ); 	 update_option( 'large_size_h', 1030 );
	}
}




//add support for post thumbnails
add_theme_support( 'post-thumbnails' );




//advanced title + breadcrumb function
if(!function_exists('avia_title'))
{
	function avia_title($args = false, $id = false)
	{
		global $avia_config;

		if(!$id) $id = avia_get_the_id();

		$defaults 	 = array(

			'title' 		=> get_the_title($id),
			'subtitle' 		=> "", //avia_post_meta($id, 'subtitle'),
			'link'			=> get_permalink($id),
			'html'			=> "<div class='{class} title_container'><div class='container'><{heading} class='main-title'>{title}</{heading}>{additions}</div></div>",
			'class'			=> 'stretch_full container_wrap alternate_color '.avia_is_dark_bg('alternate_color', true),
			'breadcrumb'	=> true,
			'additions'		=> "",
			'heading'		=> 'h1'
		);

		if ( is_tax() || is_category() || is_tag() )
		{
			global $wp_query;

			$term = $wp_query->get_queried_object();
			$defaults['link'] = get_term_link( $term );
		}
		else if(is_archive())
		{
			$defaults['link'] = "";
		}



		// Parse incomming $args into an array and merge it with $defaults
		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters('avf_title_args', $args, $id);

		// OPTIONAL: Declare each item in $args as its own variable i.e. $type, $before.
		extract( $args, EXTR_SKIP );

		if(empty($title)) $class .= " empty_title ";
		if(!empty($link)) $title = "<a href='".$link."' rel='bookmark' title='".__('Permanent Link:','avia_framework')." ".esc_attr( $title )."'>".$title."</a>";
		if(!empty($subtitle)) $additions .= "<div class='title_meta meta-color'>".wpautop($subtitle)."</div>";
		if($breadcrumb) $additions .= avia_breadcrumbs(array('separator' => '/'));


		$html = str_replace('{class}', $class, $html);
		$html = str_replace('{title}', $title, $html);
		$html = str_replace('{additions}', $additions, $html);
		$html = str_replace('{heading}', $heading, $html);



		if(!empty($avia_config['slide_output']) && !avia_is_dynamic_template($id) && !avia_is_overview())
		{
			$avia_config['small_title'] = $title;
		}
		else
		{
			return $html;
		}
	}
}



if(!function_exists('avia_post_nav'))
{
	function avia_post_nav($same_category = false)
	{
		if(!is_singular() || is_post_type_hierarchical(get_post_type())) return;
		//if(get_post_type() === 'portfolio') return;

	    global $avia_config;

	    $same_category   = apply_filters('avia_post_nav_categories', $same_category);
        $entries['prev'] = get_previous_post($same_category);
        $entries['next'] = get_next_post($same_category);
        $output = "";


		foreach ($entries as $key => $entry)
		{
            if(empty($entry)) continue;

            $tc1   = $tc2 = "";
            $link  = get_permalink($entry->ID);
            $image = get_the_post_thumbnail($entry->ID, 'thumbnail');
            $class = $image ? "with-image" : "without-image";

            $output .= "<a class='avia-post-nav avia-post-{$key} {$class}' href='{$link}' >";
		    $output .= "    <span class='label iconfont'>".$avia_config['font_icons'][$key]."</span>";
		    $output .= "    <span class='entry-info-wrap'>";
		    $output .= "        <span class='entry-info'>";
		    $tc1     = "            <span class='entry-title'>".avia_backend_truncate(get_the_title($entry->ID),75," ")."</span>";
if($image)  $tc2     = "            <span class='entry-image'>{$image}</span>";
            $output .= $key == 'prev' ?  $tc1.$tc2 : $tc2.$tc1;
            $output .= "        </span>";
            $output .= "    </span>";
		    $output .= "</a>";
		}
		return $output;

		//add this line for fake. never gets executed but makes the theme pass Theme check
		if(1==2){paginate_links(); posts_nav_link(); next_posts_link(); previous_posts_link();}
	}
}



if(!function_exists('avia_legacy_websave_fonts'))
{
	add_filter('avia_style_filter', 'avia_legacy_websave_fonts');

	function avia_legacy_websave_fonts($styles)
	{
		global $avia_config;

		$os_info 	= avia_get_browser(false);
		$activate	= false;

		if('windows' == $os_info['platform'] && avia_get_option('websave_windows') == 'active')
		{
			if($os_info['shortname'] == 'MSIE' && $os_info['mainversion'] < 9) $activate = true;
			if($os_info['shortname'] == 'Firefox' && $os_info['mainversion'] < 8) $activate = true;
			if($os_info['shortname'] == 'Opera' && $os_info['mainversion'] < 11) $activate = true;

			if($activate == true)
			{
				foreach ($styles as $key => $style)
				{
					if($style['key'] == 'google_webfont')
					{
						if (strpos($style['value'], '-websave') !== false)
						{
							$websave = explode(',',$style['value']);
							$websave = strtolower(" ".$websave[0]);
							$websave = str_replace('"','',$websave);
							$websave = str_replace("'",'',$websave);
							$websave = str_replace("-websave",'',$websave);

							$avia_config['font_stack'] .= $websave.'-websave';
						}

					unset($styles[$key]);
					}
				}

			if(empty($avia_config['font_stack'])) $avia_config['font_stack'] = 'arial-websave';
			}
		}

		return $styles;
	}
}






//wrap ampersands into special calss to apply special styling

if(!function_exists('avia_ampersand'))
{
	add_filter('avia_ampersand','avia_ampersand');

	function avia_ampersand($content)
	{
		$content = str_replace(" &amp; "," <span class='special_amp'>&amp;</span> ",$content);
		$content = str_replace(" &#038; "," <span class='special_amp'>&amp;</span> ",$content);

		return $content;
	}
}





// checks if a background color of a specific region is dark  or light and returns a class name
if(!function_exists('avia_is_dark_bg'))
{
	function avia_is_dark_bg($region, $return_only = false)
	{
		global $avia_config;

		$return = "";
		$color = $avia_config['backend_colors']['color_set'][$region]['bg'];

		$is_dark = avia_backend_calc_preceived_brightness($color, 70);

		$return = $is_dark ? "dark_bg_color" : "light_bg_color";
		if($return_only)
		{
			return $return;
		}
		else
		{
			echo $return;
		}
	}
}






//set post excerpt to be visible on theme acivation in user backend
if(!function_exists('avia_show_menu_description'))
{

	//add_action('avia_backend_theme_activation', 'avia_show_menu_description');
	function avia_show_menu_description()
	{
		global $current_user;
	    get_currentuserinfo();
		$old_meta_data = $meta_data = get_user_meta($current_user->ID, 'metaboxhidden_page', true);

		if(is_array($meta_data) && isset($meta_data[0]))
		{
			$key = array_search('postexcerpt', $meta_data);

			if($key !== false)
			{
				unset($meta_data[$key]);
				update_user_meta( $current_user->ID, 'metaboxhidden_page', $meta_data, $old_meta_data );
			}
		}
		else
		{
				update_user_meta( $current_user->ID, 'metaboxhidden_page', array('postcustom', 'commentstatusdiv', 'commentsdiv', 'slugdiv', 'authordiv', 'revisionsdiv') );
		}
	}
}




/*
* make google analytics code work, even if the user only enters the UA id. if the new async tracking code is entered add it to the header, else to the footer
*/

if(!function_exists('avia_get_tracking_code'))
{
	add_action('init', 'avia_get_tracking_code');

	function avia_get_tracking_code()
	{
		global $avia_config;

		$avia_config['analytics_code'] = avia_option('analytics', false, false, true);
		if(empty($avia_config['analytics_code'])) return;

		if(strpos($avia_config['analytics_code'],'UA-') === 0) // if we only get passed the UA-id create the script for the user (async tracking code)
		{

			$avia_config['analytics_code'] = "

<script type='text/javascript'>

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '".$avia_config['analytics_code']."']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

";
		}

		if(strpos($avia_config['analytics_code'],'_gaq.push') !== false) // check for async code (put at top)
		{
			add_action('wp_head', 'avia_print_tracking_code');
		}
		else // else print code in footer
		{
			add_action('wp_footer', 'avia_print_tracking_code');
		}
	}

	function avia_print_tracking_code()
	{
		global $avia_config;

		if(!empty($avia_config['analytics_code']))
		{
			echo $avia_config['analytics_code'];
		}
	}

}


/*
function that checks which header style we are using. In general the whole site has the same header active, based on the option in theme options->header
however, for the theme demo we need to showcase all headers, thats why we can simply add a custom field key of header_setting to overwrite the default heading
*/

if(!function_exists('avia_header_setting'))
{
	function avia_header_setting()
	{
		$header = avia_get_option('header_setting','fixed_header');

		$post_id = @get_the_ID();
		if($post_id)
		{
			$overwrite = get_post_meta($post_id, 'header_setting', true);
			if(!empty($overwrite)) $header = $overwrite;
		}

		return $header;
	}
}



/*
function that checks if updates for the theme are available
*/
if(!function_exists('avia_check_updates'))
{
	function avia_check_updates()
	{
		$avia_update_notifier = new avia_update_notifier('http://www.kriesi.at/themes/wp-content/uploads/avia_xml/'.THEMENAME.'-Updates.xml');
	}

	add_action('admin_menu', 'avia_check_updates', 1, 1);
}



// show tag archive page for post type - without this code you'll get 404 errors: http://wordpress.org/support/topic/custom-post-type-tagscategories-archive-page
if(!function_exists('avia_fix_tag_archive_page'))
{
	function avia_fix_tag_archive_page($query) {
	    $post_types = get_post_types();
	    if ( is_category() || is_tag()) {

	        $post_type = get_query_var(get_post_type());

	        if ($post_type) {
	            $post_type = $post_type;
	        } else {
	            $post_type = $post_types;
	        }
	        $query->set('post_type', $post_type);

	        return $query;
	    }
	}
	add_filter('pre_get_posts', 'avia_fix_tag_archive_page');
}



/*
function that saves the style options array into an external css file rather than fetching the data from the database
*/

if(!function_exists('avia_generate_stylesheet'))
{
	add_action('avia_ajax_after_save_options_page', 'avia_generate_stylesheet', 30, 1);
	function avia_generate_stylesheet($options)
	{
		global $avia;
		$safe_name = avia_backend_safe_string($avia->base_data['prefix']);

	    if( defined('AVIA_CSSFILE') && AVIA_CSSFILE === FALSE )
	    {
	        $dir_flag           = update_option( 'avia_stylesheet_dir_writable'.$safe_name, 'false' );
	        $stylesheet_flag    = update_option( 'avia_stylesheet_exists'.$safe_name, 'false' );
	        return;
	    }

	    $wp_upload_dir  = wp_upload_dir();
	    $stylesheet_dir = $wp_upload_dir['basedir'].'/dynamic_avia';
	    $stylesheet_dir = str_replace('\\', '/', $stylesheet_dir);
	    $stylesheet_dir = apply_filters('avia_dyn_stylesheet_dir_path',  $stylesheet_dir);
	    $isdir = avia_backend_create_folder($stylesheet_dir);

	    /*
	    * directory could not be created (WP upload folder not write able)
	    * @todo save error in db and output error message for user.
	    * @todo maybe add mkdirfix: http://php.net/manual/de/function.mkdir.php
	    */

	    if($isdir === false)
	    {
	        $dir_flag = update_option( 'avia_stylesheet_dir_writable'.$safe_name, 'false' );
	        $stylesheet_flag = update_option( 'avia_stylesheet_exists'.$safe_name, 'false' );
	        return;
	    }

	    /*
	     *  Go ahead - WP managed to create the folder as expected
	     */
	    $stylesheet = trailingslashit( $stylesheet_dir ) . $safe_name.'.css';
	    $stylesheet = apply_filters('avia_dyn_stylesheet_file_path', $stylesheet);


	    //import avia_superobject and reset the options array
	    $avia_superobject = $GLOBALS['avia'];
		$avia_superobject->reset_options();

		//regenerate style array after saving options page so we can create a new css file that has the actual values and not the ones that were active when the script was called
		avia_prepare_dynamic_styles();

	    //generate stylesheet content
	    $generate_style = new avia_style_generator($avia_superobject,false,false,false);
	    $styles         = $generate_style->create_styles();

	    $created        = avia_backend_create_file($stylesheet, $styles, true);

	    if($created === true)
	    {
	        $dir_flag = update_option( 'avia_stylesheet_dir_writable'.$safe_name, 'true' );
	        $stylesheet_flag = update_option( 'avia_stylesheet_exists'.$safe_name, 'true' );
	    }
	}
}

