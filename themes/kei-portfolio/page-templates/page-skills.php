<?php
/**
 * Template Name: Skills Page
 * 
 * Skills page template displaying technical skills and programming languages
 * 
 * @package kei-portfolio
 */

get_header(); ?>

<main class="min-h-screen">
    <?php 
    // Include Skills Hero section
    get_template_part('template-parts/skills/skills-hero');
    
    // Include Programming Languages section
    get_template_part('template-parts/skills/programming-languages');
    
    // Include Frameworks & Tools section (to be converted in group 8)
    // get_template_part('template-parts/skills/frameworks-tools');
    
    // Include Specialized Skills section (to be converted in group 8)  
    // get_template_part('template-parts/skills/specialized-skills');
    
    // Include Learning Approach section (to be converted in group 8)
    // get_template_part('template-parts/skills/learning-approach');
    ?>
</main>

<?php get_footer(); ?>