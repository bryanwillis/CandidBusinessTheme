<?php  if ( ! defined('AVIA_FW')) exit('No direct script access allowed');
/**
 * This file holds several widgets exclusive to the framework
 *
 * @author		Christian "Kriesi" Budschedl
 * @copyright	Copyright (c) Christian Budschedl
 * @link		http://Kriesi.at
 * @link		http://aviathemes.com
 * @since		Version 1.0
 * @package 	AviaFramework
 */


/**
 * AVIA TWEETBOX
 *
 * Widget that creates a list of latest tweets
 *
 * @package AviaFramework
 * @todo replace the widget system with a dynamic one, based on config files for easier widget creation
 */



/*
Twitter widget only for compatibility reasons with older themes present. no onger used since API will be shut down by twitter
*/
class avia_tweetbox extends WP_Widget {

	function avia_tweetbox() {
		//Constructor
		$widget_ops = array('classname' => 'tweetbox', 'description' => 'A widget to display your latest twitter messages' );
		$this->WP_Widget( 'tweetbox', THEMENAME.' Twitter Widget', $widget_ops );
	}

	function widget($args, $instance) {
		// prints the widget

		extract($args, EXTR_SKIP);
		echo $before_widget;

		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$count = empty($instance['count']) ? '' : $instance['count'];
		$username = empty($instance['username']) ? '' : $instance['username'];
		$exclude_replies = empty($instance['exclude_replies']) ? '' : $instance['exclude_replies'];
		$time = empty($instance['time']) ? 'no' : $instance['time'];
		$display_image = empty($instance['display_image']) ? 'no' : $instance['display_image'];

		if ( !empty( $title ) ) { echo $before_title . "<a href='http://twitter.com/$username/' title='".strip_tags($title)."'>".$title ."</a>". $after_title; };

		$messages = tweetbox_get_tweet($count, $username, $widget_id, $time, $exclude_replies, $display_image);
		echo $messages;

		echo $after_widget;


	}

	function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;
		foreach($new_instance as $key=>$value)
		{
			$instance[$key]	= strip_tags($new_instance[$key]);
		}

		delete_transient(THEMENAME.'_tweetcache_id_'.$instance['username'].'_'.$this->id_base."-".$this->number);
		return $instance;
	}

	function form($instance) {
		//widgetform in backend

		$instance = wp_parse_args( (array) $instance, array( 'title' => 'Latest Tweets', 'count' => '3', 'username' => avia_get_option('twitter') ) );
		$title = 			isset($instance['title']) ? strip_tags($instance['title']): "";
		$count = 			isset($instance['count']) ? strip_tags($instance['count']): "";
		$username = 		isset($instance['username']) ? strip_tags($instance['username']): "";
		$exclude_replies = 	isset($instance['exclude_replies']) ? strip_tags($instance['exclude_replies']): "";
		$time = 			isset($instance['time']) ? strip_tags($instance['time']): "";
		$display_image = 	isset($instance['display_image']) ? strip_tags($instance['display_image']): "";
?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>">Title:
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('username'); ?>">Enter your twitter username:
		<input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo esc_attr($username); ?>" /></label></p>

		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>">How many entries do you want to display: </label>
			<select class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>">
				<?php
				$list = "";
				for ($i = 1; $i <= 20; $i++ )
				{
					$selected = "";
					if($count == $i) $selected = 'selected="selected"';

					$list .= "<option $selected value='$i'>$i</option>";
				}
				$list .= "</select>";
				echo $list;
				?>


		</p>

		<p>
			<label for="<?php echo $this->get_field_id('exclude_replies'); ?>">Exclude @replies: </label>
			<select class="widefat" id="<?php echo $this->get_field_id('exclude_replies'); ?>" name="<?php echo $this->get_field_name('exclude_replies'); ?>">
				<?php
				$list = "";
				$answers = array('yes','no');
				foreach ($answers as $answer)
				{
					$selected = "";
					if($answer == $exclude_replies) $selected = 'selected="selected"';

					$list .= "<option $selected value='$answer'>$answer</option>";
				}
				$list .= "</select>";
				echo $list;
				?>


		</p>

		<p>
			<label for="<?php echo $this->get_field_id('time'); ?>">Display time of tweet</label>
			<select class="widefat" id="<?php echo $this->get_field_id('time'); ?>" name="<?php echo $this->get_field_name('time'); ?>">
				<?php
				$list = "";
				$answers = array('yes','no');
				foreach ($answers as $answer)
				{
					$selected = "";
					if($answer == $time) $selected = 'selected="selected"';

					$list .= "<option $selected value='$answer'>$answer</option>";
				}
				$list .= "</select>";
				echo $list;
				?>


		</p>

		<p>
			<label for="<?php echo $this->get_field_id('display_image'); ?>">Display Twitter User Avatar</label>
			<select class="widefat" id="<?php echo $this->get_field_id('display_image'); ?>" name="<?php echo $this->get_field_name('display_image'); ?>">
				<?php
				$list = "";
				$answers = array('yes','no');
				foreach ($answers as $answer)
				{
					$selected = "";
					if($answer == $display_image) $selected = 'selected="selected"';

					$list .= "<option $selected value='$answer'>$answer</option>";
				}
				$list .= "</select>";
				echo $list;
				?>
		</p>



	<?php
	}
}



