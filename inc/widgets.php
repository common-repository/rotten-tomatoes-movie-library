<?php
/**
 * Recent Projects
 *
 * @since 1.0
 */
class nt_Movie_Widget extends WP_Widget {
		/** constructor */	
	function __construct() {
    	$widget_ops = array(
			'classname'   => '', 
			'description' => __('Shows Movies in Sidebar.')
		);
    	parent::__construct('nt_movie_widget', __('Movies'), $widget_ops);
	}
	function widget($args, $instance) {
			extract( $args );
			$cats = array();
			$title = apply_filters( 'widget_title', empty($instance['title']) ? 'List of Movies' : $instance['title'], $instance, $this->id_base);	
			$number = isset($instance['number']) ? abs($instance['number']) : 5;			
			$genres = isset($instance['genres']) ? $instance['genres'] : array();
			$cast = isset($instance['cast']) ? $instance['cast'] : array();
			$thumbnail = $instance['thumbnail'];
			echo $before_widget;
			echo $before_title;
			echo $instance["title"];
			echo $after_title;
			// Get Data
			echo do_shortcode('[nt_movies_widget id="'.$this->id.'" genres="'.implode(",",array_values($genres)).'" cast="'.implode(",",array_values($cast)).'" items="'.$number.'" thumbnail="'.$thumbnail.'"]' );
			wp_reset_query();
			echo $after_widget;
		}
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$instance['title'] = stripslashes(strip_tags($new_instance['title']));
			$instance['number'] = stripslashes(strip_tags($new_instance['number']));
			$instance['genres'] = $new_instance['genres'];
			$instance['cast'] = $new_instance['cast'];
			$instance['thumbnail'] = isset($new_instance['thumbnail']) ? $new_instance['thumbnail'] : '';
			return $instance;
		}
		
		function form( $instance ) {
			$title = isset($instance['title']) ? esc_attr($instance['title']) : 'List of Movies';
			$number = isset($instance['number']) ? absint($instance['number']) : 5;
			$genres = isset($instance['genres']) ? $instance['genres'] : ''; 
			$cast = isset($instance['cast']) ? $instance['cast'] : ''; 
			$thumbnail = $instance['thumbnail'];
		?>
        
        <p>
          <label for="<?php echo $this->get_field_id('title'); ?>">
            <?php _e('Title:'); ?>
          </label>
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('number'); ?>">
            <?php _e('Number of Movies to show:'); ?>
          </label>
          <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id('genres'); ?>">
            <?php _e('Genres:');?>
          </label>
          <select id="<?php echo $this->get_field_id('genres'); ?>" name="<?php echo $this->get_field_name('genres'); ?>[]" multiple="multiple" style="width:100%;">
            <?php
			 $arGenres =  get_terms('genres',array( 'parent' => 0 , 'hide_empty'    => false));
			 foreach ($arGenres as $valGenres) {
				 echo '<option value="'.$valGenres->slug.'" '.((in_array($valGenres->slug ,$genres)) ? "selected":""). '>'.$valGenres->name.'</option>';
			 }
			?>
          </select>
        </p>        
        <p>
          <label for="<?php echo $this->get_field_id('cast'); ?>">
            <?php _e('Cast:');?>
          </label>
          <select id="<?php echo $this->get_field_id('cast'); ?>" name="<?php echo $this->get_field_name('cast'); ?>[]" multiple="multiple" style="width:100%;">
            <?php
			 $arCast =  get_terms('cast',array( 'parent' => 0 , 'hide_empty'    => false));
			 foreach ($arCast as $valCast) {
				 echo '<option value="'.$valCast->slug.'" '.((in_array($valCast->slug,$cast)) ? "selected":""). '>'.$valCast->name.'</option>';
			 }
			?>
          </select>
        </p>
        <p><label for="<?php echo $this->get_field_id('thumbnail'); ?>"><?php _e('Display Thumbnail:'); ?></label>
        <input id="<?php echo $this->get_field_id('thumbnail'); ?>" name="<?php echo $this->get_field_name('thumbnail'); ?>" value="true" type="checkbox" <?php echo ($thumbnail=='true') ? "checked":"";?> /></p>

<?php }
}

/* Register Widgets.
 *
 * @since 1.0
 */
function nt_register_movie_widgets() {
	register_widget( 'nt_Movie_Widget' );
}

add_action( 'widgets_init', 'nt_register_movie_widgets' );
?>
