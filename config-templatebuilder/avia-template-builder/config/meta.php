<?php
global $builder;

$boxes = array(
	array( 'title' =>__('Avia Layout Builder','avia_framework' ), 'id'=>'avia_builder', 'page'=>array('portfolio','page'), 'context'=>'normal', 'priority'=>'high', 'expandable'=>true ),
	array( 'title' =>__('Layout','avia_framework' ), 'id'=>'layout', 'page'=>array('portfolio', 'page' , 'post'), 'context'=>'side', 'priority'=>'low'),
);

$boxes = apply_filters('avf_builder_boxes', $boxes);


$elements = array(

    array(
		"slug"			=> "avia_builder",
		"name" 			=> "Visual layout editor",
		"id" 			=> "layout_editor",
		"type" 			=> array($builder,'visual_editor'),
		"tab_order"		=> array(__('Layout Elements','avia_framework' ), __('Content Elements','avia_framework' ) , __('Media Elements','avia_framework' )),
		"desc"			=>  '<h4>'.__('Quick Info & Hotkeys', 'avia_framework' )."</h4>".
							'<strong>'.__('General Info', 'avia_framework' ).'</strong>'.
							"<ul>".
							'   <li>'.__('To insert an Element either click the insert button for that element or drag the button onto the canvas', 'avia_framework' ).'</li>'.
							'   <li>'.__('If you place your mouse above the insert button a short info tooltip will appear', 'avia_framework' ).'</li>'.
							'   <li>'.__('To sort and arrange your elements just drag them to a position of your choice and release them', 'avia_framework' ).'</li>'.
							'   <li>'.__('Valid drop targets will be highlighted. Some elements like fullwidth sliders and color section can not be dropped onto other elements', 'avia_framework' ).'</li>'.
							"</ul>".
							'<strong>'.__('Edit Elements in Popup Window:', 'avia_framework' ).'</strong>'.
							"<ul>".
							'   <li>'.__('Most elements open a popup window if you click them', 'avia_framework' ).'</li>'.
							'   <li>'.__('Press TAB to navigate trough the various form fields of a popup window.', 'avia_framework' ).'</li>'.
							'   <li>'.__('Press ESC on your keyboard or the Close Button to close popup window.', 'avia_framework' ).'</li>'.
							'   <li>'.__('Press ENTER on your keyboard or the Save Button to save current state of a popup window', 'avia_framework' ).'</li>'.
							"</ul>"
	),


	array(

        "slug"	=> "layout",
        "name" 	=> "Layout",
        "desc" 	=> "Select the desired Page layout",
        "id" 	=> "layout",
        "type" 	=> "select",
        "std" 	=> "",
        "class" => "avia-style",
        "subtype" => array( "Default Layout - set in ".THEMENAME." > Layout & Options" => '',
        					"No Sidebar"       => 'fullsize',
        					"Left Sidebar"     => 'sidebar_left',
        					"Right Sidebar"    => 'sidebar_right',

        ),
		),

	array(

        "slug"	=> "layout",
        "name" 	=> "Sidebar Setting",
        "desc" 	=> "Choose a custom sidebar for this entry",
        "id" 	=> "sidebar",
        "type" 	=> "select",
        "std" 	=> "",
        "class" => "avia-style",
        "required" => array('layout','not','fullsize'),
        "subtype" => AviaHelper::get_registered_sidebars(array('Default Sidebars' => ""), array('Displayed Everywhere'))

		),
		array(

        "slug"	=> "layout",
        "name" 	=> "Header Settings",
        "desc" 	=> "Display the Header with Page Title and Breadcrumb Navigation?",
        "id" 	=> "header",
        "type" 	=> "select",
        "std" 	=> "yes",
        "class" => "avia-style",
        "subtype" => array( "Display the Header" => 'yes',
        					"Don't display the Header"  => "no",

                    )
        ),
        array(

        "slug"  => "layout",
        "name"  => "Footer Settings",
        "desc"  => "Display the footer widgets?",
        "id"    => "footer",
        "type"  => "select",
        "std"   => avia_get_option('display_widgets_socket'),
        "class" => "avia-style",
        "subtype" => array(
                        'Display the footer widgets & socket'=>'all',
                        'Display only the footer widgets (no socket)'=>'nosocket',
                        'Display only the socket (no footer widgets)'=>'nofooterwidgets',
                        'Don\'t display the socket & footer widgets'=>'nofooterarea'
                    ),

    )

);


$elements = apply_filters('avf_builder_elements', $elements);




/*
array(

        "slug"	=> "avia_builder",
        "name" 	=> "Layout",
        "desc" 	=> "Select the desired Page layout",
        "id" 	=> "layout",
        "type" 	=> "radio",
        "class" => "image_radio image_radio_layout",
        "std" 	=> "fullwidth",
        "options" => array( 'default' 		=> "Default layout",
        					'sidebar_left' 	=> "Left Sidebar",
        					'sidebar_right' => "Right Sidebar",
        					'fullwidth' 	=> "No Sidebar"
        ),

        "images" => array(  'default' 		=> AviaBuilder::$path['imagesURL']."layout-slideshow.png",
        					'sidebar_left' 	=> AviaBuilder::$path['imagesURL']."layout-left.png",
        					'sidebar_right' => AviaBuilder::$path['imagesURL']."layout-right.png",
        					'fullwidth' 	=> AviaBuilder::$path['imagesURL']."layout-fullwidth.png",
        ),
    ),
*/