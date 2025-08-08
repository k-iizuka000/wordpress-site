<?php
/**
 * サンプルプロジェクトデータ投入機能
 *
 * @package Kei_Portfolio
 */

/**
 * サンプルプロジェクトデータの設定
 */
function kei_portfolio_get_sample_projects() {
    return array(
        array(
            'title' => '通信会社向けWebサイト開発',
            'content' => 'Spring Boot移行後の技術文書整備とナレッジ共有体制の確立を行いました。若手エンジニアの教育とコードレビュー体制の確立、リリース管理プロセスの改善やPRテンプレートの導入により、チーム全体の生産性向上に貢献しました。',
            'excerpt' => '開発リーダーとしてSpring Boot移行とチーム体制の改善を推進',
            'meta' => array(
                '_project_period_start' => '2024-01',
                '_project_period_end' => '2025-09',
                '_project_team_size' => '8',
                '_project_role' => '開発リーダー',
                '_project_technologies' => array('Python(pypika)', 'Java(Spring)', 'JavaScript', 'JSP', 'Git'),
                '_project_github_url' => '',
                '_project_demo_url' => '',
                '_project_client_type' => '通信会社',
                '_project_status' => 'completed',
            ),
            'technologies' => array('Java', 'Spring Boot', 'Python', 'JavaScript', 'Git'),
            'industries' => array('通信業'),
        ),
        array(
            'title' => '料理レシピ投稿サイト開発',
            'content' => 'ユーザー側・管理者側の既存機能の改修を担当しました。課題一覧に基づく機能改善と品質向上、複数言語・フレームワークが混在する環境での開発経験を積みました。Python、PHP、Java、JavaScriptという多様な技術スタックを活用しています。',
            'excerpt' => '多言語・多フレームワーク環境での機能改修とUI/UX向上',
            'meta' => array(
                '_project_period_start' => '2022-10',
                '_project_period_end' => '2023-12',
                '_project_team_size' => '5',
                '_project_role' => 'SE（詳細設計～試験）',
                '_project_technologies' => array('Python(pypika)', 'PHP(FuelPHP)', 'Java(Spring)', 'JavaScript(Vue.js)'),
                '_project_github_url' => '',
                '_project_demo_url' => '',
                '_project_client_type' => 'Webサービス企業',
                '_project_status' => 'completed',
            ),
            'technologies' => array('Python', 'PHP', 'Java', 'Vue.js', 'Spring Boot'),
            'industries' => array('Web サービス'),
        ),
        array(
            'title' => 'PSNサーバーサイド開発・運用',
            'content' => 'PlayStation Network（PSN）のサーバーサイド開発・運用を担当しました。ユーザーログイン管理機能の開発・運用、JUnitとE2Eテストの拡充によるテスト品質向上、AWS CloudWatchを用いたメトリクス監視と障害対応を行いました。',
            'excerpt' => 'PSNのユーザーログイン機能開発とAWS監視システムの構築',
            'meta' => array(
                '_project_period_start' => '2021-04',
                '_project_period_end' => '2022-09',
                '_project_team_size' => '12',
                '_project_role' => 'SE（詳細設計～運用・保守）',
                '_project_technologies' => array('Java(Spring)', 'AWS', 'JUnit', 'E2E', 'Git', 'Maven'),
                '_project_github_url' => '',
                '_project_demo_url' => '',
                '_project_client_type' => 'エンターテイメント企業',
                '_project_status' => 'completed',
            ),
            'technologies' => array('Java', 'Spring Boot', 'AWS', 'JUnit', 'Maven'),
            'industries' => array('エンターテイメント'),
        ),
        array(
            'title' => 'Push通知機能開発',
            'content' => 'PCやスマートフォンへのPush通知機能の新規開発を担当しました。フロントエンドからバックエンドまでの一貫した開発を行い、Docker環境での開発、Ruby on Rails、Vue.jsを活用したモダンな開発手法を実践しました。',
            'excerpt' => 'マルチプラットフォーム対応のPush通知システム開発',
            'meta' => array(
                '_project_period_start' => '2019-10',
                '_project_period_end' => '2020-03',
                '_project_team_size' => '6',
                '_project_role' => 'SE（詳細設計～製造・試験）',
                '_project_technologies' => array('Docker', 'Ruby on Rails', 'Vue.js', 'Git'),
                '_project_github_url' => '',
                '_project_demo_url' => '',
                '_project_client_type' => 'モバイルアプリ企業',
                '_project_status' => 'completed',
            ),
            'technologies' => array('Ruby on Rails', 'Vue.js', 'Docker', 'Git'),
            'industries' => array('モバイルアプリ'),
        ),
        array(
            'title' => '従業員向けシフト表作成アプリ',
            'content' => '従業員のシフト管理を効率化するWebアプリケーションを開発しました。カレンダー画面や従業員情報管理画面の開発、シフト表画面UI・機能の実装を担当。FullCalendarライブラリを活用した直感的なUI設計を行いました。',
            'excerpt' => 'FullCalendarを活用した直感的なシフト管理システム',
            'meta' => array(
                '_project_period_start' => '2018-04',
                '_project_period_end' => '2019-09',
                '_project_team_size' => '4',
                '_project_role' => 'SE（要件定義～製造）',
                '_project_technologies' => array('Java(Spring)', 'JavaScript(FullCalendar)', 'Git', 'SVN'),
                '_project_github_url' => '',
                '_project_demo_url' => '',
                '_project_client_type' => '人材派遣会社',
                '_project_status' => 'completed',
            ),
            'technologies' => array('Java', 'Spring Boot', 'JavaScript', 'FullCalendar'),
            'industries' => array('人材サービス'),
        ),
    );
}

