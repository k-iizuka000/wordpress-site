<?php
/**
 * 実績一覧アーカイブテンプレート
 *
 * @package Kei_Portfolio_Pro
 */

get_header(); ?>

<?php
// Portfolio JSON 読込とフィルタ用下準備
$portfolio = Portfolio_Data::get_instance();
$projects_all = $portfolio->get_projects_data(0);
$use_wp_query_fallback = is_wp_error($projects_all);

$q = isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '';
$filter_tech = isset($_GET['technology']) ? sanitize_text_field(wp_unslash($_GET['technology'])) : '';
$filter_industry = isset($_GET['industry']) ? sanitize_text_field(wp_unslash($_GET['industry'])) : '';
$orderby = isset($_GET['orderby']) ? sanitize_text_field(wp_unslash($_GET['orderby'])) : 'date-desc';

$tech_counts = array();
$industry_counts = array();
if (!$use_wp_query_fallback && is_array($projects_all)) {
    foreach ($projects_all as $p) {
        if (!empty($p['technologies']) && is_array($p['technologies'])) {
            foreach ($p['technologies'] as $t) {
                $name = is_array($t) && isset($t['name']) ? (string)$t['name'] : (string)$t;
                $key = trim($name);
                if ($key === '') continue;
                $tech_counts[$key] = isset($tech_counts[$key]) ? $tech_counts[$key] + 1 : 1;
            }
        }
        if (!empty($p['industry'])) {
            $name = (string)$p['industry'];
            $key = trim($name);
            if ($key !== '') {
                $industry_counts[$key] = isset($industry_counts[$key]) ? $industry_counts[$key] + 1 : 1;
            }
        }
    }
    ksort($tech_counts);
    ksort($industry_counts);
}
?>

