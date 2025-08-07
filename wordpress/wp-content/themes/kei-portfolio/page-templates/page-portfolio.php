<?php
/**
 * Template Name: Portfolio Page
 * 
 * Portfolio page template showing featured projects and technical approach
 * Converted from React: app/portfolio/page.tsx
 */

get_header(); ?>

<main class="min-h-screen">
    <?php 
    // Portfolio Hero Section
    get_template_part('template-parts/portfolio/portfolio-hero');
    
    // Featured Projects Section
    get_template_part('template-parts/portfolio/featured-projects');
    
    // All Projects Section (will be converted in next group)
    // get_template_part('template-parts/portfolio/all-projects');
    
    // Technical Approach Section (will be converted in next group)
    // get_template_part('template-parts/portfolio/technical-approach');
    ?>
</main>

<?php get_footer(); ?>