/**
 * 技術スタックタクソノミーのサンプルデータ
 */
function kei_portfolio_get_sample_technologies() {
    return array(
        // バックエンド
        array('name' => 'Java', 'parent' => 'Backend'),
        array('name' => 'Spring Boot', 'parent' => 'Backend'),
        array('name' => 'Python', 'parent' => 'Backend'),
        array('name' => 'Ruby on Rails', 'parent' => 'Backend'),
        array('name' => 'PHP', 'parent' => 'Backend'),
        array('name' => 'Node.js', 'parent' => 'Backend'),
        
        // フロントエンド
        array('name' => 'JavaScript', 'parent' => 'Frontend'),
        array('name' => 'Vue.js', 'parent' => 'Frontend'),
        array('name' => 'React', 'parent' => 'Frontend'),
        array('name' => 'HTML5', 'parent' => 'Frontend'),
        array('name' => 'CSS3', 'parent' => 'Frontend'),
        array('name' => 'FullCalendar', 'parent' => 'Frontend'),
        
        // インフラ・ツール
        array('name' => 'AWS', 'parent' => 'Infrastructure'),
        array('name' => 'Docker', 'parent' => 'Infrastructure'),
        array('name' => 'Git', 'parent' => 'Tools'),
        array('name' => 'Maven', 'parent' => 'Tools'),
        array('name' => 'JUnit', 'parent' => 'Testing'),
        
        // 親カテゴリ
        array('name' => 'Backend', 'parent' => null),
        array('name' => 'Frontend', 'parent' => null),
        array('name' => 'Infrastructure', 'parent' => null),
        array('name' => 'Tools', 'parent' => null),
        array('name' => 'Testing', 'parent' => null),
    );
}

/**
 * 業界タクソノミーのサンプルデータ
 */
function kei_portfolio_get_sample_industries() {
    return array(
        '通信業',
        'Web サービス',
        'エンターテイメント',
        'モバイルアプリ',
        '人材サービス',
        '金融業',
        '公共機関',
        'EC・小売',
    );
}

/**
 * サンプルタクソノミーの作成
 */
function kei_portfolio_create_sample_taxonomies() {
    $results = array(
        'technologies' => array(),
        'industries' => array(),
        'errors' => array(),
    );

    // 技術スタック作成
    $technologies = kei_portfolio_get_sample_technologies();
    $created_terms = array();
    
    // まず親カテゴリを作成
    foreach ($technologies as $tech) {
        if (is_null($tech['parent'])) {
            $term = wp_insert_term($tech['name'], 'technology');
            if (!is_wp_error($term)) {
                $created_terms[$tech['name']] = $term['term_id'];
                $results['technologies'][] = $tech['name'];
            } else {
                $results['errors'][] = 'Technology: ' . $tech['name'] . ' - ' . $term->get_error_message();
            }
        }
    }
    
    // 次に子カテゴリを作成
    foreach ($technologies as $tech) {
        if (!is_null($tech['parent']) && isset($created_terms[$tech['parent']])) {
            $parent_id = $created_terms[$tech['parent']];
            $term = wp_insert_term($tech['name'], 'technology', array(
                'parent' => $parent_id
            ));
            
            if (!is_wp_error($term)) {
                $created_terms[$tech['name']] = $term['term_id'];
                $results['technologies'][] = $tech['name'];
            } else if ($term->get_error_code() !== 'term_exists') {
                $results['errors'][] = 'Technology: ' . $tech['name'] . ' - ' . $term->get_error_message();
            }
        }
    }

    // 業界カテゴリ作成
    $industries = kei_portfolio_get_sample_industries();
    foreach ($industries as $industry) {
        $term = wp_insert_term($industry, 'industry');
        if (!is_wp_error($term)) {
            $results['industries'][] = $industry;
        } else if ($term->get_error_code() !== 'term_exists') {
            $results['errors'][] = 'Industry: ' . $industry . ' - ' . $term->get_error_message();
        }
    }

    return $results;
}

