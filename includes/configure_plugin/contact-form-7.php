<?php

// Prevent direct calls
if ( ! defined( 'WPINC' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

$auto_created_form = Boldgrid_Inspirations_Utility::get_page_by_title( 'Contact form 1', 'OBJECT', 'wpcf7_contact_form' );

$short_code = '[contact-form-7 id="' . $auto_created_form->ID . '" title="Contact form 1"]';

// update the post and replace [imhwpb-form] with the shortcode used by cf7
// $post_id is coming from the file in which this was included
$page_needing_form = get_post( $post_id );
$page_needing_form->post_content = str_replace( '[imhwpb-form]', $short_code, 
	$page_needing_form->post_content );
wp_update_post( $page_needing_form );

// update the email address of the recipient
global $current_user;
get_currentuserinfo();
$postmeta = get_post_meta( $auto_created_form->ID, '_mail', true );
$postmeta['recipient'] = "wpb@boldgrid.com";
if ( $current_user->user_email != "" ) {
	$postmeta['recipient'] = $current_user->user_email;
}
update_post_meta( $auto_created_form->ID, '_mail', $postmeta );

