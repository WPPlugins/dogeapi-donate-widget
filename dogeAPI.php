<?php 
	/*
	Plugin Name: DogeAPI Donate
	Plugin URI: http://www.ireland-bitcoin.com
	Description: Plugin for integrating the DogeAPI Donate Widget
	Author: R. Mulcahy
	Version: 1.1.1
	Author URI: http://www.ireland-bitcoin.com
	Wallet ID: DDqN7ruSAMP3qKz2iRfmLcpSZwF9vPTHET
	*/

function dogeAPI_admin() {

	if ($_POST['action'] == 'update')  
	{  
	    $_POST['show_pages'] == 'on' ? update_option('dogeAPI_on_page', 'checked') : update_option('dogeAPI_on_page', '');  
	    $_POST['show_posts'] == 'on' ? update_option('dogeAPI_on_post', 'checked') : update_option('dogeAPI_on_post', '');
	    update_option('animation_type', $_POST['animation_type']); 
	    update_option('dogeAPI_message', $_POST['dogeAPI_message']);  
	    $message = '<div id="message" class="updated fade"><p><strong>Options Saved</strong></p></div>';  
	}  

	$options['page'] = get_option('dogeAPI_on_page'); 
    $options['post'] = get_option('dogeAPI_on_post');
    $options['dogeAPI_message'] = get_option('dogeAPI_message');
    get_option('animation_type') == '' ? $options['animation_type'] = "expand_right" : $options['animation_type']=get_option('animation_type');
    //$options['animation_type'] = get_option('animation_type'); 
     
    if(strlen($options['dogeAPI_message'])==0){
    	 $options['dogeAPI_message']='Send the author to the moon!';
    }

    echo '  
    <div class="wrap">  
        '.$message.'  
        <div id="icon-options-general" class="icon32"><br /></div>  
        <h2>DogeAPI Donate Author Links</h2>  
          
        <p><a href="https://www.dogeapi.com/register">Create a DogeAPI account</a>.</p>

        <form method="post" action="">  
        <input type="hidden" name="action" value="update" />  
          
        <h3>When to Display Author Donate Button</h3> 
        <p>Button will only display if Author Payment Address is set in <a href="'.admin_url().'users.php">User Profiles</a>.</p>  
        <input name="show_pages" type="checkbox" id="show_pages" '.$options['page'].' /> Pages<br />  
        <input name="show_posts" type="checkbox" id="show_posts" '.$options['post'].' /> Posts<br />  
		<br /> 
		<h3>Animation Type</h3>
		<p>Default is expand_right, which slides the stuff out to the right. You can also use checkout to skip the animation and send them right to the checkout page.</p>
		<input type="radio" name="animation_type" value="expand_right" ';

		if($options['animation_type']=="expand_right"){
			echo "checked";
		}

		echo ' /> Expand Right <br />
		<input type="radio" name="animation_type" value="checkout"
		';

		if($options['animation_type']=="checkout"){
			echo "checked";
		}
        
        echo ' /> Checkout <br />
        <h3>Donate Message</h3>
        <input type="text" name="dogeAPI_message" value="'.$options['dogeAPI_message'].'" width="320" style="width: 320px;" />
        <br style="clear:both;" />  
        <input type="submit" class="button-primary" value="Save Changes" style="margin-top:20px;" />  
        </form>  
          
    </div>'; 
}

function dogeAPI_admin_actions() {
	add_options_page("DogeAPI", "DogeAPI", 1, "DogeAPI", "dogeAPI_admin");
}

add_action('admin_menu', 'dogeAPI_admin_actions');

add_action( 'show_user_profile', 'dogeAPI_profile_fields' );
add_action( 'edit_user_profile', 'dogeAPI_profile_fields' );

function dogeAPI_profile_fields( $user ) { ?>
	<h3><?php _e("DogeAPI Donate Information", "blank"); ?></h3>

	<table class="form-table">
	<tr>
	<th><label for="payment_address"><?php _e("Payment Address"); ?></label></th>
	<td>
	<input type="text" name="payment_address" id="payment_address" value="<?php echo esc_attr( get_the_author_meta( 'payment_address', $user->ID ) ); ?>" class="regular-text" /><br />
	<span class="description"><?php _e("Please enter your DogeAPI Payment Address."); ?></span>
	</td>
	</tr>
	</table>
<?php }

add_action( 'personal_options_update', 'save_dogeAPI_profile_fields' );
add_action( 'edit_user_profile_update', 'save_dogeAPI_profile_fields' );

function save_dogeAPI_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }

	update_user_meta( $user_id, 'payment_address', $_POST['payment_address'] );
}

