<?php
/*
Plugin Name: Access-a-Read
Description: Add a widget to your site that lets readers change font colors and size to make it more accessible
Author: Cat Rymer
Version: 1.0
*/

add_action( 'wp_enqueue_scripts', 'clr_enqueue_scripts' );

function clr_enqueue_scripts() {
	wp_enqueue_script( 'change_fonts_javascript', plugin_dir_url(__FILE__) . 'clr-js-changefonts.js', array('jquery'), '1.0', true );

	wp_localize_script( 'change_fonts_javascript', 'clr_fontchanger', array( 
		'ajax_url' => admin_url( 'admin-ajax.php' ) )
	);
}

add_action( 'wp_ajax_clr_submit_fontchange', 'clr_submit_fontchange' );
add_action( 'wp_ajax_nopriv_clr_submit_fontchange', 'clr_submit_fontchange' );

add_action( 'wp_ajax_clr_stored_fontchange', 'clr_stored_fontchange' );
add_action( 'wp_ajax_nopriv_clr_stored_fontchange', 'clr_stored_fontchange' );

function clr_submit_fontchange() {
	
	parse_str( $_POST['value'], $submitted_form_data );
	
	if ( ! wp_verify_nonce( $submitted_form_data['access-a-nonce'], 'request-font-change' ) )
		wp_send_json_error( "invalid nonce" );
	
	$submitted_form_data = clr_check_valid_inputs( $submitted_form_data );
	
	if ( $submitted_form_data == false ) {
		wp_send_json_error( 'Invalid inputs. Please enter a Hex code for color and a percent for size.');
	}
	
	$submitted_form_data[ 'fontcolor' ] = sanitize_text_field( $submitted_form_data[ 'fontcolor' ] );
	$submitted_form_data[ 'fontsize' ] = sanitize_text_field( $submitted_form_data[ 'fontsize' ] );
	
	if ( is_user_logged_in() ) {
		update_user_option( get_current_user_id(), 'accessaread_font_preferences', $submitted_form_data );
	}
	
    setcookie('accessaread_cookie[fontcolor]', $submitted_form_data[ 'fontcolor' ], time() + ( 5 * YEAR_IN_SECONDS ) );
    setcookie('accessaread_cookie[fontsize]', $submitted_form_data[ 'fontsize' ], time() + ( 5 * YEAR_IN_SECONDS ) );

	wp_send_json_success( $submitted_form_data );	
	
}

function clr_check_valid_inputs( $submitted_form_data ) {

    // function modified from http://php.net/manual/en/function.ctype-xdigit.php#60707
    
    $fontcolor = $submitted_form_data[ 'fontcolor' ];
    $fontcolor = ltrim( $fontcolor, '#' );

    if ( ! ctype_xdigit( $fontcolor ) || ( strlen( $fontcolor ) !== 6 && strlen( $fontcolor ) !== 3 ) ) {
    	return false;
    }
    else {
	    $submitted_form_data[ 'fontcolor' ] = '#' . $fontcolor;
    }
    
    $fontsize = $submitted_form_data[ 'fontsize' ];
    $fontsize = rtrim( $fontsize, '%' );
    
    if ( ! ctype_digit( $fontsize ) ) {
	    return false;
    }
    else {
	    $submitted_form_data[ 'fontsize' ] = $fontsize . '%';
    }
    
    return $submitted_form_data;

}

function clr_stored_fontchange() {
	
	$stored_font_preferences = $_COOKIE[ 'accessaread_cookie' ];
	
	if ( empty( $stored_font_preferences ) && is_user_logged_in() ) {
		$stored_font_preferences = get_user_option( 'accessaread_font_preferences', get_current_user_id() );
	}
	
	if ( empty( $stored_font_preferences ) ) {
		wp_send_json_error();
	}
	
	wp_send_json_success( $stored_font_preferences );
}

class clr_access_a_read_widget extends WP_Widget {

	function __construct() {

		parent::__construct( false, 'Access_a_Read' );
	}

	function widget( $args, $instance ) {

		$title = $instance['title'];

		echo $args['before_widget'];
		echo $args['before_title'] . $title . $args['after_title'];

		?>
                <form id='font-form'>
                	<?php wp_nonce_field( 'request-font-change', 'access-a-nonce' ); ?>
                	Font Color:<br /><input type='text' name='fontcolor' id='fontcolor'><br />
                    Font Size: <br /><input type='text' name='fontsize' id='fontsize'><br />
                    <br /><input type='submit' value='submit'>
                </form> 
		<?php
		echo $args['after_widget'];
	}

	function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array( 'title' => 'Access-a-Read') );

		$title = esc_attr( $instance['title'] );
		?>
		<p>
			<label> Title: </label>
			<input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<?php
	}
}

function myplugin_register_widgets() {
	register_widget( 'clr_access_a_read_widget' );
}

add_action( 'widgets_init', 'myplugin_register_widgets' );
