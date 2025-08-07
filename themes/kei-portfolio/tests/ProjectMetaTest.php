<?php
/**
 * Test suite for project metadata and custom fields
 * 
 * @package Kei_Portfolio
 * @subpackage Tests
 */

class ProjectMetaTest extends WP_UnitTestCase {
    
    /**
     * Test project ID
     */
    private $test_project_id;
    
    /**
     * Test user ID
     */
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
        
        // Create test user with appropriate capabilities
        $this->test_user_id = $this->factory->user->create( array(
            'role' => 'administrator',
        ) );
        
        // Set current user
        wp_set_current_user( $this->test_user_id );
        
        // Create a test project
        $this->test_project_id = $this->factory->post->create( array(
            'post_type'   => 'project',
            'post_title'  => 'Test Project for Metadata',
            'post_status' => 'publish',
            'post_author' => $this->test_user_id,
        ) );
    }
    
    /**
     * Clean up after each test
     */
    public function tearDown(): void {
        // Delete test project
        if ( $this->test_project_id ) {
            wp_delete_post( $this->test_project_id, true );
        }
        
        // Delete test user
        if ( $this->test_user_id ) {
            wp_delete_user( $this->test_user_id );
        }
        
        parent::tearDown();
    }
    
    /**
     * Test adding custom meta to project
     * 
     * @test
     */
    public function test_add_project_meta() {
        // Add project period meta
        $period_added = add_post_meta( $this->test_project_id, 'project_period', '2024年1月 - 2024年3月' );
        $this->assertNotFalse( $period_added, 'Should be able to add project_period meta' );
        
        // Add project role meta
        $role_added = add_post_meta( $this->test_project_id, 'project_role', 'フルスタック開発者' );
        $this->assertNotFalse( $role_added, 'Should be able to add project_role meta' );
        
        // Add project URL meta
        $url_added = add_post_meta( $this->test_project_id, 'project_url', 'https://example.com/project' );
        $this->assertNotFalse( $url_added, 'Should be able to add project_url meta' );
    }
    
    /**
     * Test retrieving project meta
     * 
     * @test
     */
    public function test_get_project_meta() {
        // Add meta data
        add_post_meta( $this->test_project_id, 'project_period', '2024年1月 - 2024年3月' );
        add_post_meta( $this->test_project_id, 'project_role', 'フルスタック開発者' );
        add_post_meta( $this->test_project_id, 'project_url', 'https://example.com/project' );
        
        // Retrieve and verify meta data
        $period = get_post_meta( $this->test_project_id, 'project_period', true );
        $this->assertEquals( '2024年1月 - 2024年3月', $period, 'Should retrieve correct project period' );
        
        $role = get_post_meta( $this->test_project_id, 'project_role', true );
        $this->assertEquals( 'フルスタック開発者', $role, 'Should retrieve correct project role' );
        
        $url = get_post_meta( $this->test_project_id, 'project_url', true );
        $this->assertEquals( 'https://example.com/project', $url, 'Should retrieve correct project URL' );
    }
    
    /**
     * Test updating project meta
     * 
     * @test
     */
    public function test_update_project_meta() {
        // Add initial meta
        add_post_meta( $this->test_project_id, 'project_period', '2024年1月 - 2024年3月' );
        
        // Update meta
        $updated = update_post_meta( $this->test_project_id, 'project_period', '2024年1月 - 2024年6月' );
        $this->assertNotFalse( $updated, 'Should be able to update project meta' );
        
        // Verify updated value
        $period = get_post_meta( $this->test_project_id, 'project_period', true );
        $this->assertEquals( '2024年1月 - 2024年6月', $period, 'Should retrieve updated project period' );
    }
    
    /**
     * Test deleting project meta
     * 
     * @test
     */
    public function test_delete_project_meta() {
        // Add meta
        add_post_meta( $this->test_project_id, 'project_period', '2024年1月 - 2024年3月' );
        
        // Delete meta
        $deleted = delete_post_meta( $this->test_project_id, 'project_period' );
        $this->assertTrue( $deleted, 'Should be able to delete project meta' );
        
        // Verify deletion
        $period = get_post_meta( $this->test_project_id, 'project_period', true );
        $this->assertEquals( '', $period, 'Deleted meta should return empty string' );
    }
    
    /**
     * Test project technologies meta (array values)
     * 
     * @test
     */
    public function test_project_technologies_meta() {
        // Add multiple technology meta values
        add_post_meta( $this->test_project_id, 'project_technologies', 'WordPress' );
        add_post_meta( $this->test_project_id, 'project_technologies', 'React' );
        add_post_meta( $this->test_project_id, 'project_technologies', 'PHP' );
        
        // Get all values
        $technologies = get_post_meta( $this->test_project_id, 'project_technologies', false );
        
        $this->assertIsArray( $technologies, 'Technologies should be an array' );
        $this->assertCount( 3, $technologies, 'Should have 3 technologies' );
        $this->assertContains( 'WordPress', $technologies, 'Should contain WordPress' );
        $this->assertContains( 'React', $technologies, 'Should contain React' );
        $this->assertContains( 'PHP', $technologies, 'Should contain PHP' );
    }
    
    /**
     * Test project client information meta
     * 
     * @test
     */
    public function test_project_client_meta() {
        // Add client-related meta
        add_post_meta( $this->test_project_id, 'client_name', '株式会社サンプル' );
        add_post_meta( $this->test_project_id, 'client_industry', 'IT・通信' );
        add_post_meta( $this->test_project_id, 'client_size', '100-500人' );
        
        // Retrieve and verify
        $client_name = get_post_meta( $this->test_project_id, 'client_name', true );
        $this->assertEquals( '株式会社サンプル', $client_name, 'Should retrieve correct client name' );
        
        $client_industry = get_post_meta( $this->test_project_id, 'client_industry', true );
        $this->assertEquals( 'IT・通信', $client_industry, 'Should retrieve correct client industry' );
        
        $client_size = get_post_meta( $this->test_project_id, 'client_size', true );
        $this->assertEquals( '100-500人', $client_size, 'Should retrieve correct client size' );
    }
    
    /**
     * Test project deliverables meta
     * 
     * @test
     */
    public function test_project_deliverables_meta() {
        // Add deliverables as serialized array
        $deliverables = array(
            'ウェブサイトデザイン',
            'フロントエンド開発',
            'バックエンド開発',
            'デプロイメント',
            'ドキュメント作成',
        );
        
        add_post_meta( $this->test_project_id, 'project_deliverables', $deliverables );
        
        // Retrieve and verify
        $saved_deliverables = get_post_meta( $this->test_project_id, 'project_deliverables', true );
        
        $this->assertIsArray( $saved_deliverables, 'Deliverables should be an array' );
        $this->assertCount( 5, $saved_deliverables, 'Should have 5 deliverables' );
        $this->assertEquals( $deliverables, $saved_deliverables, 'Deliverables should match' );
    }
    
    /**
     * Test project challenges and solutions meta
     * 
     * @test
     */
    public function test_project_challenges_solutions_meta() {
        // Add challenges
        $challenges = "レガシーシステムとの統合\n大量データの処理\nリアルタイム同期の実装";
        add_post_meta( $this->test_project_id, 'project_challenges', $challenges );
        
        // Add solutions
        $solutions = "APIアダプターパターンの実装\nキューシステムの導入\nWebSocketによるリアルタイム通信";
        add_post_meta( $this->test_project_id, 'project_solutions', $solutions );
        
        // Retrieve and verify
        $saved_challenges = get_post_meta( $this->test_project_id, 'project_challenges', true );
        $this->assertEquals( $challenges, $saved_challenges, 'Challenges should match' );
        
        $saved_solutions = get_post_meta( $this->test_project_id, 'project_solutions', true );
        $this->assertEquals( $solutions, $saved_solutions, 'Solutions should match' );
    }
    
    /**
     * Test project results/outcomes meta
     * 
     * @test
     */
    public function test_project_results_meta() {
        // Add project results as structured data
        $results = array(
            'performance_improvement' => '50%',
            'user_satisfaction' => '4.8/5.0',
            'conversion_rate' => '+35%',
            'load_time_reduction' => '2.5秒 → 0.8秒',
        );
        
        add_post_meta( $this->test_project_id, 'project_results', $results );
        
        // Retrieve and verify
        $saved_results = get_post_meta( $this->test_project_id, 'project_results', true );
        
        $this->assertIsArray( $saved_results, 'Results should be an array' );
        $this->assertEquals( '50%', $saved_results['performance_improvement'], 'Performance improvement should match' );
        $this->assertEquals( '4.8/5.0', $saved_results['user_satisfaction'], 'User satisfaction should match' );
        $this->assertEquals( '+35%', $saved_results['conversion_rate'], 'Conversion rate should match' );
        $this->assertEquals( '2.5秒 → 0.8秒', $saved_results['load_time_reduction'], 'Load time reduction should match' );
    }
    
    /**
     * Test project team members meta
     * 
     * @test
     */
    public function test_project_team_meta() {
        // Add team members
        $team = array(
            array(
                'name' => '山田太郎',
                'role' => 'プロジェクトマネージャー',
            ),
            array(
                'name' => '佐藤花子',
                'role' => 'UIデザイナー',
            ),
            array(
                'name' => '鈴木一郎',
                'role' => 'バックエンド開発者',
            ),
        );
        
        add_post_meta( $this->test_project_id, 'project_team', $team );
        
        // Retrieve and verify
        $saved_team = get_post_meta( $this->test_project_id, 'project_team', true );
        
        $this->assertIsArray( $saved_team, 'Team should be an array' );
        $this->assertCount( 3, $saved_team, 'Should have 3 team members' );
        $this->assertEquals( '山田太郎', $saved_team[0]['name'], 'First team member name should match' );
        $this->assertEquals( 'プロジェクトマネージャー', $saved_team[0]['role'], 'First team member role should match' );
    }
    
    /**
     * Test project gallery/screenshots meta
     * 
     * @test
     */
    public function test_project_gallery_meta() {
        // Create attachment IDs (simulated)
        $attachment_ids = array( 101, 102, 103, 104, 105 );
        
        add_post_meta( $this->test_project_id, 'project_gallery', $attachment_ids );
        
        // Retrieve and verify
        $gallery = get_post_meta( $this->test_project_id, 'project_gallery', true );
        
        $this->assertIsArray( $gallery, 'Gallery should be an array' );
        $this->assertCount( 5, $gallery, 'Should have 5 gallery items' );
        $this->assertEquals( $attachment_ids, $gallery, 'Gallery IDs should match' );
    }
    
    /**
     * Test project status meta
     * 
     * @test
     */
    public function test_project_status_meta() {
        // Test different project statuses
        $statuses = array( 'planning', 'in_progress', 'completed', 'maintenance' );
        
        foreach ( $statuses as $status ) {
            update_post_meta( $this->test_project_id, 'project_status', $status );
            $saved_status = get_post_meta( $this->test_project_id, 'project_status', true );
            $this->assertEquals( $status, $saved_status, "Status '{$status}' should be saved correctly" );
        }
    }
    
    /**
     * Test project budget meta
     * 
     * @test
     */
    public function test_project_budget_meta() {
        // Add budget range
        add_post_meta( $this->test_project_id, 'project_budget_min', '1000000' );
        add_post_meta( $this->test_project_id, 'project_budget_max', '5000000' );
        add_post_meta( $this->test_project_id, 'project_budget_currency', 'JPY' );
        
        // Retrieve and verify
        $budget_min = get_post_meta( $this->test_project_id, 'project_budget_min', true );
        $budget_max = get_post_meta( $this->test_project_id, 'project_budget_max', true );
        $currency = get_post_meta( $this->test_project_id, 'project_budget_currency', true );
        
        $this->assertEquals( '1000000', $budget_min, 'Minimum budget should match' );
        $this->assertEquals( '5000000', $budget_max, 'Maximum budget should match' );
        $this->assertEquals( 'JPY', $currency, 'Currency should match' );
    }
    
    /**
     * Test project testimonial meta
     * 
     * @test
     */
    public function test_project_testimonial_meta() {
        // Add testimonial
        $testimonial = array(
            'content' => '素晴らしいプロジェクトでした。期待以上の成果を出していただきました。',
            'author' => '田中部長',
            'company' => '株式会社サンプル',
            'position' => 'IT部門責任者',
        );
        
        add_post_meta( $this->test_project_id, 'project_testimonial', $testimonial );
        
        // Retrieve and verify
        $saved_testimonial = get_post_meta( $this->test_project_id, 'project_testimonial', true );
        
        $this->assertIsArray( $saved_testimonial, 'Testimonial should be an array' );
        $this->assertEquals( $testimonial['content'], $saved_testimonial['content'], 'Testimonial content should match' );
        $this->assertEquals( $testimonial['author'], $saved_testimonial['author'], 'Testimonial author should match' );
    }
    
    /**
     * Test meta data sanitization
     * 
     * @test
     */
    public function test_meta_sanitization() {
        // Test HTML sanitization
        $html_content = '<script>alert("XSS")</script>Safe content';
        add_post_meta( $this->test_project_id, 'test_html', wp_kses_post( $html_content ) );
        
        $saved_html = get_post_meta( $this->test_project_id, 'test_html', true );
        $this->assertStringNotContainsString( '<script>', $saved_html, 'Script tags should be removed' );
        $this->assertStringContainsString( 'Safe content', $saved_html, 'Safe content should remain' );
        
        // Test URL sanitization
        $url = 'javascript:alert("XSS")';
        add_post_meta( $this->test_project_id, 'test_url', esc_url_raw( $url ) );
        
        $saved_url = get_post_meta( $this->test_project_id, 'test_url', true );
        $this->assertEmpty( $saved_url, 'JavaScript URL should be rejected' );
    }
    
    /**
     * Test meta capabilities
     * 
     * @test
     */
    public function test_meta_capabilities() {
        // Test as administrator (should have capability)
        $this->assertTrue( 
            current_user_can( 'edit_post_meta', $this->test_project_id, 'project_period' ),
            'Administrator should be able to edit project meta'
        );
        
        // Create and test as subscriber (should not have capability)
        $subscriber_id = $this->factory->user->create( array(
            'role' => 'subscriber',
        ) );
        wp_set_current_user( $subscriber_id );
        
        $this->assertFalse( 
            current_user_can( 'edit_post_meta', $this->test_project_id, 'project_period' ),
            'Subscriber should not be able to edit project meta'
        );
        
        // Clean up
        wp_delete_user( $subscriber_id );
        wp_set_current_user( $this->test_user_id );
    }
    
    /**
     * Test bulk meta operations
     * 
     * @test
     */
    public function test_bulk_meta_operations() {
        // Add multiple meta fields at once
        $meta_fields = array(
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => 'value3',
            'field4' => 'value4',
            'field5' => 'value5',
        );
        
        foreach ( $meta_fields as $key => $value ) {
            add_post_meta( $this->test_project_id, $key, $value );
        }
        
        // Get all meta for the post
        $all_meta = get_post_meta( $this->test_project_id );
        
        // Verify all fields exist
        foreach ( $meta_fields as $key => $value ) {
            $this->assertArrayHasKey( $key, $all_meta, "Meta field '{$key}' should exist" );
            $this->assertEquals( $value, $all_meta[$key][0], "Meta field '{$key}' value should match" );
        }
    }
}