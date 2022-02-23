<?php
    /*
    * Template Name: Contact Page Template
    */
?>

<?php get_header(); ?>
<?php if (have_posts()): while (have_posts()): the_post(); ?>
    <main class="layout contact">
        <h2 class="layout__title"><?= get_the_title() ?></h2>
        <div class="contact__content">
            <?= get_the_content() ?>
        </div>
        <div class="contact__form">
            <?= apply_filters('the_content', '[contact-form-7 id="40" title="formulaire de contact"]'); ?>
        </div>
    </main>
<?php endwhile; endif; ?>
<?php get_footer(); ?>