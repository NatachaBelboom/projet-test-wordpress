<?php
/*
* Template Name: About Page Template
*/
?>
<?php get_header(); ?>
<?php if (have_posts()): while (have_posts()): the_post(); ?>
    <main class="layout about">
        <h2 class="layout__title"><?= get_the_title() ?></h2>
        <div class="about__content">
            <?= get_the_content() ?>
        </div>
    </main>
<?php endwhile; endif; ?>
<?php get_footer(); ?>