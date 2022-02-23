<?php get_header(); ?>
<?php if (have_posts()): while (have_posts()): the_post(); ?>
    <main class="layout singlePost">
        <h2 class="layout__title"><?= get_the_title() ?></h2>
        <figure class="singlePost__fig">
            <?= get_the_post_thumbnail(null, 'medium', ['class' => 'post__thumb']) ?>
        </figure>
        <div class="singlePost__content">
            <?= get_the_content() ?>
        </div>
    </main>
<?php endwhile; endif; ?>
<?php get_footer(); ?>