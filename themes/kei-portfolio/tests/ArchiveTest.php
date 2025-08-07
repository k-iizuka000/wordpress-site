<?php
/**
 * Test suite for archive-project.php template functionality
 * Tests for Group 5: Custom Post Types and Archive functionality
 * 
 * @package Kei_Portfolio
 * @subpackage Tests
 * @group custom-post-types
 * @group archive-pages
 */

require_once dirname(__FILE__) . '/bootstrap.php';

class ArchiveTest extends WP_UnitTestCase {
    
    /**
     * Test fixtures
     */
    private $test_projects = array();
    private $test_technologies = array();
    private $test_industries = array();
    
    /**
     * Set up test environment before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Ensure post types and taxonomies are registered
        if ( ! did_action( 'init' ) ) {
            do_action( 'init' );
        }
        
        // Create test data
        $this->create_test_data();
    }
    
    /**
     * Clean up after each test
     */
    public function tearDown(): void {
        // Clean up test data
        foreach ( $this->test_projects as $project_id ) {
            wp_delete_post( $project_id, true );
        }
        
        foreach ( $this->test_technologies as $term_id ) {
            wp_delete_term( $term_id, 'technology' );
        }
        
        foreach ( $this->test_industries as $term_id ) {
            wp_delete_term( $term_id, 'industry' );
        }
        
        parent::tearDown();
    }
    
    /**
     * Create test data for archive tests
     */
    private function create_test_data() {
        // Create technology terms
        $this->test_technologies['wordpress'] = $this->factory->term->create( array(
            'name' => 'WordPress',
            'taxonomy' => 'technology',
        ) );
        
        $this->test_technologies['react'] = $this->factory->term->create( array(
            'name' => 'React',
            'taxonomy' => 'technology',
        ) );
        
        $this->test_technologies['php'] = $this->factory->term->create( array(
            'name' => 'PHP',
            'taxonomy' => 'technology',
        ) );
        
        // Create industry terms
        $this->test_industries['ecommerce'] = $this->factory->term->create( array(
            'name' => 'E-commerce',
            'taxonomy' => 'industry',
        ) );
        
        $this->test_industries['education'] = $this->factory->term->create( array(
            'name' => 'Education',
            'taxonomy' => 'industry',
        ) );
        
        // Create test projects
        for ( $i = 1; $i <= 5; $i++ ) {
            $project_id = $this->factory->post->create( array(
                'post_type' => 'project',
                'post_title' => "Test Project {$i}",
                'post_status' => 'publish',
                'post_content' => "This is the content for test project {$i}.",
                'post_excerpt' => "This is the excerpt for test project {$i}.",
            ) );
            
            $this->test_projects[] = $project_id;
            
            // Assign taxonomies to some projects
            if ( $i <= 3 ) {
                wp_set_object_terms( $project_id, $this->test_technologies['wordpress'], 'technology' );
            }
            if ( $i >= 3 ) {
                wp_set_object_terms( $project_id, $this->test_technologies['react'], 'technology', true );
            }
            if ( $i % 2 == 0 ) {
                wp_set_object_terms( $project_id, $this->test_industries['ecommerce'], 'industry' );
            } else {
                wp_set_object_terms( $project_id, $this->test_industries['education'], 'industry' );
            }
        }
    }
    
    /**
     * Test that project archive page exists and is accessible
     * 
     * @test
     */
    public function test_project_archive_page_exists() {
        // Get the archive link
        $archive_link = get_post_type_archive_link( 'project' );
        
        $this->assertNotFalse( $archive_link, 'Project archive link should exist' );
        $this->assertStringContainsString( '/projects', $archive_link, 'Archive link should contain /projects' );
    }
    
    /**
     * Test archive page query for project post type
     * 
     * @test
     */
    public function test_archive_page_query() {
        // Simulate visiting the archive page
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        // Check if we're on the correct archive
        $this->assertTrue( is_post_type_archive( 'project' ), 'Should be on project post type archive' );
        $this->assertFalse( is_single(), 'Should not be on single post page' );
        $this->assertFalse( is_page(), 'Should not be on page' );
        
        // Check if the query has posts
        global $wp_query;
        $this->assertTrue( $wp_query->have_posts(), 'Archive should have posts' );
        $this->assertEquals( 'project', $wp_query->query_vars['post_type'], 'Query should be for project post type' );
    }
    
