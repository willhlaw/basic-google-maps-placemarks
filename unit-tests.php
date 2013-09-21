<?php

/**
 * Unit tests. Uses SimpleTest for WordPress plugin.
 *
 * @package BasicGoogleMapsPlacemarks
 * @author Ian Dunn <ian@iandunn.name>
 * @link http://wordpress.org/extend/plugins/basic-google-maps-placemarks/
 * @link http://wordpress.org/extend/plugins/simpletest-for-wordpress/
 */

require_once( WP_PLUGIN_DIR . '/simpletest-for-wordpress/WpSimpleTest.php' );
require_once( WP_PLUGIN_DIR . '/basic-google-maps-placemarks/core.php' );

// how to do for functhions that don't return anything and just do api stuff? is that where integration tests come in?
// setUp() backs up all postmarks then deletes. tearDown() restores backup? 
	// probably write separate functions for that 'cause won't want to call them each time
// test results, not internals
/*
http://www.ibm.com/developerworks/opensource/library/os-refactoringphp/index.html
	instead of using globals, pass them in
		if nothing passed in, then assign to the global var you originally used
	try to make the function more abstract instead of relying on the current state
	init function separate from constructor, option to not call it
*/


class bgmpCoreUnitTests extends UnitTestCase
{
	/**
	 * Sets a protected or private method to be accessible
	 * @author Joel Uckelman <http://www.nomic.net/~uckelman/>
	 * @link http://stackoverflow.com/questions/249664/best-practices-to-test-protected-methods-with-phpunit
	 * @param string $methodName
	 * @return ?
	 */
	protected static function getHiddenMethod( $methodName )
	{
		$class = new ReflectionClass( 'BasicGoogleMapsPlacemarks' );
		$method = $class->getMethod( $methodName );
		$method->setAccessible( true );
		
		return $method;
	}
	
	// addfeaturedimage support?
	// mapshortcode called
		// method from faq of setting it to true

	
	/*
	 * getShortcodes()
	 */
	public function testInit()
	{
		// make sure it gracefully handles bad data in option field
	}
	
	/*
	 * getShortcodes()
	 */
	public function testGetShortcodes()
	{
		$bgmp = new BasicGoogleMapsPlacemarks();
		$bgmp->init();
		
		$getShortcodes = self::getHiddenMethod( 'getShortcodes' );
		
		// detects presence of [bgmp-list]
		// detects presecne of [bgmp-map]
		// detects parameters?
		// others?
		
		//$this->assertFalse( $getShortcodes->invokeArgs( $bgmp, array( '39,7589478.-84,1916069' ) ) );
		//$this->assertFalse( $getShortcodes->invokeArgs( $bgmp, array( '50,0252 19,4520' ) ) );
	}
	
	
	/*
	 * cleanMapShortcodeArguments()
	 */
	public function testCleanMapShortcodeArguments()
	{
		$bgmp = new BasicGoogleMapsPlacemarks();
		$bgmp->init();
		$settings = &$bgmp->getSettings();
		$settings->init();
		$cleanMapShortcodeArguments = self::getHiddenMethod( 'cleanMapShortcodeArguments' );
		
		// Should always get an array back
		$emptyArray = array();
		$this->assertEqual( $emptyArray, $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( $emptyArray ) ) );
		$this->assertEqual( $emptyArray, $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( null ) ) );
		$this->assertEqual( $emptyArray, $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( '' ) ) );
		$this->assertEqual( $emptyArray, $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( 'asdfasdfas' ) ) );
		$this->assertEqual( $emptyArray, $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( 234 ) ) );
		
		// Placemark - invalid
		// @todo setup and tear down the IDs you test with for testing if post id exists in db
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'placemark' => 0 ) ) );
		$this->assertFalse( isset( $cleaned[ 'placemark' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'placemark' => '0' ) ) );
		$this->assertFalse( isset( $cleaned[ 'placemark' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'placemark' => 'alpha' ) ) );
		$this->assertFalse( isset( $cleaned[ 'placemark' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'placemark' => '10alpha' ) ) );
		$this->assertFalse( isset( $cleaned[ 'placemark' ] ) );
