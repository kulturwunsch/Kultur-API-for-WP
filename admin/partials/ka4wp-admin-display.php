<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://kulturwunsch.de
 * @since      1.0.0
 *
 * @package    KA4WP
 * @subpackage KA4WP/admin/partials
 */
$ka4wp_object = new KA4WP();
$ka4wp_options = $ka4wp_object->ka4wp_get_api_options(get_the_ID());


$ka4wp_api_type = (empty($ka4wp_options['ka4wp_api_type']) ? '' : $ka4wp_options['ka4wp_api_type']);
$ka4wp_api_key = (empty($ka4wp_options['ka4wp_api_key']) ? '' : $ka4wp_options['ka4wp_api_key']);
$ka4wp_base_url = (empty($ka4wp_options['ka4wp_base_url']) ? '' : $ka4wp_options['ka4wp_base_url']);
$ka4wp_input_type = (empty($ka4wp_options['ka4wp_input_type']) ? '' : $ka4wp_options['ka4wp_input_type']);
$ka4wp_method = (empty($ka4wp_options['ka4wp_method']) ? '' : $ka4wp_options['ka4wp_method']);
$ka4wp_header_request = (empty($ka4wp_options['ka4wp_header_request']) ? '' : $ka4wp_options['ka4wp_header_request']);


if(!class_exists('WPCF7_ContactForm')){
?>
<div id="ka4wp_admin" class="ka4wp_wrap">
    <p><?php esc_html_e('To send data to external servers, Kultur-API integrations requires CONTACT FORM 7 Plugin to be installed and active.', 'kultur-api-for-wp' ); ?></p>
</div>
<?php } ?>

<div id="ka4wp_admin" class="ka4wp_wrap">
	<?php wp_nonce_field('ka4wp_cpt_nonce','ka4wp_cpt_nonce' ); ?>
    <div id="ka4wp_select_api_type" class="ka4wp_full_width">
        <label for="ka4wp_api_type"><?php esc_html_e( 'API type', 'kultur-api-for-wp' ); ?></label>
        <select id="ka4wp_api_type" name="ka4wp_api_type" required>
            <option value=""><?php esc_html_e( 'Select API type', 'kultur-api-for-wp' ); ?></option>
            <option value="wunsch.events" <?php selected('wunsch.events',$ka4wp_api_type); ?>>WUNSCH.events</option>
            <!--<option value="other" <?php #selected('other',$ka4wp_api_type); ?>>other</option>-->
        </select>
    </div>
	<div id="ka4wp_api_settings_wunschevents" class="ka4wp_detailed_api_settings">
	
		<div class="ka4wp_field">
			<label for="ka4wp_api_key"><?php esc_html_e( 'API Key', 'kultur-api-for-wp' ); ?></label>
			<input type="text" id="ka4wp_api_key" name="ka4wp_api_key" value="<?php echo esc_html($ka4wp_api_key); ?>" placeholder="<?php esc_attr_e( 'Enter Your API Key', 'kultur-api-for-wp' ); ?>">
		</div>
		
	</div>
	<div id="ka4wp_api_settings_other" class="ka4wp_detailed_api_settings" style="display:none;">
	<!-- Temporary disabled, waiting for final integration -->
		<div class="ka4wp_field">
			<label for="ka4wp_base_url"><?php esc_html_e( 'API base url', 'kultur-api-for-wp' ); ?></label>
			<input type="text" id="ka4wp_base_url" name="ka4wp_base_url" value="<?php echo esc_html($ka4wp_base_url); ?>" placeholder="<?php esc_attr_e( 'Enter Your API base URL', 'kultur-api-for-wp' ); ?>">
		</div>
		
		<div class="ka4wp_field">
			<label for="ka4wp_input_type"><?php esc_html_e( 'Input type', 'kultur-api-for-wp' ); ?></label>
			<select id="ka4wp_input_type" name="ka4wp_input_type">
				<option value="params" <?php selected('params',$ka4wp_input_type); ?>><?php esc_html_e( 'Parameters - GET/POST', 'kultur-api-for-wp' ); ?></option>
				<option value="json" <?php selected('json',$ka4wp_input_type); ?>><?php esc_html_e( 'json', 'kultur-api-for-wp' ); ?></option>
			</select>
		</div>
		
		<div class="ka4wp_field">
			<label for="ka4wp_method"><?php esc_html_e( 'Method', 'kultur-api-for-wp' ); ?></label>
			<select id="ka4wp_method" name="ka4wp_method">
				<option value=""><?php esc_html_e( 'Select Method', 'kultur-api-for-wp' ); ?></option>
				<option value="GET" <?php selected('GET',$ka4wp_method); ?>>GET</option>
				<option value="POST" <?php selected('POST',$ka4wp_method); ?>>POST</option>
			</select>
		</div>
		
		<div class="ka4wp_full_width">
			<label for="ka4wp_header_request"><?php esc_html_e( 'Header Request', 'kultur-api-for-wp' ); ?></label>
			<textarea id="ka4wp_header_request" name="ka4wp_header_request" placeholder="<?php esc_attr_e( 'Authorization: MY_API_KEY 
Authorization : Bearer xxxxxxx
Authorization : Basic xxxxxx
Content-Type: application/json
All your header Parameters set here.', 'kultur-api-for-wp' ); ?>

"><?php echo esc_textarea($ka4wp_header_request); ?></textarea>
		</div>
		
	</div>
    
</div>