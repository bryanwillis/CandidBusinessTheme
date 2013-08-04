<?php 
global $avia_config;

##############################################################################
# Display the sidebar
##############################################################################

$default_sidebar = true;
$sidebar_pos 	 = avia_layout_class('main', false);

if(strpos($sidebar_pos, 'sidebar_left')  !== false) $sidebar = 'left';	
if(strpos($sidebar_pos, 'sidebar_right') !== false) $sidebar = 'right';	


//if the layout hasnt the sidebar keyword defined we dont need to display one
if(empty($sidebar)) return;



echo "<div class='sidebar sidebar_".$sidebar." ".avia_layout_class( 'sidebar', false )." units'>";

	echo "<div class='inner_sidebar extralight-border'>";

	/*
	* Display a subnavigation for pages that is automatically generated, so the users doesnt need to work with widgets
	*/
	$sidebar_menu = "";
	
	$subNav = avia_get_option('page_nesting_nav');
	$the_id = @get_the_ID();
	
	if($subNav && !empty($the_id) && is_page())
	{
		global $post;
		$subNav = false;
		$parent = $post->ID;
		$sidebar_menu = "";
		
		if (!empty($post->post_parent))	
		{
			if(isset($post->ancestors)) $ancestors  = $post->ancestors;
			if(!isset($ancestors)) $ancestors  = get_post_ancestors($post->ID);
			$root		= count($ancestors)-1;
			$parent 	= $ancestors[$root];
		} 
		
		$args = array('title_li'=>'', 'child_of'=>$parent, 'echo'=>0, 'sort_column'  => 'menu_order, post_title');
		$children = wp_list_pages($args);

		if ($children) 
		{ 
			$default_sidebar = false;
			$sidebar_menu .= "<div class='widget widget_nav_menu'><ul class='nested_nav'>";
			$sidebar_menu .= $children;
			$sidebar_menu .= "</ul></div>";
		} 
	}

	echo apply_filters('avia_sidebar_menu_filter', $sidebar_menu);
	
	$custom_sidebar = "";
	if(!empty($the_id) && is_singular())
	{
		$custom_sidebar = get_post_meta($the_id, 'sidebar', true);
	}
	
	if($custom_sidebar)
	{
		dynamic_sidebar($custom_sidebar);
		$default_sidebar = false;
	}
	else
	{
		if(empty($avia_config['currently_viewing'])) $avia_config['currently_viewing'] = 'page';
		
		// single shop sidebars
		if ($avia_config['currently_viewing'] == 'shop_single' && dynamic_sidebar('Single Product Pages') ) : $default_sidebar = false; endif;
		
		// general shop sidebars
		if ($avia_config['currently_viewing'] == 'shop' && dynamic_sidebar('Shop Overview Page') ) : $default_sidebar = false; endif;
		
		// general shop sidebars
		if ($avia_config['currently_viewing'] == 'shop_single') $default_sidebar = false;
		if ($avia_config['currently_viewing'] == 'shop_single' && dynamic_sidebar('Single Product Pages') ) : $default_sidebar = false; endif;
	
		// general blog sidebars
		if ($avia_config['currently_viewing'] == 'blog' && dynamic_sidebar('Sidebar Blog') ) : $default_sidebar = false; endif;
						
		// general pages sidebars
		if ($avia_config['currently_viewing'] == 'page' && dynamic_sidebar('Sidebar Pages') ) : $default_sidebar = false; endif;
		
		// forum pages sidebars
		if ($avia_config['currently_viewing'] == 'forum' && dynamic_sidebar('Forum') ) : $default_sidebar = false; endif;
	
	}
	
	//global sidebar
	if (dynamic_sidebar('Displayed Everywhere')) : $default_sidebar = false; endif;


	
	//default dummy sidebar
	if ($default_sidebar)
	{
		 avia_dummy_widget(2);
		 avia_dummy_widget(3);
		 avia_dummy_widget(4);
	}
						
	echo "</div>";
	
echo "</div>";
				





      