<?php
/**
 * Test suite for project meta fields and custom data
 * Tests for Group 5: Custom Post Types - Meta Fields functionality
 * 
 * @package Kei_Portfolio
 * @subpackage Tests
 * @group custom-post-types
 * @group meta-fields
 */

require_once dirname(__FILE__) . '/bootstrap.php';

class ProjectMetaFieldsTest extends WP_UnitTestCase {
    
    /**
     * Test fixtures
     */
    private $test_project_id;
    
    /**
     * Set up test environment before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Ensure post types are registered
        if ( ! did_action( 'init' ) ) {
            do_action( 'init' );
        }
        
        // Create a test project
        $this->test_project_id = $this->factory->post->create( array(
            'post_type' => 'project',
            'post_title' => 'Meta Test Project',
            'post_status' => 'publish',
        ) );
    }
    
    /**
     * Clean up after each test
     */
    public function tearDown(): void {
        if ( $this->test_project_id ) {
            wp_delete_post( $this->test_project_id, true );
        }
        
        parent::tearDown();
    }
    
    /**
     * Test project meta data storage and retrieval
     * 
     * @test
     */
    public function test_project_meta_storage() {
        $meta_data = array(
            'project_url' => 'https://example.com/project',
            'github_url' => 'https://github.com/user/project',
            'project_status' => 'completed',
            'completion_date' => '2023-06-15',
            'client_name' => 'Test Client',
            'project_budget' => '$5000',
            'team_size' => '3',
            'technologies_used' => 'WordPress, PHP, JavaScript, MySQL',
        );
        
        // Store meta data
        foreach ( $meta_data as $key => $value ) {
            $result = update_post_meta( $this->test_project_id, $key, $value );
            $this->assertTrue( $result !== false, "Should store meta data for {$key}" );
        }
        
        // Retrieve and verify meta data
        foreach ( $meta_data as $key => $expected_value ) {
            $actual_value = get_post_meta( $this->test_project_id, $key, true );
            $this->assertEquals( $expected_value, $actual_value, "Meta data for {$key} should match" );
        }
    }
    
    /**
     * Test meta data sanitization
     * 
     * @test
     */
    public function test_meta_data_sanitization() {
        $malicious_data = array(
            'project_url' => '<script>alert("XSS")</script>https://evil.com',
            'client_name' => '<b>Client</b> & Company <script>alert(1)</script>',
            'description' => 'Normal text with <script>malicious()</script> content',
        );
        
        foreach ( $malicious_data as $key => $value ) {
            update_post_meta( $this->test_project_id, $key, $value );
            $stored_value = get_post_meta( $this->test_project_id, $key, true );
            
            // WordPress should sanitize the data automatically
            $this->assertEquals( $value, $stored_value, 'Meta data should be stored as-is (sanitization happens on output)' );
        }
        
        // Test sanitized output
        foreach ( $malicious_data as $key => $value ) {
            $stored_value = get_post_meta( $this->test_project_id, $key, true );
            $sanitized_value = sanitize_text_field( $stored_value );
            
            $this->assertStringNotContainsString( '<script>', $sanitized_value, 'Sanitized value should not contain script tags' );
        }
    }
    
    /**
     * Test featured image (thumbnail) functionality
     * 
     * @test
     */
    public function test_project_featured_image() {
        // Test if project supports thumbnails
        $this->assertTrue( 
            post_type_supports( 'project', 'thumbnail' ), 
            'Project post type should support thumbnails' 
        );
        
        // Create a test attachment (mock image)
        $attachment_id = $this->factory->attachment->create( array(
            'post_mime_type' => 'image/jpeg',
            'post_title' => 'Test Project Image',
        ) );
        
        // Set as featured image
        $result = set_post_thumbnail( $this->test_project_id, $attachment_id );
        $this->assertTrue( $result, 'Should set featured image successfully' );
        
        // Verify featured image is set
        $this->assertTrue( 
            has_post_thumbnail( $this->test_project_id ), 
            'Project should have featured image' 
        );
        
        $thumbnail_id = get_post_thumbnail_id( $this->test_project_id );
        $this->assertEquals( $attachment_id, $thumbnail_id, 'Thumbnail ID should match' );
        
        // Clean up
        wp_delete_attachment( $attachment_id, true );
    }
    
