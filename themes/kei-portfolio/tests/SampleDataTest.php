<?php
/**
 * Sample Data Test
 * サンプルデータ投入機能のテスト
 *
 * @package Kei_Portfolio
 */

class SampleDataTest extends WP_UnitTestCase {

    public function setUp(): void {
        parent::setUp();
        
        // テスト環境でテーマの必要ファイルを読み込み
        require_once get_template_directory() . '/inc/post-types.php';
        require_once get_template_directory() . '/inc/sample-data.php';
        
        // カスタム投稿タイプとタクソノミーを登録
        kei_portfolio_pro_register_post_types();
        kei_portfolio_pro_register_taxonomies();
        
        // テストデータをクリア
        $this->cleanup_test_data();
    }

    public function tearDown(): void {
        // テスト後にクリーンアップ
        $this->cleanup_test_data();
        parent::tearDown();
    }

    /**
     * テストデータのクリーンアップ
     */
    private function cleanup_test_data() {
        // プロジェクトデータの削除
        $projects = get_posts(array(
            'post_type' => 'project',
            'post_status' => 'any',
            'numberposts' => -1,
        ));
        
        foreach ($projects as $project) {
            wp_delete_post($project->ID, true);
        }
        
        // タクソノミーデータの削除
        $technologies = get_terms(array('taxonomy' => 'technology', 'hide_empty' => false));
        foreach ($technologies as $term) {
            wp_delete_term($term->term_id, 'technology');
        }
        
        $industries = get_terms(array('taxonomy' => 'industry', 'hide_empty' => false));
        foreach ($industries as $term) {
            wp_delete_term($term->term_id, 'industry');
        }
        
        // オプションの削除
        delete_option('kei_portfolio_sample_data_created');
        delete_option('kei_portfolio_sample_data_log');
    }

    /**
     * サンプルプロジェクト設定取得テスト
     */
    public function test_get_sample_projects() {
        $projects = kei_portfolio_get_sample_projects();
        
        $this->assertIsArray($projects);
        $this->assertCount(5, $projects);
        
        foreach ($projects as $project) {
            $this->assertArrayHasKey('title', $project);
            $this->assertArrayHasKey('content', $project);
            $this->assertArrayHasKey('excerpt', $project);
            $this->assertArrayHasKey('meta', $project);
            $this->assertArrayHasKey('technologies', $project);
            $this->assertArrayHasKey('industries', $project);
            
            // 必須メタデータの確認
            $this->assertArrayHasKey('_project_period_start', $project['meta']);
            $this->assertArrayHasKey('_project_period_end', $project['meta']);
            $this->assertArrayHasKey('_project_role', $project['meta']);
            $this->assertArrayHasKey('_project_status', $project['meta']);
        }
    }

    /**
     * サンプル技術スタック設定取得テスト
     */
    public function test_get_sample_technologies() {
        $technologies = kei_portfolio_get_sample_technologies();
        
        $this->assertIsArray($technologies);
        $this->assertGreaterThan(10, count($technologies));
        
        // 親カテゴリの存在確認
        $parent_categories = array_filter($technologies, function($tech) {
            return is_null($tech['parent']);
        });
        
        $this->assertGreaterThan(0, count($parent_categories));
        
        // 子カテゴリの存在確認
        $child_categories = array_filter($technologies, function($tech) {
            return !is_null($tech['parent']);
        });
        
        $this->assertGreaterThan(0, count($child_categories));
    }

    /**
     * サンプル業界設定取得テスト
     */
    public function test_get_sample_industries() {
        $industries = kei_portfolio_get_sample_industries();
        
        $this->assertIsArray($industries);
        $this->assertGreaterThan(5, count($industries));
        
        // 特定の業界が含まれているか確認
        $expected_industries = array('通信業', 'Web サービス', 'エンターテイメント');
        foreach ($expected_industries as $industry) {
            $this->assertContains($industry, $industries);
        }
    }