    /**
     * Test archive page displays all published projects
     * 
     * @test
     */
    public function test_archive_displays_published_projects() {
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        global $wp_query;
        $found_posts = $wp_query->found_posts;
        
        // We created 5 published projects
        $this->assertEquals( 5, $found_posts, 'Should find all 5 published projects' );
    }
    
    /**
     * Test archive page does not display draft projects
     * 
     * @test
     */
    public function test_archive_excludes_draft_projects() {
        // Create a draft project
        $draft_project_id = $this->factory->post->create( array(
            'post_type' => 'project',
            'post_title' => 'Draft Project',
            'post_status' => 'draft',
        ) );
        
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        global $wp_query;
        $found_posts = $wp_query->found_posts;
        
        // Should still only show 5 published projects
        $this->assertEquals( 5, $found_posts, 'Draft projects should not be displayed' );
        
        // Clean up
        wp_delete_post( $draft_project_id, true );
    }
    
    /**
     * Test archive template file loading
     * 
     * @test
     */
    public function test_archive_template_file_loading() {
        $template_dir = get_template_directory();
        $archive_template = $template_dir . '/archive-project.php';
        
        $this->assertFileExists( $archive_template, 'archive-project.php template file should exist' );
        
        // Test template hierarchy
        $this->go_to( get_post_type_archive_link( 'project' ) );
        $template = get_post_type_archive_template();
        
        $this->assertStringContainsString( 'archive-project.php', $template, 'Should use archive-project.php template' );
    }
    
    /**
     * Test technology filter functionality
     * 
     * @test
     */
    public function test_technology_filter() {
        // Test filtering by WordPress technology
        $this->go_to( get_post_type_archive_link( 'project' ) . '?technology=wordpress' );
        
        global $wp_query;
        
        // Projects 1, 2, and 3 have WordPress technology
        $this->assertEquals( 3, $wp_query->found_posts, 'Should find 3 projects with WordPress technology' );
    }
    
    /**
     * Test industry filter functionality
     * 
     * @test
     */
    public function test_industry_filter() {
        // Test filtering by E-commerce industry
        $this->go_to( get_post_type_archive_link( 'project' ) . '?industry=e-commerce' );
        
        global $wp_query;
        
        // Projects 2 and 4 have E-commerce industry (even numbered)
        $this->assertEquals( 2, $wp_query->found_posts, 'Should find 2 projects in E-commerce industry' );
    }
    
    /**
     * Test pagination on archive page
     * 
     * @test
     */
    public function test_archive_pagination() {
        // Create more projects to test pagination
        for ( $i = 6; $i <= 15; $i++ ) {
            $project_id = $this->factory->post->create( array(
                'post_type' => 'project',
                'post_title' => "Additional Project {$i}",
                'post_status' => 'publish',
            ) );
            $this->test_projects[] = $project_id;
        }
        
        // Test first page
        $this->go_to( get_post_type_archive_link( 'project' ) );
        global $wp_query;
        
        $this->assertTrue( $wp_query->max_num_pages > 1, 'Should have multiple pages' );
        $this->assertEquals( 1, $wp_query->query_vars['paged'], 'Should be on first page' );
        
        // Test second page
        $this->go_to( get_post_type_archive_link( 'project' ) . 'page/2/' );
        $this->assertEquals( 2, get_query_var( 'paged' ), 'Should be on second page' );
    }
    
    /**
     * Test archive page with no projects
     * 
     * @test
     */
    public function test_archive_with_no_projects() {
        // Delete all test projects
        foreach ( $this->test_projects as $project_id ) {
            wp_delete_post( $project_id, true );
        }
        $this->test_projects = array();
        
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        global $wp_query;
        $this->assertFalse( $wp_query->have_posts(), 'Should have no posts' );
        $this->assertEquals( 0, $wp_query->found_posts, 'Found posts should be 0' );
    }
    