function tweetbox_get_tweet($count, $username, $widget_id, $time='yes', $exclude_replies='yes', $avatar = 'yes')
{
		$filtered_message = "";
		$output = "";
		$iterations = 0;

		$cache = get_transient(THEMENAME.'_tweetcache_id_'.$username.'_'.$widget_id);

		if($cache)
		{
			$tweets = get_option(THEMENAME.'_tweetcache_'.$username.'_'.$widget_id);
		}
		else
		{
			//$response = wp_remote_get( 'http://api.twitter.com/1/statuses/user_timeline.xml?screen_name='.$username );
			$response = wp_remote_get( 'http://api.twitter.com/1/statuses/user_timeline.xml?include_rts=true&screen_name='.$username );
			if (!is_wp_error($response))
			{
				$xml = simplexml_load_string($response['body']);
				//follower: (int) $xml->status->user->followers_count

				if( empty( $xml->error ) )
			    {
			    	if ( isset($xml->status[0]))
			    	{

			    	    $tweets = array();
			    	    foreach ($xml->status as $tweet)
			    	    {
			    	    	if($iterations == $count) break;

			    	    	$text = (string) $tweet->text;
			    	    	if($exclude_replies == 'no' || ($exclude_replies == 'yes' && $text[0] != "@"))
			    	    	{
			    	    		$iterations++;
			    	    		$tweets[] = array(
			    	    			'text' => tweetbox_filter( $text ),
			    	    			'created' =>  strtotime( $tweet->created_at ),
			    	    			'user' => array(
			    	    				'name' => (string)$tweet->user->name,
			    	    				'screen_name' => (string)$tweet->user->screen_name,
			    	    				'image' => (string)$tweet->user->profile_image_url,
			    	    				'utc_offset' => (int) $tweet->user->utc_offset[0],
			    	    				'follower' => (int) $tweet->user->followers_count

			    	    			));
			    			}
			    		}

			    		set_transient(THEMENAME.'_tweetcache_id_'.$username.'_'.$widget_id, 'true', 60*30);
			    		update_option(THEMENAME.'_tweetcache_'.$username.'_'.$widget_id, $tweets);
			    	}
			    }
			}
		}



		if(!isset($tweets[0]))
		{
			$tweets = get_option(THEMENAME.'_tweetcache_'.$username.'_'.$widget_id);
		}

	    if(isset($tweets[0]))
	    {
	    	$time_format = apply_filters( 'avia_widget_time' , get_option('date_format')." - ".get_option('time_format') );

	    	foreach ($tweets as $message)
	    	{
	    		$output .= '<li class="tweet">';
	    		if($avatar == "yes") $output .= '<div class="tweet-thumb"><a href="http://twitter.com/'.$username.'" title=""><img src="'.$message['user']['image'].'" alt="" /></a></div>';
	    		$output .= '<div class="tweet-text avatar_'.$avatar.'">'.$message['text'];
	    		if($time == "yes") $output .= '<div class="tweet-time">'.date_i18n( $time_format, $message['created'] + $message['user']['utc_offset']).'</div>';
	    		$output .= '</div></li>';
			}
	    }


		if($output != "")
		{
			$filtered_message = "<ul class='tweets'>$output</ul>";
		}
		else
		{
			$filtered_message = "<ul class='tweets'><li>No public Tweets found</li></ul>";
		}

		return $filtered_message;
}


