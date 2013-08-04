<?php
	global $avia_config, $more;

	/*
	 * get_header is a basic wordpress function, used to retrieve the header.php file in your theme directory.
	 */
	 get_header();


	 $description = is_tag() ? tag_description() : category_description();
	 echo avia_title(array('title' => avia_which_archive(), 'subtitle' => $description, 'link'=>false));

	 $author_id    = get_query_var( 'author' );
	 $gravatar     = get_avatar( get_the_author_meta('email', $author_id), '75' );
	 $name         = get_the_author_meta('display_name', $author_id);
     $heading      = __("About",'avia_framework') ." ".$name;
     $heading_s    = __("Entries by",'avia_framework') ." ".$name;
	 $description  = get_the_author_meta('description', $author_id);

	 if(empty($description))
	 {
       $description  = __("This author has yet to write their bio.",'avia_framework');
       $description .= '</br>'.sprintf( __( 'Meanwhile lets just say that we are proud %s contributed a whooping %s entries.' ), $name, count_user_posts( $author_id ) );

       if(current_user_can('edit_users') || get_current_user_id() == $author_id)
       {
           $description .= "</br><a href='".admin_url( 'profile.php?user_id=' . $author_id )."'>".__( 'Edit the profile description here.' )."</a>";
	   }
	 }

	?>



		<div class='container_wrap main_color <?php avia_layout_class( 'main' ); ?>'>

			<div class='container template-blog template-author '>

				<div class='content <?php avia_layout_class( 'content' ); ?> units'>

				<div class='page-heading-container clearfix'>
				<?php

                echo "<span class='post-author-format-type blog-meta'><span class='rounded-container'>{$gravatar}</span></span>";
                echo "<div class='author_description '><h3 class='author-title'>{$heading}</h3>".wpautop($description)."<span class='author-extra-border'></span></div>";

				?>
				</div>


				<?php
                echo "<h4 class='extra-mini-title widgettitle'>{$heading_s}</h4>";



				/* Run the loop to output the posts.
				* If you want to overload this in a child theme then include a file
				* called loop-index.php and that will be used instead.
				*/


				$more = 0;
				get_template_part( 'includes/loop', 'author' );
				?>


				<!--end content-->
				</div>

				<?php

				//get the sidebar
				$avia_config['currently_viewing'] = 'blog';
				get_sidebar();

				?>

			</div><!--end container-->




<?php get_footer(); ?>