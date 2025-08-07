<?php
/*
Template Name: Contact Page
*/

get_header(); ?>

<main class="min-h-screen">
    <?php get_template_part('template-parts/contact/hero'); ?>
    <?php get_template_part('template-parts/contact/contact-form'); ?>
    <?php get_template_part('template-parts/contact/contact-info'); ?>
</main>

<?php get_footer(); ?>