    /**
     * Test project gallery meta (multiple images)
     * 
     * @test
     */
    public function test_project_gallery_meta() {
        // Create multiple test attachments
        $gallery_ids = array();
        for ( $i = 1; $i <= 3; $i++ ) {
            $gallery_ids[] = $this->factory->attachment->create( array(
                'post_mime_type' => 'image/jpeg',
                'post_title' => "Gallery Image {$i}",
            ) );
        }
        
        // Store gallery as meta data
        $gallery_meta = implode( ',', $gallery_ids );
        update_post_meta( $this->test_project_id, 'project_gallery', $gallery_meta );
        
        // Retrieve and verify
        $stored_gallery = get_post_meta( $this->test_project_id, 'project_gallery', true );
        $stored_ids = array_map( 'intval', explode( ',', $stored_gallery ) );
        
        $this->assertEquals( $gallery_ids, $stored_ids, 'Gallery IDs should match' );
        $this->assertCount( 3, $stored_ids, 'Should have 3 gallery images' );
        
        // Clean up
        foreach ( $gallery_ids as $id ) {
            wp_delete_attachment( $id, true );
        }
    }
    
    /**
     * Test project testimonial meta
     * 
     * @test
     */
    public function test_project_testimonial_meta() {
        $testimonial_data = array(
            'testimonial_text' => 'This was an excellent project. The developer exceeded our expectations.',
            'testimonial_author' => 'John Doe',
            'testimonial_position' => 'CEO',
            'testimonial_company' => 'Example Corp',
            'testimonial_rating' => '5',
        );
        
        foreach ( $testimonial_data as $key => $value ) {
            update_post_meta( $this->test_project_id, $key, $value );
        }
        
        // Verify testimonial data
        foreach ( $testimonial_data as $key => $expected_value ) {
            $actual_value = get_post_meta( $this->test_project_id, $key, true );
            $this->assertEquals( $expected_value, $actual_value, "Testimonial {$key} should match" );
        }
        
        // Test testimonial rating validation
        $rating = get_post_meta( $this->test_project_id, 'testimonial_rating', true );
        $this->assertGreaterThanOrEqual( 1, intval( $rating ), 'Rating should be at least 1' );
        $this->assertLessThanOrEqual( 5, intval( $rating ), 'Rating should be at most 5' );
    }
    
    /**
     * Test project timeline meta
     * 
     * @test
     */
    public function test_project_timeline_meta() {
        $timeline_data = array(
            array(
                'phase' => 'Planning',
                'description' => 'Requirements gathering and planning',
                'start_date' => '2023-01-01',
                'end_date' => '2023-01-15',
            ),
            array(
                'phase' => 'Development',
                'description' => 'Core development work',
                'start_date' => '2023-01-16',
                'end_date' => '2023-04-30',
            ),
            array(
                'phase' => 'Testing & Launch',
                'description' => 'Testing and deployment',
                'start_date' => '2023-05-01',
                'end_date' => '2023-05-15',
            ),
        );
        
        // Store timeline as serialized data
        update_post_meta( $this->test_project_id, 'project_timeline', $timeline_data );
        
        // Retrieve and verify
        $stored_timeline = get_post_meta( $this->test_project_id, 'project_timeline', true );
        
        $this->assertEquals( $timeline_data, $stored_timeline, 'Timeline data should match' );
        $this->assertCount( 3, $stored_timeline, 'Should have 3 timeline phases' );
        
        // Verify individual phases
        foreach ( $stored_timeline as $index => $phase ) {
            $this->assertArrayHasKey( 'phase', $phase, "Phase {$index} should have phase name" );
            $this->assertArrayHasKey( 'description', $phase, "Phase {$index} should have description" );
            $this->assertArrayHasKey( 'start_date', $phase, "Phase {$index} should have start date" );
            $this->assertArrayHasKey( 'end_date', $phase, "Phase {$index} should have end date" );
        }
    }
    
    /**
     * Test custom field validation and constraints
     * 
     * @test
     */
    public function test_meta_field_validation() {
        // Test URL validation
        $valid_url = 'https://example.com/project';
        $invalid_url = 'not-a-valid-url';
        
        update_post_meta( $this->test_project_id, 'project_url', $valid_url );
        $stored_url = get_post_meta( $this->test_project_id, 'project_url', true );
        
        // Test if stored URL is valid
        $this->assertEquals( filter_var( $stored_url, FILTER_VALIDATE_URL ), $stored_url, 'Should store valid URL' );
        
        // Test date validation
        $valid_date = '2023-06-15';
        $invalid_date = 'invalid-date';
        
        update_post_meta( $this->test_project_id, 'completion_date', $valid_date );
        $stored_date = get_post_meta( $this->test_project_id, 'completion_date', true );
        
        // Verify date format
        $this->assertMatchesRegularExpression( '/^\d{4}-\d{2}-\d{2}$/', $stored_date, 'Date should be in YYYY-MM-DD format' );
        
        // Test numeric validation
        $team_size = '5';
        update_post_meta( $this->test_project_id, 'team_size', $team_size );
        $stored_team_size = get_post_meta( $this->test_project_id, 'team_size', true );
        
        $this->assertTrue( is_numeric( $stored_team_size ), 'Team size should be numeric' );
        $this->assertGreaterThan( 0, intval( $stored_team_size ), 'Team size should be positive' );
    }
    
