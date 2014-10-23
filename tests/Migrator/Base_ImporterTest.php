<?php

use TenUp\Exodus\TestCase as TestCase;

class Base_ImporterTest extends TestCase {

	protected $base_importer;

	public static function setUpBeforeClass() {
		require_once dirname( __DIR__ ) . '/test-tools/wp-cli-mock.php';
	}

	public function setUp() {
		parent::setUp();
		$this->base_importer = $this->getMockForAbstractClass( 'TenUp\Exodus\Migrator\Base_Importer' );
		$this->base_importer->expects( $this->any() )->method( "insert_post" )->will( $this->returnValue( true ) );
	}

	public function testInsertPost() {
		global $wpdb;

		date_default_timezone_set( 'America/New_York' );

		$wpdb = \Mockery::mock( 'wpdb' );
		$wpdb->shouldReceive( 'get_var' )->andReturn( false )->once();
		$wpdb->postmeta = 'postmeta';

		\WP_Mock::wpFunction( 'is_wp_error', array(
			'times'  => '1+',
			'args'   => array( 1 ),
			'return' => false,
		) );

		$post = $this->get_import_data( 1 );

		\WP_Mock::wpFunction( 'wp_insert_post', array(
			'times'  => 1,
			'args'   => array(
				array(
					'post_status'   => 'publish',
					'post_type'     => $post->post_type,
					'post_title'    => $post->post_title,
					'post_content'  => $post->post_content,
					'post_author'   => $post->post_author,
					'post_excerpt'  => '',
					'post_date'     => '2014-08-19 12:01:00',
					'post_date_gmt' => '2014-08-19 12:01:00'
				)
			),
			'return' => 1,
		) );

		$user_data = $this->get_user_data();

		\WP_Mock::wpFunction( 'email_exists', array(
			'times'  => '1+',
			'args'   => array( $user_data->user_email ),
			'return' => false,
		) );

		\WP_Mock::wpFunction( 'username_exists', array(
			'times'  => '1+',
			'args'   => array( $user_data->user_login ),
			'return' => false,
		) );

		\WP_Mock::wpPassthruFunction( 'sanitize_user', array(
			'times'  => '1+',
			'args'   => array( $user_data->user_login ),
			'return' => $user_data->user_login,
		) );

		\WP_Mock::wpFunction( 'wp_generate_password', array(
			'times'  => '1+',
			'return' => 'testpassword',
		) );

		\WP_Mock::wpFunction( 'wp_insert_user', array(
			'times'  => '1+',
			'args'   => array(
				array(
					'user_login'    => $user_data->user_login,
					'user_pass'     => wp_generate_password(),
					'user_nicename' => $user_data->user_login,
					'nickname'      => $user_data->user_login,
					'display_name'  => $user_data->user_login,
					'first_name'    => $user_data->first_name,
					'last_name'     => $user_data->last_name,
					'user_email'    => $user_data->user_email,
				)
			),
			'return' => 1,
		) );

		\WP_Mock::wpFunction( 'wp_update_post', array(
			'times'  => 1,
			'args'   => array( array( 'ID' => 1, 'post_content' => $post->post_content ) ),
			'return' => 1,
		) );

		\WP_Mock::wpFunction( 'update_post_meta', array(
			'times'  => '1+',
			'args'   => array( 1, "migration_import_id", "eed262f4bda149b826a1fc4954e7568e" ),
			'return' => 1,
		) );

		$id = $this->base_importer->insert_post( $this->get_import_data( $this->get_user_data() ) );

		$this->assertEquals( 1, $id );
	}

	public function testCreateNewUser() {
		$user_data = $this->get_user_data();

		\WP_Mock::wpFunction( 'email_exists', array(
			'times'  => '1',
			'args'   => array( $user_data->user_email ),
			'return' => false,
		) );

		\WP_Mock::wpFunction( 'username_exists', array(
			'times'  => '1',
			'args'   => array( $user_data->user_login ),
			'return' => false,
		) );

		\WP_Mock::wpPassthruFunction( 'sanitize_user', array(
			'times'  => '2',
			'args'   => array( $user_data->user_login ),
			'return' => $user_data->user_login,
		) );

		\WP_Mock::wpFunction( 'wp_generate_password', array(
			'times'  => '2',
			'return' => 'anotherpassword',
		) );

		\WP_Mock::wpFunction( 'wp_insert_user', array(
			'times'  => '1+',
			'args'   => array(
				array(
					'user_login'    => $user_data->user_login,
					'user_pass'     => wp_generate_password(),
					'user_nicename' => $user_data->user_login,
					'nickname'      => $user_data->user_login,
					'display_name'  => $user_data->user_login,
					'first_name'    => $user_data->first_name,
					'last_name'     => $user_data->last_name,
					'user_email'    => $user_data->user_email,
				)
			),
			'return' => 10,
		) );

		\WP_Mock::wpFunction( 'is_wp_error', array(
			'times'  => '1+',
			'args'   => array( 10 ),
			'return' => false,
		) );

		$user = $this->invoke_protected_method( $this->base_importer, 'user', array( $user_data ) );
		$this->assertEquals( 10, $user );
	}

