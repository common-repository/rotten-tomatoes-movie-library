<?php
/*
Plugin Name: RT Movie Library
Plugin URI: http://www.NtechCorporate.com
Description: 
Author: ntech-technologies
Author URI: http://www.NtechCorporate.com
Version: 1.1
*/

// Create Custom Post Type and Taxonomy
function nt_movies_init() {
	$labels = array(
    	'name' => _x('Movies', 'movies general name'),
    	'singular_name' => _x('Movie', 'movies singular name'),
    	'add_new' => _x('Add New Movie','Movie'),
    	'add_new_item' => __('Add New Movie'),
    	'edit_item' => __('Edit Movie'),
    	'new_item' => __('New Movie'),
    	'view_item' => __('View Movie'),
    	'search_items' => __('Search Movie'),
    	'not_found' =>  __('No Movie found'),
    	'not_found_in_trash' => __('No Movie found in Trash'), 
    	'parent_item_colon' => ''
	);		
	$args = array(
    	'labels' => $labels,
    	'public' => true,
    	'publicly_queryable' => true,
    	'show_ui' => true, 
    	'query_var' => true,
    	'rewrite' => true,
    	'capability_type' => 'post',
    	'hierarchical' => false,
    	'menu_position' => null,
    	'supports' => array('title', 'editor','thumbnail'),
    	'menu_icon' => ''
	); 		

	register_post_type( 'movies', $args );
	
	$labels = array(			  
  	  'name' => _x( 'Genres', 'Genres general name' ),
  	  'singular_name' => _x( 'Genre', 'Genres singular name'),
  	  'search_items' =>  __( 'Search Genres'),
  	  'all_items' => __( 'All Genres' ),
  	  'parent_item' => __( 'Parent Genres' ),
  	  'parent_item_colon' => __( 'Parent Genres'),
  	  'edit_item' => __( 'Edit Genre' ), 
  	  'update_item' => __( 'Update Genre'),
  	  'add_new_item' => __( 'Add New Genre'),
  	  'new_item_name' => __( 'New Genre'),
  	); 							  
  	
  	register_taxonomy(
		'genres',
		'movies',
		array(
			'public'=>true,
			'hierarchical' => true,
			'labels'=> $labels,
			'query_var' => 'genres',
			'show_ui' => true,
			'rewrite' => array( 'slug' => 'genres', 'with_front' => false ),
		)
	);
	$labels = array(			  
  	  'name' => _x( 'Cast', 'Cast general name' ),
  	  'singular_name' => _x( 'Cast', 'Cast singular name'),
  	  'search_items' =>  __( 'Search Cast'),
  	  'all_items' => __( 'All Cast' ),
  	  'parent_item' => __( 'Parent Cast' ),
  	  'parent_item_colon' => __( 'Parent Cast'),
  	  'edit_item' => __( 'Edit Cast' ), 
  	  'update_item' => __( 'Update Cast'),
  	  'add_new_item' => __( 'Add New Cast'),
  	  'new_item_name' => __( 'New Cast'),
  	); 		
	register_taxonomy(
		'cast',
		'movies',
		array(
			'public'=>true,
			'hierarchical' => true,
			'labels'=> $labels,
			'query_var' => 'cast',
			'show_ui' => true,
			'rewrite' => array( 'slug' => 'cast', 'with_front' => false ),
		)
	);		
	
	/* Register our stylesheet. */
	wp_register_style( 'nt_movies_grid_css', plugins_url('/css/grid.css', __FILE__) );  
} 
								  
add_action('init', 'nt_movies_init');

// Admin Menu Icon Style Function
function add_menu_review_icons_styles(){
?> 
<style type="text/css">
#adminmenu .menu-icon-movies div.wp-menu-image:before {content: "\f126";}
</style> 
<?php
}
// Call Admin Menu Icon Style Function
add_action( 'admin_head', 'add_menu_review_icons_styles' );

/* creating custom fields */
$postmetas = 
	array (
		'movies' => array(
			array("section" => "Movie Info", "id" => "year", "type" => "text", "title" => "Year", "description" => "Enter Year"),
			array("section" => "Movie Info", "id" => "directed_by", "type" => "text", "title" => "Directed By", "description" => "Enter Director Name"),
			array("section" => "Movie Info", "id" => "release_date", "type" => "text", "title" => "In Theaters:", "description" => "Enter Release Date"),
			array("section" => "Movie Info", "id" => "on_dvd", "type" => "text", "title" => "On DVD", "description" => "Enter DVD Release Date"),
			array("section" => "Movie Info", "id" => "critics_rating", "type" => "text", "title" => "Critics Rating", "description" => "Enter Critics Rating"),
			array("section" => "Movie Info", "id" => "critics_score", "type" => "text", "title" => "Critics Score", "description" => "Enter Critics Score"),
			array("section" => "Movie Info", "id" => "audience_rating", "type" => "text", "title" => "Audience Rating", "description" => "Enter Audience Rating"),
			array("section" => "Movie Info", "id" => "audience_score", "type" => "text", "title" => "Audience Score", "description" => "Enter Audience Score"),
			array("section" => "Movie Info", "id" => "studio", "type" => "text", "title" => "Studio", "description" => "Enter Studio"),
			array("section" => "Movie Info", "id" => "url", "type" => "text", "title" => "Movie Link", "description" => "Enter Movie URL"),
		),
);