function dogeAPI_donate_display($content)  
{  

	$options['page'] = get_option('dogeAPI_on_page');  
	$options['post'] = get_option('dogeAPI_on_post');  
	$options['dogeAPI_message'] = get_option('dogeAPI_message');  
	$options['animation_type'] = get_option('animation_type');

    if ( (is_single() && $options['post']) || (is_page() && $options['page']) )  
    {  
    	$payment_address = get_the_author_meta("payment_address");

    	if(strlen($payment_address)>1){
	        $donate_button =   
				"<div class='widget-text dogeapi_donate_plugin_box'>
					<h4>".$options['dogeAPI_message']."</h4>
					<div class='doge-widget-wrapper'>
						<form method='get' action='https://www.dogeapi.com/checkout'>
							<input type='hidden' name='widget_type' value='donate'>
							<input type='hidden' name='animation_type' value='".$options['animation_type']."'>
							<input type='hidden' name='payment_address' value='".$payment_address."'>
							<div class='doge-widget' style='display:none;'></div>
						</form>
					</div>
					</div>"; 

	        return $content . $donate_button; 
	     } else {
	     	return $content; 
	     }
    } else {  
        return $content;  
    }   
} 

function dogeAPI_donate_style()  
{  
    // this is where we'll style our box  
}  
  
add_action('wp_head', 'dogeAPI_donate_style');
add_action('the_content', 'dogeAPI_donate_display'); 

class dogeapi_donate_plugin extends WP_Widget {

	// constructor
	function dogeapi_donate_plugin() {
		parent::WP_Widget(false, $name = __('DogeAPI Donate', 'dogeapi_donate_plugin') );
	}

	// widget form creation
	function form($instance) {

		// Check values
		if( $instance) {
		     $payment_address = esc_attr($instance['payment_address']);
		     $widget_key = esc_attr($instance['widget_key']);
		     $animation_type = esc_attr($instance['animation_type']);
		} else {
		     $payment_address = '';
		     $widget_key = '';
		     $animation_type = '';
		}

		$checkout_checked="";
		$expand_checked="";

		if($animation_type=="checked") {
			$checkout_checked="checked";
		} else {
			$expand_checked="checked";
		}

		?>

		<p>
		<label for="<?php echo $this->get_field_id('payment_address'); ?>"><?php _e('Payment Address', 'wp_widget_plugin'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('payment_address'); ?>" name="<?php echo $this->get_field_name('payment_address'); ?>" type="text" value="<?php echo $payment_address; ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id('widget_key'); ?>"><?php _e('Widget Key (optional):', 'wp_widget_plugin'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('widget_key'); ?>" name="<?php echo $this->get_field_name('widget_key'); ?>" type="text" value="<?php echo $widget_key; ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id('animation_type'); ?>"><?php _e('Animation type:', 'wp_widget_plugin'); ?></label><br />
		<input id="<?php echo $this->get_field_id('animation_type'); ?>_expand_right" name="<?php echo $this->get_field_name('animation_type'); ?>" type="radio" value="expand_right" <?php echo $expand_checked; ?> /> Expand right<br />
		<input id="<?php echo $this->get_field_id('animation_type'); ?>_checkout" name="<?php echo $this->get_field_name('animation_type'); ?>" type="radio" value="checkout" <?php echo $checkout_checked; ?> /> Checkout
		</p>

		<?php
	}

	// widget update
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		// Fields
		$instance['payment_address'] = strip_tags($new_instance['payment_address']);
		$instance['widget_key'] = strip_tags($new_instance['widget_key']);
		$instance['animation_type'] = strip_tags($new_instance['animation_type']);
		return $instance;
	}

	// widget display
	// display widget
	function widget($args, $instance) {

		
		wp_enqueue_script( 'dogeapi', 'https://www.dogeapi.com/widget/dogeapi.js' );
		extract( $args );
		// these are the widget options
		$payment_address = $instance['payment_address'];
		$widget_key = $instance['widget_key'];
		$animation_type = $instance['animation_type'];

		echo $before_widget;
		// Display the widget
		echo '<div class="widget-text dogeapi_donate_plugin_box">';

		echo "<div class='doge-widget-wrapper'>
	<form method='get' action='https://www.dogeapi.com/checkout'>
		<input type='hidden' name='widget_type' value='donate'>
		<input type='hidden' name='animation_type' value='".$animation_type."'>
		<input type='hidden' name='payment_address' value='".$payment_address."'>";

		// Check if text is set
		if( $widget_key ) {
			echo "<input type='hidden' name='widget_key' value='".$widget_key."'>
				<input type='hidden' name='show_received' value='1'>";
		}
		
		echo "<div class='doge-widget' style='display:none;'></div>
	</form>
</div>";

		echo '</div>';
		echo $after_widget;
	}
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("dogeapi_donate_plugin");'));

?>