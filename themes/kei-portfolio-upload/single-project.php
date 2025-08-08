<?php
/**
 * 実績詳細テンプレート
 *
 * @package Kei_Portfolio_Pro
 */

get_header(); ?>

<main id="main" class="site-main">

<?php while ( have_posts() ) : the_post(); ?>

    <!-- プロジェクトヘッダー -->
    <section class="project-header" style="background: linear-gradient(135deg, var(--color-primary-light), var(--color-secondary-light)); color: white; padding: 4rem 0;">
        <div class="container">
            <div class="project-header-content">
                <h1 style="color: white; font-size: 2.5rem; margin-bottom: 1rem;"><?php the_title(); ?></h1>
                
                <div style="display: flex; gap: 2rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
                    <?php
                    $project_period = get_field( 'project_period' );
                    $project_role = get_field( 'project_role' );
                    $team_size = get_field( 'team_size' );
                    ?>
                    
                    <?php if ( $project_period ) : ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.25rem;">📅</span>
                            <span>期間: <?php echo esc_html( $project_period ); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $project_role ) : ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.25rem;">👤</span>
                            <span>役割: <?php echo esc_html( $project_role ); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $team_size ) : ?>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.25rem;">👥</span>
                            <span>チーム規模: <?php echo esc_html( $team_size ); ?>名</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- 技術タグ -->
                <?php
                $technologies = get_the_terms( get_the_ID(), 'technology' );
                if ( $technologies && ! is_wp_error( $technologies ) ) : ?>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <?php foreach ( $technologies as $tech ) : ?>
                            <span style="display: inline-block; padding: 0.5rem 1rem; background: rgba(255,255,255,0.2); border-radius: 9999px; font-size: 0.875rem;">
                                <?php echo esc_html( $tech->name ); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- プロジェクト詳細 -->
    <article class="project-detail py-5">
        <div class="container">
            <div style="max-width: 900px; margin: 0 auto;">
                
                <!-- アイキャッチ画像 -->
                <?php if ( has_post_thumbnail() ) : ?>
                    <div style="margin-bottom: 3rem; border-radius: var(--border-radius-lg); overflow: hidden; box-shadow: var(--shadow-lg);">
                        <?php the_post_thumbnail( 'project-large', array( 'style' => 'width: 100%; height: auto;' ) ); ?>
                    </div>
                <?php endif; ?>
                
                <!-- プロジェクト概要 -->
                <section class="project-overview mb-5">
                    <h2>プロジェクト概要</h2>
                    <div class="card">
                        <?php the_content(); ?>
                    </div>
                </section>
                
                <!-- 課題と解決策 -->
                <?php
                $challenges = get_field( 'project_challenges' );
                $solutions = get_field( 'project_solutions' );
                if ( $challenges || $solutions ) : ?>
                    <section class="challenges-solutions mb-5">
                        <h2>課題と解決策</h2>
                        <div class="grid grid-2" style="gap: 2rem;">
                            <?php if ( $challenges ) : ?>
                                <div class="card" style="border-left: 4px solid var(--color-accent-main);">
                                    <h3 style="color: var(--color-accent-main);">🔍 課題</h3>
                                    <div><?php echo wp_kses_post( $challenges ); ?></div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ( $solutions ) : ?>
                                <div class="card" style="border-left: 4px solid var(--color-secondary-main);">
                                    <h3 style="color: var(--color-secondary-main);">💡 解決策</h3>
                                    <div><?php echo wp_kses_post( $solutions ); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                <?php endif; ?>
                
                <!-- 技術詳細 -->
                <?php
                $tech_details = get_field( 'technical_details' );
                if ( $tech_details ) : ?>
                    <section class="technical-details mb-5">
                        <h2>技術的なポイント</h2>
                        <div class="card">
                            <?php echo wp_kses_post( $tech_details ); ?>
                        </div>
                    </section>
                <?php endif; ?>
                
                <!-- 使用技術スタック詳細 -->
                <section class="tech-stack-detail mb-5">
                    <h2>使用技術スタック</h2>
                    <div class="card">
                        <?php
                        $tech_categories = array(
                            'frontend' => 'フロントエンド',
                            'backend' => 'バックエンド',
                            'database' => 'データベース',
                            'infrastructure' => 'インフラ',
                            'tools' => 'ツール・その他'
                        );
                        
                        foreach ( $tech_categories as $key => $label ) :
                            $techs = get_field( 'tech_' . $key );
                            if ( $techs ) : ?>
                                <div style="margin-bottom: 2rem;">
                                    <h4 style="color: var(--color-primary-main); margin-bottom: 1rem;"><?php echo $label; ?></h4>
                                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                        <?php foreach ( explode( ',', $techs ) as $tech ) : ?>
                                            <span class="tech-badge"><?php echo esc_html( trim( $tech ) ); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif;
                        endforeach; ?>
                        
                        <?php if ( $technologies ) : ?>
                            <div style="margin-bottom: 2rem;">
                                <h4 style="color: var(--color-primary-main); margin-bottom: 1rem;">主要技術</h4>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                    <?php foreach ( $technologies as $tech ) : ?>
                                        <span class="tech-badge"><?php echo esc_html( $tech->name ); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>
                
                <!-- 成果・数値 -->
                <?php
                $achievements = get_field( 'project_achievements' );
                if ( $achievements ) : ?>
                    <section class="achievements mb-5">
                        <h2>成果・効果</h2>
                        <div class="card" style="background: linear-gradient(135deg, var(--color-primary-light), var(--color-secondary-light)); color: white;">
                            <div style="font-size: 1.125rem;">
                                <?php echo wp_kses_post( $achievements ); ?>
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
                
                <!-- クライアントの声 -->
                <?php
                $testimonial = get_field( 'client_testimonial' );
                if ( $testimonial ) : ?>
                    <section class="testimonial mb-5">
                        <h2>クライアントの声</h2>
                        <div class="card" style="border-left: 4px solid var(--color-primary-main); font-style: italic;">
                            <div style="font-size: 1.125rem; line-height: 1.8;">
                                "<?php echo wp_kses_post( $testimonial ); ?>"
                            </div>
                        </div>
                    </section>
                <?php endif; ?>
                
                <!-- GitHubリンク -->
                <?php
                $github_url = get_field( 'github_url' );
                if ( $github_url ) : ?>
                    <section class="github-link mb-5">
                        <div class="card text-center">
                            <h3>ソースコード</h3>
                            <p>このプロジェクトのソースコードはGitHubで公開しています。</p>
                            <a href="<?php echo esc_url( $github_url ); ?>" target="_blank" rel="noopener" class="btn btn-primary">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-right: 0.5rem;">
                                    <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                                </svg>
                                GitHubで見る
                            </a>
                        </div>
                    </section>
                <?php endif; ?>
                
            </div>
        </div>
    </article>
    
    <!-- 関連プロジェクト -->
    <section class="related-projects py-5" style="background-color: var(--color-background-alt);">
        <div class="container">
            <h2 class="text-center mb-5">関連プロジェクト</h2>
            
            <?php
            // 同じ技術を使用している他のプロジェクトを取得
            $tech_ids = wp_get_post_terms( get_the_ID(), 'technology', array( 'fields' => 'ids' ) );
            
            $related_projects = new WP_Query( array(
                'post_type'      => 'project',
                'posts_per_page' => 3,
                'post__not_in'   => array( get_the_ID() ),
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'technology',
                        'field'    => 'term_id',
                        'terms'    => $tech_ids,
                    ),
                ),
            ) );
            
            if ( $related_projects->have_posts() ) : ?>
                <div class="grid grid-3">
                    <?php while ( $related_projects->have_posts() ) : $related_projects->the_post(); ?>
                        <article class="project-card">
                            <?php if ( has_post_thumbnail() ) : ?>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail( 'project-thumbnail', array( 'class' => 'project-card-image' ) ); ?>
                                </a>
                            <?php else : ?>
                                <div class="project-card-image" style="background: linear-gradient(135deg, var(--color-primary-light), var(--color-secondary-light)); height: 200px;"></div>
                            <?php endif; ?>
                            
                            <div class="project-card-content">
                                <h3 class="project-card-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h3>
                                <p class="project-card-description">
                                    <?php echo wp_trim_words( get_the_excerpt(), 20 ); ?>
                                </p>
                                <a href="<?php the_permalink(); ?>" class="btn btn-sm btn-primary">
                                    詳細を見る →
                                </a>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>
            <?php else : ?>
                <p class="text-center">関連するプロジェクトはありません。</p>
            <?php endif;
            wp_reset_postdata();
            ?>
            
            <div class="text-center mt-5">
                <a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>" class="btn btn-outline">
                    すべての実績を見る
                </a>
            </div>
        </div>
    </section>
    
    <!-- ナビゲーション -->
    <section class="project-navigation py-3">
        <div class="container">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <?php
                $prev_post = get_previous_post();
                $next_post = get_next_post();
                ?>
                
                <?php if ( $prev_post ) : ?>
                    <a href="<?php echo get_permalink( $prev_post->ID ); ?>" style="display: flex; align-items: center; gap: 0.5rem;">
                        ← <?php echo esc_html( $prev_post->post_title ); ?>
                    </a>
                <?php else : ?>
                    <span></span>
                <?php endif; ?>
                
                <?php if ( $next_post ) : ?>
                    <a href="<?php echo get_permalink( $next_post->ID ); ?>" style="display: flex; align-items: center; gap: 0.5rem;">
                        <?php echo esc_html( $next_post->post_title ); ?> →
                    </a>
                <?php else : ?>
                    <span></span>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php endwhile; ?>

</main>

<style>
/* 実績詳細ページ専用スタイル */
.tech-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: var(--color-background-alt);
    border: 1px solid var(--color-border);
    border-radius: 9999px;
    font-size: 0.875rem;
    color: var(--color-text-primary);
}

.tech-badge:hover {
    background: var(--color-primary-main);
    color: white;
    border-color: var(--color-primary-main);
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

.project-detail h2 {
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--color-primary-main);
}

.project-detail h3 {
    margin-bottom: 1rem;
}

.project-detail ul,
.project-detail ol {
    margin-left: 2rem;
    margin-bottom: 1rem;
}

.project-detail li {
    margin-bottom: 0.5rem;
}
</style>

<?php get_footer(); ?>