function tweetbox_filter($text) {
    // Props to Allen Shaw & webmancers.com & Michael Voigt
    $text = preg_replace('/\b([a-zA-Z]+:\/\/[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"$1\" class=\"twitter-link\">$1</a>", $text);
    $text = preg_replace('/\b(?<!:\/\/)(www\.[\w_.\-]+\.[a-zA-Z]{2,6}[\/\w\-~.?=&%#+$*!]*)\b/i',"<a href=\"http://$1\" class=\"twitter-link\">$1</a>", $text);
    $text = preg_replace("/\b([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})\b/i","<a href=\"mailto://$1\" class=\"twitter-link\">$1</a>", $text);
    $text = preg_replace("/#([\p{L}\p{Mn}]+)/u", "<a class=\"twitter-link\" href=\"http://search.twitter.com/search?q=\\1\">#\\1</a>", $text);
    $text = preg_replace("/@([\p{L}\p{Mn}]+)/u", "<a class=\"twitter-link\" href=\"http://twitter.com/\\1\">@\\1</a>", $text);

    return $text;
}









/**
 * AVIA NEWSBOX
 *
 * Widget that creates a list of latest news entries
 *
 * @package AviaFramework
 * @todo replace the widget system with a dynamic one, based on config files for easier widget creation
 */


class avia_newsbox extends WP_Widget {

	var $avia_term = '';
	var $avia_post_type = '';
	var $avia_new_query = '';

	function avia_newsbox()
	{
		$widget_ops = array('classname' => 'newsbox', 'description' => 'A Sidebar widget to display latest post entries in your sidebar' );

		$this->WP_Widget( 'newsbox', THEMENAME.' Latest News', $widget_ops );
	}

	function widget($args, $instance)
	{

		global $avia_config;

		extract($args, EXTR_SKIP);
		echo $before_widget;

		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$count = empty($instance['count']) ? '' : $instance['count'];
		$cat = empty($instance['cat']) ? '' : $instance['cat'];
		$excerpt = empty($instance['excerpt']) ? '' : $instance['excerpt'];
		$image_size = isset($avia_config['widget_image_size']) ? $avia_config['widget_image_size'] : 'widget';

		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };


		if(empty($this->avia_term))
		{
			$additional_loop = new WP_Query("cat=".$cat."&posts_per_page=".$count);
		}
		else
		{
			$catarray = explode(',', $cat);


			if(empty($catarray[0]))
			{
				$new_query = array("posts_per_page"=>$count,"post_type"=>$this->avia_post_type);
			}
			else
			{
				if($this->avia_new_query)
				{
					$new_query = $this->avia_new_query;
				}
				else
				{
					$new_query = array(	"posts_per_page"=>$count, 'tax_query' => array(
													array( 'taxonomy' => $this->avia_term,
														   'field' => 'id',
														   'terms' => explode(',', $cat),
														   'operator' => 'IN')
														  )
													);
				}
			}

			$additional_loop = new WP_Query($new_query);
		}

		if($additional_loop->have_posts()) :



		echo '<ul class="news-wrap image_size_'.$image_size.'">';
		while ($additional_loop->have_posts()) : $additional_loop->the_post();

		$format = "";
		if(empty($this->avia_post_type)) 	$format = $this->avia_post_type;
		if(empty($format)) 					$format = get_post_format();
     	if(empty($format)) 					$format = 'standard';

		echo '<li class="news-content post-format-'.$format.'">';

		//check for preview images:
		$image = "";
		$slides = avia_post_meta(get_the_ID(), 'slideshow', true);



		if( $slides != "" && !empty( $slides[0]['slideshow_image'] ) )
		{
			$image = avia_image_by_id($slides[0]['slideshow_image'], $image_size, 'image');
		}

		if(!$image && current_theme_supports( 'post-thumbnails' ))
		{
			$image = get_the_post_thumbnail( get_the_ID(), $image_size );
		}

		$time_format = apply_filters( 'avia_widget_time' , get_option('date_format')." - ".get_option('time_format') );


		echo "<a class='news-link' title='".get_the_title()."' href='".get_permalink()."'>";
		echo "<span class='news-thumb'>";
		echo $image;
		echo "</span>";
		if(empty($avia_config['widget_image_size']) || 'display title and excerpt' != $excerpt) { echo "<strong class='news-headline'>".get_the_title()."<span class='news-time'>".get_the_time($time_format)."</span></strong>";  }
		echo "</a>";

		if('display title and excerpt' == $excerpt)
		{
			echo "<div class='news-excerpt'>";

			if(!empty($avia_config['widget_image_size']))
			{
				echo "<a class='news-link-inner' title='".get_the_title()."' href='".get_permalink()."'>";
				echo "<strong class='news-headline'>".get_the_title()."</strong>";
				echo "</a>";
				echo "<span class='news-time'>".get_the_time($time_format)."</span>";
			}
			the_excerpt();
			echo "</div>";
		}

		echo '</li>';


		endwhile;
		echo "</ul>";
		wp_reset_postdata();
		endif;


		echo $after_widget;

	}


	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['count'] = strip_tags($new_instance['count']);
		$instance['excerpt'] = strip_tags($new_instance['excerpt']);
		$instance['cat'] = implode(',',$new_instance['cat']);
		return $instance;
	}



	function form($instance)
	{
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'count' => '', 'cat' => '', 'excerpt'=>'' ) );
		$title = strip_tags($instance['title']);
		$count = strip_tags($instance['count']);
		$excerpt = strip_tags($instance['excerpt']);


		$elementCat = array("name" 	=> "Which categories should be used for the portfolio?",
							"desc" 	=> "You can select multiple categories here",
				            "id" 	=> $this->get_field_name('cat')."[]",
				            "type" 	=> "select",
				            "std"   => strip_tags($instance['cat']),
				            "class" => "",
            				"multiple"=>6,
				            "subtype" => "cat");
		//check if a different taxonomy than the default is set
		if(!empty($this->avia_term))
		{
			$elementCat['taxonomy'] = $this->avia_term;
		}




		$html = new avia_htmlhelper();