// Create Meta Box 
function nt_create_meta_box() {
	global $postmetas;	
	if ( function_exists('nt_meta_box') && isset($postmetas) && count($postmetas) > 0 ) {  
		foreach($postmetas as $key => $postmeta)
		{
			if(!empty($postmeta))
			{
				add_meta_box( 'metabox', ucfirst($key).' Options', 'nt_meta_box', $key, 'side', 'high' );
			}
		}
	}
}  

// Create Custom Post Meta 
function nt_meta_box() {
	global $post, $postmetas;
	echo '<input type="hidden" name="myplugin_noncename" id="myplugin_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';	
	$meta_section = '';
	foreach ( $postmetas as $key => $postmeta ) {	
		foreach ( $postmeta as $postmeta_key => $each_meta ) {		
			if(isset($postmeta['section']))
			{
				$meta_section = $postmeta['section'];
			}			
			echo "<strong>".$each_meta['title']."</strong>";			
			echo "<input type='text' name='".$each_meta['id']."' id='".$each_meta['id']."' class='' value='".get_post_meta($post->ID, $each_meta['id'], true)."' style='width:99%' />";			
			echo "<div>".$each_meta['description']."</div><br/>";
		}		
	}	
	echo '<br/>';
}

// Save Custom Post Meta
function nt_link_save_meta( $post_id ) {

	global $postmetas;
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( isset($_POST['myplugin_noncename']) && !wp_verify_nonce( $_POST['myplugin_noncename'], plugin_basename(__FILE__) )) {
		return $post_id;
	}

	// verify if this is an auto save routine. If it is our form has not been submitted, so we dont want to do anything
	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id;

	// Check permissions
	if ( isset($_POST['post_type']) && 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) )
			return $post_id;
		} else {
		if ( !current_user_can( 'edit_post', $post_id ) )
			return $post_id;
	}

	// OK, we're authenticated
	if ( $parent_id = wp_is_post_revision($post_id) )
	{
		$post_id = $parent_id;
	}
	
	foreach ( $postmetas as $postmeta ) {
		foreach ( $postmeta as $each_meta ) {	
			if (isset($_POST[$each_meta['id']]) && $_POST[$each_meta['id']]) {
				update_review_custom_meta($post_id, $_POST[$each_meta['id']], $each_meta['id']);
			}
			
			if (isset($_POST[$each_meta['id']]) && $_POST[$each_meta['id']] == "") {
				delete_post_meta($post_id, $each_meta['id']);
			}
			
			if (!isset($_POST[$each_meta['id']])) {
				delete_post_meta($post_id, $each_meta['id']);
			}		
		}
	}
}

// Update Custom Post Meta
function update_review_custom_meta($postID, $newvalue, $field_name) {

	if (!get_post_meta($postID, $field_name)) {
		add_post_meta($postID, $field_name, $newvalue);
	} else {
		update_post_meta($postID, $field_name, $newvalue);
	}
}

// Add Column on Custom Post Type Listing
add_filter( 'manage_movies_posts_columns', 'nt_add_review_thumbnail_col');
function nt_add_review_thumbnail_col($cols) {
	$cols['thumbnail'] = __('Thumbnail');
	return $cols;
}

add_action( 'manage_movies_posts_custom_column', 'nt_get_movie_thumbnail');
function nt_get_movie_thumbnail($column_name ) {
	if ( $column_name  == 'thumbnail'  ) {
		echo get_the_post_thumbnail(get_the_ID(), array(100, 100));
	}
}

//init
add_action('save_post', 'nt_link_save_meta'); 
add_action('admin_menu', 'nt_create_meta_box'); 

// Add Custom Script in Plugin
function nt_enqueue($hook) {
	//Add Jsonp
	wp_enqueue_script( 'jquery.jsonp', plugin_dir_url( __FILE__ ) . 'js/jquery.jsonp-2.4.0.min.js' );
}
add_action( 'admin_enqueue_scripts', 'nt_enqueue' );

// Add Admin Submenu of plugin 
function nt_movies_admin_menu() {
	add_submenu_page('edit.php?post_type=movies', 'Get Movie Info', 'Get Movie Info', 'manage_options', 'movie_info', 'nt_fn_fetch_movie_info' );
	add_submenu_page('edit.php?post_type=movies', 'Search Movies', 'Search Movies', 'manage_options', 'search_movies', 'nt_fn_search_movies' );		
	add_submenu_page('edit.php?post_type=movies', 'Get Movie List', 'Get Movie List', 'manage_options', 'fetch_movies', 'nt_fn_fetch_movie_list' );		
	add_submenu_page('edit.php?post_type=movies', 'Get DVD List', 'Get DVD List', 'manage_options', 'fetch_dvd', 'nt_fn_fetch_dvd_list' );
}
add_action('admin_menu', 'nt_movies_admin_menu');

