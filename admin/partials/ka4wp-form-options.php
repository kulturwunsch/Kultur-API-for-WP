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
 
	if(!is_plugin_active('contact-form-7/wp-contact-form-7.php') && (is_multisite() && !is_plugin_active_for_network('contact-form-7/wp-contact-form-7.php')))
	{
		wp_die(esc_html_e('You have to install the Contact Form 7 plugin before you can configure this form.','kultur-api-for-wp'), esc_html_e('Contact Form 7 plugin is required','kultur-api-for-wp'));
	}

	$form_id = !empty($_GET['post']) ? sanitize_text_field($_GET['post']) : null;
	$ContactForm = WPCF7_ContactForm::get_instance( $form_id );
	if(!empty($ContactForm))
	{
		$props = $ContactForm->get_properties();
	
		$wpcf7_api_data = $props['ka4wp_api_integrations'] ?: [];
	
		$form_fields = $ContactForm->scan_form_tags();
		foreach($form_fields as $form_fields_value){
			if($form_fields_value->basetype != 'submit'){
				$ka4wp_field_array[$form_fields_value->raw_name] = $wpcf7_api_data['mapping-'.$form_fields_value->raw_name] ?? '';
			}
		}
		
		$defaultsOptions = KA4WP_Admin::ka4wp_get_endpoint_defaults($wpcf7_api_data["apiendpoint"] ?? []);
		$defaultMappings = $defaultsOptions[$wpcf7_api_data["predefined-mapping"]]['options'] ?? [];
	}
	
	//load API types
	$ApiEndpointPosts = get_posts([
		'post_type' => 'ka4wp',
		'post_status' => 'publish',
		'numberposts' => -1,
		'order'    => 'ASC'
	]);
