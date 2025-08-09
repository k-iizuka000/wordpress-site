<?php
/**
 * Security test suite for custom post types and templates
 * Tests for Group 5: Security and vulnerability assessment
 * 
 * @package Kei_Portfolio
 * @subpackage Tests
 * @group security
 * @group custom-post-types
 */

require_once dirname(__FILE__) . '/bootstrap.php';

class SecurityTest extends WP_UnitTestCase {
    
    /**
     * Test fixtures
     */
    private $test_project_id;
    private $test_user_id;
    
    /**
     * Set up test environment before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Ensure post types are registered
        if ( ! did_action( 'init' ) ) {
            do_action( 'init' );
        }
        
        // Create test project
        $this->test_project_id = $this->factory->post->create( array(
            'post_type' => 'project',
            'post_title' => 'Security Test Project',
            'post_status' => 'publish',
        ) );
        
        // Create test user with limited permissions
        $this->test_user_id = $this->factory->user->create( array(
            'role' => 'subscriber',
        ) );
    }
    
    /**
     * Clean up after each test
     */
    public function tearDown(): void {
        if ( $this->test_project_id ) {
            wp_delete_post( $this->test_project_id, true );
        }
        
        if ( $this->test_user_id ) {
            wp_delete_user( $this->test_user_id );
        }
        
        parent::tearDown();
    }
    
    /**
     * Test XSS protection in project output
     * 
     * @test
     */
    public function test_xss_protection_in_project_content() {
        $xss_payloads = array(
            '<script>alert("XSS")</script>',
            '"><script>alert(1)</script>',
            '<img src="x" onerror="alert(1)">',
            '<svg onload="alert(1)">',
            'javascript:alert(1)',
            '<iframe src="javascript:alert(1)"></iframe>',
            '<object data="javascript:alert(1)">',
            '<embed src="javascript:alert(1)">',
        );
        
        foreach ( $xss_payloads as $payload ) {
            // Update project with malicious content
            wp_update_post( array(
                'ID' => $this->test_project_id,
                'post_title' => 'XSS Test: ' . $payload,
                'post_content' => 'Content with payload: ' . $payload,
                'post_excerpt' => 'Excerpt with payload: ' . $payload,
            ) );
            
            // Test title output
            $title = get_the_title( $this->test_project_id );
            $escaped_title = esc_html( $title );
            $this->assertStringNotContainsString( '<script>', $escaped_title, 'Title should be escaped' );
            
            // Test content output
            $content = get_post_field( 'post_content', $this->test_project_id );
            $escaped_content = wp_kses_post( $content );
            $this->assertStringNotContainsString( 'javascript:', $escaped_content, 'Content should filter javascript: URLs' );
            
            // Test excerpt output
            $excerpt = get_the_excerpt( $this->test_project_id );
            $this->assertStringNotContainsString( '<script>', $excerpt, 'Excerpt should be safe' );
        }
    }
    
    /**
     * Test SQL injection protection in custom queries
     * 
     * @test
     */
    public function test_sql_injection_protection() {
        global $wpdb;
        
        $injection_payloads = array(
            "' OR '1'='1",
            "'; DROP TABLE wp_posts; --",
            "' UNION SELECT user_pass FROM wp_users --",
            "1' AND (SELECT COUNT(*) FROM wp_users) > 0 --",
        );
        
        foreach ( $injection_payloads as $payload ) {
            // Test prepared statement protection
            $prepared_query = $wpdb->prepare(
                "SELECT * FROM {$wpdb->posts} WHERE post_type = %s AND post_title = %s",
                'project',
                $payload
            );
            
            $this->assertStringNotContainsString( 'DROP TABLE', $prepared_query, 'Prepared statement should escape SQL injection' );
            $this->assertStringNotContainsString( 'UNION SELECT', $prepared_query, 'Prepared statement should prevent UNION attacks' );
            
            // Execute query safely
            $results = $wpdb->get_results( $prepared_query );
            $this->assertIsArray( $results, 'Query should execute without error' );
        }
    }
    
