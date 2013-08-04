<?php

/**
 *  These functions shows a number of posts related to the currently displayed post.
 *  Relations are defined by tags: if post tags match, the post will be displayed as related
 */
global $avia_config;

$is_portfolio 		= false; //avia_is_portfolio_single();
$columns 			= 8;
$image_size 		= 'square';
$related_posts 		= false;
$this_id 			= $post->ID;
$post_class         = "av_one_eighth no_margin ";
$slidecount 		= 0;
$postcount 			= ($columns * 1);
$format 			= "";
$fake_image			= "";

$tags               = wp_get_post_tags($this_id);


if (!empty($tags) && is_array($tags))
{
     $tag_ids = array();
     foreach ($tags as $tag ) $tag_ids[] = (int)$tag->term_id;

     if(!empty($tag_ids))
     {


        $my_query = get_posts(
                            array(
                                'tag__in' => $tag_ids,
                                'post_type' => get_post_type($this_id),
                                'showposts'=>$postcount, 'ignore_sticky_posts'=>1,
                                'orderby'=>'rand',
                                'post__not_in' => array($this_id))
                            );


  		if (!empty($my_query))
  		{
  			$extra = 'alpha';
  			$count = 1;
  			$output = "";

  			//create seperator

     		$output .= "<div class ='related_posts'>";


     		$output .= "<h5 class='related_title'>".__('You might also like', 'avia_framework')."</h5>";
     		$output .= "<div class='related_entries_container '>";

     		foreach ($my_query as $related_post)
     		{
     			$related_posts = true;
     			$slidecount ++;
     			$format = "";
     			if($is_portfolio) $format = "portfolio";
     			if(!$format) $format = get_post_format($related_post->ID);
     			if(!$format) $format = 'standard';

	 			$post_thumb = get_the_post_thumbnail( $related_post->ID, $image_size );
     			$image 		= $post_thumb ? $post_thumb : "<span class='related_posts_default_image'>{image}</span>";
     			$fake_image = $post_thumb ? $post_thumb : $fake_image;
     			$extra_class= $post_thumb ? "" : "related-format-visible";

     			$output .= "<div class='$post_class $extra relThumb relThumb".$count." post-format-".$format." related_column'>\n";
	 			$output .= "	<a href='".get_permalink($related_post->ID)."' class='relThumWrap noLightbox'>\n";
     			$output .= "	<span class='related_image_wrap' data-avia-related-tooltip='".$related_post->post_title."'>";
	 			$output .= 		$image;
	 			$output .= "	<span class='related-format-icon avia-font-entypo-fontello {$extra_class}'><span class='related-format-icon-inner'>".$avia_config['font_icons'][$format]."</span></span>";
	 			$output .= "	</span>";
	 			$output .= "	</a>";
	 			$output .= "</div>";

     			$count++;
     			$extra = "";

     			if($count == count($my_query)) $extra = 'omega';

     		}


     		$output .= "</div></div>";
     		$output = str_replace("{image}",$fake_image,$output);

     		if($related_posts) echo $output;

     	}

     	wp_reset_query();
    }
}