?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">Title:
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>

		<p>
			<label for="<?php echo $this->get_field_id('count'); ?>">How many entries do you want to display: </label>
			<select class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>">
				<?php
				$list = "";
				for ($i = 1; $i <= 20; $i++ )
				{
					$selected = "";
					if($count == $i) $selected = 'selected="selected"';

					$list .= "<option $selected value='$i'>$i</option>";
				}
				$list .= "</select>";
				echo $list;
				?>


		</p>

		<p><label for="<?php echo $this->get_field_id('cat'); ?>">Choose the categories you want to display (multiple selection possible):
		<?php echo $html->select($elementCat); ?>
		</label></p>

		<p>
			<label for="<?php echo $this->get_field_id('excerpt'); ?>">Display title only or title &amp; excerpt</label>
			<select class="widefat" id="<?php echo $this->get_field_id('excerpt'); ?>" name="<?php echo $this->get_field_name('excerpt'); ?>">
				<?php
				$list = "";
				$answers = array('show title only','display title and excerpt');
				foreach ($answers as $answer)
				{
					$selected = "";
					if($answer == $excerpt) $selected = 'selected="selected"';

					$list .= "<option $selected value='$answer'>$answer</option>";
				}
				$list .= "</select>";
				echo $list;
				?>


		</p>


<?php
	}
}



/**
 * AVIA PORTFOLIOBOX
 *
 * Widget that creates a list of latest portfolio entries. Basically the same widget as the newsbox with some minor modifications, therefore it just extends the Newsbox
 *
 * @package AviaFramework
 * @todo replace the widget system with a dynamic one, based on config files for easier widget creation
 */

class avia_portfoliobox extends avia_newsbox
{
	function avia_portfoliobox()
	{
		$this->avia_term = 'portfolio_entries';
		$this->avia_post_type = 'portfolio';
		$this->avia_new_query = ''; //set a custom query here


		$widget_ops = array('classname' => 'newsbox', 'description' => 'A Sidebar widget to display latest portfolio entries in your sidebar' );

		$this->WP_Widget( 'portfoliobox', THEMENAME.' Latest Portfolio', $widget_ops );
	}
}




/**
 * AVIA SOCIALCOUNT
 *
 * Widget that retrieves, stores and displays the number of twitter and rss followers
 *
 * @package AviaFramework
 * @todo replace the widget system with a dynamic one, based on config files for easier widget creation
 */


class avia_socialcount extends WP_Widget {

	function avia_socialcount() {
		//Constructor
		$widget_ops = array('classname' => 'avia_socialcount', 'description' => 'A widget to display a linkt to your twitter profile and rss feed' );
		$this->WP_Widget( 'avia_socialcount', THEMENAME.' RSS Link and Twitter Account', $widget_ops );
	}

	function widget($args, $instance) {
		// prints the widget

		extract($args, EXTR_SKIP);
		$twitter = empty($instance['twitter']) ? '' : $instance['twitter'];
		$rss 	 = empty($instance['rss'])     ? '' : $instance['rss'];
		$rss = preg_replace('!https?:\/\/feeds.feedburner.com\/!','',$rss);

		$follower = $this->count_followers($twitter, $rss, $widget_id);


		if(!empty($follower) && is_array($follower))
		{
			$addClass = "asc_multi_count";
			if(!isset($follower['twitter']) || !isset($follower['rss'])) $addClass = 'asc_single_count';

			echo $before_widget;
			$output = "";
			if(isset($follower['twitter']))
			{
				$link = 'http://twitter.com/'.$twitter.'/';
				$before = apply_filters('avf_social_widget', "", 'twitter');
				
				if ( $follower['twitter'] <= '1' )
				{
					$output .= "<a href='$link' class='asc_twitter $addClass'>{$before}<strong class='asc_count'>".$follower['twitter']."</strong><span>".__('Follower','avia_framework')."</span></a>";
				}
				else
				{
					$output .= "<a href='$link' class='asc_twitter $addClass'>{$before}<strong class='asc_count'>".$follower['twitter']."</strong><span>".__('Followers','avia_framework')."</span></a>";
				}
			}
			
			if(isset($follower['rss']) && $rss)
			{
				$link = $rss;

				if(is_numeric($follower['rss']))
				{
					$feed_text = __('Subscribers','avia_framework');
				}
				else
				{
					$follower['rss'] = __('Subscribe','avia_framework');
					$feed_text = __('to RSS Feed','avia_framework');
				}

				$output .= "<a href='$link' class='asc_rss $addClass'>".apply_filters('avf_social_widget',"", 'rss')."<strong class='asc_count'>".$follower['rss']."</strong><span>".$feed_text."</span></a>";
			}
			
			echo $output;
			echo $after_widget;
		}
	}