    /**
     * Test capability checks for project operations
     * 
     * @test
     */
    public function test_project_capability_checks() {
        // Set current user to subscriber (limited permissions)
        wp_set_current_user( $this->test_user_id );
        
        // Test read capabilities
        $this->assertTrue( 
            current_user_can( 'read_post', $this->test_project_id ), 
            'Subscribers should be able to read published projects' 
        );
        
        // Test write capabilities (should fail for subscribers)
        $this->assertFalse( 
            current_user_can( 'edit_post', $this->test_project_id ), 
            'Subscribers should not be able to edit projects' 
        );
        
        $this->assertFalse( 
            current_user_can( 'delete_post', $this->test_project_id ), 
            'Subscribers should not be able to delete projects' 
        );
        
        // Test project creation (should fail for subscribers)
        $this->assertFalse( 
            current_user_can( 'edit_posts' ), 
            'Subscribers should not be able to create projects' 
        );
        
        // Test with admin user
        wp_set_current_user( 1 ); // Admin user
        
        $this->assertTrue( 
            current_user_can( 'edit_post', $this->test_project_id ), 
            'Admins should be able to edit projects' 
        );
        
        $this->assertTrue( 
            current_user_can( 'delete_post', $this->test_project_id ), 
            'Admins should be able to delete projects' 
        );
    }
    
    /**
     * Test nonce verification in admin operations
     * 
     * @test
     */
    public function test_nonce_verification() {
        // Test nonce creation
        $action = 'edit_project_' . $this->test_project_id;
        $nonce = wp_create_nonce( $action );
        
        $this->assertNotEmpty( $nonce, 'Nonce should be created' );
        $this->assertIsString( $nonce, 'Nonce should be a string' );
        
        // Test nonce verification (valid)
        $this->assertTrue( 
            wp_verify_nonce( $nonce, $action ), 
            'Valid nonce should verify successfully' 
        );
        
        // Test nonce verification (invalid)
        $this->assertFalse( 
            wp_verify_nonce( 'invalid_nonce', $action ), 
            'Invalid nonce should fail verification' 
        );
        
        // Test nonce verification (wrong action)
        $this->assertFalse( 
            wp_verify_nonce( $nonce, 'different_action' ), 
            'Nonce should fail with different action' 
        );
        
        // Test generic nonces for common operations
        $common_actions = array(
            'project_meta_update',
            'project_status_change',
            'project_bulk_edit',
        );
        
        foreach ( $common_actions as $action ) {
            $action_nonce = wp_create_nonce( $action );
            $this->assertTrue( 
                wp_verify_nonce( $action_nonce, $action ), 
                "Nonce verification should work for {$action}" 
            );
        }
    }
    