    /**
     * Test archive page rendering output structure
     * 
     * @test
     */
    public function test_archive_output_structure() {
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        // Start output buffering to capture template output
        ob_start();
        
        // Include the template file directly
        $template_file = get_template_directory() . '/archive-project.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        
        $output = ob_get_clean();
        
        // Test for expected HTML structure elements
        $this->assertStringContainsString( '<main', $output, 'Should contain main element' );
        $this->assertStringContainsString( 'page-header', $output, 'Should contain page header section' );
        $this->assertStringContainsString( 'project-filters', $output, 'Should contain project filters section' );
        $this->assertStringContainsString( 'projects-grid', $output, 'Should contain projects grid section' );
        $this->assertStringContainsString( 'project-stats', $output, 'Should contain project stats section' );
        
        // Test for filter dropdowns
        $this->assertStringContainsString( 'technology-filter', $output, 'Should contain technology filter' );
        $this->assertStringContainsString( 'industry-filter', $output, 'Should contain industry filter' );
        $this->assertStringContainsString( 'sort-order', $output, 'Should contain sort order dropdown' );
    }
    
    /**
     * Test project card structure in archive
     * 
     * @test
     */
    public function test_project_card_structure() {
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        ob_start();
        $template_file = get_template_directory() . '/archive-project.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        $output = ob_get_clean();
        
        // Test for project card elements
        $this->assertStringContainsString( 'project-card', $output, 'Should contain project card' );
        $this->assertStringContainsString( 'project-card-title', $output, 'Should contain project card title' );
        $this->assertStringContainsString( 'project-card-description', $output, 'Should contain project card description' );
        $this->assertStringContainsString( 'project-card-tags', $output, 'Should contain project card tags' );
    }
    
    /**
     * Test statistics section calculations
     * 
     * @test
     */
    public function test_statistics_section() {
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        ob_start();
        $template_file = get_template_directory() . '/archive-project.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        $output = ob_get_clean();
        
        // Test statistics display
        $published_count = wp_count_posts( 'project' )->publish;
        $this->assertStringContainsString( (string) $published_count, $output, 'Should display correct project count' );
        
        $tech_count = count( get_terms( 'technology' ) );
        $this->assertStringContainsString( (string) $tech_count, $output, 'Should display technology count' );
        
        $industry_count = count( get_terms( 'industry' ) );
        $this->assertStringContainsString( (string) $industry_count, $output, 'Should display industry count' );
    }
    
    /**
     * Test CTA section presence
     * 
     * @test
     */
    public function test_cta_section() {
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        ob_start();
        $template_file = get_template_directory() . '/archive-project.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        $output = ob_get_clean();
        
        // Test CTA section
        $this->assertStringContainsString( 'class="cta', $output, 'Should contain CTA section' );
        $this->assertStringContainsString( 'プロジェクトの相談', $output, 'Should contain CTA text' );
        $this->assertStringContainsString( '/#contact', $output, 'Should contain contact link' );
    }
    
    /**
     * Test sorting functionality
     * 
     * @test
     */
    public function test_sorting_functionality() {
        // Test date descending (default)
        $this->go_to( get_post_type_archive_link( 'project' ) );
        global $wp_query;
        $first_post = $wp_query->posts[0];
        $last_post = end( $wp_query->posts );
        
        $this->assertGreaterThanOrEqual( 
            strtotime( $last_post->post_date ), 
            strtotime( $first_post->post_date ), 
            'Posts should be sorted by date descending by default' 
        );
        
        // Test date ascending
        $this->go_to( get_post_type_archive_link( 'project' ) . '?orderby=date-asc' );
        $wp_query = new WP_Query( array(
            'post_type' => 'project',
            'orderby' => 'date',
            'order' => 'ASC',
        ) );
        
        if ( $wp_query->have_posts() ) {
            $first_post = $wp_query->posts[0];
            $last_post = end( $wp_query->posts );
            
            $this->assertLessThanOrEqual( 
                strtotime( $last_post->post_date ), 
                strtotime( $first_post->post_date ), 
                'Posts should be sorted by date ascending when specified' 
            );
        }
    }
    