	function count_followers($twitter, $rss, $widget_id)
	{
		$follower = array();
		$optionkey = strtolower(THEMENAME.'fc_id'.$widget_id);
		$cache = get_transient($optionkey);

		if($cache)
		{
			$follower = get_option($optionkey);
		}
		else
		{
			if($twitter != "")
			{
				//this function also works without the twitter api beeing active. instead of usercount we will display the word "Twitter"
				$twittercount = wp_remote_get( 'http://api.twitter.com/1/statuses/user_timeline.xml?screen_name='.$twitter );

				if (!is_wp_error($twittercount))
				{
					$xml = simplexml_load_string($twittercount['body']);

					if( empty( $xml->error ) && isset($xml->status->user->followers_count))
					{
						$follower['twitter'] = (int) $xml->status->user->followers_count;
					}
					else
					{
						$follower['twitter'] = "Twitter";
					}
				}
			}

			if($rss != "")
			{
				$requesturl = "http://feedburner.google.com/api/awareness/1.0/GetFeedData?uri=http://feeds.feedburner.com/".$rss.'&dates=' . date('Y-m-d', strtotime('-2 days', time()));

				//$feedcount = wp_remote_get($requesturl);
				$feedcount = ""; $follower['rss'] = true;

				if (!empty($feedcount) && !is_wp_error($feedcount))
				{
					$xml = @simplexml_load_string($feedcount['body']);

					if(is_object($xml->feed->entry))
					{
						$follower['rss'] = (int) $xml->feed->entry->attributes()->circulation;
					}
					else
					{
						$follower['rss'] = true;
					}
				}
			}

			$fallback = get_option($optionkey);

			if(!isset($follower['rss']) && isset($fallback['rss'])) $follower['rss'] = $fallback['rss'];
			if(!isset($follower['twitter']) && isset($fallback['twitter'])) $follower['twitter'] = $fallback['twitter'];

			set_transient($optionkey, 1, 60*60*36);
			update_option($optionkey, $follower);

		}

		return $follower;
	}




	function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;
		foreach($new_instance as $key=>$value)
		{
			$instance[$key]	= strip_tags($new_instance[$key]);
		}

		delete_transient(strtolower(THEMENAME.'fc_id'.$this->id_base."-".$this->number));
		return $instance;
	}

	function form($instance) {
		//widgetform in backend

		$instance = wp_parse_args( (array) $instance, array('rss' => avia_get_option('feedburner'), 'twitter' => avia_get_option('twitter') ) );
		$twitter = empty($instance['twitter']) ? '' :  strip_tags($instance['twitter']);
		$rss 	 = empty($instance['rss'])     ? '' :  strip_tags($instance['rss']);
?>
		<p>
		<label for="<?php echo $this->get_field_id('twitter'); ?>">Twitter Username:
		<input class="widefat" id="<?php echo $this->get_field_id('twitter'); ?>" name="<?php echo $this->get_field_name('twitter'); ?>" type="text" value="<?php echo esc_attr($twitter); ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('rss'); ?>">Enter your feed url:
		<input class="widefat" id="<?php echo $this->get_field_id('rss'); ?>" name="<?php echo $this->get_field_name('rss'); ?>" type="text" value="<?php echo esc_attr($rss); ?>" /></label></p>



	<?php
	}
}





/**
 * AVIA ADVERTISING WIDGET
 *
 * Widget that retrieves, stores and displays the number of twitter and rss followers
 *
 * @package AviaFramework
 * @todo replace the widget system with a dynamic one, based on config files for easier widget creation
 */


//multiple images

class avia_partner_widget extends WP_Widget {

	function avia_partner_widget() {

		$this->add_cont = 2;
		//Constructor
		$widget_ops = array('classname' => 'avia_partner_widget', 'description' => 'An advertising widget that displays 2 images with 125 x 125 px in size' );
		$this->WP_Widget( 'avia_partner_widget', THEMENAME.' Advertising Area', $widget_ops );
	}

	function widget($args, $instance)
	{
		extract($args, EXTR_SKIP);
		echo $before_widget;

		global $kriesiaddwidget, $firsttitle;
		$kriesiaddwidget ++;

		$title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
		$image_url = empty($instance['image_url']) ? '<span class="avia_parnter_empty"><span>'.__('Advertise here','avia_framework').'</span></span>' : '<img class="rounded" src="'.$instance['image_url'].'" title="" alt=""/>';
		$ref_url = empty($instance['ref_url']) ? '#' : apply_filters('widget_comments_title', $instance['ref_url']);
		$image_url2 = empty($instance['image_url2']) ? '<span class="avia_parnter_empty"><span>'.__('Advertise here','avia_framework').'</span></span>' : '<img class="rounded" src="'.$instance['image_url2'].'" title="" alt=""/>';
		$ref_url2 = empty($instance['ref_url2']) ? '#' : apply_filters('widget_comments_title', $instance['ref_url2']);

		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		echo '<a href="'.$ref_url.'" class="preloading_background  avia_partner1 link_list_item'.$kriesiaddwidget.' '.$firsttitle.'" >'.$image_url.'</a>';
		if($this->add_cont == 2) echo '<a href="'.$ref_url2.'" class="preloading_background avia_partner2 link_list_item'.$kriesiaddwidget.' '.$firsttitle.'" >'.$image_url2.'</a>';
		echo $after_widget;

		if($title == '')
		{
			$firsttitle = 'no_top_margin';
		}

	}


	function update($new_instance, $old_instance) {
		//save the widget
		$instance = $old_instance;
		foreach($new_instance as $key=>$value)
		{
			$instance[$key]	= strip_tags($new_instance[$key]);
		}
		return $instance;
	}