<main id="main" class="site-main">
    
    <!-- ページヘッダー（skills と同様のスタイル） -->
    <div class="max-w-6xl mx-auto px-4 pt-12">
    <header class="page-header text-center mb-12">
        <h1 class="text-5xl font-bold text-gray-800 mb-4">
            <?php echo esc_html( post_type_archive_title('', false) ?: '実績一覧' ); ?>
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            これまでに手がけたプロジェクトと開発実績をご紹介します
        </p>
    </header>
    </div>

    <!-- フィルター -->
    <section class="project-filters py-3" style="background-color: var(--color-background-alt); border-bottom: 1px solid var(--color-border);">
        <div class="container">
            <div style="display: flex; gap: 2rem; align-items: center; flex-wrap: wrap;">
                <?php if ( ! $use_wp_query_fallback ) : ?>
                <div>
                    <label style="font-weight: 600; margin-right: 0.5rem;">技術で絞り込み:</label>
                    <select id="technology-filter" style="padding: 0.5rem; border: 1px solid var(--color-border); border-radius: var(--border-radius);">
                        <option value="">すべて</option>
                        <?php foreach ($tech_counts as $tech_name => $count) :
                            $selected = ($filter_tech === $tech_name) ? 'selected' : '';
                        ?>
                            <option value="<?php echo esc_attr($tech_name); ?>" <?php echo $selected; ?>>
                                <?php echo esc_html($tech_name); ?> (<?php echo (int)$count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label style="font-weight: 600; margin-right: 0.5rem;">業界で絞り込み:</label>
                    <select id="industry-filter" style="padding: 0.5rem; border: 1px solid var(--color-border); border-radius: var(--border-radius);">
                        <option value="">すべて</option>
                        <?php foreach ($industry_counts as $industry_name => $count) :
                            $selected = ($filter_industry === $industry_name) ? 'selected' : '';
                        ?>
                            <option value="<?php echo esc_attr($industry_name); ?>" <?php echo $selected; ?>>
                                <?php echo esc_html($industry_name); ?> (<?php echo (int)$count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php else: ?>
                <div>
                    <label style="font-weight: 600; margin-right: 0.5rem;">技術で絞り込み:</label>
                    <select id="technology-filter" style="padding: 0.5rem; border: 1px solid var(--color-border); border-radius: var(--border-radius);">
                        <option value="">すべて</option>
                        <?php
                        $technologies = get_terms( array(
                            'taxonomy' => 'technology',
                            'hide_empty' => true,
                        ) );
                        foreach ( $technologies as $tech ) :
                            $selected = isset( $_GET['technology'] ) && $_GET['technology'] == $tech->slug ? 'selected' : '';
                            ?>
                            <option value="<?php echo esc_attr( $tech->slug ); ?>" <?php echo $selected; ?>>
                                <?php echo esc_html( $tech->name ); ?> (<?php echo $tech->count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label style="font-weight: 600; margin-right: 0.5rem;">業界で絞り込み:</label>
                    <select id="industry-filter" style="padding: 0.5rem; border: 1px solid var(--color-border); border-radius: var(--border-radius);">
                        <option value="">すべて</option>
                        <?php
                        $industries = get_terms( array(
                            'taxonomy' => 'industry',
                            'hide_empty' => true,
                        ) );
                        foreach ( $industries as $industry ) :
                            $selected = isset( $_GET['industry'] ) && $_GET['industry'] == $industry->slug ? 'selected' : '';
                            ?>
                            <option value="<?php echo esc_attr( $industry->slug ); ?>" <?php echo $selected; ?>>
                                <?php echo esc_html( $industry->name ); ?> (<?php echo $industry->count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div>
                    <label style="font-weight: 600; margin-right: 0.5rem;">並び順:</label>
                    <select id="sort-order" style="padding: 0.5rem; border: 1px solid var(--color-border); border-radius: var(--border-radius);">
                        <option value="date-desc" <?php selected($orderby, 'date-desc'); ?>>新しい順</option>
                        <option value="date-asc" <?php selected($orderby, 'date-asc'); ?>>古い順</option>
                        <option value="title-asc" <?php selected($orderby, 'title-asc'); ?>>タイトル順</option>
                    </select>
                </div>
                
                <div style="margin-left: auto;">
                    <label for="search-input" class="screen-reader-text">検索</label>
                    <input id="search-input" type="search" placeholder="キーワード検索" value="<?php echo esc_attr($q); ?>" style="padding: 0.5rem; border: 1px solid var(--color-border); border-radius: var(--border-radius);" />
                </div>
            </div>
        </div>
    </section>

    <!-- プロジェクト一覧 -->
    <section class="projects-grid py-5">
        <div class="container">
            <?php if ( ! $use_wp_query_fallback ) : ?>
                <?php
                // 検索・フィルタ
                $projects = array_filter($projects_all, function($p) use ($q, $filter_tech, $filter_industry) {
                    if (!is_array($p)) return false;
                    if ($q !== '') {
                        $hay = array();
                        $hay[] = isset($p['title']) ? (string)$p['title'] : '';
                        $hay[] = isset($p['description']) ? (string)$p['description'] : '';
                        $hay[] = isset($p['impactSummary']) ? (string)$p['impactSummary'] : '';
                        $hay[] = isset($p['industry']) ? (string)$p['industry'] : '';
                        if (!empty($p['technologies']) && is_array($p['technologies'])) {
                            foreach ($p['technologies'] as $t) {
                                $hay[] = is_array($t) && isset($t['name']) ? (string)$t['name'] : (string)$t;
                            }
                        }
                        $matched = false;
                        foreach ($hay as $h) {
                            if ($h !== '' && mb_stripos($h, $q) !== false) { $matched = true; break; }
                        }
                        if (!$matched) return false;
                    }
                    if ($filter_tech !== '') {
                        $hasTech = false;
                        if (!empty($p['technologies']) && is_array($p['technologies'])) {
                            foreach ($p['technologies'] as $t) {
                                $name = is_array($t) && isset($t['name']) ? (string)$t['name'] : (string)$t;
                                if ($name === $filter_tech) { $hasTech = true; break; }
                            }
                        }
                        if (!$hasTech) return false;
                    }
                    if ($filter_industry !== '') {
                        $ind = isset($p['industry']) ? (string)$p['industry'] : '';
                        if ($ind !== $filter_industry) return false;
                    }
                    return true;
                });

                // ソート
                usort($projects, function($a, $b) use ($orderby) {
                    $get_ts = function($p) {
                        $end = isset($p['period']['end']) ? strtotime($p['period']['end']) : null;
                        $start = isset($p['period']['start']) ? strtotime($p['period']['start']) : null;
                        return $end ?: $start ?: 0;
                    };
                    if ($orderby === 'title-asc') {
                        return strcmp( (string)($a['title'] ?? ''), (string)($b['title'] ?? '') );
                    }
                    $ta = $get_ts($a);
                    $tb = $get_ts($b);
                    if ($orderby === 'date-asc') return $ta <=> $tb;
                    return $tb <=> $ta; // date-desc
                });
                ?>
                <?php if (!empty($projects)) : ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($projects as $p) : ?>
                        <article class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                            <!-- ヘッダー（アイコン） -->
                            <div class="h-32 bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                                <i class="ri-folder-3-line text-4xl text-blue-600"></i>
                            </div>

                            <!-- 本文 -->
                            <div class="p-5">
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">
                                    <?php echo esc_html((string)($p['title'] ?? '')); ?>
                                </h3>

                                <?php
                                $period = isset($p['periodCompact']) ? (string)$p['periodCompact'] : '';
                                $industry = isset($p['industry']) ? (string)$p['industry'] : '';
                                $role = isset($p['role']) ? (string)$p['role'] : '';
                                ?>
                                <?php if ($period || $industry || $role) : ?>
                                <div class="text-xs text-gray-600 mb-3">
                                    <?php if ($period) : ?><span>📅 <?php echo esc_html($period); ?></span><?php endif; ?>
                                    <?php if ($industry) : ?><span class="ml-3">🏢 <?php echo esc_html($industry); ?></span><?php endif; ?>
                                    <?php if ($role) : ?><span class="ml-3">👤 <?php echo esc_html($role); ?></span><?php endif; ?>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($p['description'])) : ?>
                                <p class="text-sm text-gray-700 leading-relaxed mb-4">
                                    <?php echo esc_html( wp_trim_words( (string)$p['description'], 28 ) ); ?>
                                </p>
                                <?php endif; ?>

                                <?php
                                $techs = array();
                                if (!empty($p['technologies']) && is_array($p['technologies'])) {
                                    foreach ($p['technologies'] as $t) {
                                        $techs[] = is_array($t) && isset($t['name']) ? (string)$t['name'] : (string)$t;
                                    }
                                }
                                ?>
                                <?php if (!empty($techs)) : ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach ( array_slice($techs, 0, 5) as $tech_name ) : ?>
                                        <span class="px-2.5 py-1 bg-gray-100 text-gray-800 text-xs rounded-full border border-gray-200">
                                            <?php echo esc_html($tech_name); ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <?php if ( count($techs) > 5 ) : ?>
                                        <span class="px-2.5 py-1 bg-gray-100 text-gray-800 text-xs rounded-full border border-gray-200">+<?php echo count($techs) - 5; ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="no-results text-center py-5">
                    <div style="font-size: 5rem; opacity: 0.3; margin-bottom: 1rem;">📂</div>
                    <h2>プロジェクトが見つかりません</h2>
                    <p style="color: var(--color-text-secondary); margin: 1rem 0;">
                        現在、表示できるプロジェクトがありません。
                    </p>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">
                        トップページへ戻る
                    </a>
                </div>
                <?php endif; ?>
                
            <?php else : ?>
                <?php if ( have_posts() ) : ?>
                <div class="grid grid-3">
                    <?php while ( have_posts() ) : the_post(); ?>
                        <article class="project-card fade-in">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'project-thumbnail', array( 'class' => 'project-card-image' ) ); ?>
                                </a>
                            <?php else : ?>
                                <a href="<?php the_permalink(); ?>">
                                    <div class="project-card-image" style="background: linear-gradient(135deg, var(--color-primary-light), var(--color-secondary-light)); height: 200px; display: flex; align-items: center; justify-content: center;">
                                        <span style="color: white; font-size: 3rem; opacity: 0.5;">📁</span>
                                    </div>
                                </a>
                            <?php endif; ?>
                            
                            <div class="project-card-content">
                                <h2 class="project-card-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                
                                <?php
                                $project_period = function_exists('get_field') ? get_field( 'project_period' ) : '';
                                $project_role = function_exists('get_field') ? get_field( 'project_role' ) : '';
                                if ( $project_period || $project_role ) : ?>
                                    <div style="font-size: 0.875rem; color: var(--color-text-secondary); margin-bottom: 0.5rem;">
                                        <?php if ( $project_period ) : ?>
                                            <span>📅 <?php echo esc_html( $project_period ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $project_role ) : ?>
                                            <span style="margin-left: 1rem;">👤 <?php echo esc_html( $project_role ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <p class="project-card-description">
                                    <?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 30 ) ); ?>
                                </p>
                                
                                <?php
                                $technologies = get_the_terms( get_the_ID(), 'technology' );
                                if ( $technologies && ! is_wp_error( $technologies ) ) : ?>
                                    <div class="project-card-tags">
                                        <?php foreach ( array_slice( $technologies, 0, 3 ) as $tech ) : ?>
                                            <span class="project-tag"><?php echo esc_html( $tech->name ); ?></span>
                                        <?php endforeach; ?>
                                        <?php if ( count( $technologies ) > 3 ) : ?>
                                            <span class="project-tag">+<?php echo count( $technologies ) - 3; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php
                                $industries = get_the_terms( get_the_ID(), 'industry' );
                                if ( $industries && ! is_wp_error( $industries ) ) : ?>
                                    <div style="margin-top: 0.5rem;">
                                        <?php foreach ( $industries as $industry ) : ?>
                                            <span style="font-size: 0.75rem; color: var(--color-text-secondary);">
                                                🏢 <?php echo esc_html( $industry->name ); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-primary">
                                        詳細を見る →
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
                
                <div class="pagination-wrapper" style="margin-top: 3rem;">
                    <?php
                    the_posts_pagination( array(
                        'mid_size'  => 2,
                        'prev_text' => '← 前へ',
                        'next_text' => '次へ →',
                    ) );
                    ?>
                </div>
                <?php else : ?>
                <div class="no-results text-center py-5">
                    <div style="font-size: 5rem; opacity: 0.3; margin-bottom: 1rem;">📂</div>
                    <h2>プロジェクトが見つかりません</h2>
                    <p style="color: var(--color-text-secondary); margin: 1rem 0;">
                        現在、表示できるプロジェクトがありません。
                    </p>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary">
                        トップページへ戻る
                    </a>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- 統計情報 -->
    <!-- <section class="project-stats py-5" style="background-color: var(--color-background-alt);">
        <div class="container">
            <h2 class="text-center mb-5">プロジェクト統計</h2>
            <div class="grid grid-4">
                <div class="text-center">
                    <div style="font-size: 3rem; font-weight: 700; color: var(--color-primary-main);">
                        <?php echo ! $use_wp_query_fallback && is_array($projects_all) ? (int)count($projects_all) : (int)wp_count_posts( 'project' )->publish; ?>
                    </div>
                    <p>総プロジェクト数</p>
                </div>
                <?php if ( ! $use_wp_query_fallback ) : ?>
                    <div class="text-center">
                        <div style="font-size: 3rem; font-weight: 700; color: var(--color-secondary-main);">
                            <?php echo (int)count($tech_counts); ?>
                        </div>
                        <p>使用技術数</p>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 3rem; font-weight: 700; color: var(--color-accent-main);">
                            <?php echo (int)count($industry_counts); ?>
                        </div>
                        <p>対応業界数</p>
                    </div>
                <?php else : ?>
                    <div class="text-center">
                        <div style="font-size: 3rem; font-weight: 700; color: var(--color-secondary-main);">
                            <?php echo count( get_terms( 'technology' ) ); ?>
                        </div>
                        <p>使用技術数</p>
                    </div>
                    <div class="text-center">
                        <div style="font-size: 3rem; font-weight: 700; color: var(--color-accent-main);">
                            <?php echo count( get_terms( 'industry' ) ); ?>
                        </div>
                        <p>対応業界数</p>
                    </div>
                <?php endif; ?>
                <div class="text-center">
                    <div style="font-size: 3rem; font-weight: 700; color: var(--color-primary-main);">
                        100%
                    </div>
                    <p>完了率</p>
                </div>
            </div>
        </div> 
    </section>-->

    <!-- CTA -->
    <!-- <section class="cta py-5" style="background: linear-gradient(135deg, var(--color-primary-main), var(--color-secondary-main)); color: white;">
        <div class="container text-center">
            <h2 style="color: white;">あなたのプロジェクトもお手伝いします</h2>
            <p style="font-size: 1.125rem; margin: 1.5rem 0; opacity: 0.95;">
                アイデア段階から運用まで、トータルでサポートいたします
            </p>
            <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="btn" style="background: white; color: var(--color-primary-main);">
                プロジェクトの相談をする
            </a>
        </div>
    </section> -->

</main>

<script>
// フィルター機能
document.addEventListener('DOMContentLoaded', function() {
    const techFilter = document.getElementById('technology-filter');
    const industryFilter = document.getElementById('industry-filter');
    const sortOrder = document.getElementById('sort-order');
    const searchInput = document.getElementById('search-input');
    
    function applyFilters() {
        const params = new URLSearchParams();
        
        if (techFilter.value) params.set('technology', techFilter.value);
        if (industryFilter.value) params.set('industry', industryFilter.value);
        if (sortOrder.value !== 'date-desc') params.set('orderby', sortOrder.value);
        if (searchInput && searchInput.value) params.set('s', searchInput.value);
        
        const queryString = params.toString();
        const newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
        window.location.href = newUrl;
    }
    
    if (techFilter) techFilter.addEventListener('change', applyFilters);
    if (industryFilter) industryFilter.addEventListener('change', applyFilters);
    if (sortOrder) sortOrder.addEventListener('change', applyFilters);
    if (searchInput) searchInput.addEventListener('keydown', function(e){ if (e.key === 'Enter') applyFilters(); });
});
</script>

<style>
/* アーカイブページ専用スタイル */
.pagination-wrapper {
    display: flex;
    justify-content: center;
}

.pagination-wrapper .page-numbers {
    display: inline-block;
    padding: 0.5rem 1rem;
    margin: 0 0.25rem;
    border: 1px solid var(--color-border);
    border-radius: var(--border-radius);
    color: var(--color-text-primary);
    text-decoration: none;
    transition: var(--transition-base);
}

.pagination-wrapper .page-numbers:hover {
    background: var(--color-primary-main);
    color: white;
    border-color: var(--color-primary-main);
}

.pagination-wrapper .current {
    background: var(--color-primary-main);
    color: white;
    border-color: var(--color-primary-main);
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}
</style>

<?php get_footer(); ?>