    /**
     * サンプルタクソノミー作成テスト
     */
    public function test_create_sample_taxonomies() {
        $results = kei_portfolio_create_sample_taxonomies();
        
        $this->assertIsArray($results);
        $this->assertArrayHasKey('technologies', $results);
        $this->assertArrayHasKey('industries', $results);
        $this->assertArrayHasKey('errors', $results);
        
        // 技術スタックが作成されているか確認
        $this->assertGreaterThan(0, count($results['technologies']));
        
        // 業界が作成されているか確認
        $this->assertGreaterThan(0, count($results['industries']));
        
        // 実際にデータベースに保存されているか確認
        $tech_terms = get_terms(array('taxonomy' => 'technology', 'hide_empty' => false));
        $this->assertGreaterThan(0, count($tech_terms));
        
        $industry_terms = get_terms(array('taxonomy' => 'industry', 'hide_empty' => false));
        $this->assertGreaterThan(0, count($industry_terms));
        
        // 階層構造の確認（親子関係）
        $parent_terms = get_terms(array(
            'taxonomy' => 'technology',
            'parent' => 0,
            'hide_empty' => false
        ));
        $this->assertGreaterThan(0, count($parent_terms));
        
        $child_terms = get_terms(array(
            'taxonomy' => 'technology',
            'parent' => $parent_terms[0]->term_id,
            'hide_empty' => false
        ));
        $this->assertGreaterThan(0, count($child_terms));
    }

    /**
     * サンプルプロジェクト作成テスト
     */
    public function test_create_sample_projects() {
        // まずタクソノミーを作成
        kei_portfolio_create_sample_taxonomies();
        
        $results = kei_portfolio_create_sample_projects();
        
        $this->assertIsArray($results);
        $this->assertArrayHasKey('success', $results);
        $this->assertArrayHasKey('errors', $results);
        
        // 5つのプロジェクトが作成されることを確認
        $this->assertCount(5, $results['success']);
        $this->assertEmpty($results['errors']);
        
        // 実際にプロジェクトが作成されているか確認
        $projects = get_posts(array(
            'post_type' => 'project',
            'post_status' => 'publish',
            'numberposts' => -1,
        ));
        
        $this->assertCount(5, $projects);
        
        // 各プロジェクトの詳細確認
        foreach ($results['success'] as $project_info) {
            $project = get_post($project_info['id']);
            $this->assertEquals('project', $project->post_type);
            $this->assertEquals('publish', $project->post_status);
            
            // メタデータの確認
            $period_start = get_post_meta($project->ID, '_project_period_start', true);
            $this->assertNotEmpty($period_start);
            
            $role = get_post_meta($project->ID, '_project_role', true);
            $this->assertNotEmpty($role);
            
            // タクソノミーの確認
            $technologies = wp_get_object_terms($project->ID, 'technology');
            $this->assertGreaterThan(0, count($technologies));
            
            $industries = wp_get_object_terms($project->ID, 'industry');
            $this->assertGreaterThan(0, count($industries));
        }
    }

