<?php
/**
 * Template Name: About Me
 * Template Post Type: page
 *
 * @package Kei_Portfolio_Pro
 */

get_header(); ?>

<main id="main" class="site-main">
    
    <!-- ページヘッダー -->
    <section class="page-header" style="background: linear-gradient(135deg, var(--color-primary-light), var(--color-secondary-light)); color: white; padding: 4rem 0;">
        <div class="container text-center">
            <h1 style="color: white;">自己紹介</h1>
            <p style="font-size: 1.25rem; opacity: 0.95;">エンジニアとしての歩みと、価値観</p>
        </div>
    </section>

    <!-- プロフィール -->
    <section class="profile py-5">
        <div class="container">
            <div class="grid grid-2" style="align-items: center; gap: 4rem;">
                <div class="text-center">
                    <div style="width: 300px; height: 300px; margin: 0 auto; border-radius: 50%; overflow: hidden; box-shadow: var(--shadow-lg);">
                        <?php 
                        $profile_image = get_field('profile_image'); // ACFを使用する場合
                        if ( $profile_image ) : ?>
                            <img src="<?php echo esc_url( $profile_image['url'] ); ?>" alt="<?php echo esc_attr( $profile_image['alt'] ); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else : ?>
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--color-primary-main), var(--color-secondary-main)); display: flex; align-items: center; justify-content: center;">
                                <span style="color: white; font-size: 4rem; font-weight: 700;">KA</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h2 class="mt-3">青木 圭（Kei Aokiki）</h2>
                    <p style="color: var(--color-text-secondary); font-size: 1.125rem;">フルスタックエンジニア</p>
                    
                    <!-- SNSリンク -->
                    <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
                        <a href="https://github.com/kei-aokiki" target="_blank" rel="noopener" class="social-link">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z"/>
                            </svg>
                        </a>
                        <a href="https://linkedin.com/in/kei-aokiki" target="_blank" rel="noopener" class="social-link">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        <a href="https://twitter.com/kei_aokiki" target="_blank" rel="noopener" class="social-link">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h2>10年以上の経験を持つフルスタックエンジニア</h2>
                    <p style="font-size: 1.125rem; line-height: 1.8; color: var(--color-text-secondary);">
                        主にJava/SQLをベースとした業務システム開発に従事してきました。
                        フロントエンドからバックエンドまでの幅広い技術スタックを活用し、
                        レガシーシステムからクラウド環境まで対応しています。
                    </p>
                    <p style="font-size: 1.125rem; line-height: 1.8; color: var(--color-text-secondary);">
                        直近では通信会社向けWebサイト開発のリーダーとして、
                        Spring Boot移行後の技術文書整備やプロセス改善を推進し、
                        チーム全体の生産性向上に貢献しました。
                    </p>
                    
                    <div class="mt-4">
                        <h3>基本情報</h3>
                        <table style="width: 100%; margin-top: 1rem;">
                            <tr>
                                <td style="padding: 0.5rem 0; font-weight: 600; width: 40%;">経験年数</td>
                                <td style="padding: 0.5rem 0;">10年以上</td>
                            </tr>
                            <tr>
                                <td style="padding: 0.5rem 0; font-weight: 600;">専門分野</td>
                                <td style="padding: 0.5rem 0;">Java/Spring Boot、フルスタック開発</td>
                            </tr>
                            <tr>
                                <td style="padding: 0.5rem 0; font-weight: 600;">得意領域</td>
                                <td style="padding: 0.5rem 0;">レガシーシステムモダン化、チーム開発</td>
                            </tr>
                            <tr>
                                <td style="padding: 0.5rem 0; font-weight: 600;">保有資格</td>
                                <td style="padding: 0.5rem 0;">基本情報技術者、AWS認定ソリューションアーキテクト</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 経歴タイムライン -->
    <section class="career-timeline py-5" style="background-color: var(--color-background-alt);">
        <div class="container">
            <h2 class="text-center mb-5">エンジニアとしての歩み</h2>
            
            <div class="timeline" style="max-width: 800px; margin: 0 auto;">
                <div class="timeline-item">
                    <div class="timeline-date">2015年</div>
                    <div class="timeline-content card">
                        <h3>キャリアスタート</h3>
                        <p>SIerに新卒入社。Java/JavaEEを使用した業務システム開発からキャリアをスタート。</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-date">2016-2017年</div>
                    <div class="timeline-content card">
                        <h3>レガシーシステムモダン化</h3>
                        <p>COBOLシステムのJava移行プロジェクトに参画。レガシーシステムの分析と新システムの要件定義から実装まで担当。</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-date">2018-2019年</div>
                    <div class="timeline-content card">
                        <h3>フルスタック開発</h3>
                        <p>JavaScript/Vue.jsを習得し、フロントエンドからバックエンドまで一貫した開発を経験。</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-date">2020-2021年</div>
                    <div class="timeline-content card">
                        <h3>チームリーダー</h3>
                        <p>バッチ機能改修プロジェクトでリーダーを務め、他チームとの調整を含めた単独でのプロジェクト推進を経験。</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-date">2022-2023年</div>
                    <div class="timeline-content card">
                        <h3>技術リード</h3>
                        <p>Spring Boot移行プロジェクトで技術リードとして、アーキテクチャ設計と若手エンジニアの教育を担当。</p>
                    </div>
                </div>
                
                <div class="timeline-item">
                    <div class="timeline-date">2024年〜現在</div>
                    <div class="timeline-content card">
                        <h3>開発リーダー</h3>
                        <p>通信会社向けWebサイト開発で開発リーダーとして、技術文書整備とナレッジ共有体制の確立に貢献。</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 価値観 -->
    <section class="values py-5">
        <div class="container">
            <h2 class="text-center mb-5">大切にしている価値観</h2>
            
            <div class="grid grid-3">
                <div class="card text-center">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎯</div>
                    <h3>確実性最優先</h3>
                    <p>時間効率よりも確実性を常に優先し、品質の高いコードを提供します。</p>
                </div>
                
                <div class="card text-center">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🤝</div>
                    <h3>チームワーク</h3>
                    <p>個人の成果よりもチーム全体の成功を重視し、ナレッジ共有を積極的に行います。</p>
                </div>
                
                <div class="card text-center">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">📚</div>
                    <h3>継続的な学習</h3>
                    <p>技術の進化に対応するため、常に新しい技術を学び続けています。</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta py-5" style="background: linear-gradient(135deg, var(--color-primary-main), var(--color-secondary-main)); color: white;">
        <div class="container text-center">
            <h2 style="color: white;">一緒に価値あるプロダクトを作りませんか？</h2>
            <p style="font-size: 1.125rem; margin: 1.5rem 0; opacity: 0.95;">
                技術的な課題解決から新規開発まで、幅広くサポートいたします
            </p>
            <a href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" class="btn" style="background: white; color: var(--color-primary-main);">
                お問い合わせはこちら
            </a>
        </div>
    </section>

