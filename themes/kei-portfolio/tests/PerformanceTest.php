<?php
/**
 * Performance test suite for custom post types and templates
 * Tests for Group 5: Performance optimization and scalability
 * 
 * @package Kei_Portfolio
 * @subpackage Tests
 * @group performance
 * @group custom-post-types
 */

require_once dirname(__FILE__) . '/bootstrap.php';

class PerformanceTest extends WP_UnitTestCase {
    
    /**
     * Test fixtures
     */
    private $test_projects = array();
    private $test_terms = array();
    
    /**
     * Set up test environment before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Ensure post types are registered
        if ( ! did_action( 'init' ) ) {
            do_action( 'init' );
        }
        
        // Create test data for performance tests
        $this->create_test_data();
    }
    
    /**
     * Clean up after each test
     */
    public function tearDown(): void {
        // Clean up test projects
        foreach ( $this->test_projects as $project_id ) {
            wp_delete_post( $project_id, true );
        }
        
        // Clean up test terms
        foreach ( $this->test_terms as $term_id ) {
            wp_delete_term( $term_id, 'technology' );
            wp_delete_term( $term_id, 'industry' );
        }
        
        parent::tearDown();
    }
    
    /**
     * Create test data for performance testing
     */
    private function create_test_data() {
        // Create technologies
        for ( $i = 1; $i <= 10; $i++ ) {
            $this->test_terms[] = $this->factory->term->create( array(
                'name' => "Technology {$i}",
                'taxonomy' => 'technology',
            ) );
        }
        
        // Create industries
        for ( $i = 1; $i <= 5; $i++ ) {
            $this->test_terms[] = $this->factory->term->create( array(
                'name' => "Industry {$i}",
                'taxonomy' => 'industry',
            ) );
        }
        
        // Create projects with meta data and taxonomy assignments
        for ( $i = 1; $i <= 100; $i++ ) {
            $project_id = $this->factory->post->create( array(
                'post_type' => 'project',
                'post_title' => "Performance Test Project {$i}",
                'post_content' => "This is the content for performance test project {$i}. It contains enough text to simulate real-world content length and complexity.",
                'post_excerpt' => "Excerpt for project {$i}",
                'post_status' => 'publish',
            ) );
            
            // Add meta data
            update_post_meta( $project_id, 'project_url', "https://example.com/project-{$i}" );
            update_post_meta( $project_id, 'project_status', 'completed' );
            update_post_meta( $project_id, 'team_size', rand( 1, 10 ) );
            update_post_meta( $project_id, 'project_budget', '$' . rand( 1000, 100000 ) );
            
            // Assign random technologies
            $tech_count = rand( 1, 3 );
            $random_techs = array_rand( array_slice( $this->test_terms, 0, 10 ), $tech_count );
            if ( is_array( $random_techs ) ) {
                wp_set_object_terms( $project_id, $random_techs, 'technology' );
            } else {
                wp_set_object_terms( $project_id, array( $random_techs ), 'technology' );
            }
            
            // Assign random industry
            $random_industry = array_rand( array_slice( $this->test_terms, 10, 5 ), 1 );
            wp_set_object_terms( $project_id, array( $random_industry ), 'industry' );
            
            $this->test_projects[] = $project_id;
        }
    }
    
    /**
     * Test basic query performance for project archives
     * 
     * @test
     */
    public function test_archive_query_performance() {
        $start_time = microtime( true );
        
        // Simulate archive page query
        $query = new WP_Query( array(
            'post_type' => 'project',
            'post_status' => 'publish',
            'posts_per_page' => 12, // Typical archive page size
            'orderby' => 'date',
            'order' => 'DESC',
        ) );
        
        $end_time = microtime( true );
        $execution_time = $end_time - $start_time;
        
        $this->assertLessThan( 1.0, $execution_time, 'Basic archive query should execute in less than 1 second' );
        $this->assertTrue( $query->have_posts(), 'Query should return posts' );
        $this->assertLessThanOrEqual( 12, $query->post_count, 'Query should respect posts_per_page limit' );
        
        // Test memory usage
        $memory_usage = memory_get_peak_usage( true );
        $this->assertLessThan( 128 * 1024 * 1024, $memory_usage, 'Memory usage should be under 128MB' ); // 128MB limit
    }
    