    /**
     * Test file upload security for project attachments
     * 
     * @test
     */
    public function test_file_upload_security() {
        // Test allowed file types
        $allowed_types = get_allowed_mime_types();
        
        // Common safe file types should be allowed
        $safe_types = array( 'jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx' );
        foreach ( $safe_types as $type ) {
            $found = false;
            foreach ( $allowed_types as $ext => $mime ) {
                if ( strpos( $ext, $type ) !== false ) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue( $found, "Safe file type {$type} should be allowed" );
        }
        
        // Dangerous file types should not be allowed
        $dangerous_types = array( 'php', 'exe', 'js', 'html', 'htm', 'bat', 'sh' );
        foreach ( $dangerous_types as $type ) {
            $this->assertArrayNotHasKey( $type, $allowed_types, "Dangerous file type {$type} should not be allowed" );
        }
        
        // Test file name sanitization
        $dangerous_names = array(
            '../../etc/passwd',
            '<script>alert(1)</script>.jpg',
            'file.php.jpg',
            'normal file.exe',
        );
        
        foreach ( $dangerous_names as $name ) {
            $sanitized = sanitize_file_name( $name );
            $this->assertStringNotContainsString( '..', $sanitized, 'Path traversal should be prevented' );
            $this->assertStringNotContainsString( '<script>', $sanitized, 'HTML should be stripped from filename' );
        }
    }
    
    /**
     * Test data sanitization in meta fields
     * 
     * @test
     */
    public function test_meta_field_sanitization() {
        $malicious_inputs = array(
            'project_url' => '<script>alert(1)</script>https://evil.com',
            'client_name' => 'Client & <b>Company</b> <script>alert(1)</script>',
            'project_description' => 'Description with <iframe src="javascript:alert(1)"></iframe>',
            'testimonial_text' => 'Great work! <script>steal_cookies()</script>',
        );
        
        foreach ( $malicious_inputs as $key => $value ) {
            // Store the malicious input
            update_post_meta( $this->test_project_id, $key, $value );
            
            // Retrieve and test different sanitization methods
            $stored_value = get_post_meta( $this->test_project_id, $key, true );
            
            // Test various sanitization functions
            $sanitized_text = sanitize_text_field( $stored_value );
            $this->assertStringNotContainsString( '<script>', $sanitized_text, 'sanitize_text_field should remove scripts' );
            
            $sanitized_textarea = sanitize_textarea_field( $stored_value );
            $this->assertStringNotContainsString( '<script>', $sanitized_textarea, 'sanitize_textarea_field should remove scripts' );
            
            $escaped_html = esc_html( $stored_value );
            $this->assertStringNotContainsString( '<script>', $escaped_html, 'esc_html should escape scripts' );
            
            $kses_filtered = wp_kses_post( $stored_value );
            $this->assertStringNotContainsString( '<script>', $kses_filtered, 'wp_kses_post should filter scripts' );
        }
    }
    
    /**
     * Test URL validation and sanitization
     * 
     * @test
     */
    public function test_url_validation() {
        $test_urls = array(
            'https://example.com' => true,
            'http://example.com' => true,
            'ftp://example.com' => false,
            'javascript:alert(1)' => false,
            'data:text/html,<script>alert(1)</script>' => false,
            'vbscript:msgbox(1)' => false,
            '//evil.com' => false,
            'https://trusted-domain.com/path?param=value' => true,
        );
        
        foreach ( $test_urls as $url => $should_be_valid ) {
            // Test WordPress URL validation
            $is_valid = filter_var( $url, FILTER_VALIDATE_URL ) !== false;
            
            if ( $should_be_valid ) {
                $this->assertTrue( $is_valid, "URL {$url} should be valid" );
                
                // Test additional WordPress-specific validation
                $escaped_url = esc_url( $url );
                $this->assertNotEmpty( $escaped_url, "URL {$url} should pass esc_url" );
            } else {
                // For dangerous URLs, ensure they're properly handled
                $escaped_url = esc_url( $url );
                $this->assertStringNotContainsString( 'javascript:', $escaped_url, "Dangerous URL {$url} should be neutralized" );
                $this->assertStringNotContainsString( 'vbscript:', $escaped_url, "Dangerous URL {$url} should be neutralized" );
            }
        }
    }
    
    /**
     * Test authentication and authorization
     * 
     * @test
     */
    public function test_authentication_authorization() {
        // Test unauthenticated access
        wp_set_current_user( 0 ); // No user
        
        $this->assertTrue( 
            current_user_can( 'read_post', $this->test_project_id ), 
            'Unauthenticated users should be able to read published projects' 
        );
        
        $this->assertFalse( 
            current_user_can( 'edit_posts' ), 
            'Unauthenticated users should not be able to edit posts' 
        );
        
        // Test role-based access
        $roles_and_permissions = array(
            'subscriber' => array(
                'read' => true,
                'edit_posts' => false,
                'publish_posts' => false,
                'delete_posts' => false,
            ),
            'contributor' => array(
                'read' => true,
                'edit_posts' => true,
                'publish_posts' => false,
                'delete_posts' => false,
            ),
            'author' => array(
                'read' => true,
                'edit_posts' => true,
                'publish_posts' => true,
                'delete_posts' => true,
            ),
            'editor' => array(
                'read' => true,
                'edit_posts' => true,
                'publish_posts' => true,
                'delete_posts' => true,
            ),
        );
        
        foreach ( $roles_and_permissions as $role => $permissions ) {
            $user_id = $this->factory->user->create( array( 'role' => $role ) );
            wp_set_current_user( $user_id );
            
            foreach ( $permissions as $cap => $should_have ) {
                $has_cap = current_user_can( $cap );
                if ( $should_have ) {
                    $this->assertTrue( $has_cap, "Role {$role} should have {$cap} capability" );
                } else {
                    $this->assertFalse( $has_cap, "Role {$role} should not have {$cap} capability" );
                }
            }
            
            wp_delete_user( $user_id );
        }
    }
    
    /**
     * Test rate limiting and spam protection
     * 
     * @test
     */
    public function test_rate_limiting() {
        // Simulate rapid requests
        $request_count = 0;
        $max_requests = 100;
        $start_time = time();
        
        // Simulate multiple project creations (would be rate limited in production)
        for ( $i = 0; $i < 10; $i++ ) {
            $project_id = $this->factory->post->create( array(
                'post_type' => 'project',
                'post_title' => "Rate Limit Test Project {$i}",
                'post_status' => 'publish',
            ) );
            
            $this->assertGreaterThan( 0, $project_id, "Project creation {$i} should succeed" );
            $request_count++;
            
            // Clean up immediately
            wp_delete_post( $project_id, true );
        }
        
        $end_time = time();
        $duration = $end_time - $start_time;
        
        $this->assertEquals( 10, $request_count, 'All test requests should be processed' );
        $this->assertLessThan( 30, $duration, 'Requests should complete within reasonable time' );
    }
    
    /**
     * Test security headers and content policies
     * 
     * @test
     */
    public function test_security_headers() {
        // These tests would normally check HTTP headers in a real environment
        // For unit tests, we verify that security functions are available
        
        // Test that WordPress security functions exist
        $this->assertTrue( function_exists( 'wp_nonce_field' ), 'wp_nonce_field should be available' );
        $this->assertTrue( function_exists( 'check_admin_referer' ), 'check_admin_referer should be available' );
        $this->assertTrue( function_exists( 'sanitize_text_field' ), 'sanitize_text_field should be available' );
        $this->assertTrue( function_exists( 'wp_kses' ), 'wp_kses should be available' );
        $this->assertTrue( function_exists( 'esc_html' ), 'esc_html should be available' );
        $this->assertTrue( function_exists( 'esc_url' ), 'esc_url should be available' );
        $this->assertTrue( function_exists( 'esc_attr' ), 'esc_attr should be available' );
        
        // Test content security policy helpers
        $this->assertTrue( function_exists( 'wp_kses_post' ), 'wp_kses_post should be available' );
        $this->assertTrue( function_exists( 'wp_kses_data' ), 'wp_kses_data should be available' );
        
        // Test our custom security functions
        $this->assertTrue( function_exists( 'kei_portfolio_send_security_headers' ), 'Security headers function should exist' );
        $this->assertTrue( function_exists( 'kei_portfolio_dev_csp_policy' ), 'CSP policy filter function should exist' );
        $this->assertTrue( function_exists( 'kei_portfolio_log_security_event' ), 'Security logging function should exist' );
    }
    
    /**
     * Test CSP policy configuration
     * 
     * @test
     */
    public function test_csp_policy_configuration() {
        // Test default CSP policy structure
        $default_policy = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://www.googletagmanager.com https://www.google-analytics.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "img-src 'self' data: https: http:",
            "font-src 'self' data: https://fonts.gstatic.com",
            "connect-src 'self' https://www.google-analytics.com",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'"
        ];
        
        // Apply our filter
        $filtered_policy = apply_filters( 'kei_portfolio_csp_policy', $default_policy );
        $this->assertIsArray( $filtered_policy, 'CSP policy should be an array' );
        $this->assertNotEmpty( $filtered_policy, 'CSP policy should not be empty' );
        
        // Check if essential policies are present
        $policy_string = implode( '; ', $filtered_policy );
        $this->assertStringContainsString( "default-src 'self'", $policy_string, 'Default-src policy should be present' );
        $this->assertStringContainsString( "object-src 'none'", $policy_string, 'Object-src policy should prevent dangerous objects' );
        $this->assertStringContainsString( "base-uri 'self'", $policy_string, 'Base-uri policy should be restricted' );
    }
    
