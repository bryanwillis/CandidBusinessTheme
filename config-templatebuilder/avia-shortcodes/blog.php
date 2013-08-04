<?php
/**
 * Sidebar
 * Displays one of the registered Widget Areas of the theme
 */

if ( !class_exists( 'avia_sc_blog' ) )
{
	class avia_sc_blog extends aviaShortcodeTemplate
	{
			/**
			 * Create the config array for the shortcode button
			 */
			function shortcode_insert_button()
			{
				$this->config['name']		= __('Blog Posts', 'avia_framework' );
				$this->config['tab']		= __('Content Elements', 'avia_framework' );
				$this->config['icon']		= AviaBuilder::$path['imagesURL']."sc-blog.png";
				$this->config['order']		= 40;
				$this->config['target']		= 'avia-target-insert';
				$this->config['shortcode'] 	= 'av_blog';
				$this->config['tooltip'] 	= __('Displays Posts from your Blog', 'avia_framework' );
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

                    array(	"name" 		=> __("Do you want to display blog posts?", 'avia_framework' ),
                        "desc" 		=> __("Do you want to display blog posts or entries from a custom taxonomy?", 'avia_framework' ),
                        "id" 		=> "blog_type",
                        "type" 		=> "select",
                        "std" 	=> "posts",
                        "subtype" => array( 'Display blog posts' =>'posts',
                                            'Display entries from a custom taxonomy' =>'taxonomy')),

					array(	"name" 		=> __("Which categories should be used for the blog?", 'avia_framework' ),
							"desc" 		=> __("You can select multiple categories here. The Page will then show posts from only those categories.", 'avia_framework' ),
				            "id" 		=> "categories",
				            "type" 		=> "select",
	        				"multiple"	=> 6,
                            "required" 	=> array('blog_type', 'equals', 'posts'),
				            "subtype" 	=> "cat"),

                    array(
                        "name" 	=> __("Which Entries?", 'avia_framework' ),
                        "desc" 	=> __("Select which entries should be displayed by selecting a taxonomy", 'avia_framework' ),
                        "id" 	=> "link",
                        "fetchTMPL"	=> true,
                        "type" 	=> "linkpicker",
                        "subtype"  => array( __('Display Entries from:',  'avia_framework' )=>'taxonomy'),
                        "multiple"	=> 6,
                        "required" 	=> array('blog_type', 'equals', 'taxonomy'),
                        "std" 	=> "category"
                    ),

					array(
							"name" 	=> "Blog Style",
							"desc" 	=> "Choose the default blog layout here.",
							"id" 	=> "blog_style",
							"type" 	=> "select",
							"std" 	=> "single-big",
							"no_first"=>true,
							"subtype" => array( 'Multi Author Blog (displays Gravatar of the article author beside the entry and feature images above)' =>'multi-big',
												'Single Author, small preview Pic (no author picture is displayed, feature image is small)' =>'single-small',
												'Single Author, big preview Pic (no author picture is displayed, feature image is big)' =>'single-big',
												'Grid Layout' =>'blog-grid',
												/* 'no sidebar'=>'fullsize' */
										)),

					array(
							"name" 	=> __("Blog Grid Columns", 'avia_framework' ),
							"desc" 	=> __("How many columns do you want to display?", 'avia_framework' ),
							"id" 	=> "columns",
							"type" 	=> "select",
							"std" 	=> "3",
							"required" 	=> array('blog_style', 'equals', 'blog-grid'),
							"subtype" => AviaHtmlHelper::number_array(1,5,1)),

					array(
							"name" 	=> __("Display Read More Link", 'avia_framework' ),
							"desc" 	=> __("Do you want to display a read more link?", 'avia_framework' ),
							"id" 	=> "contents",
							"type" 	=> "select",
							"std" 	=> "excerpt",
							"required" 	=> array('blog_style', 'equals', 'blog-grid'),
							"subtype" => array( 'Excerpt' =>'excerpt', 'Excerpt With Read More Link' =>'excerpt_read_more')),


					array(
							"name" 	=> __("Blog Content length", 'avia_framework' ),
							"desc" 	=> __("Should the full entry be displayed or just a small excerpt?", 'avia_framework' ),
							"id" 	=> "content_length",
							"type" 	=> "select",
							"std" 	=> "content",
							"required" 	=> array('blog_style', 'not', 'blog-grid'),
							"subtype" => array(
								__('Full Content',  'avia_framework' ) =>'content',
								__('Excerpt',  'avia_framework' ) =>'excerpt',
                                __('Excerpt With Read More Link',  'avia_framework' ) =>'excerpt_read_more')),


					array(
							"name" 	=> __("Post Number", 'avia_framework' ),
							"desc" 	=> __("How many items should be displayed per page?", 'avia_framework' ),
							"id" 	=> "items",
							"type" 	=> "select",
							"std" 	=> "3",
							"subtype" => AviaHtmlHelper::number_array(1,100,1, array('All'=>'-1'))),


					array(
							"name" 	=> __("Pagination", 'avia_framework' ),
							"desc" 	=> __("Should a pagination be displayed?", 'avia_framework' ),
							"id" 	=> "paginate",
							"type" 	=> "select",
							"std" 	=> "yes",
							"subtype" => array(
								__('yes',  'avia_framework' ) =>'yes',
								__('no',  'avia_framework' ) =>'no')),

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
				global $avia_config;

				if(empty($atts['categories'])) $atts['categories'] = "";
                if(isset($atts['link']) && isset($atts['blog_type']) && $atts['blog_type'] == 'taxonomy')
                {
                    $atts['link'] = explode(',', $atts['link'], 2 );
                    $atts['taxonomy'] = $atts['link'][0];

                    if(!empty($atts['link'][1]))
                    {
                        $atts['categories'] = $atts['link'][1];
                    }
                    else if(!empty($atts['taxonomy']))
                    {
                        $taxonomy_terms_obj = get_terms($atts['taxonomy']);
                        foreach ($taxonomy_terms_obj as $taxonomy_term)
                        {
                            $atts['categories'] .= $taxonomy_term->term_id . ',';
                        }
                    }
                }

				$atts = shortcode_atts(array('blog_style'	=> '',
											 'columns' 		=> 3,
                                             'blog_type'    => 'posts',
			                                 'items' 		=> '16',
			                                 'paginate' 	=> 'yes',
			                                 'categories' 	=> '',
			                                 'post_type'	=> 'post',
			                                 'taxonomy'		=> 'category',
                                             'contents'     => 'excerpt',
			                                 'content_length' => 'content'
			                                 ), $atts);

				if($atts['blog_style'] == "blog-grid")
				{
					$atts['class'] = $meta['el_class'];
					$atts['type']  = 'grid';

					//using the post slider with inactive js will result in displaying a nice post grid
					$slider = new avia_post_slider($atts);
					$slider->query_entries();
					return $slider->html();
				}


				$this->query_entries($atts);

				$avia_config['blog_style'] = $atts['blog_style'];
				$avia_config['blog_content'] = $atts['content_length'];
				$avia_config['remove_pagination'] = $atts['paginate'] === "yes" ? false :true;

				$more = 0;
				ob_start(); //start buffering the output instead of echoing it
				get_template_part( 'includes/loop', 'index' );
				$output = ob_get_clean();
				wp_reset_query();
				avia_set_layout_array();

				if($output)
				{
					$output = "<div class='template-blog'>{$output}</div>";
				}

				return $output;
			}


			function query_entries($params)
			{
				$query = array();

				if(!empty($params['categories']) && is_string($params['categories']))
				{
					//get the categories
					$terms 	= explode(',', $params['categories']);
				}

				$page = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' );
				if(!$page) $page = 1;

				//if we find categories perform complex query, otherwise simple one
				if(isset($terms[0]) && !empty($terms[0]) && !is_null($terms[0]) && $terms[0] != "null" && !empty($params['taxonomy']))
				{
					$query = array(	'paged' 	=> $page,
									'posts_per_page' => $params['items'],
									'tax_query' => array( 	array( 	'taxonomy' 	=> $params['taxonomy'],
																	'field' 	=> 'id',
																	'terms' 	=> $terms,
																	'operator' 	=> 'IN')));
				}
                else
				{
					$query = array(	'paged'=> $page,
                                    'posts_per_page' => $params['items'],
                                    'post_type' => $params['post_type']);
				}

				$query = apply_filters('avia_blog_post_query', $query, $params);

				query_posts($query);
			}




	}
}
