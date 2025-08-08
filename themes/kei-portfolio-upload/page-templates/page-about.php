<?php
/**
 * Template Name: About Page
 * 
 * About ページ用テンプレート
 * app/about/page.tsx から変換
 */

get_header(); ?>

<main class="min-h-screen">
    <?php
    // AboutHero コンポーネント
    get_template_part('template-parts/about/hero');
    
    // PersonalitySection コンポーネント
    get_template_part('template-parts/about/personality');
    
    // EngineerHistory コンポーネント
    get_template_part('template-parts/about/engineer-history');
    
    // CyclingPassion コンポーネント
    get_template_part('template-parts/about/cycling-passion');
    ?>
</main>

<?php get_footer(); ?>