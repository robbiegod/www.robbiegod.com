<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the
 * #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Twelve
 * @since Twenty Twelve 1.0
 */
?>

<div class="twelve col">
	<div class="padding-twenty">
    
	<footer id="colophon" role="contentinfo">
		<div class="site-info">
			<?php do_action( 'genstarter_credits' ); ?>
			<a href="<?php echo esc_url( __( 'http://wordpress.org/', 'genstarter' ) ); ?>" title="<?php esc_attr_e( 'Semantic Personal Publishing Platform', 'genstarter' ); ?>"><?php printf( __( 'Proudly powered by %s', 'genstarter' ), 'WordPress' ); ?></a>
		</div><!-- .site-info -->
	</footer><!-- #colophon -->
    
    </div><!-- padding -->
</div><!-- twelve -->


</div><!-- #container -->

<?php wp_footer(); ?>
</body>
</html>