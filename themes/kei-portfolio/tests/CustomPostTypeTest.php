<?php
/**
 * Test suite for custom post type registration
 * Tests for Group 5: Custom Post Types and Archive functionality
 * 
 * @package Kei_Portfolio
 * @subpackage Tests
 * @group custom-post-types
 */

require_once dirname(__FILE__) . '/bootstrap.php';

class CustomPostTypeTest extends WP_UnitTestCase {
    
    /**
     * Set up test environment before each test
     */
    public function setUp(): void {
        parent::setUp();
        
        // Ensure post types are registered
        if ( ! did_action( 'init' ) ) {
            do_action( 'init' );
        }
    }
    
    /**
     * Test that project post type exists
     * 
     * @test
     */
    public function test_project_post_type_exists() {
        $this->assertTrue( 
            post_type_exists( 'project' ), 
            'Project post type should be registered' 
        );
    }
    
    /**
     * Test project post type registration parameters
     * 
     * @test
     */
    public function test_project_post_type_parameters() {
        $post_type_object = get_post_type_object( 'project' );
        
        $this->assertNotNull( $post_type_object, 'Project post type object should exist' );
        
        // Test public visibility
        $this->assertTrue( $post_type_object->public, 'Project post type should be public' );
        $this->assertTrue( $post_type_object->publicly_queryable, 'Project post type should be publicly queryable' );
        
        // Test UI visibility
        $this->assertTrue( $post_type_object->show_ui, 'Project post type should show UI' );
        $this->assertTrue( $post_type_object->show_in_menu, 'Project post type should show in menu' );
        
        // Test archive support
        $this->assertTrue( $post_type_object->has_archive, 'Project post type should have archive' );
        
        // Test REST API support
        $this->assertTrue( $post_type_object->show_in_rest, 'Project post type should support REST API' );
        
        // Test hierarchical setting
        $this->assertFalse( $post_type_object->hierarchical, 'Project post type should not be hierarchical' );
        
        // Test rewrite slug
        $this->assertEquals( 'projects', $post_type_object->rewrite['slug'], 'Project rewrite slug should be "projects"' );
    }
    
    /**
     * Test project post type supports
     * 
     * @test
     */
    public function test_project_post_type_supports() {
        $supports = get_all_post_type_supports( 'project' );
        
        // Test required support features
        $this->assertTrue( isset( $supports['title'] ) && $supports['title'], 'Project should support title' );
        $this->assertTrue( isset( $supports['editor'] ) && $supports['editor'], 'Project should support editor' );
        $this->assertTrue( isset( $supports['thumbnail'] ) && $supports['thumbnail'], 'Project should support thumbnail' );
        $this->assertTrue( isset( $supports['excerpt'] ) && $supports['excerpt'], 'Project should support excerpt' );
        $this->assertTrue( isset( $supports['custom-fields'] ) && $supports['custom-fields'], 'Project should support custom fields' );
    }
    
    /**
     * Test project post type labels
     * 
     * @test
     */
    public function test_project_post_type_labels() {
        $post_type_object = get_post_type_object( 'project' );
        $labels = $post_type_object->labels;
        
        $this->assertEquals( 'Projects', $labels->name, 'Post type general name should be "Projects"' );
        $this->assertEquals( 'Project', $labels->singular_name, 'Post type singular name should be "Project"' );
        $this->assertEquals( 'Projects', $labels->menu_name, 'Admin menu text should be "Projects"' );
        $this->assertEquals( 'Add New Project', $labels->add_new_item, 'Add new item label should be "Add New Project"' );
        $this->assertEquals( 'Edit Project', $labels->edit_item, 'Edit item label should be "Edit Project"' );
        $this->assertEquals( 'View Project', $labels->view_item, 'View item label should be "View Project"' );
        $this->assertEquals( 'All Projects', $labels->all_items, 'All items label should be "All Projects"' );
        $this->assertEquals( 'Search Projects', $labels->search_items, 'Search items label should be "Search Projects"' );
    }
    
    /**
     * Test project post type menu icon
     * 
     * @test
     */
    public function test_project_post_type_menu_icon() {
        $post_type_object = get_post_type_object( 'project' );
        
        $this->assertEquals( 
            'dashicons-portfolio', 
            $post_type_object->menu_icon, 
            'Project post type menu icon should be dashicons-portfolio' 
        );
    }
    
    /**
     * Test project post type menu position
     * 
     * @test
     */
    public function test_project_post_type_menu_position() {
        $post_type_object = get_post_type_object( 'project' );
        
        $this->assertEquals( 
            5, 
            $post_type_object->menu_position, 
            'Project post type menu position should be 5' 
        );
    }
    
    /**
     * Test technology taxonomy exists
     * 
     * @test
     */
    public function test_technology_taxonomy_exists() {
        $this->assertTrue( 
            taxonomy_exists( 'technology' ), 
            'Technology taxonomy should be registered' 
        );
    }
    
    /**
     * Test technology taxonomy is associated with project post type
     * 
     * @test
     */
    public function test_technology_taxonomy_association() {
        $taxonomies = get_object_taxonomies( 'project' );
        
        $this->assertContains( 
            'technology', 
            $taxonomies, 
            'Technology taxonomy should be associated with project post type' 
        );
    }
    
