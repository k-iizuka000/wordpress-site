<?php
/**
 * 実績一覧アーカイブテンプレート
 *
 * @package Kei_Portfolio_Pro
 */

get_header(); ?>

<main id="main" class="site-main">
    
    <!-- ページヘッダー -->
    <section class="page-header" style="background: linear-gradient(135deg, var(--color-primary-light), var(--color-secondary-light)); color: white; padding: 4rem 0;">
        <div class="container text-center">
            <h1 style="color: white;">実績一覧</h1>
            <p style="font-size: 1.25rem; opacity: 0.95;">これまでに開発したプロジェクトをご紹介します</p>
        </div>
    </section>

    <!-- フィルター -->
    <section class="project-filters py-3" style="background-color: var(--color-background-alt); border-bottom: 1px solid var(--color-border);">
        <div class="container">
            <div style="display: flex; gap: 2rem; align-items: center; flex-wrap: wrap;">
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
                
                <div>
                    <label style="font-weight: 600; margin-right: 0.5rem;">並び順:</label>
                    <select id="sort-order" style="padding: 0.5rem; border: 1px solid var(--color-border); border-radius: var(--border-radius);">
                        <option value="date-desc">新しい順</option>
                        <option value="date-asc">古い順</option>
                        <option value="title-asc">タイトル順</option>
                    </select>
                </div>
            </div>
        </div>
    </section>

    <!-- プロジェクト一覧 -->
    <section class="projects-grid py-5">
        <div class="container">
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
                                
                                <!-- カスタムフィールド（期間・役割） -->
                                <?php
                                $project_period = get_field( 'project_period' ); // ACF使用時
                                $project_role = get_field( 'project_role' );
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
                                    <?php echo wp_trim_words( get_the_excerpt(), 30 ); ?>
                                </p>
                                
                                <!-- 技術タグ -->
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
                                
                                <!-- 業界 -->
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
                
                <!-- ページネーション -->
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
        </div>
    </section>

    <!-- 統計情報 -->
    <section class="project-stats py-5" style="background-color: var(--color-background-alt);">
        <div class="container">
            <h2 class="text-center mb-5">プロジェクト統計</h2>
            <div class="grid grid-4">
                <div class="text-center">
                    <div style="font-size: 3rem; font-weight: 700; color: var(--color-primary-main);">
                        <?php echo wp_count_posts( 'project' )->publish; ?>
                    </div>
                    <p>総プロジェクト数</p>
                </div>
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
                <div class="text-center">
                    <div style="font-size: 3rem; font-weight: 700; color: var(--color-primary-main);">
                        100%
                    </div>
                    <p>完了率</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta py-5" style="background: linear-gradient(135deg, var(--color-primary-main), var(--color-secondary-main)); color: white;">
        <div class="container text-center">
            <h2 style="color: white;">あなたのプロジェクトもお手伝いします</h2>
            <p style="font-size: 1.125rem; margin: 1.5rem 0; opacity: 0.95;">
                アイデア段階から運用まで、トータルでサポートいたします
            </p>
            <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="btn" style="background: white; color: var(--color-primary-main);">
                プロジェクトの相談をする
            </a>
        </div>
    </section>

</main>

<script>
// フィルター機能
document.addEventListener('DOMContentLoaded', function() {
    const techFilter = document.getElementById('technology-filter');
    const industryFilter = document.getElementById('industry-filter');
    const sortOrder = document.getElementById('sort-order');
    
    function applyFilters() {
        const params = new URLSearchParams();
        
        if (techFilter.value) params.set('technology', techFilter.value);
        if (industryFilter.value) params.set('industry', industryFilter.value);
        if (sortOrder.value !== 'date-desc') params.set('orderby', sortOrder.value);
        
        const queryString = params.toString();
        const newUrl = window.location.pathname + (queryString ? '?' + queryString : '');
        window.location.href = newUrl;
    }
    
    if (techFilter) techFilter.addEventListener('change', applyFilters);
    if (industryFilter) industryFilter.addEventListener('change', applyFilters);
    if (sortOrder) sortOrder.addEventListener('change', applyFilters);
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