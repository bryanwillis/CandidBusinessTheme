<?php
/**
 * Post/Page Content
 *
 * Element is in Beta and by default disabled. Todo: test with layerslider elements. currently throws error bc layerslider is only included if layerslider element is detected which is not the case with the post/page element
 */

if ( !class_exists( 'avia_sc_postslider' ))
{
	class avia_sc_postslider extends aviaShortcodeTemplate
	{

		/**
		 * Create the config array for the shortcode button
		 */
		function shortcode_insert_button()
		{
			$this->config['name']		= __('Post Slider', 'avia_framework' );
			$this->config['tab']		= __('Content Elements', 'avia_framework' );
			$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-postslider.png";
			$this->config['order']		= 30;
			$this->config['target']		= 'avia-target-insert';
			$this->config['shortcode'] 	= 'av_postslider';
			$this->config['tooltip'] 	= __('Display a Slideshow of Post Entries', 'avia_framework' );
			$this->config['drag-level'] = 3;
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
						"name" 	=> __("Which Entries?", 'avia_framework' ),
						"desc" 	=> __("Select which entries should be displayed by selecting a taxonomy", 'avia_framework' ),
						"id" 	=> "link",
						"fetchTMPL"	=> true,
						"type" 	=> "linkpicker",
						"subtype"  => array( __('Display Entries from:',  'avia_framework' )=>'taxonomy'),
						"multiple"	=> 6,
						"std" 	=> "category"
				),

				array(
						"name" 	=> __("Columns", 'avia_framework' ),
						"desc" 	=> __("How many columns should be displayed?", 'avia_framework' ),
						"id" 	=> "columns",
						"type" 	=> "select",
						"std" 	=> "3",
						"subtype" => array(	__('1 Columns', 'avia_framework' )=>'2',
											__('2 Columns', 'avia_framework' )=>'2',
											__('3 Columns', 'avia_framework' )=>'3',
											__('4 Columns', 'avia_framework' )=>'4',
											__('5 Columns', 'avia_framework' )=>'5',
											)),
				array(
						"name" 	=> __("Entry Number", 'avia_framework' ),
						"desc" 	=> __("How many items should be displayed?", 'avia_framework' ),
						"id" 	=> "items",
						"type" 	=> "select",
						"std" 	=> "9",
						"subtype" => AviaHtmlHelper::number_array(1,100,1, array('All'=>'-1'))),

				array(
						"name" 	=> __("Title and Excerpt",'avia_framework' ),
						"desc" 	=> __("Choose if you want to only display the post title or title and excerpt",'avia_framework' ),
						"id" 	=> "contents",
						"type" 	=> "select",
						"std" 	=> "excerpt",
						"subtype" => array(__('Title and Excerpt','avia_framework' ) =>'excerpt',__('Title only','avia_framework' ) =>'title')),

				array(
						"name" 	=> __("Autorotation active?",'avia_framework' ),
						"desc" 	=> __("Check if the slideshow should rotate by default",'avia_framework' ),
						"id" 	=> "autoplay",
						"type" 	=> "select",
						"std" 	=> "no",
						"subtype" => array(__('Yes','avia_framework' ) =>'yes',__('No','avia_framework' ) =>'no')),

				array(
					"name" 	=> __("Slideshow autorotation duration",'avia_framework' ),
					"desc" 	=> __("Slideshow will rotate every X seconds",'avia_framework' ),
					"id" 	=> "interval",
					"type" 	=> "select",
					"std" 	=> "5",
					"required" 	=> array('autoplay','equals','yes'),
					"subtype" =>
					array('3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9','10'=>'10','15'=>'15','20'=>'20','30'=>'30','40'=>'40','60'=>'60','100'=>'100')),


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
			$params['innerHtml'] = "<img src='".$this->config['icon']."' title='".$this->config['name']."' />";
			$params['innerHtml'].= "<div class='avia-element-label'>".$this->config['name']."</div>";
			$params['content'] 	 = NULL; //remove to allow content elements
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
			if(isset($atts['link']))
			{
				$atts['link'] = explode(',', $atts['link'], 2 );
				$atts['taxonomy'] = $atts['link'][0];

				if(isset($atts['link'][1]))
				{
					$atts['categories'] = $atts['link'][1];
				}
			}

			$atts['class'] = $meta['el_class'];

			$slider = new avia_post_slider($atts);
			$slider->query_entries();
			return $slider->html();
		}

	}
}