    /**
     * Test memory manager bug fix
     * 
     * @test
     */
    public function test_memory_manager_calculation() {
        // Test if MemoryManager class exists and works correctly
        if ( class_exists( '\KeiPortfolio\Performance\MemoryManager' ) ) {
            $memory_manager = \KeiPortfolio\Performance\MemoryManager::get_instance();
            $this->assertInstanceOf( '\KeiPortfolio\Performance\MemoryManager', $memory_manager, 'MemoryManager should be instantiable' );
            
            // Test memory limit parsing (if method is public or we can access it through reflection)
            if ( method_exists( $memory_manager, 'parse_memory_limit' ) ) {
                $reflection = new ReflectionClass( $memory_manager );
                $method = $reflection->getMethod( 'parse_memory_limit' );
                $method->setAccessible( true );
                
                // Test different memory limit formats
                $this->assertEquals( 1073741824, $method->invoke( $memory_manager, '1G' ), '1G should equal 1073741824 bytes' );
                $this->assertEquals( 134217728, $method->invoke( $memory_manager, '128M' ), '128M should equal 134217728 bytes' );
                $this->assertEquals( 1024, $method->invoke( $memory_manager, '1K' ), '1K should equal 1024 bytes' );
                
                // Test that the calculation doesn't cascade (bug fix verification)
                $this->assertEquals( 2147483648, $method->invoke( $memory_manager, '2G' ), '2G should equal 2147483648 bytes' );
                $this->assertNotEquals( 2097152, $method->invoke( $memory_manager, '2G' ), '2G should not be calculated as 2*1024*1024*1024*1024' );
            }
        } else {
            $this->markTestSkipped( 'MemoryManager class not available' );
        }
    }
    
