<?php
/**
 * Template Name: Homepage
 */
get_header(); ?>

<div class="row twelve">

	<div class="col nine">
    	<div class="padding-ten">
<!--
		<div id="videowrapper">	

		<video width="248" height="140" preload="auto" controls="controls" autoplay="autoplay" loop="loop" class="myvideo" >
  			<source src="/wp-content/themes/robbiegod/videos/lighteffect.mp4" type="video/mp4" />
  			<source src="/wp-content/themes/robbiegod/videos/lighteffect.ogg" type="video/ogg" />
		</video>
		<div id="videocover"></div>

		</div>    
		
-->

<?php while ( have_posts() ) : the_post(); ?>

	<?php get_template_part( 'content', 'page' ); ?>

	<?php // comments_template( '', false ); ?>

<?php endwhile; wp_reset_query(); // end of the loop. ?>

		</div>
    </div><!-- # .nine -->


	<div class="col three">
    	<div class="padding-ten">
       		<p>I'm testing the automatic publishing from Github.</p>
        </div>
	</div><!-- col two -->
    
	</div><!-- col twelve -->


<?php get_footer(); ?>