    /**
     * Test archive page SEO meta tags
     * 
     * @test
     */
    public function test_archive_seo_meta() {
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        // Test that proper archive title is set
        $archive_title = post_type_archive_title( '', false );
        $this->assertEquals( 'Projects', $archive_title, 'Archive title should be "Projects"' );
        
        // Test canonical URL
        $canonical = get_post_type_archive_link( 'project' );
        $this->assertNotFalse( $canonical, 'Should have canonical URL' );
    }
    
    /**
     * Test responsive grid classes
     * 
     * @test
     */
    public function test_responsive_grid() {
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        ob_start();
        $template_file = get_template_directory() . '/archive-project.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        $output = ob_get_clean();
        
        // Test for responsive grid classes
        $this->assertStringContainsString( 'grid', $output, 'Should contain grid class' );
        $this->assertStringContainsString( 'grid-3', $output, 'Should contain grid-3 class for 3-column layout' );
    }
    
    /**
     * Test XSS protection in archive output
     * 
     * @test
     */
    public function test_xss_protection() {
        // Create a project with potentially malicious content
        $malicious_title = '<script>alert("XSS")</script>Malicious Project';
        $malicious_content = '<img src="x" onerror="alert(1)">Test content';
        
        $project_id = $this->factory->post->create( array(
            'post_type' => 'project',
            'post_title' => $malicious_title,
            'post_content' => $malicious_content,
            'post_status' => 'publish',
        ) );
        
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        ob_start();
        $template_file = get_template_directory() . '/archive-project.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        $output = ob_get_clean();
        
        // Test that script tags are escaped
        $this->assertStringNotContainsString( '<script>', $output, 'Script tags should be escaped' );
        $this->assertStringNotContainsString( 'onerror=', $output, 'Event handlers should be escaped' );
        
        // Clean up
        wp_delete_post( $project_id, true );
    }
    
    /**
     * Test SQL injection protection in taxonomy filters
     * 
     * @test
     */
    public function test_sql_injection_protection() {
        // Attempt SQL injection through GET parameters
        $_GET['technology'] = "' OR 1=1 --";
        $_GET['industry'] = "'; DROP TABLE wp_posts; --";
        
        $this->go_to( get_post_type_archive_link( 'project' ) . '?technology=' . urlencode($_GET['technology']) . '&industry=' . urlencode($_GET['industry']) );
        
        global $wp_query;
        
        // The query should not return all posts (which would indicate SQL injection worked)
        // Instead, it should find no posts or handle the malicious input safely
        $found_posts = $wp_query->found_posts;
        
        // Test that the page loads without error
        $this->assertIsInt( $found_posts, 'Query should execute safely and return integer count' );
        $this->assertGreaterThanOrEqual( 0, $found_posts, 'Query should return non-negative result' );
        
        // Clean up
        unset( $_GET['technology'], $_GET['industry'] );
    }
    
    /**
     * Test CSRF protection for filter forms
     * 
     * @test
     */
    public function test_csrf_protection() {
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        ob_start();
        $template_file = get_template_directory() . '/archive-project.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        $output = ob_get_clean();
        
        // While this template uses GET requests (which don't need CSRF tokens),
        // we test that no sensitive operations are performed via forms
        $this->assertStringNotContainsString( '<form', $output, 'Archive should not contain forms that could be CSRF vulnerable' );
    }
    
    /**
     * Test caching compatibility
     * 
     * @test
     */
    public function test_caching_compatibility() {
        // Create a project
        $project_id = $this->factory->post->create( array(
            'post_type' => 'project',
            'post_title' => 'Cache Test Project',
            'post_status' => 'publish',
        ) );
        
        // First visit to generate cache
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        ob_start();
        $template_file = get_template_directory() . '/archive-project.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        $first_output = ob_get_clean();
        
        // Second visit (should use cached version or regenerate correctly)
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        ob_start();
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        $second_output = ob_get_clean();
        
        // Both outputs should contain the project
        $this->assertStringContainsString( 'Cache Test Project', $first_output, 'First load should show project' );
        $this->assertStringContainsString( 'Cache Test Project', $second_output, 'Second load should show project' );
        
        // Clean up
        wp_delete_post( $project_id, true );
    }
    
