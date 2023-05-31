<?php
/**
 * An array of config settings for the Inspirations survey.
 *
 * @since 1.3.6
 */

$address = Boldgrid_Inspirations_Survey::get_value( 'address' );
$address = $address ? $address : '';

$email = Boldgrid_Inspirations_Survey::get_value( 'email' );
$phone = Boldgrid_Inspirations_Survey::get_value( 'phone' );
$blogname = get_option( 'blogname' );

$display_address = Boldgrid_Inspirations_Survey::should_display( 'address' );
$display_email = Boldgrid_Inspirations_Survey::should_display( 'email' );
$display_phone = Boldgrid_Inspirations_Survey::should_display( 'phone' );

$mailto = sprintf(
	'<a href="mailto:%1$s">%1$s</a>',
	$email
);

$map_iframe = sprintf(
	'<iframe style="width:100%%;height:100%%;" src="https://maps.google.com/maps?q=%1$s&amp;t=m&amp;z=16&amp;output=embed" frameborder="0"></iframe>',
	urlencode( $address )
);

return array(
	'find_and_replace' => array(
		array(
			'removal_key' =>		'phone',
			'value' =>				$phone,
			'display' =>			$display_phone,
			'on_success' =>			'node_value',
		),
		array(
			'removal_key' =>		'address',
			'value' =>				$address,
			'display' =>			$display_address,
			'on_success' =>			'node_value',
		),
		array(
			'removal_key' =>		'email',
			'value' =>				$email,
			'display' =>			$display_email,
			'parent_attributes' =>	array( 'href' => 'mailto:' . $email, ),
			'on_success' =>			'node_value',
		),
		array(
			'removal_key' =>		'map',
			'value' =>				$map_iframe,
			'display' =>			$display_address,
			'on_success' =>			'remove_children',
			'parent_style' =>		true,
		),
	),
	'footer_company_details' => array(
		'&copy; ' .  date( 'Y' ) . ' ' . $blogname,
		$display_address ? $address : null,
		$display_phone ? $phone : null,
		$display_email ? $mailto : null,
	),
);
?>