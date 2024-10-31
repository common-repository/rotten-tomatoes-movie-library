<?php
class NT_Movie_Options {
	
	private $sections;
	private $checkboxes;
	private $settings;
	
	/**
	 * Construct
	 *
	 * @since 1.0
	 */
	public function __construct() {
		
		// This will keep track of the checkbox options for the validate_settings function.
		$this->checkboxes = array();
		$this->settings = array();
		$this->nt_movie_get_settings();
		
		$this->sections['general']      = __( 'General Settings' );
		
		add_action( 'admin_menu', array( $this, 'nt_movie_option_page' ) );
		add_action( 'admin_init', array( &$this, 'nt_movie_register_settings' ) );
		
		if ( ! get_option( 'nt_movie_options' ) )
			$this->nt_movie_initialize_settings();
		
	}
	
	/**
	 * Add options page
	 *
	 * @since 1.0
	 */
	public function nt_movie_option_page() {
		
		$admin_page = add_submenu_page(
			'edit.php?post_type=movies', 
			'Settings', 
			'Settings', 
			'manage_options', 
			'nt_movie_settings', array( $this, 'nt_movie_display_page' ));
		
		add_action( 'admin_print_scripts-' . $admin_page, array( &$this, 'nt_movie_scripts' ) );
		add_action( 'admin_print_styles-' . $admin_page, array( &$this, 'nt_movie_styles' ) );
		
	}
	
	/**
	 * Create settings field
	 *
	 * @since 1.0
	 */
	public function nt_movie_create_setting( $args = array() ) {
		
		$defaults = array(
			'id'      => 'default_field',
			'title'   => __( 'Default Field' ),
			'desc'    => __( 'This is a default description.' ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'general',
			'choices' => array(),
			'class'   => ''
		);
			
		extract( wp_parse_args( $args, $defaults ) );
		
		$field_args = array(
			'type'      => $type,
			'id'        => $id,
			'desc'      => $desc,
			'std'       => $std,
			'choices'   => $choices,
			'label_for' => $id,
			'class'     => $class
		);
		
		if ( $type == 'checkbox' )
			$this->checkboxes[] = $id;
		
		add_settings_field( $id, $title, array( $this, 'nt_movie_display_setting' ), 'nt_movie_settings', $section, $field_args );
	}
	
	/**
	 * Display options page
	 *
	 * @since 1.0
	 */
	public function nt_movie_display_page() {
		
		echo '<div class="wrap">
	<div class="icon32" id="icon-options-general"></div>
	<h2>' . __( 'Settings' ) . '</h2>';
	
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == true )
			echo '<div class="updated fade"><p>' . __( 'Plugin options updated.' ) . '</p></div>';
		
		echo '<form action="options.php" method="post">';
	
		settings_fields( 'nt_movie_options' );
		echo '<div class="ui-tabs">
			<ul class="ui-tabs-nav">';
		
		foreach ( $this->sections as $section_slug => $section )
			echo '<li><a href="#' . $section_slug . '">' . $section . '</a></li>';
		
		echo '</ul>';
		do_settings_sections( $_GET['page'] );
		
		echo '</div>
		<p class="submit"><input name="Submit" type="submit" class="button-primary" value="' . __( 'Save Changes' ) . '" /></p>
		
	</form>';
	
	echo '<script type="text/javascript">
		jQuery(document).ready(function($) {
			var sections = [];';
			
			foreach ( $this->sections as $section_slug => $section )
				echo "sections['$section'] = '$section_slug';";
			
			echo 'var wrapped = $(".wrap h3").wrap("<div class=\"ui-tabs-panel\">");
			wrapped.each(function() {
				$(this).parent().append($(this).parent().nextUntil("div.ui-tabs-panel"));
			});
			$(".ui-tabs-panel").each(function(index) {
				$(this).attr("id", sections[$(this).children("h3").text()]);
				if (index > 0)
					$(this).addClass("ui-tabs-hide");
			});
			$(".ui-tabs").tabs({
				fx: { opacity: "toggle", duration: "fast" }
			});
			
			$("input[type=text], textarea").each(function() {
				if ($(this).val() == $(this).attr("placeholder") || $(this).val() == "")
					$(this).css("color", "#999");
			});
			
			$("input[type=text], textarea").focus(function() {
				if ($(this).val() == $(this).attr("placeholder") || $(this).val() == "") {
					$(this).val("");
					$(this).css("color", "#000");
				}
			}).blur(function() {
				if ($(this).val() == "" || $(this).val() == $(this).attr("placeholder")) {
					$(this).val($(this).attr("placeholder"));
					$(this).css("color", "#999");
				}
			});
			
			$(".wrap h3, .wrap table").show();
			
			// This will make the "warning" checkbox class really stand out when checked.
			// I use it here for the Reset checkbox.
			$(".warning").change(function() {
				if ($(this).is(":checked"))
					$(this).parent().css("background", "#c00").css("color", "#fff").css("fontWeight", "bold");
				else
					$(this).parent().css("background", "none").css("color", "inherit").css("fontWeight", "normal");
			});
			
			// Browser compatibility
			if ($.browser.mozilla) 
			         $("form").attr("autocomplete", "off");
		});
	</script>
</div>';
		
	}
	
	/**
	 * Description for section
	 *
	 * @since 1.0
	 */
	public function nt_movie_display_section() {
		// code
	}
	