	function form($instance)
	{
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'image_url' => '', 'ref_url' => '', 'image_url2' => '', 'ref_url2' => '' ) );
		$title = strip_tags($instance['title']);
		$image_url = strip_tags($instance['image_url']);
		$ref_url = strip_tags($instance['ref_url']);
		$image_url2 = strip_tags($instance['image_url2']);
		$ref_url2 = strip_tags($instance['ref_url2']);
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>">Title:
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('image_url'); ?>">Image URL: <?php if($this->add_cont == 2) echo "(125px * 125px):"; ?>
		<input class="widefat" id="<?php echo $this->get_field_id('image_url'); ?>" name="<?php echo $this->get_field_name('image_url'); ?>" type="text" value="<?php echo esc_attr($image_url); ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('ref_url'); ?>">Referal URL:
		<input class="widefat" id="<?php echo $this->get_field_id('ref_url'); ?>" name="<?php echo $this->get_field_name('ref_url'); ?>" type="text" value="<?php echo esc_attr($ref_url); ?>" /></label></p>

		<?php if($this->add_cont == 2)
		{ ?>

				<p><label for="<?php echo $this->get_field_id('image_url2'); ?>">Image URL 2: (125px * 125px):
		<input class="widefat" id="<?php echo $this->get_field_id('image_url2'); ?>" name="<?php echo $this->get_field_name('image_url2'); ?>" type="text" value="<?php echo esc_attr($image_url2); ?>" /></label></p>

		<p><label for="<?php echo $this->get_field_id('ref_url2'); ?>">Referal URL 2:
		<input class="widefat" id="<?php echo $this->get_field_id('ref_url2'); ?>" name="<?php echo $this->get_field_name('ref_url2'); ?>" type="text" value="<?php echo esc_attr($ref_url2); ?>" /></label></p>

		<?php }?>

<?php
	}
}


//one image
class avia_one_partner_widget extends avia_partner_widget
{
	function avia_one_partner_widget()
	{

		$this->add_cont = 1;

		$widget_ops = array('classname' => 'avia_one_partner_widget', 'description' => 'An advertising widget that displays 1 big image' );

		$this->WP_Widget( 'avia_one_partner_widget', THEMENAME.' Big Advertising Area', $widget_ops );
	}
}




/**
 * AVIA COMBO WIDGET
 *
 * Widget that retrieves, stores and displays the number of twitter and rss followers
 *
 * @package AviaFramework
 * @todo replace the widget system with a dynamic one, based on config files for easier widget creation
 */


class avia_combo_widget extends WP_Widget {

	function avia_combo_widget() {
		//Constructor
		$widget_ops = array('classname' => 'avia_combo_widget', 'description' => 'A widget that displays your popular posts, recent posts, recent comments and a tagcloud' );
		$this->WP_Widget( 'avia_combo_widget', THEMENAME.' Combo Widget', $widget_ops );
	}

	function widget($args, $instance)
	{
		// prints the widget

		extract($args, EXTR_SKIP);
		$posts = empty($instance['count']) ? 4 : $instance['count'];

		echo $before_widget;
		echo "<div class='tabcontainer border_tabs top_tab tab_initial_open tab_initial_open__1'>";

		echo '<div class="tab first_tab active_tab widget_tab_popular"><span>'.__('Popular', 'avia_framework').'</span></div>';
		echo "<div class='tab_content active_tab_content'>";
		avia_get_post_list('cat=&orderby=comment_count&posts_per_page='.$posts);
		echo "</div>";

		echo '<div class="tab widget_tab_recent"><span>'.__('Recent', 'avia_framework').'</span></div>';
		echo "<div class='tab_content'>";
		avia_get_post_list('showposts='. $posts .'&orderby=post_date&order=desc');
		echo "</div>";

		echo '<div class="tab widget_tab_comments"><span>'.__('Comments', 'avia_framework').'</span></div>';
		echo "<div class='tab_content'>";
		avia_get_comment_list( array('number' => $posts, 'status' => 'approve', 'order' => 'DESC') );
		echo "</div>";

		echo '<div class="tab last_tab widget_tab_tags"><span>'.__('Tags', 'avia_framework').'</span></div>';
		echo "<div class='tab_content tagcloud'>";
		wp_tag_cloud('smallest=12&largest=12&unit=px');
		echo "</div>";

		echo "</div>";
		echo $after_widget;
	}


	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;
		foreach($new_instance as $key=>$value)
		{
			$instance[$key]	= strip_tags($new_instance[$key]);
		}

		return $instance;
	}

	function form($instance) {
		//widgetform in backend

		$instance = wp_parse_args( (array) $instance, array('count' => 4) );
		if(!is_numeric($instance['count'])) $instance['count'] = 4;

?>
		<p>
		<label for="<?php echo $this->get_field_id('count'); ?>">Number of posts you want to display:
		<input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($instance['count']); ?>" /></label></p>


	<?php
	}
}

