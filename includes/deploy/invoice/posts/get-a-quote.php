<?php

// $form_id must exist.

$post = array(
	'post_name'      => 'get-a-quote',
	'post_title'     => 'Get a Quote',
	'post_status'    => 'publish',
	'post_type'      => 'page',
	'comment_status' => 'closed',
	'post_content'   => '
<div class="boldgrid-section">
<div class="container">
<div class="row">
<div class="col-md-12 col-xs-12 col-sm-12">
<p>&nbsp;</p>
<p class="">Are you looking to get a quote for our services? Please fill out the form below and provide us with all necessary information for us to generate a quote. We will get back with you within 1 to 2 business days.</p>
<p class="">[weforms id="' . $form->id . '"]</p>
</div>
</div>
</div>
</div>
',
);

return $post;