    /**
     * Test data encryption and privacy
     * 
     * @test
     */
    public function test_data_privacy() {
        // Test that sensitive data is not exposed
        $sensitive_meta = array(
            'client_email' => 'client@example.com',
            'project_budget' => '$50000',
            'internal_notes' => 'Confidential project notes',
        );
        
        foreach ( $sensitive_meta as $key => $value ) {
            update_post_meta( $this->test_project_id, $key, $value );
        }
        
        // Test that meta data is not exposed in REST API without proper authentication
        // This would require additional REST API security measures in production
        $post_data = get_post( $this->test_project_id, ARRAY_A );
        
        // Verify post data doesn't contain meta by default
        $this->assertArrayNotHasKey( 'client_email', $post_data, 'Sensitive meta should not be in post data by default' );
        $this->assertArrayNotHasKey( 'project_budget', $post_data, 'Sensitive meta should not be in post data by default' );
        
        // Test meta data access control
        wp_set_current_user( $this->test_user_id ); // Subscriber
        
        $meta_value = get_post_meta( $this->test_project_id, 'internal_notes', true );
        // In a real implementation, you might want to restrict access to certain meta fields
        // For this test, we just verify the meta system works correctly
        $this->assertIsString( $meta_value, 'Meta access should return string value' );
    }
}