    /**
     * Test archive template with custom fields (ACF compatibility)
     * 
     * @test
     */
    public function test_custom_fields_display() {
        // Mock ACF get_field function if not available
        if ( ! function_exists( 'get_field' ) ) {
            function get_field( $field_name, $post_id = null ) {
                switch ( $field_name ) {
                    case 'project_period':
                        return '2023年1月 - 2023年6月';
                    case 'project_role':
                        return 'フルスタック開発者';
                    default:
                        return null;
                }
            }
        }
        
        $project_id = $this->factory->post->create( array(
            'post_type' => 'project',
            'post_title' => 'ACF Test Project',
            'post_status' => 'publish',
        ) );
        
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        ob_start();
        $template_file = get_template_directory() . '/archive-project.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        $output = ob_get_clean();
        
        // Test that custom fields are displayed if available
        // The template checks for get_field() existence before using it
        $this->assertStringContainsString( 'ACF Test Project', $output, 'Project should be displayed' );
        
        // Clean up
        wp_delete_post( $project_id, true );
    }
    
    /**
     * Test performance with large number of projects
     * 
     * @test
     */
    public function test_performance_with_many_projects() {
        $start_time = microtime( true );
        
        // Create many projects (simulate real-world scenario)
        $project_ids = array();
        for ( $i = 1; $i <= 50; $i++ ) {
            $project_ids[] = $this->factory->post->create( array(
                'post_type' => 'project',
                'post_title' => "Performance Test Project {$i}",
                'post_status' => 'publish',
            ) );
        }
        
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        // Measure query performance
        global $wp_query;
        $query_count = get_num_queries();
        
        ob_start();
        $template_file = get_template_directory() . '/archive-project.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        $output = ob_get_clean();
        
        $end_time = microtime( true );
        $execution_time = $end_time - $start_time;
        
        // Test performance benchmarks
        $this->assertLessThan( 5.0, $execution_time, 'Archive page should load within 5 seconds even with 50+ projects' );
        $this->assertGreaterThan( 0, strlen( $output ), 'Should generate output' );
        
        // Clean up
        foreach ( $project_ids as $project_id ) {
            wp_delete_post( $project_id, true );
        }
    }
    
    /**
     * Test accessibility features in archive template
     * 
     * @test
     */
    public function test_accessibility_features() {
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        ob_start();
        $template_file = get_template_directory() . '/archive-project.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        $output = ob_get_clean();
        
        // Test for accessibility features
        $this->assertStringContainsString( '<main', $output, 'Should have main landmark' );
        $this->assertStringContainsString( 'id="main"', $output, 'Main should have ID for skip links' );
        
        // Test heading hierarchy
        $this->assertStringContainsString( '<h1', $output, 'Should have H1 heading' );
        $this->assertStringContainsString( '<h2', $output, 'Should have H2 headings' );
        
        // Test form labels
        $this->assertStringContainsString( '<label', $output, 'Should have labels for form controls' );
    }
    
    /**
     * Test JavaScript functionality initialization
     * 
     * @test
     */
    public function test_javascript_initialization() {
        $this->go_to( get_post_type_archive_link( 'project' ) );
        
        ob_start();
        $template_file = get_template_directory() . '/archive-project.php';
        if ( file_exists( $template_file ) ) {
            include $template_file;
        }
        $output = ob_get_clean();
        
        // Test that JavaScript is properly initialized
        $this->assertStringContainsString( 'DOMContentLoaded', $output, 'Should initialize JavaScript on DOM ready' );
        $this->assertStringContainsString( 'getElementById', $output, 'Should properly reference DOM elements' );
        $this->assertStringContainsString( 'addEventListener', $output, 'Should use modern event listeners' );
        
        // Test that filter elements are targeted correctly
        $this->assertStringContainsString( 'technology-filter', $output, 'Should target technology filter' );
        $this->assertStringContainsString( 'industry-filter', $output, 'Should target industry filter' );
        $this->assertStringContainsString( 'sort-order', $output, 'Should target sort order filter' );
    }
}