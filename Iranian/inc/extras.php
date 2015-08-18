<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package Decode
 */

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 */

if ( ! function_exists( 'decode_page_menu_args' ) ) {

function decode_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'decode_page_menu_args' );

}

/**
 * Adds custom classes to the array of body classes.
 */
if ( ! function_exists( 'decode_body_classes' ) ) {

function decode_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	return $classes;
}
}
add_filter( 'body_class', 'decode_body_classes' );

/**
 * Filter in a link to a content ID attribute for the next/previous image links on image attachment pages
 */
if ( ! function_exists( 'decode_enhanced_image_navigation' ) ) {

function decode_enhanced_image_navigation( $url, $id ) {
	if ( ! is_attachment() && ! wp_attachment_is_image( $id ) )
		return $url;

	$image = get_post( $id );
	if ( ! empty( $image->post_parent ) && $image->post_parent != $id )
		$url .= '#main';

	return $url;
}
}
add_filter( 'attachment_link', 'decode_enhanced_image_navigation', 10, 2 );

/**
 * Highlight search terms in search results.
 */
function decode_highlight_search_results( $text ) {
    if ( is_search() ) {
    	$sr = get_search_query();
		$keys = implode( '|', explode( ' ', get_search_query() ) );
		if ($keys != '') { // Check for empty search, and don't modify text if empty
			$text = preg_replace( '/(' . $keys .')/iu', '<mark class="search-highlight">\0</mark>', $text );
		}
    }
    return $text;
}
add_filter( 'the_excerpt', 'decode_highlight_search_results' );
add_filter( 'the_title', 'decode_highlight_search_results' );

/**
 * Link to post in excerpt [...] links.
 */
if ( ! function_exists( 'link_ellipses' ) ) {

function link_ellipses( $more ) {
	if ( ! is_search() ) {
	return ' <a class="read-more" href="'. get_permalink( get_the_ID() ) . '">[&hellip;]</a>';
	}
}
}
add_filter( 'excerpt_more', 'link_ellipses' );

if ( ! function_exists( '_wp_render_title_tag' ) ) :
	/**
	 * Filters wp_title to print a neat <title> tag based on what is being viewed.
	 *
	 * @param string $title Default title text for current view.
	 * @param string $sep Optional separator.
	 * @return string The filtered title.
	 */
	function decode_wp_title( $title, $sep ) {
		if ( is_feed() ) {
			return $title;
		}
		global $page, $paged;
		// Add the blog name
		$title .= get_bloginfo( 'name', 'display' );
		// Add the blog description for the home/front page.
		$site_description = get_bloginfo( 'description', 'display' );
		if ( $site_description && ( is_home() || is_front_page() ) ) {
			$title .= " $sep $site_description";
		}
		// Add a page number if necessary:
		if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
			$title .= " $sep " . sprintf( __( 'Page %s', 'decode' ), max( $paged, $page ) );
		}
		return $title;
	}
	add_filter( 'wp_title', 'decode_wp_title', 10, 2 );
endif;
if ( ! function_exists( '_wp_render_title_tag' ) ) :
	/**
	 * Title shim for sites older than WordPress 4.1.
	 *
	 * @link https://make.wordpress.org/core/2014/10/29/title-tags-in-4-1/
	 * @todo Remove this function when WordPress 4.3 is released.
	 */
	function decode_render_title() {
		?>
		<title><?php wp_title( '|', false, 'right' ); ?></title>
		<?php
	}
	add_action( 'wp_head', 'decode_render_title' );
endif;

/**
 * Sets the authordata global when viewing an author archive.
 *
 * This provides backwards compatibility for WP versions below 3.7
 * that don't have this change:
 * http://core.trac.wordpress.org/changeset/25574.
 *
 * It removes the need to call the_post() and rewind_posts() in an author
 * template to print information about the author.
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @return void
 */
if ( ! function_exists( 'decode_setup_author' ) ) {

function decode_setup_author() {
    global $wp_query;

    if ( $wp_query->is_author() && isset( $wp_query->post ) ) {
            $GLOBALS['authordata'] = get_userdata( $wp_query->post->post_author );
    }
}
}
add_action( 'wp', 'decode_setup_author' );