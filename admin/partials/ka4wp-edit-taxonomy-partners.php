<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://kulturwunsch.de
 * @since      1.2.0
 *
 * @package    KA4WP
 * @subpackage KA4WP/admin/partials
 */
 
	$image_id = get_term_meta($term->term_id ?? 0, 'logo_image_id', true);
?>	

	<tr class="form-field">
		<th>
			<label for="ka4wp_logo_image_id"><?php esc_html_e('Logo','kultur-api-for-wp') ?></label>
		</th>
		<td>
			<?php if($image = wp_get_attachment_image_url($image_id, 'medium')) { ?>
				<a href="#" class="ka4wp-upload-file">
					<img src="<?php echo esc_url( $image ) ?>" />
				</a>
				<a href="#" class="ka4wp-remove-file"><?php esc_html_e('Remove image','kultur-api-for-wp') ?></a>
				<input type="hidden" name="ka4wp_logo_image_id" value="<?php echo absint( $image_id ) ?>">
			<?php } else { ?>
				<a href="#" class="button ka4wp-upload-file"><?php esc_html_e('Upload image','kultur-api-for-wp') ?></a>
				<a href="#" class="ka4wp-remove-file d-none"><?php esc_html_e('Remove image','kultur-api-for-wp') ?></a>
				<input type="hidden" name="ka4wp_logo_image_id" value="">
			<?php } ?>
		</td>
	</tr>