<?php

// PHPUnit Polyfills library is a requirement for running the WP test suite.
require_once dirname( dirname( __FILE__ ) ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	// todo Do we need to manually require these files?
	require_once dirname( dirname( __FILE__ ) ) . '/vendor/boldgrid/library/src/Library/License.php';
	require_once dirname( dirname( __FILE__ ) ) . '/vendor/boldgrid/library/src/Library/Api/Call.php';
	require_once dirname( dirname( __FILE__ ) ) . '/vendor/boldgrid/library/src/Library/Api/Availability.php';
	require_once dirname( dirname( __FILE__ ) ) . '/vendor/boldgrid/library/src/Library/Configs.php';
	require_once dirname( dirname( __FILE__ ) ) . '/vendor/boldgrid/library/src/Library/Filter.php';

	require dirname( dirname( __FILE__ ) ) . '/boldgrid-inspirations.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';