    /**
     * Test technology taxonomy parameters
     * 
     * @test
     */
    public function test_technology_taxonomy_parameters() {
        $taxonomy_object = get_taxonomy( 'technology' );
        
        $this->assertNotNull( $taxonomy_object, 'Technology taxonomy object should exist' );
        
        // Test hierarchical setting
        $this->assertTrue( $taxonomy_object->hierarchical, 'Technology taxonomy should be hierarchical' );
        
        // Test UI visibility
        $this->assertTrue( $taxonomy_object->show_ui, 'Technology taxonomy should show UI' );
        $this->assertTrue( $taxonomy_object->show_admin_column, 'Technology taxonomy should show admin column' );
        
        // Test REST API support
        $this->assertTrue( $taxonomy_object->show_in_rest, 'Technology taxonomy should support REST API' );
        
        // Test rewrite slug
        $this->assertEquals( 'technology', $taxonomy_object->rewrite['slug'], 'Technology rewrite slug should be "technology"' );
    }
    
    /**
     * Test industry taxonomy exists
     * 
     * @test
     */
    public function test_industry_taxonomy_exists() {
        $this->assertTrue( 
            taxonomy_exists( 'industry' ), 
            'Industry taxonomy should be registered' 
        );
    }
    
    /**
     * Test industry taxonomy is associated with project post type
     * 
     * @test
     */
    public function test_industry_taxonomy_association() {
        $taxonomies = get_object_taxonomies( 'project' );
        
        $this->assertContains( 
            'industry', 
            $taxonomies, 
            'Industry taxonomy should be associated with project post type' 
        );
    }
    
    /**
     * Test industry taxonomy parameters
     * 
     * @test
     */
    public function test_industry_taxonomy_parameters() {
        $taxonomy_object = get_taxonomy( 'industry' );
        
        $this->assertNotNull( $taxonomy_object, 'Industry taxonomy object should exist' );
        
        // Test hierarchical setting (should be false for tags-like taxonomy)
        $this->assertFalse( $taxonomy_object->hierarchical, 'Industry taxonomy should not be hierarchical' );
        
        // Test UI visibility
        $this->assertTrue( $taxonomy_object->show_ui, 'Industry taxonomy should show UI' );
        $this->assertTrue( $taxonomy_object->show_admin_column, 'Industry taxonomy should show admin column' );
        
        // Test REST API support
        $this->assertTrue( $taxonomy_object->show_in_rest, 'Industry taxonomy should support REST API' );
        
        // Test rewrite slug
        $this->assertEquals( 'industry', $taxonomy_object->rewrite['slug'], 'Industry rewrite slug should be "industry"' );
    }
    
    /**
     * Test creating a project post
     * 
     * @test
     */
    public function test_create_project_post() {
        $project_id = $this->factory->post->create( array(
            'post_type'   => 'project',
            'post_title'  => 'Test Project',
            'post_status' => 'publish',
            'post_content' => 'This is a test project description.',
        ) );
        
        $this->assertNotEquals( 0, $project_id, 'Project post should be created successfully' );
        
        $project = get_post( $project_id );
        $this->assertEquals( 'project', $project->post_type, 'Created post should be of type "project"' );
        $this->assertEquals( 'Test Project', $project->post_title, 'Project title should match' );
        $this->assertEquals( 'publish', $project->post_status, 'Project status should be "publish"' );
    }
    
    /**
     * Test assigning technology taxonomy to project
     * 
     * @test
     */
    public function test_assign_technology_to_project() {
        // Create a project
        $project_id = $this->factory->post->create( array(
            'post_type' => 'project',
            'post_title' => 'Tech Test Project',
        ) );
        
        // Create technology terms
        $tech_term_id = $this->factory->term->create( array(
            'name' => 'WordPress',
            'taxonomy' => 'technology',
        ) );
        
        // Assign technology to project
        wp_set_object_terms( $project_id, array( $tech_term_id ), 'technology' );
        
        // Verify assignment
        $technologies = wp_get_object_terms( $project_id, 'technology' );
        $this->assertCount( 1, $technologies, 'Project should have one technology assigned' );
        $this->assertEquals( 'WordPress', $technologies[0]->name, 'Technology name should be "WordPress"' );
    }
    
    /**
     * Test assigning industry taxonomy to project
     * 
     * @test
     */
    public function test_assign_industry_to_project() {
        // Create a project
        $project_id = $this->factory->post->create( array(
            'post_type' => 'project',
            'post_title' => 'Industry Test Project',
        ) );
        
        // Create industry term
        $industry_term_id = $this->factory->term->create( array(
            'name' => 'E-commerce',
            'taxonomy' => 'industry',
        ) );
        
        // Assign industry to project
        wp_set_object_terms( $project_id, array( $industry_term_id ), 'industry' );
        
        // Verify assignment
        $industries = wp_get_object_terms( $project_id, 'industry' );
        $this->assertCount( 1, $industries, 'Project should have one industry assigned' );
        $this->assertEquals( 'E-commerce', $industries[0]->name, 'Industry name should be "E-commerce"' );
    }
    
