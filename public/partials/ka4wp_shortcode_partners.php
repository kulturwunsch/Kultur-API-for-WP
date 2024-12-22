<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://kulturwunsch.de
 * @since      1.2.0
 *
 * @package    KA4WP
 * @subpackage KA4WP/public/partials
 */
	
	//prepare meta_query filter for partner types
	if($shortcode_options['partnertype'] != '*')
	{
		$meta_query = [];
		foreach(explode(",", $shortcode_options['partnertype']) as $type)
		{
			$meta_query[] = [
						'key'       => $type,
						'value'     => 1,
						'compare'   => '='
						];
		}
		
		if(count($meta_query) > 1)
		{
			$meta_query['relation'] = 'OR';
		}
	}
	
	$terms = get_terms(array(
				'taxonomy' => 'partners',
				'orderby' => 'name',
				'order' => 'ASC',
				'hide_empty' => false,
				'meta_query' => $meta_query ?? [],
			));
			
	if(strtolower($shortcode_options['style']) != 'table')
	{
		// calculate xs Grid
		try {
			$grid_xs = 12 / absint($shortcode_options['grid_xs']);
		} catch(DivisionByZeroError $e){
			$grid_xs = 2;
		}
		
		// calculate sm Grid
		try {
			$grid_sm = 12 / absint($shortcode_options['grid_sm']);
		} catch(DivisionByZeroError $e){
			$grid_sm = 3;
		}
		
		// calculate lg Grid
		try {
			$grid_lg = 12 / absint($shortcode_options['grid_lg']);
		} catch(DivisionByZeroError $e){
			$grid_lg = 4;
		}
		
		// calculate xl Grid
		try {
			$grid_xl = 12 / absint($shortcode_options['grid_xl']);
		} catch(DivisionByZeroError $e){
			$grid_xl = 4;
		}
	}
			
	if(!empty($terms))
	{
		if(strtolower($shortcode_options['style']) == 'table')
		{
?>
			<table class="ka4wp-table ka4wp-table-striped ka4wp-table-hover">
				<thead>
					<tr>
						<?php if(!empty($shortcode_options['view_logo'])) { ?>
							<th scope="col"><?php esc_html_e('Logo', 'kultur-api-for-wp' ); ?></th>
						<?php } ?>
						<th scope="col"><?php esc_html_e('Name', 'kultur-api-for-wp' ); ?></th>
						<?php if(!empty($shortcode_options['view_adress'])) { ?>
							<th scope="col"><?php esc_html_e('Adress', 'kultur-api-for-wp' ); ?></th>
						<?php } ?>
						<?php if(!empty($shortcode_options['view_phone'])) { ?>
							<th scope="col"><?php esc_html_e('Phone', 'kultur-api-for-wp' ); ?></th>
						<?php } ?>
						<?php if(!empty($shortcode_options['view_website'])) { ?>
							<th scope="col"><?php esc_html_e('Website', 'kultur-api-for-wp' ); ?></th>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
<?php			foreach($terms as $term) 
				{
					$term_meta = get_term_meta($term->term_id);
					$image = wp_get_attachment_image_url($term_meta['logo_image_id'][0], 'thumbnail') ?: '';
?>
					<tr>
						<?php if(!empty($shortcode_options['view_logo'])) { ?>
							<td><?php if(!empty($image)) { print '<img src="'.$image.'" alt="">'; } ?></td>
						<?php } ?>
						<td><?php print esc_html($term->name); ?></td>
						<?php if(!empty($shortcode_options['view_adress'])) { ?>
							<td><?php print esc_html($term_meta['street'][0].' '.$term_meta['streetnumber'][0].', '.$term_meta['zipcode'][0].' '.$term_meta['city'][0]); ?></td>
						<?php } ?>
						<?php if(!empty($shortcode_options['view_phone'])) { ?>
							<td><?php print esc_html($term_meta['phonenumber'][0]); ?></td>
						<?php } ?>
						<?php if(!empty($shortcode_options['view_website'])) { ?>
							<td><a href="<?php print esc_url($term_meta['website'][0]); ?>" target="_blank"><?php print esc_url($term_meta['website'][0]); ?></a></td>
						<?php } ?>
					</tr>
<?php 			}
?>
				</tbody>
			</table>
<?php
		} else {
?>		
			<div class="ka4wp-grid-row">
<?php
			foreach($terms as $term)
			{
				$term_meta = get_term_meta($term->term_id);
				$image_id = $term_meta['logo_image_id'][0] ?: get_option('ka4wp_partnerlogo_default', '0') ?: 0;
				$image = wp_get_attachment_image_url($image_id, 'medium');
?>
				<div class="col-xs-<?php print esc_html($grid_xs); ?> col-sm-<?php print esc_html($grid_sm); ?> col-lg-<?php print esc_html($grid_lg); ?> col-xl-<?php print esc_html($grid_xl); ?> mb-4">
					<div class="card h-100">
<?php if(!empty($shortcode_options['view_logo']) && !empty($image)) { ?>
						<img class="card-img-top" src="<?php print $image; ?>">
<?php } ?>
						<div class="ka4wp-card-body">
<?php if(!empty($term_meta['organisation_holder'][0])) { ?>
							<span class="fw-bold"><?php print esc_html($term_meta['organisation_holder'][0]); ?></span>
<?php } ?>
							<h4 class="card-title">
								<?php print esc_html($term->name); ?>
							</h4>
							<div class="mb-4">
<?php if(!empty($shortcode_options['view_adress']) && !empty($term_meta['street'][0]) && !empty($term_meta['city'][0])) { ?>
								<span class="fw-bold"><?php esc_html_e('Adress', 'kultur-api-for-wp' ); ?>:</span>
								<div><?php print esc_html($term_meta['street'][0].' '.$term_meta['streetnumber'][0]); ?></div>
								<div><?php print esc_html($term_meta['zipcode'][0].' '.$term_meta['city'][0]); ?></div>
<?php }
if(!empty($shortcode_options['view_phone']) && !empty($term_meta['phonenumber'][0])) { ?>
								<div><span class="fw-bold"><?php esc_html_e('Phone', 'kultur-api-for-wp' ); ?>:</span> <?php print esc_html($term_meta['phonenumber'][0]); ?></div>
<?php } ?>
							</div>
<?php if(!empty($shortcode_options['view_website']) && !empty($term_meta['website'][0])) { ?>
							<a href="<?php print esc_url($term_meta['website'][0]); ?>" class="wp-block-button__link wp-element-button" target="_blank"><?php esc_html_e('Open Website', 'kultur-api-for-wp' ); ?></a>
<?php } ?>
						</div>
					</div>
				</div>
<?php
			}
?>
			</div>
<?php
		}
	}