// Include Files
include_once "inc/settings.php";
include_once "inc/RottenTomatoes.php";
include_once "inc/movie_info.php";
include_once "inc/search_movie.php";
include_once "inc/movie_list.php";
include_once "inc/dvd_list.php";
include_once "inc/widgets.php";

// shortcode Function of Movies Widget
function nt_fn_movies_widget($atts, $content) {
	 wp_enqueue_style( 'nt_movies_grid_css' );
	//extract short code attr
	extract(shortcode_atts(array(
		'genres' => '',
		'cast' => '',
		'items' => '',
		'thumbnail'=>''
	), $atts));
	
	if(!is_numeric($items))
	{
		$items = '';
	}
	$count_column = 1;
	$return_html ='';
	
	$movies_order = 'ASC';
	$movies_order_by = 'menu_order';
	//Get Movies
	$args = array(
	    'numberposts' => $items,
		'posts_per_page'  => $items,
	    'order' => $movies_order,
	    'orderby' => $movies_order_by,
	    'post_type' => array('movies'),
	);
	
	if(!empty($genres))
	{
		$args['genres'] = $genres;
	}
	if(!empty($cast))
	{
		$args['cast'] = $cast;
	}
	
	$arMovies = get_posts($args);
	if(!empty($arMovies) && is_array($arMovies))
	{
		//Begin display HTML
		$return_html.= '<div class="group"><ul>';
		foreach($arMovies as $key => $movie)
		{
			$small_image_url = '';
			$movieID = $movie->ID;
			$return_html.='<li>';
			$return_html.='<div class="col span_4_of_5">';
			$titleClass="span_4_of_4";
			if($thumbnail)
			{
				$return_html.='<div class="col span_1_of_4">';
				if(has_post_thumbnail($movieID, 'thumbnail'))
				{
					$imageID = get_post_thumbnail_id($movieID);
					$small_image_url = wp_get_attachment_image_src($imageID, 'thumbnail', true);
					$return_html.='<img class="thumbnail" src="'.$small_image_url[0].'" alt="'.$movie_name.'"/>';
				}
				$return_html.='</div>';
				$titleClass="span_3_of_4";
			}
			$return_html.='<div class="col '.$titleClass.'">';
			$return_html.= '<a href="'.esc_url( get_permalink($movie->ID) ).'">'.$movie->post_title.' ('.get_post_meta($movie->ID,'year',true).')'.'</a>';
			$return_html.='</div></div>';
			$return_html.='<div class="col span_1_of_5">';
			$rate = get_post_meta($movieID, 'critics_score', true);
			$return_html.= ($rate>0) ? $rate."%" : "-";
			$return_html.='</div>';
			$return_html.='<div style="clear:both;"></div>';
			$return_html.='</li>';
		}
		$return_html.= '</ul></div>';
	}
	return $return_html;
}
// Add Shortcode of Movies Widget
add_shortcode('nt_movies_widget', 'nt_fn_movies_widget');

// Shortcode Function of Listing Movies
function nt_fn_movies( $atts ) {	
	wp_enqueue_style( 'nt_movies_grid_css' );
	extract( shortcode_atts( array(  'type' => 'movies'), $atts ) );	
	$paged = get_query_var('paged') ? get_query_var('paged') : 1;  	
	global $post, $query_string;
	query_posts(  array ( 
		//'posts_per_page' => $limit, 
		'post_type' => $type, 
		'order' => 'ASC', 
		'orderby' =>'menu_order', 
		'paged' => $paged ) );
			
	$return_html = ' ';   
	$count =1;	
	
	while ( have_posts() ) { the_post();
		$rate = get_post_meta(get_the_ID(), 'critics_score', true);		
		$return_html .= '<li class="col span_1_of_3">' 
		. '<div><a class="listing-thumb" href="' . get_permalink() . '">' . get_the_post_thumbnail(get_the_ID(), 'thumbnail')  . '</a></div>'
		. '<div><a href="' . get_permalink() . '">' . get_the_title() . '</a></div>' 
		. '<div>'.(($rate>0) ? $rate."%" : "-").'</div>'
		. '</li>';
		$count++;
		if($count==4)
		{
			$count = 1;
			$return_html.='<li class="clear"></li>';
		}
	}
	
	return 
	'<div class="group"><ul>' 
	. $return_html 
	.'<li class="clear"></li></ul>'
	. '<hr /><nav class="navigation clear"><div class="nav-previous">' . get_next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts' ) ) . '</div>'
	. '<div class="nav-next">' . get_previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>' ) ) . '</div>'
	. '</nav></div>' .
	wp_reset_query();
}

// Add Shortcode of Movies Listing
add_shortcode('nt_movies', 'nt_fn_movies');
?>