</main>

<style>
/* About ページ専用スタイル */
.social-link {
    display: inline-block;
    width: 48px;
    height: 48px;
    padding: 12px;
    background: var(--color-background-alt);
    border-radius: 50%;
    color: var(--color-text-primary);
    transition: var(--transition-base);
}

.social-link:hover {
    background: var(--color-primary-main);
    color: white;
    transform: translateY(-2px);
}

.timeline {
    position: relative;
    padding: 2rem 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 50px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--color-border);
}

.timeline-item {
    position: relative;
    padding-left: 100px;
    margin-bottom: 3rem;
}

.timeline-date {
    position: absolute;
    left: 0;
    top: 0;
    width: 80px;
    text-align: right;
    font-weight: 700;
    color: var(--color-primary-main);
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 44px;
    top: 8px;
    width: 12px;
    height: 12px;
    background: var(--color-primary-main);
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 0 0 2px var(--color-primary-main);
}

@media (max-width: 768px) {
    .timeline::before {
        left: 30px;
    }
    
    .timeline-item {
        padding-left: 60px;
    }
    
    .timeline-date {
        width: auto;
        position: static;
        margin-bottom: 0.5rem;
    }
    
    .timeline-item::before {
        left: 24px;
    }
}
</style>

<?php get_footer(); ?>