<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    KA4WP
 * @subpackage KA4WP/admin/partials
 */

	
	$ContactForm = WPCF7_ContactForm::get_instance( $_GET['post'] );
	
	$props = $ContactForm->get_properties();
	$wpcf7_api_data = $props['ka4wp_api_integrations'];
	
	$form_fields = $ContactForm->scan_form_tags();
		foreach($form_fields as $form_fields_value){
			if($form_fields_value->basetype != 'submit'){
				$ka4wp_field_array[$form_fields_value->raw_name] = (isset($wpcf7_api_data['mapping-'.$form_fields_value->raw_name]) ? $wpcf7_api_data['mapping-'.$form_fields_value->raw_name] : '');
			}
		}
		$options['cf7anyapi_form_field'] = $ka4wp_field_array;
	
	$wpcf7_api_data["send_to_api"] = isset($wpcf7_api_data["send_to_api"]) ? true : false;

?>	
	<h2><?php esc_html_e('API Integration','kultur-api-for-wp'); ?></h2>
	<fieldset>
		<?php do_action('before_base_fields', $post); ?>

		<div class="cf7_row">
			<label for="wpcf7-sf-send_to_api">
				<input type="checkbox" id="wpcf7-sf-send_to_api"
					name="wpcf7-ka4wp[send_to_api]" value = "1" <?php checked(1, $wpcf7_api_data["send_to_api"]); ?>/>
				<?php esc_html_e('Enable send to API','kultur-api-for-wp'); ?>
			</label>
			<p class="description"><?php esc_html_e('If enabled, you can configure the API and options for this form.','kultur-api-for-wp') ?></p>
		</div>

		<div class="cf7_row">
			<label for="wpcf7-sf-stop_email">
				<input type="checkbox" id="wpcf7-sf-stop_email"
					name="wpcf7-ka4wp[stop_email]" value = "1" <?php echo checked(1, $wpcf7_api_data["stop_email"]); ?>/>
				<?php esc_html_e('Skip sending emails','kultur-api-for-wp'); ?>
			</label>
			<p class="description"><?php esc_html_e('If enabled, emails will not be sent upon form submission.','kultur-api-for-wp') ?></p>
		</div>
		
		<div class="cf7_row">
			<label for="wpcf7-sf-enable-logging">
				<input type="checkbox" id="wpcf7-sf-enable-logging"
					name="wpcf7-ka4wp[logging]" value = "1" <?php checked(1,$wpcf7_api_data["logging"]); ?>/>
				<?php esc_html_e('Enable API logs','kultur-api-for-wp'); ?>
			</label>
			<p class="description"><?php esc_html_e('If enabled, submissions and API resultswill be logged.','kultur-api-for-wp') ?></p>
		</div>

		<?php do_action('after_base_fields', $post); ?>
	</fieldset>
	
	<div id="cf7-ka4wp-api-definition">
		<h2><?php esc_html_e('Field definition','kultur-api-for-wp'); ?></h2>
		<label><?php esc_html_e('Here you can define the mapping between form fields and API fields','kultur-api-for-wp'); ?></label>
	
		<div id="cf7-ka4wp-api-type-wunschevents">
			<fieldset>
				<div id="cf7anyapi-form-fields" class="form-fields">        
				<?php
					if($ka4wp_field_array){
						foreach($ka4wp_field_array as $ka4wp_form_field_key => $ka4wp_form_field_value){
				?>
					<div class="cf7_row">
						<label for="wpcf7-field-mapping-<?php echo esc_html($ka4wp_form_field_key); ?>">
							<?php echo esc_html($ka4wp_form_field_key); ?> (<?php echo esc_html($ka4wp_form_field_value); ?>)
							<select id="wpcf7-field-mapping-<?php echo esc_html($ka4wp_form_field_key); ?>" name="wpcf7-ka4wp[mapping-<?php echo esc_html($ka4wp_form_field_key); ?>]" required>
								<option value="GUEST" <?php echo selected('GUEST', $ka4wp_form_field_value); ?>><?php esc_html_e('GUEST', 'kultur-api-for-wp' ); ?></option>
								<option value="LOL" <?php echo selected('LOL', $ka4wp_form_field_value); ?>><?php esc_html_e('LOL', 'kultur-api-for-wp' ); ?></option>
							</select>
							
						</label>
						<p class="description"><?php esc_html_e('Please select the API side mapping key. Empty fields won\'t be send.','kultur-api-for-wp') ?></p>
					</div>
				<?php
						}
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