/*-----------------------------------------------------------------------------------
get posts posts
-----------------------------------------------------------------------------------*/
if (!function_exists('avia_get_post_list'))
{
	function avia_get_post_list( $avia_new_query , $excerpt = false)
	{
		global $avia_config;

		$additional_loop = new WP_Query($avia_new_query);

		if($additional_loop->have_posts()) :
		echo '<ul class="news-wrap">';
		while ($additional_loop->have_posts()) : $additional_loop->the_post();

		$format = "";
		if(get_post_type() != 'post') 		$format = get_post_type();
		if(empty($format)) 					$format = get_post_format();
     	if(empty($format)) 					$format = 'standard';

		echo '<li class="news-content post-format-'.$format.'">';

		//check for preview images:
		$image = "";
		$slides = avia_post_meta(get_the_ID(), 'slideshow');

		if( $slides != "" && !empty( $slides[0]['slideshow_image'] ) )
		{
			$image = avia_image_by_id($slides[0]['slideshow_image'], 'widget', 'image');
		}

		$time_format = apply_filters( 'avia_widget_time' , get_option('date_format')." - ".get_option('time_format') );

		echo "<a class='news-link' title='".get_the_title()."' href='".get_permalink()."'>";
		echo "<span class='news-thumb'>";
		echo $image;
		echo "</span>";
		echo "<strong class='news-headline'>".avia_backend_truncate(get_the_title(), 55," ");
		echo "<span class='news-time'>".get_the_time($time_format)."</span>";
		echo "</strong>";
		echo "</a>";

		if('display title and excerpt' == $excerpt)
		{
			echo "<div class='news-excerpt'>";
			the_excerpt();
			echo "</div>";
		}

		echo '</li>';


		endwhile;
		echo "</ul>";
		wp_reset_postdata();
		endif;
	}
}





if (!function_exists('avia_get_comment_list'))
{

	function avia_get_comment_list($avia_new_query)
	{
		$time_format = apply_filters( 'avia_widget_time' , get_option('date_format')." - ".get_option('time_format') );
	
		global $avia_config;

		$comments = get_comments($avia_new_query);

		if(!empty($comments)) :
		echo '<ul class="news-wrap">';
		foreach($comments as $comment)
		{

		echo '<li class="news-content">';
		echo "<a class='news-link' title='".get_the_title($comment->comment_post_ID)."' href='".get_comment_link($comment)."'>";
		echo "<span class='news-thumb'>";
		echo get_avatar($comment,'48');
		echo "</span>";
		echo "<strong class='news-headline'>".avia_backend_truncate($comment->comment_content, 55," ");
		echo "<span class='news-time'>".get_the_time($time_format, $comment->comment_post_ID)." by ".$comment->comment_author."</span>";
		echo "</strong>";
		echo "</a>";

		echo '</li>';


		}
		echo "</ul>";
		wp_reset_postdata();
		endif;
	}
}