    /**
     * Test meta query performance
     * 
     * @test
     */
    public function test_meta_query_performance() {
        $start_time = microtime( true );
        
        // Complex meta query
        $query = new WP_Query( array(
            'post_type' => 'project',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'project_status',
                    'value' => 'completed',
                    'compare' => '=',
                ),
                array(
                    'key' => 'team_size',
                    'value' => 5,
                    'type' => 'NUMERIC',
                    'compare' => '>=',
                ),
            ),
        ) );
        
        $end_time = microtime( true );
        $execution_time = $end_time - $start_time;
        
        $this->assertLessThan( 2.0, $execution_time, 'Meta query should execute in less than 2 seconds' );
        $this->assertGreaterThan( 0, $query->found_posts, 'Meta query should find some posts' );
    }
    
    /**
     * Test taxonomy query performance
     * 
     * @test
     */
    public function test_taxonomy_query_performance() {
        $start_time = microtime( true );
        
        // Get a technology term for testing
        $tech_term = get_term( $this->test_terms[0], 'technology' );
        
        $query = new WP_Query( array(
            'post_type' => 'project',
            'tax_query' => array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'technology',
                    'field' => 'term_id',
                    'terms' => $tech_term->term_id,
                ),
                array(
                    'taxonomy' => 'industry',
                    'field' => 'term_id',
                    'terms' => array_slice( $this->test_terms, 10, 2 ),
                    'operator' => 'IN',
                ),
            ),
        ) );
        
        $end_time = microtime( true );
        $execution_time = $end_time - $start_time;
        
        $this->assertLessThan( 1.5, $execution_time, 'Taxonomy query should execute in less than 1.5 seconds' );
    }
    
    /**
     * Test combined meta and taxonomy query performance
     * 
     * @test
     */
    public function test_combined_query_performance() {
        $start_time = microtime( true );
        
        $query = new WP_Query( array(
            'post_type' => 'project',
            'posts_per_page' => 10,
            'meta_query' => array(
                array(
                    'key' => 'project_status',
                    'value' => 'completed',
                    'compare' => '=',
                ),
            ),
            'tax_query' => array(
                array(
                    'taxonomy' => 'technology',
                    'field' => 'term_id',
                    'terms' => array_slice( $this->test_terms, 0, 3 ),
                    'operator' => 'IN',
                ),
            ),
            'orderby' => array(
                'date' => 'DESC',
                'menu_order' => 'ASC',
            ),
        ) );
        
        $end_time = microtime( true );
        $execution_time = $end_time - $start_time;
        
        $this->assertLessThan( 3.0, $execution_time, 'Combined query should execute in less than 3 seconds' );
        $this->assertLessThanOrEqual( 10, $query->post_count, 'Query should respect posts_per_page limit' );
    }
    
    /**
     * Test pagination performance
     * 
     * @test
     */
    public function test_pagination_performance() {
        $page_times = array();
        
        // Test first 5 pages
        for ( $page = 1; $page <= 5; $page++ ) {
            $start_time = microtime( true );
            
            $query = new WP_Query( array(
                'post_type' => 'project',
                'posts_per_page' => 10,
                'paged' => $page,
            ) );
            
            $end_time = microtime( true );
            $page_times[] = $end_time - $start_time;
            
            $this->assertTrue( $query->have_posts(), "Page {$page} should have posts" );
        }
        
        // Test that pagination performance doesn't degrade significantly
        $first_page_time = $page_times[0];
        $last_page_time = end( $page_times );
        
        $this->assertLessThan( $first_page_time * 2, $last_page_time, 'Later pages should not be significantly slower than first page' );
        $this->assertLessThan( 2.0, max( $page_times ), 'No page should take more than 2 seconds to load' );
    }
    
    /**
     * Test archive template rendering performance
     * 
     * @test
     */
    public function test_template_rendering_performance() {
        // Simulate visiting the archive page
        global $wp_query;
        $wp_query = new WP_Query( array(
            'post_type' => 'project',
            'posts_per_page' => 12,
        ) );
        
        $template_file = get_template_directory() . '/archive-project.php';
        $this->assertFileExists( $template_file, 'Archive template should exist' );
        
        $start_time = microtime( true );
        
        ob_start();
        include $template_file;
        $output = ob_get_clean();
        
        $end_time = microtime( true );
        $execution_time = $end_time - $start_time;
        
        $this->assertLessThan( 2.0, $execution_time, 'Template rendering should take less than 2 seconds' );
        $this->assertGreaterThan( 1000, strlen( $output ), 'Template should generate substantial output' );
        $this->assertStringContainsString( 'project-card', $output, 'Template should contain project cards' );
        
        // Reset global query
        wp_reset_query();
    }
    
    /**
     * Test database query optimization
     * 
     * @test
     */
    public function test_database_query_optimization() {
        global $wpdb;
        
        // Enable query logging
        $wpdb->show_errors();
        
        $initial_query_count = get_num_queries();
        
        // Perform typical archive page operations
        $query = new WP_Query( array(
            'post_type' => 'project',
            'posts_per_page' => 12,
            'meta_key' => 'project_status',
            'meta_value' => 'completed',
        ) );
        
        while ( $query->have_posts() ) {
            $query->the_post();
            
            // Simulate typical template operations
            get_the_title();
            get_the_excerpt();
            get_post_meta( get_the_ID(), 'project_url', true );
            get_the_terms( get_the_ID(), 'technology' );
            get_the_terms( get_the_ID(), 'industry' );
        }
        
        wp_reset_postdata();
        
        $final_query_count = get_num_queries();
        $queries_executed = $final_query_count - $initial_query_count;
        
        // Should not execute too many queries (N+1 problem prevention)
        $this->assertLessThan( 25, $queries_executed, 'Should execute reasonable number of queries (avoid N+1 problem)' );
    }
    
    /**
     * Test caching effectiveness
     * 
     * @test
     */
    public function test_caching_effectiveness() {
        // First query (cold cache)
        $start_time = microtime( true );
        
        $query1 = new WP_Query( array(
            'post_type' => 'project',
            'posts_per_page' => 10,
        ) );
        
        $cold_cache_time = microtime( true ) - $start_time;
        
        // Second identical query (warm cache)
        $start_time = microtime( true );
        
        $query2 = new WP_Query( array(
            'post_type' => 'project',
            'posts_per_page' => 10,
        ) );
        
        $warm_cache_time = microtime( true ) - $start_time;
        
        // Cached query should be faster (or at least not significantly slower)
        $this->assertLessThanOrEqual( $cold_cache_time * 1.5, $warm_cache_time, 'Cached query should not be significantly slower than cold cache' );
        
        // Results should be identical
        $this->assertEquals( $query1->found_posts, $query2->found_posts, 'Cached query should return same number of posts' );
    }
    
    /**
     * Test memory usage with large datasets
     * 
     * @test
     */
    public function test_memory_usage() {
        $initial_memory = memory_get_usage( true );
        
        // Load many projects at once
        $query = new WP_Query( array(
            'post_type' => 'project',
            'posts_per_page' => 50, // Large page size
            'meta_query' => array(
                array(
                    'key' => 'project_status',
                    'value' => 'completed',
                ),
            ),
        ) );
        
        $posts_loaded = array();
        while ( $query->have_posts() ) {
            $query->the_post();
            $posts_loaded[] = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'meta' => get_post_meta( get_the_ID() ),
                'terms' => get_the_terms( get_the_ID(), 'technology' ),
            );
        }
        
        wp_reset_postdata();
        
        $peak_memory = memory_get_peak_usage( true );
        $memory_increase = $peak_memory - $initial_memory;
        
        // Memory usage should be reasonable
        $this->assertLessThan( 64 * 1024 * 1024, $memory_increase, 'Memory increase should be less than 64MB for 50 posts' ); // 64MB limit
        $this->assertGreaterThan( 0, count( $posts_loaded ), 'Should load some posts' );
    }
    
    /**
     * Test search performance
     * 
     * @test
     */
    public function test_search_performance() {
        $search_terms = array(
            'Performance',
            'Test',
            'Project',
            'WordPress',
        );
        
        foreach ( $search_terms as $term ) {
            $start_time = microtime( true );
            
            $query = new WP_Query( array(
                'post_type' => 'project',
                's' => $term,
                'posts_per_page' => 10,
            ) );
            
            $end_time = microtime( true );
            $execution_time = $end_time - $start_time;
            
            $this->assertLessThan( 2.0, $execution_time, "Search for '{$term}' should execute in less than 2 seconds" );
        }
    }
    
    /**
     * Test bulk operations performance
     * 
     * @test
     */
    public function test_bulk_operations_performance() {
        $start_time = microtime( true );
        
        // Simulate bulk meta update
        $project_ids = array_slice( $this->test_projects, 0, 20 );
        
        foreach ( $project_ids as $project_id ) {
            update_post_meta( $project_id, 'bulk_update_test', 'updated_value' );
            update_post_meta( $project_id, 'last_modified', current_time( 'mysql' ) );
        }
        
        $bulk_update_time = microtime( true ) - $start_time;
        
        $this->assertLessThan( 3.0, $bulk_update_time, 'Bulk update of 20 posts should take less than 3 seconds' );
        
        // Verify updates
        foreach ( array_slice( $project_ids, 0, 5 ) as $project_id ) {
            $value = get_post_meta( $project_id, 'bulk_update_test', true );
            $this->assertEquals( 'updated_value', $value, 'Bulk update should be successful' );
        }
    }
    
    /**
     * Test scalability with different dataset sizes
     * 
     * @test
     */
    public function test_scalability() {
        $dataset_sizes = array( 10, 50, 100 );
        $performance_results = array();
        
        foreach ( $dataset_sizes as $size ) {
            $start_time = microtime( true );
            
            $query = new WP_Query( array(
                'post_type' => 'project',
                'posts_per_page' => $size,
            ) );
            
            $end_time = microtime( true );
            $performance_results[$size] = $end_time - $start_time;
            
            $this->assertEquals( min( $size, $query->found_posts ), $query->post_count, "Should return requested number of posts for size {$size}" );
        }
        
        // Performance should scale reasonably (not exponentially)
        $small_dataset_time = $performance_results[10];
        $large_dataset_time = $performance_results[100];
        
        // Large dataset should not be more than 10x slower than small dataset
        $this->assertLessThan( $small_dataset_time * 10, $large_dataset_time, 'Performance should scale reasonably with dataset size' );
    }
    
    /**
     * Test concurrent query performance simulation
     * 
     * @test
     */
    public function test_concurrent_queries() {
        $query_times = array();
        
        // Simulate multiple concurrent-like queries
        for ( $i = 0; $i < 5; $i++ ) {
            $start_time = microtime( true );
            
            $query = new WP_Query( array(
                'post_type' => 'project',
                'posts_per_page' => 12,
                'orderby' => 'rand', // Different ordering to avoid identical queries
                'meta_query' => array(
                    array(
                        'key' => 'team_size',
                        'value' => rand( 1, 5 ),
                        'compare' => '>=',
                        'type' => 'NUMERIC',
                    ),
                ),
            ) );
            
            $end_time = microtime( true );
            $query_times[] = $end_time - $start_time;
        }
        
        $average_time = array_sum( $query_times ) / count( $query_times );
        $max_time = max( $query_times );
        
        $this->assertLessThan( 2.0, $average_time, 'Average query time should be reasonable' );
        $this->assertLessThan( 5.0, $max_time, 'No individual query should take more than 5 seconds' );
    }
}