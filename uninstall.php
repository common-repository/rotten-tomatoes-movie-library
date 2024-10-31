<?php
// If uninstall not called from WordPress exit
if( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

// Delete option from options table
function nt_movies_delete_plugin() {
	global $wpdb;

	$posts = get_posts( array(
		'numberposts' => -1,
		'post_type' => 'movies',
		'post_status' => 'any' ) );

	foreach ( $posts as $post )
	{
		wp_delete_post( $post->ID, true );
	}
	
	// Delete Option
	delete_option('nt_movie_options');
	
	
	/** Delete All the Taxonomies */
	foreach ( array( 'cast','genres') as $taxonomy ) {
		// Prepare & excecute SQL
		$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );
		// Delete Terms
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->term_taxonomy_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
				delete_option( 'prefix_' . $taxonomy->slug . '_option_name' );
			}
		}
		// Delete Taxonomy
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
	}
}

nt_movies_delete_plugin();
?>