?>	

	<h2><?php esc_html_e('API Integration','kultur-api-for-wp'); ?></h2>
	<fieldset>
		<div class="cf7_row">
			<label for="wpcf7-sf-send_to_api">
				<input type="checkbox" id="wpcf7-sf-send_to_api"
					name="wpcf7-ka4wp[send_to_api]" value = "1" <?php checked(1, $wpcf7_api_data["send_to_api"] ?? 0); ?>/>
				<?php esc_html_e('Enable send to API','kultur-api-for-wp'); ?>
			</label>
			<p class="description"><?php esc_html_e('If enabled, you can configure the API and options for this form.','kultur-api-for-wp') ?></p>
		</div>

		<div class="cf7_row">
			<label for="wpcf7-sf-stop_email">
				<input type="checkbox" id="wpcf7-sf-stop_email"
					name="wpcf7-ka4wp[stop_email]" value = "1" <?php checked(1, $wpcf7_api_data["stop_email"] ?? 0); ?>/>
				<?php esc_html_e('Skip sending emails','kultur-api-for-wp'); ?>
			</label>
			<p class="description"><?php esc_html_e('If enabled, emails will not be sent upon form submission.','kultur-api-for-wp') ?></p>
		</div>
		
		<div class="cf7_row">
			<label for="wpcf7-sf-enable-logging">
				<input type="checkbox" id="wpcf7-sf-enable-logging"
					name="wpcf7-ka4wp[logging]" value = "1" <?php checked(1,$wpcf7_api_data["logging"] ?? 0); ?>/>
				<?php esc_html_e('Enable API logs','kultur-api-for-wp'); ?>
			</label>
			<p class="description"><?php esc_html_e('If enabled, submissions and API resultswill be logged.','kultur-api-for-wp') ?></p>
		</div>
	</fieldset>
	<div id="cf7-ka4wp-api-selection">
		<h2><?php esc_html_e('API Endpoint selection','kultur-api-for-wp'); ?></h2>
		<label><?php esc_html_e('Here you can select the specified API which should used to transfer the form entered data.','kultur-api-for-wp'); ?></label>
		<fieldset>
			<div class="cf7_row">
				<?php if(!empty($ApiEndpointPosts)) { ?>
					<label for="wpcf7-sf-select-apiendpoint">
						<select id="wpcf7-sf-select-apiendpoint" name="wpcf7-ka4wp[apiendpoint]">
							<option value="" <?php selected('', $wpcf7_api_data["apiendpoint"] ?? 0, false) ?>></option>
							<?php 
								foreach($ApiEndpointPosts as $singleEndpoint)
								{
									echo '<option value="'.$singleEndpoint->ID.'" '.selected($singleEndpoint->ID, $wpcf7_api_data["apiendpoint"] ?? 0, false).'>'.esc_attr($singleEndpoint->post_title).'</option>';
								}
							?>
						</select>
						<?php esc_html_e('Selected API endpoint','kultur-api-for-wp'); ?>
					</label>
					<p class="description"><?php esc_html_e('You can select here only published api endpoints.','kultur-api-for-wp') ?></p>
				<?php } else {
					esc_html_e('There are no published API endpoints at the moment. Please ensure that your API endpoint is published. You can add the api settings later after submit your new form.','kultur-api-for-wp');
				} ?>
			</div>
		</fieldset>
	</div>
	
	<div id="cf7-ka4wp-api-definition">
		<h2><?php esc_html_e('Field definition','kultur-api-for-wp'); ?></h2>
		<label><?php esc_html_e('Here you can define the mapping between form fields and API fields','kultur-api-for-wp'); ?></label>
		<div id="cf7-ka4wp-api-mapping-type">
			<fieldset>
				<div class="cf7_row">
					<label for="wpcf7-sf-predefined-mapping">
						<select id="wpcf7-sf-predefined-mapping" name="wpcf7-ka4wp[predefined-mapping]">
							<option value="" <?php selected('', $wpcf7_api_data["predefined-mapping"] ?? 0, true) ?>><?php esc_html_e('No predefined fields','kultur-api-for-wp') ?></option>
							<?php
							foreach($defaultsOptions as $typeKey => $typeValues)
							{
								if(!empty($typeValues['options']))
								{
									echo '<option value="'.esc_attr($typeKey).'" '.selected($typeKey, $wpcf7_api_data["predefined-mapping"] ?? 0, false).'>'.esc_attr($typeValues['name']).'</option>';
								}
							}
							?>
						</select>
						<?php esc_html_e('Select your predefined mapping','kultur-api-for-wp'); ?> <?php echo esc_html($wpcf7_api_data["predefined-mapping"]); ?>
					</label>
					<p class="description"><?php esc_html_e('You can select a predefined mapping or enter all mappings manually.','kultur-api-for-wp') ?></p>
				</div>	
			</fieldset>
		</div>
	
		
		<div id="cf7-ka4wp-api-mapping-custom">
			<fieldset>
				<div id="cf7anyapi-form-fields" class="form-fields">        
				<?php
					if(!empty($ka4wp_field_array)){
						foreach($ka4wp_field_array as $ka4wp_form_field_key => $ka4wp_form_field_value){
				?>
					<div class="cf7_row">
						<label for="wpcf7-field-mapping-<?php echo esc_html($ka4wp_form_field_key); ?>">
							<input type="text" id="wpcf7-field-mapping-<?php echo esc_html($ka4wp_form_field_key); ?>"
								name="wpcf7-ka4wp[mapping-<?php echo esc_html($ka4wp_form_field_key); ?>]" value="<?php echo esc_attr($ka4wp_form_field_value); ?>" />
							<?php esc_html_e('Field mapping for','kultur-api-for-wp'); ?> <?php echo esc_html($ka4wp_form_field_key); ?> (<?php echo esc_html($ka4wp_form_field_value); ?>)
						</label>
						<p class="description"><?php esc_html_e('Please enter the API side mapping key. Empty fields won\'t be send.','kultur-api-for-wp') ?></p>
					</div>
				<?php
						}
					} else { 
						esc_html_e('Please submit your current configuration to run a full scan of available form fields. This is required before do some mapping configurations.','kultur-api-for-wp');
					}
				?>
				</div>
			</fieldset>
		</div>
		<div id="cf7-ka4wp-api-mapping-predefined">
			<fieldset>
				<div id="cf7anyapi-form-fields" class="form-fields">        
				<?php
					if(!empty($ka4wp_field_array)){
						foreach($ka4wp_field_array as $ka4wp_form_field_key => $ka4wp_form_field_value){
				?>
					<div class="cf7_row">
						<label for="wpcf7-field-mapping-<?php echo esc_html($ka4wp_form_field_key); ?>">
							<?php echo esc_html($ka4wp_form_field_key); ?> (<?php echo esc_html($ka4wp_form_field_value); ?>)
							<select id="wpcf7-field-mapping-<?php echo esc_html($ka4wp_form_field_key); ?>" name="wpcf7-ka4wp[mapping-<?php echo esc_html($ka4wp_form_field_key); ?>]" class="predefined-field-mapping">
								<option value="" <?php selected('', $ka4wp_form_field_value, '', false); ?>><?php esc_html_e("Don't send this field to endpoint via API", 'kultur-api-for-wp' ); ?></option>
								<?php
								if(!empty($defaultMappings))
								{
									foreach($defaultMappings as $mappingKey => $mappingValue)
									{
										echo '<option value="'.esc_attr($mappingValue['value']).'" '.selected($mappingValue['value'], $ka4wp_form_field_value ?? '', false).'>'.esc_attr($mappingValue['name']).'</option>';
									}
								} ?>
							</select>
							
						</label>
						<p class="description"><?php esc_html_e('Please select the API side mapping key. Empty fields won\'t be send.','kultur-api-for-wp') ?></p>
					</div>
				<?php
						}
					} else { 
						esc_html_e('Please submit your current configuration to run a full scan of available form fields. This is required before do some mapping configurations.','kultur-api-for-wp');
					}
				?>
				</div>
			</fieldset>
		</div>
	</div>
	<div id="cf7-ka4wp-no-api-definition">
		<h2><?php esc_html_e('Select an API','kultur-api-for-wp'); ?></h2>
		<p class="description"><?php esc_html_e('Currently, there is no API selected. Please select first an API before you can configure the API definitions.','kultur-api-for-wp') ?></p>
	</div>