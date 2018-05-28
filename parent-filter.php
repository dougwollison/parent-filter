<?php
/*
Plugin Name: Parent Filter
Plugin URI: https://github.com/dougwollison/parent-filter
Description: Simply adds parent filtering capability to hierarchal WordPress post types.
Version: 1.0.0
Author: Doug Wollison
Author URI: http://dougw.me
Tags: parent filter, filter, parent, hierarchy
License: GPL2
Text Domain: parent-filter
*/

// Setup the hooks only within the admin
if ( is_admin() ) {
	add_filter( 'query_vars', 'parentfilter_add_query_var' );
	add_action( 'restrict_manage_posts', 'parentfilter_add_dropdown' );
}

/**
 * Filter the query variable whitelist; add post_parent.
 *
 * @since 1.0.0
 *
 * @param array $vars The whitelist of query variables.
 *
 * @return array The modified whitelist.
 */
function parentfilter_add_query_var( $vars ) {
	$vars[] = 'post_ancestor';
	return $vars;
}

/**
 * Add the dropdown filter listing all available parent posts to filter by.
 *
 * @since 1.0.0
 *
 * @global string $typenow The current post type being managed.
 */
function parentfilter_add_dropdown() {
	global $typenow;

	$post_type_obj = get_post_type_object( $typenow );

	if ( $post_type_obj->hierarchical ) {
		// Build the query for the dropdown
		$request = array(
			'post_type'      => $typenow,
			'post_parent'    => '',
			'posts_per_page' => -1,
			'orderby'        => array( 'menu_order', 'title' ),
			'order'          => 'asc',
			'selected'       => null,
			// Identify the context of the query for 3rd parties
			'plugin-context' => 'parent-filter',
		);

		// Update the selected option if needed
		if ( isset( $_GET['post_ancestor'] ) ) {
			$request['selected'] = $_GET['post_ancestor'];
		}

		// Run the query
		$query = new WP_Query( $request );

		// Print the dropdown
		echo '<select name="post_ancestor" id="post_ancestor">';
			// Print the no filtering option
			echo '<option value="">' . __( 'Any Parent', 'parent-fitler' ) . '</option>';
			// Print the 0 option for showing only top level posts
			echo '<option value="0"' . ( $request['selected'] === '0' ? ' selected="selected"' : '' ) . '>' .
				__( '&mdash; None/Root &mdash;', 'parent-filter' ) . '</option>';
			// Print the queried items
			echo walk_page_dropdown_tree( $query->posts, 0, $request );
		echo '</select>';
	}
}