//		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'placemark' => 5.25 ) ) );
//		$this->assertFalse( isset( $cleaned[ 'id' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'placemark' => '-5' ) ) );
		$this->assertFalse( isset( $cleaned[ 'placemark' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'placemark' => '1,000' ) ) );	// has to exist to really test, otherwise will be triggered by statement that checks if ID exists
		$this->assertFalse( isset( $cleaned[ 'placemark' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'placemark' => array( 10 ) ) ) );
		$this->assertFalse( isset( $cleaned[ 'placemark' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'placemark' => 9999999 ) ) );	// one that doesn't exist
		$this->assertFalse( isset( $cleaned[ 'placemark' ] ) );
		// @todo add coordiantes tests
		
		// Placemark - valid
		// Note: The corresponding posts have to actually exist in order to test. @todo add setup/teardown w/ test data instead.
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'placemark' => 16 ) ) );	// one that exists
		$this->assertTrue( isset( $cleaned[ 'placemark' ] ) && is_int( $cleaned[ 'placemark' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'placemark' => '16' ) ) );
		$this->assertTrue( isset( $cleaned[ 'placemark' ] ) && is_int( $cleaned[ 'placemark' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'placemark' => '16.00' ) ) );
		$this->assertTrue( isset( $cleaned[ 'placemark' ] ) && is_int( $cleaned[ 'placemark' ] ) );
		// @todo add coordiantes tests
		
		
		
		// Categories
		// @todo insert categories before test, then delete after?
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'categories' => 'parks,restaurants,shopping-malls' ) ) );
		$this->assertTrue( in_array( 'parks', $cleaned[ 'categories' ] ) );
		$this->assertTrue( in_array( 'restaurants', $cleaned[ 'categories' ] ) );
		$this->assertFalse( in_array( 'shopping-malls', $cleaned[ 'categories' ] ) );
		
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'categories' => array( 'parks', 'restaurants', 'shopping-malls' ) ) ) );
		$this->assertTrue( in_array( 'parks', $cleaned[ 'categories' ] ) );
		$this->assertTrue( in_array( 'restaurants', $cleaned[ 'categories' ] ) );
		$this->assertFalse( in_array( 'shopping-malls', $cleaned[ 'categories' ] ) );
		
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'categories' => new stdClass() ) ) );
		$this->assertFalse( isset( $cleaned[ 'categories' ] ) );
		
		// @todo add new category unit tests if 1.9 problems not solved
			// not set - not set
			// set to soething other than array|string - not set
			// set to string w/ 1 entry - array w/ 1 entry
			// set to string w/ 2+ entries - array w/ 2+ entries
			// set to empty array - not set
			// set to non empty array - unchanged
		
		// Width
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'width' => 100 ) ) );
		$this->assertTrue( isset( $cleaned[ 'mapWidth' ] ) && $cleaned[ 'mapWidth' ] == 100 );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'width' => -5 ) ) );
		$this->assertFalse( isset( $cleaned[ 'mapWidth' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'width' => 'seven' ) ) );
		$this->assertFalse( isset( $cleaned[ 'mapWidth' ] ) );
		
		// Height
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'height' => '100' ) ) );
		$this->assertTrue( isset( $cleaned[ 'mapHeight' ] ) && $cleaned[ 'mapHeight' ] == 100 );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'height' => -5 ) ) );
		$this->assertFalse( isset( $cleaned[ 'mapHeight' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'height' => 'seven' ) ) );
		$this->assertFalse( isset( $cleaned[ 'mapHeight' ] ) );
		
		// Center
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'center' => 'Portland, Oregon' ) ) );
		$this->assertTrue( isset( $cleaned[ 'latitude' ] ) && $cleaned[ 'latitude' ] == '45.5234515' );
		$this->assertTrue( isset( $cleaned[ 'longitude' ] ) && $cleaned[ 'longitude' ] == '-122.6762071' );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'center' => '-40, 105' ) ) );
		$this->assertTrue( isset( $cleaned[ 'latitude' ] ) && $cleaned[ 'latitude' ] == '-40' );
		$this->assertTrue( isset( $cleaned[ 'longitude' ] ) && $cleaned[ 'longitude' ] == '105' );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'center' => '-95, 105' ) ) );
		$this->assertFalse( isset( $cleaned[ 'latitude' ] ) && $cleaned[ 'latitude' ] == '-95' );
		$this->assertFalse( isset( $cleaned[ 'longitude' ] ) && $cleaned[ 'longitude' ] == '105' );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'center' => '85, 185' ) ) );
		$this->assertFalse( isset( $cleaned[ 'latitude' ] ) && $cleaned[ 'latitude' ] == '85' );
		$this->assertFalse( isset( $cleaned[ 'longitude' ] ) && $cleaned[ 'longitude' ] == '185' );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'center' => 'sdfjasldf' ) ) );
		$this->assertFalse( isset( $cleaned[ 'latitude' ] ) );
		$this->assertFalse( isset( $cleaned[ 'longitude' ] ) );
		
		// Zoom
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'zoom' => 0 ) ) );
		$this->assertTrue( isset( $cleaned[ 'zoom' ] ) && $cleaned[ 'zoom' ] == 0 );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'zoom' => 21 ) ) );
		$this->assertTrue( isset( $cleaned[ 'zoom' ] ) && $cleaned[ 'zoom' ] == 21 );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'zoom' => -1 ) ) );
		$this->assertFalse( isset( $cleaned[ 'zoom' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'zoom' => 22 ) ) );
		$this->assertFalse( isset( $cleaned[ 'zoom' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'zoom' => 'asdfa' ) ) );
		$this->assertFalse( isset( $cleaned[ 'zoom' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'zoom' => false ) ) );
		$this->assertFalse( isset( $cleaned[ 'zoom' ] ) );
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'zoom' => '' ) ) );
		$this->assertFalse( isset( $cleaned[ 'zoom' ] ) );
		
		// Type		
		if( defined( WPLANG ) && WPLANG == '' )
		{
			$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'type' => 'ROADMAP' ) ) );
			$this->assertTrue( isset( $cleaned[ 'type' ] ) && $cleaned[ 'type' ] == 'ROADMAP' );
			$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'type' => 'roadmap' ) ) );
			$this->assertTrue( isset( $cleaned[ 'type' ] ) && $cleaned[ 'type' ] == 'ROADMAP' );
		}
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( array( 'type' => 'dafsda' ) ) );
		$this->assertFalse( isset( $cleaned[ 'type' ] ) );
		
		// Everything
		$params = array(
			'categories' => 'asdfasdf,record-stores',
			'width' => '350',
			'height' => 600,
			'center' => 'Dayton, Ohio',
			'zoom' => -1,
			'type' => 'hybrid',
		);
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( $params ) );
		$this->assertTrue( in_array( 'record-stores', $cleaned[ 'categories' ] ) );
		$this->assertFalse( in_array( 'asdfasdf', $cleaned[ 'categories' ] ) );
		$this->assertTrue( isset( $cleaned[ 'mapWidth' ] ) && $cleaned[ 'mapWidth' ] == 350 );
		$this->assertTrue( isset( $cleaned[ 'mapHeight' ] ) && $cleaned[ 'mapHeight' ] == 600 );
		$this->assertTrue( isset( $cleaned[ 'latitude' ] ) && $cleaned[ 'latitude' ] == '39.7589478' );
		$this->assertTrue( isset( $cleaned[ 'longitude' ] ) && $cleaned[ 'longitude' ] == '-84.1916069' );
		$this->assertFalse( isset( $cleaned[ 'zoom' ] ) );
		$this->assertTrue( isset( $cleaned[ 'type' ] ) && $cleaned[ 'type' ] == 'HYBRID' );
		
		$params = array(
			'categories' => 'asdfasdf',
			'width' => 'safasd',
			'height' => 600,
			'center' => '50.2342,-89.383453',
			'zoom' => 15,
			'type' => 'moose',
		);
		$cleaned = $cleanMapShortcodeArguments->invokeArgs( $bgmp, array( $params ) );
		$this->assertFalse( in_array( 'asdfasdf', $cleaned[ 'categories' ] ) );
		$this->assertFalse( isset( $cleaned[ 'mapWidth' ] ) );
		$this->assertTrue( isset( $cleaned[ 'mapHeight' ] ) && $cleaned[ 'mapHeight' ] == 600 );
		$this->assertTrue( isset( $cleaned[ 'latitude' ] ) && $cleaned[ 'latitude' ] == '50.2342' );
		$this->assertTrue( isset( $cleaned[ 'longitude' ] ) && $cleaned[ 'longitude' ] == '-89.383453' );
		$this->assertTrue( isset( $cleaned[ 'zoom' ] ) && $cleaned[ 'zoom' ] == 15 );
		$this->assertFalse( isset( $cleaned[ 'type' ] ) );
	}
	
				
	/*
	 * geocode()
	 */
	public function testGeocode()
	{
		$bgmp = new BasicGoogleMapsPlacemarks();
		$bgmp->init();
		
		$this->assertFalse( $bgmp->geocode( 'fjal39802afjl;fsdjfalsdf329jfas;' ) );
		
		$address = $bgmp->geocode( "Kylie's Chicago Pizza Seattle" );
		$this->assertEqual( $address['latitude'], '47.6062095' );
	
		//$address = $bgmp->geocode( "111 Chelsea Street, Boston, MA 02128" );
		//$this->assertEqual( $address['longitude'], -71.0353591 );		// @todo write better test. the api returns different values from time to time
	}
	
	
	/*
	 * validateCoordinates()
	 */
	public function testValidateCoordinatesSucceedsWithValidCoordinates()
	{
		$bgmp = new BasicGoogleMapsPlacemarks();
		$bgmp->init();
		$validateCoordinates = self::getHiddenMethod( 'validateCoordinates' );
  
		$this->assertTrue( is_array( $validateCoordinates->invokeArgs( $bgmp, array( '-4.915833,-157.5' ) ) ) );
		$this->assertTrue( is_array( $validateCoordinates->invokeArgs( $bgmp, array( '39.7589478,-84.1916069' ) ) ) );
		$this->assertTrue( is_array( $validateCoordinates->invokeArgs( $bgmp, array( ' 39.7589478 , -84.1916069 ' ) ) ) );
		$this->assertTrue( is_array( $validateCoordinates->invokeArgs( $bgmp, array( '90,180' ) ) ) );
		$this->assertTrue( is_array( $validateCoordinates->invokeArgs( $bgmp, array( '-90,-180' ) ) ) );
		$this->assertTrue( is_array( $validateCoordinates->invokeArgs( $bgmp, array( '90,-180' ) ) ) );
		$this->assertTrue( is_array( $validateCoordinates->invokeArgs( $bgmp, array( '-90,180' ) ) ) );
		$this->assertTrue( is_array( $validateCoordinates->invokeArgs( $bgmp, array( '16.06403619205951,108.21956070873716' ) ) ) );
		$this->assertTrue( is_array( $validateCoordinates->invokeArgs( $bgmp, array( '55.939246,-3.060258' ) ) ) );
	}
	 
	public function testValidateCoordinatesFailsWithEuropeanNotation()
	{
		$bgmp = new BasicGoogleMapsPlacemarks();
		$bgmp->init();
		$validateCoordinates = self::getHiddenMethod( 'validateCoordinates' );
  
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( '39,7589478.-84,1916069' ) ) );
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( '50,0252 19,4520' ) ) );
	}
	
	public function testValidateCoordinatesFailsWithMinutesSecondsNotation()
	{
		$bgmp = new BasicGoogleMapsPlacemarks();
		$bgmp->init();
		$validateCoordinates = self::getHiddenMethod( 'validateCoordinates' );
  
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( '38°53\'23"N,77°00\'27"W' ) ) );
	}
	
	public function testValidateCoordinatesFailsWithEmptyCoordinates()
	{
		$bgmp = new BasicGoogleMapsPlacemarks();
		$bgmp->init();
		$validateCoordinates = self::getHiddenMethod( 'validateCoordinates' );
  
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( null ) ) );
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( '' ) ) );
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( false ) ) );
	}
	
	public function testValidateCoordinatesFailsWithAddressString()
	{
		$bgmp = new BasicGoogleMapsPlacemarks();
		$bgmp->init();
		$validateCoordinates = self::getHiddenMethod( 'validateCoordinates' );
  
		// want to vary the number of commas
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( '4 S Main St, Dayton, OH 45423, USA' ) ) );
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( 'Pike Place Market, Seattle' ) ) );
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( 'Unos Pizza Chicago' ) ) );
	}
	
	public function testValidateCoordinatesFailsWhenLatitudeLongitudeOutOfBounds()
	{
		$bgmp = new BasicGoogleMapsPlacemarks();
		$bgmp->init();
		$validateCoordinates = self::getHiddenMethod( 'validateCoordinates' );
  
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( '90.1,-84.1916069' ) ) );
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( '-90.1,-84.1916069' ) ) );
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( '39.7589478,180.1' ) ) );
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( '39.7589478,-180.1' ) ) );
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( '-90.1,-180.1' ) ) );
		$this->assertFalse( $validateCoordinates->invokeArgs( $bgmp, array( '90.1,180.1' ) ) );
	}
	
	/*
	 * reverseGeocode()
	 */
	public function testReverseGeocode()
	{
		$bgmp = new BasicGoogleMapsPlacemarks();
		$bgmp->init();
		$reverseGeocode = self::getHiddenMethod( 'reverseGeocode' );
		
		$this->assertFalse( $reverseGeocode->invokeArgs( $bgmp, array( '23432.324', 'tomato' ) ) );

		$address = $reverseGeocode->invokeArgs( $bgmp, array( '39.7589478', '-84.1916069' ) );
		$this->assertEqual( $address, 'Dayton Transportation Center Heliport, Dayton, OH 45402, USA' );
	}
		
	// map shortcode
	
	// list shortcode

	/*
	public function testGetPlacemarksReturnsEmptyArrayWhenNoPostsExist()
	{
		$bgmp = new BasicGoogleMapsPlacemarks();
		$markers = $bgmp->getPlacemarks();
		
		// @todo - remove all posts, or set to draft or something
		$this->assertTrue( is_array( $markers ) );
		$this->assertTrue( count( $markers ) === 0 );
		// @todo - restore the posts
	}
	*/
	
	public function testGetPlacemarksReturnsPopulatedArrayWhenPostsExist()
	{
		$bgmp = new BasicGoogleMapsPlacemarks();
		$bgmp->init();
		$markers = $bgmp->getMapPlacemarks( array() );
		
		// @todo - insert a test post to ensure at least 1 exists
		$this->assertTrue( is_array( $markers ) );
		$this->assertTrue( count( $markers ) >= 1 );
		// test that they contain actual posts w/ ids
		// @todo - remove the test post to clean up
	}
	
	public function testGetPlacemarksJsonEncode()
	{
		$bgmp = new BasicGoogleMapsPlacemarks();
		$bgmp->init();
		
		$markers = json_encode( $bgmp->getMapPlacemarks( array() ) );
		$this->assertTrue( is_string( $markers ) );
		$markers = json_decode( $markers );
		$this->assertFalse( is_null( $markers ) );
		
		// @todo again w/ various attributes
	}
	
	// json_decode getmapoptions test
	
	// public function testUpgradeFromBeforeX.X.X
	
	// createPostType() returns post type object and not WP_Error object
	
	// enquue  message
		// returns false when $message isn't a string
		// returns false when $type and $mode are invalid?
		// if message is string, adds it to $bgmp->options, increases usermessagecount if appropriate, sets $updatedoptions to true, returns true 
	
	// describe
		// if $output = 'output', returns the content
		// if echo then doesn't reutrn anything
		// if notice enquese a message and doesnt' return anything
	
	// shutdown?	
	
} // end bgmpCoreUnitTests

// setup another class to test settings.php - maybe should setup separate file and testsuite functions?

?>