	public function testScrapeContentForImages() {

		\WP_Mock::wpFunction( 'wp_get_attachment_url', array(
			'times'  => '1',
			'args'   => array( 15 ),
			'return' => 'http://updatepath.com/wp-content/uploads/2012/10/10up.jpg',
		) );

		\WP_Mock::wpFunction( 'download_url', array(
			'times'  => '1',
			'args'   => array( 'http://10up.com/wp-content/uploads/2012/10/10up.jpg' ),
			'return' => 'http://updatepath.com/wp-content/uploads/2012/10/10up.jpg',
		) );

		\WP_Mock::wpFunction( 'is_wp_error', array(
			'times'  => '1+',
			'return' => false,
		) );

		\WP_Mock::wpFunction( 'media_handle_sideload', array(
			'times'  => '1',
			'args'   => array(
				array(
					'name'     => '10up.jpg',
					'tmp_name' => 'http://updatepath.com/wp-content/uploads/2012/10/10up.jpg'
				),
				1,
				"",
				array()
			),
			'return' => 15,
		) );

		$content          = $this->get_post_content();
		$post_content     = $this->invoke_protected_method( $this->base_importer, 'media', array( $content, 1 ) );
		$expected_content = str_replace( 'http://10up.com', 'http://updatepath.com', $content );
		$this->assertEquals( $expected_content, $post_content );
	}

	public function testUploadImage() {

		\WP_Mock::wpFunction( 'download_url', array(
			'times'  => '1',
			'args'   => array( 'http://10up.com/wp-content/uploads/2012/10/10up.jpg' ),
			'return' => 'http://upload.com/wp-content/uploads/2012/10/10up.jpg',
		) );

		\WP_Mock::wpFunction( 'is_wp_error', array(
			'times'  => '1+',
			'return' => false,
		) );

		\WP_Mock::wpFunction( 'media_handle_sideload', array(
			'times'  => '1',
			'args'   => array(
				array(
					'name'     => '10up.jpg',
					'tmp_name' => 'http://upload.com/wp-content/uploads/2012/10/10up.jpg'
				),
				10,
				"",
				array()
			),
			'return' => 15,
		) );

		\WP_Mock::wpFunction( 'wp_get_attachment_url', array(
			'times'  => '1',
			'args'   => array( 15 ),
			'return' => 'http://upload.com/wp-content/uploads/2012/10/10up.jpg',
		) );

		$file = 'http://10up.com/wp-content/uploads/2012/10/10up.jpg';
		$uploaded_id = $this->invoke_protected_method( $this->base_importer, 'upload_media', array( $file, 10 ) );
		$this->assertEquals( 15, $uploaded_id );
		$this->assertEquals( 'http://upload.com/wp-content/uploads/2012/10/10up.jpg' , wp_get_attachment_url($uploaded_id) );
	}

	public function tearDown() {
		parent::tearDown();
		\Mockery::close();
	}

	protected function get_import_data( $author ) {

		$post               = new stdClass();
		$post->post_title   = 'Test title';
		$post->post_type    = 'post';
		$post->post_content = 'body content';
		$post->post_date    = 1408464060;
		$post->post_author  = $author;

		return $post;
	}

	protected function get_user_data() {
		$user             = new stdClass();
		$user->first_name = "John";
		$user->last_name  = "Smith";
		$user->user_login = "John Smith";
		$user->user_email = '';

		return $user;
	}

	protected function get_post_content() {
		return '<h1>HTML Ipsum Presents</h1>

				<p><strong>Pellentesque habitant morbi tristique</strong> senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. <em>Aenean ultricies mi vitae est.</em> Mauris placerat eleifend leo. Quisque sit amet est et sapien ullamcorper pharetra. Vestibulum erat wisi, condimentum sed, <code>commodo vitae</code>, ornare sit amet, wisi. Aenean fermentum, elit eget tincidunt condimentum, eros ipsum rutrum orci, sagittis tempus lacus enim ac dui. <a href="#">Donec non enim</a> in turpis pulvinar facilisis. Ut felis.</p>

				<h2>Header Level 2</h2>

				<img src="http://10up.com/wp-content/uploads/2012/10/10up.jpg" alt="Test Image"/>

				<ol>
				   <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
				   <li>Aliquam tincidunt mauris eu risus.</li>
				</ol>

				<blockquote><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus magna. Cras in mi at felis aliquet congue. Ut a est eget ligula molestie gravida. Curabitur massa. Donec eleifend, libero at sagittis mollis, tellus est malesuada tellus, at luctus turpis elit sit amet quam. Vivamus pretium ornare est.</p></blockquote>

				<h3>Header Level 3</h3>

				<ul>
				   <li>Lorem ipsum dolor sit amet, consectetuer adipiscing elit.</li>
				   <li>Aliquam tincidunt mauris eu risus.</li>
				</ul>';
	}

}
 