    /**
     * プロジェクトメタデータの詳細テスト
     */
    public function test_project_metadata_details() {
        // タクソノミーとプロジェクトを作成
        kei_portfolio_create_sample_taxonomies();
        $results = kei_portfolio_create_sample_projects();
        
        $first_project_id = $results['success'][0]['id'];
        
        // 期間データの確認
        $period_start = get_post_meta($first_project_id, '_project_period_start', true);
        $period_end = get_post_meta($first_project_id, '_project_period_end', true);
        
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $period_start);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $period_end);
        
        // チームサイズの確認
        $team_size = get_post_meta($first_project_id, '_project_team_size', true);
        $this->assertIsNumeric($team_size);
        $this->assertGreaterThan(0, intval($team_size));
        
        // 役割の確認
        $role = get_post_meta($first_project_id, '_project_role', true);
        $this->assertNotEmpty($role);
        
        // ステータスの確認
        $status = get_post_meta($first_project_id, '_project_status', true);
        $this->assertEquals('completed', $status);
        
        // 技術スタック配列の確認
        $technologies = get_post_meta($first_project_id, '_project_technologies');
        $this->assertIsArray($technologies);
        $this->assertGreaterThan(0, count($technologies));
    }

    /**
     * 全サンプルデータ作成テスト
     */
    public function test_create_all_sample_data() {
        // 管理者ユーザーでログイン
        $admin_user = $this->factory->user->create(array('role' => 'administrator'));
        wp_set_current_user($admin_user);
        
        $results = kei_portfolio_create_all_sample_data();
        
        $this->assertIsArray($results);
        $this->assertArrayHasKey('taxonomies', $results);
        $this->assertArrayHasKey('projects', $results);
        
        // タクソノミーの結果確認
        $this->assertArrayHasKey('technologies', $results['taxonomies']);
        $this->assertArrayHasKey('industries', $results['taxonomies']);
        
        // プロジェクトの結果確認
        $this->assertArrayHasKey('success', $results['projects']);
        $this->assertCount(5, $results['projects']['success']);
        
        // ログがオプションに保存されているか確認
        $log = get_option('kei_portfolio_sample_data_log');
        $this->assertIsArray($log);
    }

    /**
     * 権限なしでの実行テスト
     */
    public function test_create_sample_data_without_permission() {
        // 一般ユーザーでログイン
        $user = $this->factory->user->create(array('role' => 'subscriber'));
        wp_set_current_user($user);
        
        $result = kei_portfolio_create_all_sample_data();
        $this->assertFalse($result);
    }

    /**
     * サンプルデータ削除テスト
     */
    public function test_delete_sample_data() {
        // 管理者ユーザーでログイン
        $admin_user = $this->factory->user->create(array('role' => 'administrator'));
        wp_set_current_user($admin_user);
        
        // まずデータを作成
        kei_portfolio_create_all_sample_data();
        
        // データが存在することを確認
        $projects_before = get_posts(array('post_type' => 'project', 'numberposts' => -1));
        $this->assertGreaterThan(0, count($projects_before));
        
        // データを削除
        $result = kei_portfolio_delete_sample_data();
        $this->assertTrue($result);
        
        // データが削除されたことを確認
        $projects_after = get_posts(array('post_type' => 'project', 'numberposts' => -1));
        $this->assertCount(0, $projects_after);
        
        $technologies_after = get_terms(array('taxonomy' => 'technology', 'hide_empty' => false));
        $this->assertCount(0, $technologies_after);
        
        $industries_after = get_terms(array('taxonomy' => 'industry', 'hide_empty' => false));
        $this->assertCount(0, $industries_after);
    }

    /**
     * テーマアクティベーション時の自動実行テスト
     */
    public function test_create_sample_data_on_activation() {
        // 実行前の状態確認
        $this->assertFalse(get_option('kei_portfolio_sample_data_created', false));
        
        // アクティベーション関数実行
        $results = kei_portfolio_create_sample_data_on_activation();
        
        // フラグが設定されているか確認
        $this->assertTrue(get_option('kei_portfolio_sample_data_created', false));
        
        // データが作成されているか確認
        $projects = get_posts(array('post_type' => 'project', 'numberposts' => -1));
        $this->assertCount(5, $projects);
        
        // 重複実行防止テスト
        $results_second = kei_portfolio_create_sample_data_on_activation();
        $this->assertNull($results_second);
    }

    /**
     * プロジェクトのタクソノミー関連付けテスト
     */
    public function test_project_taxonomy_associations() {
        // タクソノミーとプロジェクトを作成
        kei_portfolio_create_sample_taxonomies();
        $results = kei_portfolio_create_sample_projects();
        
        $project_id = $results['success'][0]['id'];
        
        // 技術スタックの関連付け確認
        $technologies = wp_get_object_terms($project_id, 'technology');
        $this->assertGreaterThan(0, count($technologies));
        
        foreach ($technologies as $tech) {
            $this->assertNotEmpty($tech->name);
            $this->assertEquals('technology', $tech->taxonomy);
        }
        
        // 業界の関連付け確認
        $industries = wp_get_object_terms($project_id, 'industry');
        $this->assertGreaterThan(0, count($industries));
        
        foreach ($industries as $industry) {
            $this->assertNotEmpty($industry->name);
            $this->assertEquals('industry', $industry->taxonomy);
        }
    }

    /**
     * データ整合性テスト
     */
    public function test_data_integrity() {
        $sample_projects = kei_portfolio_get_sample_projects();
        
        foreach ($sample_projects as $project) {
            // タイトルの長さチェック
            $this->assertLessThan(200, strlen($project['title']));
            
            // コンテンツの存在チェック
            $this->assertGreaterThan(10, strlen($project['content']));
            
            // 期間の妥当性チェック
            $start = $project['meta']['_project_period_start'];
            $end = $project['meta']['_project_period_end'];
            
            $this->assertLessThanOrEqual($end, $start, "Project period should be valid: {$project['title']}");
            
            // 技術スタック数チェック
            $this->assertGreaterThan(0, count($project['technologies']));
            $this->assertLessThan(10, count($project['technologies']));
            
            // 業界数チェック
            $this->assertGreaterThan(0, count($project['industries']));
            $this->assertLessThan(5, count($project['industries']));
        }
    }

    /**
     * エラーハンドリングテスト
     */
    public function test_error_handling() {
        // 無効なデータでのテスト
        
        // プロジェクトタイプが登録されていない場合のテスト
        global $wp_post_types;
        $backup_project_type = $wp_post_types['project'];
        unset($wp_post_types['project']);
        
        $results = kei_portfolio_create_sample_projects();
        
        $this->assertIsArray($results);
        $this->assertArrayHasKey('errors', $results);
        $this->assertGreaterThan(0, count($results['errors']));
        
        // プロジェクトタイプを復元
        $wp_post_types['project'] = $backup_project_type;
    }
}