    /**
     * Test project post type capability type
     * 
     * @test
     */
    public function test_project_capability_type() {
        $post_type_object = get_post_type_object( 'project' );
        
        $this->assertEquals( 
            'post', 
            $post_type_object->capability_type, 
            'Project capability type should be "post"' 
        );
    }
    
    /**
     * Test project post type query var
     * 
     * @test
     */
    public function test_project_query_var() {
        $post_type_object = get_post_type_object( 'project' );
        
        $this->assertTrue( 
            $post_type_object->query_var !== false, 
            'Project post type should have query var enabled' 
        );
    }
    
    /**
     * Test project post type map_meta_cap setting
     * 
     * @test
     */
    public function test_project_map_meta_cap() {
        $post_type_object = get_post_type_object( 'project' );
        
        $this->assertTrue( 
            $post_type_object->map_meta_cap, 
            'Project post type should have map_meta_cap enabled' 
        );
    }
    
    /**
     * Test project post type delete_with_user setting
     * 
     * @test
     */
    public function test_project_delete_with_user() {
        $post_type_object = get_post_type_object( 'project' );
        
        $this->assertFalse( 
            $post_type_object->delete_with_user, 
            'Project post type should not be deleted with user' 
        );
    }
    
    /**
     * Test project post type export setting
     * 
     * @test
     */
    public function test_project_can_export() {
        $post_type_object = get_post_type_object( 'project' );
        
        $this->assertTrue( 
            $post_type_object->can_export, 
            'Project post type should be exportable' 
        );
    }
    
    /**
     * Test project post type archive link generation
     * 
     * @test
     */
    public function test_project_archive_link() {
        $archive_link = get_post_type_archive_link( 'project' );
        
        $this->assertNotFalse( $archive_link, 'Project archive link should be generated' );
        $this->assertStringContainsString( '/projects', $archive_link, 'Archive link should contain /projects slug' );
    }
    
    /**
     * Test bulk edit functionality for projects
     * 
     * @test
     */
    public function test_project_bulk_edit() {
        // Create multiple projects
        $project_ids = array();
        for ( $i = 1; $i <= 3; $i++ ) {
            $project_ids[] = $this->factory->post->create( array(
                'post_type' => 'project',
                'post_title' => "Bulk Test Project {$i}",
                'post_status' => 'draft',
            ) );
        }
        
        // Simulate bulk edit - change status to published
        foreach ( $project_ids as $project_id ) {
            wp_update_post( array(
                'ID' => $project_id,
                'post_status' => 'publish',
            ) );
        }
        
        // Verify all projects were updated
        foreach ( $project_ids as $project_id ) {
            $project = get_post( $project_id );
            $this->assertEquals( 'publish', $project->post_status, 'Project should be published after bulk edit' );
        }
        
        // Clean up
        foreach ( $project_ids as $project_id ) {
            wp_delete_post( $project_id, true );
        }
    }
    
    /**
     * Test meta box registration for projects
     * 
     * @test
     */
    public function test_project_meta_boxes() {
        global $wp_meta_boxes;
        
        // Simulate admin context for meta box registration
        set_current_screen( 'edit-project' );
        
        // Trigger meta box registration
        do_action( 'add_meta_boxes', 'project', null );
        
        // Test that standard meta boxes are available
        $this->assertTrue( 
            isset( $wp_meta_boxes['project']['side']['core']['submitdiv'] ), 
            'Submit meta box should be registered for project' 
        );
    }
    
    /**
     * Test project permalink structure
     * 
     * @test
     */
    public function test_project_permalinks() {
        // Create a test project
        $project_id = $this->factory->post->create( array(
            'post_type' => 'project',
            'post_title' => 'Test Permalink Project',
            'post_name' => 'test-permalink-project',
            'post_status' => 'publish',
        ) );
        
        $permalink = get_permalink( $project_id );
        
        $this->assertStringContainsString( 
            '/projects/test-permalink-project', 
            $permalink, 
            'Project permalink should use correct structure' 
        );
        
        // Clean up
        wp_delete_post( $project_id, true );
    }
    
    /**
     * Test project search functionality
     * 
     * @test
     */
    public function test_project_search() {
        // Create test projects with specific content
        $project_id = $this->factory->post->create( array(
            'post_type' => 'project',
            'post_title' => 'Searchable Project Title',
            'post_content' => 'This project contains searchable content about WordPress development.',
            'post_status' => 'publish',
        ) );
        
        // Perform search query
        $query = new WP_Query( array(
            'post_type' => 'project',
            's' => 'searchable',
            'post_status' => 'publish',
        ) );
        
        $this->assertTrue( $query->have_posts(), 'Search should find the project' );
        $this->assertEquals( 1, $query->found_posts, 'Search should find exactly one project' );
        
        // Verify the correct project was found
        if ( $query->have_posts() ) {
            $query->the_post();
            $this->assertEquals( $project_id, get_the_ID(), 'Search should find the correct project' );
        }
        
        wp_reset_postdata();
        
        // Clean up
        wp_delete_post( $project_id, true );
    }
}