if ( !class_exists( 'avia_post_slider' ) )
{
	class avia_post_slider
	{
		static  $slide = 0;
		private $atts;
		private $entries;

		function __construct($atts = array())
		{
			$this->atts = shortcode_atts(array(	'type'		=> 'slider', // can also be used as grid
												'style'		=> '', //no_margin
										 		'columns' 	=> '4',
		                                 		'items' 	=> '16',
		                                 		'taxonomy'  => 'category',
		                                 		'contents' 	=> 'excerpt',
		                                 		'autoplay'  => 'no',
												'animation' => 'fade',
												'paginate'	=> 'no',
												'interval'  => 5,
												'class'		=> '',
		                                 		'categories'=> array(),
		                                 		'custom_query'=> array()
		                                 		), $atts);
		}

		public function html()
		{
			global $avia_config;

			$output = "";

			if(empty($this->entries) || empty($this->entries->posts)) return $output;

			avia_post_slider::$slide ++;
			extract($this->atts);

			$extraClass 		= 'first';
			$grid 				= 'one_third';
			$image_size 		= 'portfolio';
			$post_loop_count 	= 1;
			$loop_counter		= 1;
			$autoplay 			= $autoplay == "no" ? false : true;
			$total				= $columns % 2 ? "odd" : "even";

			switch($columns)
			{
				case "1": $grid = 'av_fullwidth';  $image_size = 'large'; break;
				case "2": $grid = 'av_one_half';   break;
				case "3": $grid = 'av_one_third';  break;
				case "4": $grid = 'av_one_fourth'; $image_size = 'portfolio_small'; break;
				case "5": $grid = 'av_one_fifth';  $image_size = 'portfolio_small'; break;
			}


			$data = AviaHelper::create_data_string(array('autoplay'=>$autoplay, 'interval'=>$interval, 'animation' => $animation));

			$thumb_fallback = "";
			$output .= "<div {$data} class='avia-content-slider avia-content-{$type}-active avia-content-slider".avia_post_slider::$slide." avia-content-slider-{$total} {$class}' >";
			$output .= 		"<div class='avia-content-slider-inner'>";

				foreach ($this->entries->posts as $entry)
				{
					$the_id 	= $entry->ID;
					$parity		= $loop_counter % 2 ? 'odd' : 'even';
					$last       = $this->entries->post_count == $post_loop_count ? " post-entry-last " : "";
					$post_class = "post-entry post-entry-{$the_id} slide-entry-overview slide-loop-{$post_loop_count} slide-parity-{$parity} {$last}";
					$link 		= get_permalink($the_id);
					$excerpt	= "";
					$show_meta  = !is_post_type_hierarchical($entry->post_type);
					$commentCount = get_comments_number($the_id);
					$thumbnail  = get_the_post_thumbnail( $the_id, $image_size );
					$format 	= get_post_format( $the_id );
					if(empty($format)) $format = "standard";

					if($thumbnail)
					{
						$thumb_fallback = $thumbnail;
						$thumb_class	= "real-thumbnail";
					}
					else
					{
						$thumbnail = "<span class='avia-font-entypo-fontello fallback-post-type-icon'>". $avia_config['font_icons'][$format] ."</span><span class='slider-fallback-image'>{{thumbnail}}</span>";
						$thumb_class	= "fake-thumbnail";
					}


					if($contents == 'excerpt' || $contents == 'excerpt_read_more')
					{
						if(!empty($entry->post_excerpt))
						{
							$excerpt = $entry->post_excerpt;
						}
						else
						{
							$excerpt = avia_backend_truncate($entry->post_content, 45, ".", "…", true);
						}

                        if($contents == 'excerpt_read_more')
                        {
                            $excerpt .= '<div class="read-more-link"><a href="'.get_permalink().'" class="more-link">'.__('Read more','avia_framework').'<span class="more-link-arrow">  &rarr;</span></a></div>';
                        }
					}
                    else if($contents == 'content')
                    {
                        $excerpt = $entry->post_content;
                    }

					if($loop_counter == 1) $output .= "<div class='slide-entry-wrap'>";

					$output .= "<div class='slide-entry flex_column {$style} {$post_class} {$grid} {$extraClass} {$thumb_class}'>";
					$output .= $thumbnail ? "<a href='{$link}' data-rel='slide-".avia_post_slider::$slide."' class='slide-image' title=''>{$thumbnail}</a>" : "";
					$output .= "<div class='slide-content'>";
					$output .= "<h3 class='slide-entry-title'><a href='{$link}' title='".esc_attr(strip_tags($entry->post_title))."'>".$entry->post_title."</a></h3>";
					if($show_meta && $contents == 'excerpt')
					{
						$output .= "<div class='slide-meta'>";
						if ( $commentCount != "0" || comments_open($the_id) && $entry->post_type != 'portfolio')
						{
							$link_add = $commentCount === "0" ? "#respond" : "#comments";
							$text_add = $commentCount === "1" ? __('Comment', 'avia_framework' ) : __('Comments', 'avia_framework' );

							$output .= "<div class='slide-meta-comments'><a href='{$link}{$link_add}'>{$commentCount} {$text_add}</a></div><div class='slide-meta-del'>/</div>";
						}
						$output .= "<div class='slide-meta-time'>" .get_the_time(get_option('date_format'), $the_id)."</div>";
						$output .= "</div>";
					}
					$output .= $excerpt ? "<div class='slide-entry-excerpt'>".$excerpt."</div>" : "";
					$output .= "</div>";
					$output .= "</div>";

					$loop_counter ++;
					$post_loop_count ++;
					$extraClass = "";

					if($loop_counter > $columns)
					{
						$loop_counter = 1;
						$extraClass = 'first';
					}

					if($loop_counter == 1 || !empty($last))
					{
						$output .="</div>";
					}
				}

			$output .= 		"</div>";

			if($post_loop_count -1 > $columns && $type == 'slider')
			{
				$output .= $this->slide_navigation_arrows();
			}

			if($paginate == "yes" && $avia_pagination = avia_pagination($this->entries->max_num_pages)) $output .= "<div class='pagination-wrap pagination-slider'>{$avia_pagination}</div>";
			$output .= "</div>";

			$output = str_replace('{{thumbnail}}', $thumb_fallback, $output);

			return $output;
		}

		private function slide_navigation_arrows()
		{
			$html  = "";
			$html .= "<div class='avia-slideshow-arrows avia-slideshow-controls'>";
			$html .= 	"<a href='#next' class='prev-slide' >".__('Previous','avia_framework' )."</a>";
			$html .= 	"<a href='#next' class='next-slide' >".__('Next','avia_framework' )."</a>";
			$html .= "</div>";

			return $html;
		}

		//fetch new entries
		public function query_entries($params = array())
		{
			if(empty($params)) $params = $this->atts;

			if(empty($params['custom_query']))
            {
				$query = array();

				if(!empty($params['categories']))
				{
					//get the portfolio categories
					$terms 	= explode(',', $params['categories']);
				}

				$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
				if(!$page) $page = 1;

				//if we find no terms for the taxonomy fetch all taxonomy terms
				if(empty($terms[0]) || is_null($terms[0]) || $terms[0] === "null")
				{
					$terms = array();
					$allTax = get_terms( $params['taxonomy']);
					foreach($allTax as $tax)
					{
						$terms[] = $tax->term_id;
					}

				}

				$query = array(	'orderby' 	=> 'date',
								'order' 	=> 'DESC',
								'paged' 	=> $page,
								'posts_per_page' => $params['items'],
								'tax_query' => array( 	array( 	'taxonomy' 	=> $params['taxonomy'],
																'field' 	=> 'id',
																'terms' 	=> $terms,
																'operator' 	=> 'IN')));
			}
			else
			{
				$query = $params['custom_query'];
			}


			$query = apply_filters('avia_post_slide_query', $query, $params);

			$this->entries = new WP_Query( $query );

		}
	}
}