	/**
	 * HTML output for text field
	 *
	 * @since 1.0
	 */
	public function nt_movie_display_setting( $args = array() ) {
		
		extract( $args );
		
		$options = get_option( 'nt_movie_options' );
		
		if ( ! isset( $options[$id] ) && $type != 'checkbox' )
			$options[$id] = $std;
		elseif ( ! isset( $options[$id] ) )
			$options[$id] = 0;
		
		$field_class = '';
		if ( $class != '' )
			$field_class = ' ' . $class;
		
		switch ( $type ) {
			
			case 'heading':
				echo '</td></tr><tr valign="top"><td colspan="2"><h4>' . $desc . '</h4>';
				break;
			
			case 'checkbox':
				
				echo '<input class="checkbox' . $field_class . '" type="checkbox" id="' . $id . '" name="nt_movie_options[' . $id . ']" value="true" ' . checked( $options[$id], 'true', false ) . ' /> <label for="' . $id . '"><span class="description">' . $desc . '</span></label>';
				
				break;
			
			case 'select':
				echo '<select class="select' . $field_class . '" name="nt_movie_options[' . $id . ']">';
				
				foreach ( $choices as $value => $label )
					echo '<option value="' . esc_attr( $value ) . '"' . selected( $options[$id], $value, false ) . '>' . $label . '</option>';
				
				echo '</select>';
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'radio':
				$i = 0;
				foreach ( $choices as $value => $label ) {
					echo '<input class="radio' . $field_class . '" type="radio" name="nt_movie_options[' . $id . ']" id="' . $id . $i . '" value="' . esc_attr( $value ) . '" ' . checked( $options[$id], $value, false ) . '> <label for="' . $id . $i . '">' . $label . '</label>';
					if ( $i < count( $options ) - 1 )
						echo '<br />';
					$i++;
				}
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'textarea':
				echo '<textarea class="' . $field_class . '" id="' . $id . '" name="nt_movie_options[' . $id . ']" placeholder="' . $std . '" rows="5" cols="30">' . wp_htmledit_pre( $options[$id] ) . '</textarea>';
				
				if ( $desc != '' )
					echo '<br /><span class="description">' . $desc . '</span>';
				
				break;
			
			case 'text':
			default:
		 		echo '<input class="medium-text' . $field_class . '" type="text" id="' . $id . '" name="nt_movie_options[' . $id . ']" placeholder="' . $std . '" value="' . esc_attr( $options[$id] ) . '"  size="25"  />';
		 		
		 		if ( $desc != '' )
		 			echo '<br /><span class="description">' . $desc . '</span>';
		 		
		 		break;
		}
		
	}
	
	/**
	 * Settings and defaults
	 * 
	 * @since 1.0
	 */
	public function nt_movie_get_settings() {
		
		/* General Settings
		===========================================*/
		$this->settings['rt_api'] = array(
			'title'   => __( 'Rotten Tomatoes API Key: ' ),
			'desc'    => __( '' ),
			'std'     => '',
			'type'    => 'text',
			'section' => 'general'
		);
	}
	
	/**
	 * Initialize settings to their default values
	 * 
	 * @since 1.0
	 */
	public function nt_movie_initialize_settings() {
		
		$default_settings = array();
		foreach ( $this->settings as $id => $setting ) {
			if ( $setting['type'] != 'heading' )
				$default_settings[$id] = $setting['std'];
		}
		
		update_option( 'nt_movie_options', $default_settings );
		
	}
	
	/**
	* Register settings
	*
	* @since 1.0
	*/
	public function nt_movie_register_settings() {
		
		register_setting( 'nt_movie_options', 'nt_movie_options', array ( &$this, 'nt_movie_validate_settings' ) );
		
		foreach ( $this->sections as $slug => $title ) {
				add_settings_section( $slug, $title, array( &$this, 'nt_movie_display_section' ), 'nt_movie_settings' );
		}
		
		$this->nt_movie_get_settings();
		
		foreach ( $this->settings as $id => $setting ) {
			$setting['id'] = $id;
			$this->nt_movie_create_setting( $setting );
		}
		
	}
	
	/**
	* jQuery Tabs
	*
	* @since 1.0
	*/
	public function nt_movie_scripts() {
		wp_print_scripts( 'jquery-ui-tabs' );
	}
	
	/**
	* Styling for the theme options page
	*
	* @since 1.0
	*/
	public function nt_movie_styles() {
		
		wp_register_style( 'myplugin-admin', plugins_url('../css/nt-movie-options.css', __FILE__) );
		wp_enqueue_style( 'myplugin-admin' );
		
	}
	
	/**
	* Validate settings
	*
	* @since 1.0
	*/
	public function nt_movie_validate_settings( $input ) {
		
		$options = get_option( 'nt_movie_options' );
		foreach ( $this->checkboxes as $id ) {
			if ( isset( $options[$id] ) && ! isset( $input[$id] ) )
				unset( $options[$id] );
		}
		return $input;
	}
	
}

$nt_movie_options = new NT_Movie_Options();

function nt_movie_options( $option ) {
	
	$options = get_option( 'nt_movie_options' );
	if ( isset( $options[$option] ) )
		return $options[$option];
	else
		return false;
}
$RTApiKey = nt_movie_options('rt_api');
?>