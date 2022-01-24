<?php
/**
 * BoldGrid Source Code
 *
 * @package Boldgrid_Plugintest
 * @copyright BoldGrid.com
 * @version $Id$
 * @author BoldGrid.com <wpb@boldgrid.com>
 */

/**
 * BoldGrid Plugin Test class.
 */
class Test_Boldgrid_Inspiration extends WP_UnitTestCase {
	/**
	 * Class property $testClass.
	 */
	protected $testClass;

	/**
	 * Class property $settings.
	 */
	protected $settings = array (
		'configDir' => 'includes/config'
	);

	/**
	 * Setup.
	 */
	public function set_up() {
		// include('includes/class-boldgrid-inspirations.php');
		$this->testClass = new Boldgrid_Inspirations( $this->settings );
	}

	/**
	 * Test set get configs.
	 */
	public function testSetGetConfigs() {
		$configs = $this->testClass->get_configs();
		$this->assertNotEmpty( $configs );
		$this->testClass->set_configs( 'foo' );
		$this->assertSame( 'foo', $this->testClass->get_configs() );
	}

	/**
	 * Test passes API check.
	 */
	public function testPassesApiCheck() {
		$this->assertTrue( $this->testClass->api->passes_api_check() );
		$this->assertFalse( ( bool ) $this->testClass->api->passes_api_check( true ) );
	}

	/**
	 * Test Hash success without dashes.
	 */
	public function test_hash_api_key_success_no_dashes() {

		$result = Boldgrid_Inspirations_Api::hash_api_key( "hghghhhhhhhhhhhhhhhhhhhhhhhhhhhh" );
		$this->assertEquals( "fd66458e615abecd3ec47d3b355af162", $result );

	}

	/**
	 * Test Hash success with dashes.
	 */
	public function test_hash_api_key_success_with_dashes() {

		$result = Boldgrid_Inspirations_Api::hash_api_key( "hghgh-hhhhhhh-hhhhh-hhhhh-hhhhhhhhhh" );
		$this->assertEquals( "fd66458e615abecd3ec47d3b355af162", $result );

	}

	/**
	 * Test Hash failure with to short.
	 */
	public function test_hash_api_key_failure_too_short() {

		$result = Boldgrid_Inspirations_Api::hash_api_key( "hghgh" );
		$this->assertEquals( null, $result );

	}

	/**
	 * Test Hash failure with too long.
	 */
	public function test_hash_api_key_failure_too_long() {

		$result = Boldgrid_Inspirations_Api::hash_api_key(
			"hghghhhhhhhhhhhhhhhhhhhhhhhhhhhhhghghhhhhhhhhhhhhhhhhhhhhhhhhhhh" );
		$this->assertEquals( null, $result );

	}

	/**
	 * Test Hash failure with too short, 31 characters + 1 dash.
	 */
	public function test_hash_api_key_failure_too_short_with_dash() {

		$result = Boldgrid_Inspirations_Api::hash_api_key(
			"jjjjjjjjjjjjjjjjjjjjjjjjjjjjjjj-" );
		$this->assertEquals( null, $result );

	}

	/**
	 * Test Hash failure with empty string passed.
	 */
	public function test_hash_api_key_failure_empty_string() {

		$result = Boldgrid_Inspirations_Api::hash_api_key( '' );
		$this->assertEquals( null, $result );

	}
}