/**
 * サンプルプロジェクトの作成
 */
function kei_portfolio_create_sample_projects() {
    $projects = kei_portfolio_get_sample_projects();
    $results = array(
        'success' => array(),
        'errors' => array(),
    );

    foreach ($projects as $project) {
        // プロジェクトの作成
        $post_data = array(
            'post_title' => $project['title'],
            'post_content' => $project['content'],
            'post_excerpt' => $project['excerpt'],
            'post_status' => 'publish',
            'post_type' => 'project',
        );

        $project_id = wp_insert_post($post_data);

        if (is_wp_error($project_id)) {
            $results['errors'][] = $project['title'] . ': ' . $project_id->get_error_message();
            continue;
        }

        // メタデータの設定
        if (isset($project['meta'])) {
            foreach ($project['meta'] as $meta_key => $meta_value) {
                if (is_array($meta_value)) {
                    // 配列の場合は各値を個別に保存
                    delete_post_meta($project_id, $meta_key);
                    foreach ($meta_value as $value) {
                        add_post_meta($project_id, $meta_key, $value);
                    }
                } else {
                    update_post_meta($project_id, $meta_key, $meta_value);
                }
            }
        }

        // 技術スタックタクソノミーの設定
        if (isset($project['technologies'])) {
            $tech_term_ids = array();
            foreach ($project['technologies'] as $tech_name) {
                $term = get_term_by('name', $tech_name, 'technology');
                if ($term) {
                    $tech_term_ids[] = $term->term_id;
                }
            }
            if (!empty($tech_term_ids)) {
                wp_set_object_terms($project_id, $tech_term_ids, 'technology');
            }
        }

        // 業界タクソノミーの設定
        if (isset($project['industries'])) {
            $industry_term_ids = array();
            foreach ($project['industries'] as $industry_name) {
                $term = get_term_by('name', $industry_name, 'industry');
                if ($term) {
                    $industry_term_ids[] = $term->term_id;
                }
            }
            if (!empty($industry_term_ids)) {
                wp_set_object_terms($project_id, $industry_term_ids, 'industry');
            }
        }

        $results['success'][] = array(
            'id' => $project_id,
            'title' => $project['title'],
            'status' => 'created',
        );
    }

    // ログの記録
    $log_message = 'Sample projects creation attempted: ' . count($projects) . ' projects';
    if (!empty($results['success'])) {
        $log_message .= ', ' . count($results['success']) . ' successful';
    }
    if (!empty($results['errors'])) {
        $log_message .= ', ' . count($results['errors']) . ' errors';
    }

    error_log('[Kei Portfolio] ' . $log_message);

    return $results;
}

/**
 * 全サンプルデータの作成
 */
function kei_portfolio_create_all_sample_data() {
    // 管理者権限チェック
    if (!current_user_can('manage_options')) {
        return false;
    }

    $results = array(
        'taxonomies' => array(),
        'projects' => array(),
    );

    // タクソノミーの作成
    $results['taxonomies'] = kei_portfolio_create_sample_taxonomies();

    // プロジェクトの作成
    $results['projects'] = kei_portfolio_create_sample_projects();

    // 結果をオプションに保存
    update_option('kei_portfolio_sample_data_log', $results);

    return $results;
}

/**
 * サンプルデータの削除（テスト用）
 */
function kei_portfolio_delete_sample_data() {
    if (!current_user_can('manage_options')) {
        return false;
    }

    // プロジェクト削除
    $projects = get_posts(array(
        'post_type' => 'project',
        'post_status' => 'any',
        'numberposts' => -1,
    ));

    foreach ($projects as $project) {
        wp_delete_post($project->ID, true);
    }

    // タクソノミー削除
    $technologies = get_terms(array('taxonomy' => 'technology', 'hide_empty' => false));
    foreach ($technologies as $term) {
        wp_delete_term($term->term_id, 'technology');
    }

    $industries = get_terms(array('taxonomy' => 'industry', 'hide_empty' => false));
    foreach ($industries as $term) {
        wp_delete_term($term->term_id, 'industry');
    }

    // オプション削除
    delete_option('kei_portfolio_sample_data_log');

    return true;
}

/**
 * テーマ有効化時のサンプルデータ作成
 */
function kei_portfolio_create_sample_data_on_activation() {
    // 既に実行済みかチェック
    $sample_data_created = get_option('kei_portfolio_sample_data_created', false);
    if ($sample_data_created) {
        return;
    }

    // サンプルデータの作成
    $results = kei_portfolio_create_all_sample_data();

    // フラグを設定して再実行を防ぐ
    update_option('kei_portfolio_sample_data_created', true);

    return $results;
}

// テーマアクティベーション時のフック（オプション）
// add_action('after_switch_theme', 'kei_portfolio_create_sample_data_on_activation');