    /**
     * Test meta data query performance
     * 
     * @test
     */
    public function test_meta_query_performance() {
        // Create multiple projects with meta data
        $project_ids = array();
        for ( $i = 1; $i <= 10; $i++ ) {
            $project_id = $this->factory->post->create( array(
                'post_type' => 'project',
                'post_title' => "Performance Test Project {$i}",
                'post_status' => 'publish',
            ) );
            
            update_post_meta( $project_id, 'project_status', 'completed' );
            update_post_meta( $project_id, 'team_size', rand( 1, 10 ) );
            
            $project_ids[] = $project_id;
        }
        
        $start_time = microtime( true );
        
        // Query projects by meta data
        $query = new WP_Query( array(
            'post_type' => 'project',
            'meta_query' => array(
                array(
                    'key' => 'project_status',
                    'value' => 'completed',
                    'compare' => '=',
                ),
            ),
        ) );
        
        $end_time = microtime( true );
        $execution_time = $end_time - $start_time;
        
        $this->assertLessThan( 1.0, $execution_time, 'Meta query should execute within 1 second' );
        $this->assertGreaterThanOrEqual( 10, $query->found_posts, 'Should find at least 10 completed projects' );
        
        // Clean up
        foreach ( $project_ids as $project_id ) {
            wp_delete_post( $project_id, true );
        }
    }
    
    /**
     * Test bulk meta operations
     * 
     * @test
     */
    public function test_bulk_meta_operations() {
        // Create multiple projects
        $project_ids = array();
        for ( $i = 1; $i <= 5; $i++ ) {
            $project_ids[] = $this->factory->post->create( array(
                'post_type' => 'project',
                'post_title' => "Bulk Test Project {$i}",
                'post_status' => 'publish',
            ) );
        }
        
        // Bulk update meta data
        foreach ( $project_ids as $project_id ) {
            update_post_meta( $project_id, 'bulk_update_test', 'bulk_value' );
            update_post_meta( $project_id, 'project_category', 'web_development' );
        }
        
        // Verify bulk updates
        foreach ( $project_ids as $project_id ) {
            $bulk_value = get_post_meta( $project_id, 'bulk_update_test', true );
            $category = get_post_meta( $project_id, 'project_category', true );
            
            $this->assertEquals( 'bulk_value', $bulk_value, 'Bulk meta update should work' );
            $this->assertEquals( 'web_development', $category, 'Category meta should be set' );
        }
        
        // Test bulk deletion
        foreach ( $project_ids as $project_id ) {
            delete_post_meta( $project_id, 'bulk_update_test' );
        }
        
        // Verify deletion
        foreach ( $project_ids as $project_id ) {
            $bulk_value = get_post_meta( $project_id, 'bulk_update_test', true );
            $this->assertEmpty( $bulk_value, 'Meta should be deleted' );
        }
        
        // Clean up
        foreach ( $project_ids as $project_id ) {
            wp_delete_post( $project_id, true );
        }
    }
    
    /**
     * Test meta data export/import functionality
     * 
     * @test
     */
    public function test_meta_data_export_import() {
        // Add comprehensive meta data
        $complete_meta = array(
            'project_url' => 'https://example.com/project',
            'github_url' => 'https://github.com/user/project',
            'project_status' => 'completed',
            'completion_date' => '2023-06-15',
            'client_name' => 'Test Client',
            'team_size' => '4',
            'project_budget' => '$10000',
            'technologies_used' => 'WordPress, React, Node.js',
            'project_description' => 'A comprehensive web application',
        );
        
        foreach ( $complete_meta as $key => $value ) {
            update_post_meta( $this->test_project_id, $key, $value );
        }
        
        // Export meta data
        $exported_meta = get_post_meta( $this->test_project_id );
        
        // Verify export includes all custom meta
        foreach ( $complete_meta as $key => $expected_value ) {
            $this->assertArrayHasKey( $key, $exported_meta, "Exported meta should include {$key}" );
            $this->assertEquals( $expected_value, $exported_meta[$key][0], "Exported value for {$key} should match" );
        }
        
        // Test import to new project
        $new_project_id = $this->factory->post->create( array(
            'post_type' => 'project',
            'post_title' => 'Import Test Project',
            'post_status' => 'publish',
        ) );
        
        // Import meta data
        foreach ( $complete_meta as $key => $value ) {
            update_post_meta( $new_project_id, $key, $value );
        }
        
        // Verify import
        foreach ( $complete_meta as $key => $expected_value ) {
            $imported_value = get_post_meta( $new_project_id, $key, true );
            $this->assertEquals( $expected_value, $imported_value, "Imported value for {$key} should match" );
        }
        
        // Clean up
        wp_delete_post( $new_project_id, true );
    }
}