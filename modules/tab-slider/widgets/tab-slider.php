<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


add_action('widgets_init', create_function('', "register_widget('gmp_Video_Tabs');"));
class gmp_Video_Tabs extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'gmp_video_tab', 'description' => __('Displays Video Tab Slider', 'gmp') );
		$control_ops = array( 'width' => 200, 'height' => 100, 'id_base' => 'gmp-video-tabs' );
		$this->WP_Widget( 'gmp-video-tabs', __('GMP - Video Tabs', 'gmp'), $widget_ops, $control_ops );
	}

	function widget($args, $instance) {
		extract($args);
                
		$slidesPrefix = '_gmp_tab_slider_setting_';
		
                $instance = wp_parse_args( ( array ) $instance, array(
                    'title'     => '',
                    'slides'    => genesis_get_option( $slidesPrefix .'quantity', GMP_SETTINGS_FIELD ),
                    'slideshow' => genesis_get_option( $slidesPrefix .'slideshow', GMP_SETTINGS_FIELD ),
                        ) );
                

		echo $before_widget;

                gmp_slideshow($instance);			

		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		return $new_instance;
	}

	function form($instance) {
		
		$slidesPrefix = '_gmp_tab_slider_setting_';

		$instance = wp_parse_args( ( array ) $instance, array(
                    'title'     => '',
                    'slides'    => genesis_get_option( $slidesPrefix .'quantity', GMP_SETTINGS_FIELD ),
                    'slideshow' => genesis_get_option( $slidesPrefix .'slideshow', GMP_SETTINGS_FIELD ),
                        ) );

        ?>

                <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (will not be shown in output)', 'gmp' ); ?>:</label>
                    <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" style="width:99%;" /></p>

                <div style="background: #f1f1f1; border: 1px solid #DDD; padding: 10px 10px 0px 10px;">

                <p><label for="<?php echo $this->get_field_id( 'slides' ); ?>"><?php _e( 'Number of Slides to Show', 'gmp' ); ?>:</label>
                    <input type="text" id="<?php echo $this->get_field_id( 'slides' ); ?>" name="<?php echo $this->get_field_name( 'slides' ); ?>" value="<?php echo esc_attr( $instance['slides'] ); ?>" /></p>

                <p><label for="<?php echo $this->get_field_id( 'slideshow' ); ?>"><?php _e( 'Slideshow', 'gmp' ); ?>:</label>
                    <select id="<?php echo $this->get_field_id( 'slideshow' ); ?>" name="<?php echo $this->get_field_name( 'slideshow' ); ?>">
                        <option value="" <?php selected( '', $instance['slideshow'] ); ?>><?php _e( 'Select', 'gmp' ); ?></option>
                             <?php                           
					$terms = get_terms( 'slideshow', 'hide_empty=0' );
                                        
					foreach ( $terms as $term ) 
                                            echo '<option value="' . $term->slug . '"  ' , $instance['slideshow'] == $term->slug ? 'selected="selected"' : '' ,' >' . $term->name . '</option>';
                             ?>
                    </select></p>
        </div>

        <?php
                
	}
}
