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

	//Get the active tab from the $_GET param
	$default_tab = null;
	$tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : $default_tab;

if(!class_exists('WPCF7_ContactForm')){
?>
<div id="ka4wp_admin" class="ka4wp_wrap">
    <p><?php esc_html_e( 'To send data to external servers, Kultur-API integrations requires CONTACT FORM 7 Plugin to be installed and active.', 'kultur-api-for-wp' ); ?></p>
</div>
<?php } ?>
<div class="wrap">
	<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
	<?php settings_errors('ka4wp_settings'); ?>
	<nav class="nav-tab-wrapper">
		<a href="?post_type=ka4wp&page=ka4wp_settings" class="nav-tab <?php if($tab===null):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Default', 'kultur-api-for-wp' ); ?></a>
		<a href="?post_type=ka4wp&page=ka4wp_settings&tab=integrations" class="nav-tab <?php if($tab==='integrations'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Integrations', 'kultur-api-for-wp' ); ?></a>
		<a href="?post_type=ka4wp&page=ka4wp_settings&tab=logging" class="nav-tab <?php if($tab==='logging'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Logging', 'kultur-api-for-wp' ); ?></a>
		<a href="?post_type=ka4wp&page=ka4wp_settings&tab=miscellaneous" class="nav-tab <?php if($tab==='miscellaneous'):?>nav-tab-active<?php endif; ?>"><?php esc_html_e('Miscellaneous', 'kultur-api-for-wp' ); ?></a>
    </nav>

	<form action="options.php" method="post">
		<div class="tab-content">
			<?php switch($tab) :
				case 'integrations':
					settings_fields('ka4wp_settings_integrations');
					do_settings_sections('ka4wp_settings_integrations');
				break;
				case 'miscellaneous':
					settings_fields('ka4wp_settings_miscellaneous');
					do_settings_sections('ka4wp_settings_miscellaneous');
				break;
				case 'logging':
					settings_fields('ka4wp_settings_logging');
					do_settings_sections('ka4wp_settings_logging');
				break;
				default:
					settings_fields('ka4wp_settings_general');
					do_settings_sections('ka4wp_settings_general');
				break;
			endswitch; submit_button(); ?>
		</div>
	</form>
</div>