/*
	Google Maps Widget

	Copyright 2009  Clark Nikdel Powell  (email : taylor@cnpstudio.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


class avia_google_maps extends WP_Widget {

	// constructor
	function avia_google_maps() {
		$widget_ops = array('classname' => 'avia_google_maps', 'description' => __( 'Add a google map to your blog or site') );
		$this->WP_Widget('avia_google_maps', THEMENAME.' Google Maps Widget', $widget_ops);

	}

	// output the content of the widget
	function widget($args, $instance) {
		extract( $args );

		$title = empty($instance['title']) ? '' : apply_filters('widget_title', esc_attr($instance['title']));

		print $before_widget;
		if (!empty($instance['title'])) { print $before_title.$title.$after_title; }
		print avia_printmap($instance['lat'], $instance['lng'], $instance['zoom'], $instance['type'], $instance['content'], $instance['directionsto']);
		print $after_widget;
	}

	// process widget options to be saved
	function update($new_instance, $old_instance) {
		print_r($old_instance);
		print_r($new_instance);
		return $new_instance;
	}

	// output the options form on admin
	function form($instance) {
		global $wpdb;
		$title = empty($instance['title']) ? '' : esc_attr($instance['title']);
		$lat = empty($instance['lat']) ? '' : esc_attr($instance['lat']);
		$lng = empty($instance['lng']) ? '' : esc_attr($instance['lng']);
		$zoom = empty($instance['zoom']) ? '15' : esc_attr($instance['zoom']);
		$type = empty($instance['type']) ? 'ROADMAP' : esc_attr($instance['type']);
		$directionsto = empty($instance['directionsto']) ? '' : esc_attr($instance['directionsto']);
		$content = empty($instance['content']) ? '' : esc_attr($instance['content']);
		?>
			<p>
			<label for="<?php print $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php print $this->get_field_id('title'); ?>" name="<?php print $this->get_field_name('title'); ?>" type="text" value="<?php print $title; ?>" />
			</p>
			<p>
			Enter the latitude and longitude of the location. You can <a target='_blank' href='http://itouchmap.com/latlong.html'>fetch them here</a><br/><br/><!-- http://www.getlatlon.com/ -->
			<label for="<?php print $this->get_field_id('lat'); ?>"><?php _e('Latitude:'); ?></label>
			<input class="widefat" id="<?php print $this->get_field_id('lat'); ?>" name="<?php print $this->get_field_name('lat'); ?>" type="text" value="<?php print $lat; ?>" />
			</p>
			<p>
			<label for="<?php print $this->get_field_id('lng'); ?>"><?php _e('Longitude:'); ?></label>
			<input class="widefat" id="<?php print $this->get_field_id('lng'); ?>" name="<?php print $this->get_field_name('lng'); ?>" type="text" value="<?php print $lng; ?>" />
			</p>

			<p>
			<label for="<?php print $this->get_field_id('zoom'); ?>"><?php _e('Zoom Level: <small>(1-19)</small>'); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('zoom'); ?>" name="<?php echo $this->get_field_name('zoom'); ?>">
				<?php
				$list = "";
				$answers = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19);
				foreach ($answers as $answer)
				{
					$selected = "";
					if($answer == $zoom) $selected = 'selected="selected"';

					$list .= "<option $selected value='$answer'>$answer</option>";
				}
				$list .= "</select>";
				echo $list;
				?>


			</p>

			<p>
			<label for="<?php print $this->get_field_id('type'); ?>"><?php _e('Map Type:'); ?></label>

			<select class="widefat" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
				<?php
				$list = "";
				$answers = array('ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN');
				foreach ($answers as $answer)
				{
					$selected = "";
					if($answer == $type) $selected = 'selected="selected"';

					$list .= "<option $selected value='$answer'>$answer</option>";
				}
				$list .= "</select>";
				echo $list;
				?>

			</p>
			<p>
			<label for="<?php print $this->get_field_id('directionsto'); ?>"><?php _e('Address for directions:'); ?></label>
			<input class="widefat" id="<?php print $this->get_field_id('directionsto'); ?>" name="<?php print $this->get_field_name('directionsto'); ?>" type="text" value="<?php print $directionsto; ?>" />
			</p>
			<p>
			<label for="<?php print $this->get_field_id('content'); ?>"><?php _e('Info Bubble Content:'); ?></label>
			<textarea rows="7" class="widefat" id="<?php print $this->get_field_id('content'); ?>" name="<?php print $this->get_field_name('content'); ?>"><?php print $content; ?></textarea>
			</p>
		<?php
	}

} // SGMwidget widget




function avia_printmap($lat, $lng, $zoom, $type, $content, $directionsto) {

	global $avia_config;

	$SGMoptions = get_option('SGMoptions'); // get options defined in admin page

	if (!$lat) {$lat = '0';}
	if (!$lng) {$lng = '0';}
	if (!$zoom) {$zoom = $SGMoptions['zoom'];} // 1-19
	if (!$type) {$type = $SGMoptions['type'];} // ROADMAP, SATELLITE, HYBRID, TERRAIN
	if (!$content) {$content = $SGMoptions['content'];}
	$output = "";
	$unique = uniqid();
	$content = str_replace('&lt;', '<', $content);
	$content = str_replace('&gt;', '>', $content);
	$content = mysql_escape_string($content);
	$prefix  = isset($_SERVER['HTTPS'] ) ? "https" : "http";



	$directionsForm = "";
	if(empty($avia_config['g_maps_widget_active']))
	{
		$output .= "<script type='text/javascript' src='$prefix://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false'></script>";
		$avia_config['g_maps_widget_active'] = 0;
	}

	$avia_config['g_maps_widget_active'] ++;

$output .= "<script type='text/javascript'>
function makeMap_".$avia_config['g_maps_widget_active']."() {
   var directionsDisplay;
   directionsDisplay = new google.maps.DirectionsRenderer;
   var directionsService = new google.maps.DirectionsService;
   var map;
   var latlng = new google.maps.LatLng(".$lat.", ".$lng.");
   var directionsto = '".$directionsto."';
   var myOptions = {
      zoom:".$zoom.",
      mapTypeControl:true,
      mapTypeId:google.maps.MapTypeId.".$type.",
      mapTypeControlOptions:{style:google.maps.MapTypeControlStyle.DROPDOWN_MENU},
      navigationControl:true,
      navigationControlOptions:{style:google.maps.NavigationControlStyle.SMALL},
      center:latlng
   };
   map = new google.maps.Map(document.getElementById('avia_google_maps_$unique'), myOptions);
   if(directionsto.length > 5) {
      directionsDisplay.setMap(map);
      var request = {
         origin:directionsto,
         destination:latlng,
         travelMode:google.maps.DirectionsTravelMode.DRIVING
      };
      directionsService.route(request, function(response, status) {
         if(status == google.maps.DirectionsStatus.OK) {
            directionsDisplay.setDirections(response)
         }
      })
   }
   else {
      var contentString = '".$content."';
      var infowindow = new google.maps.InfoWindow({
         content: contentString
      });
      var marker = new google.maps.Marker({
         position: latlng,
         map: map,
         title: ''
      });
   }
}
jQuery(document).ready(function() {
   makeMap_".$avia_config['g_maps_widget_active']."()
});
</script>
   <div id='avia_google_maps_$unique' class='avia_google_maps_container'></div>
   ";

   return $output;
}

