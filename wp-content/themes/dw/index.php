<?php get_header(); ?>

    <main class="layout">
        <section class="layout__intro">
            <h2 class="layout__title">
                Introduction
            </h2>
            <p class="layout__txt">Bienvenue sur mon super site.</p>
        </section>

        <section class="layout__latest latest">
            <h2 class="latest__title">Nos dernières nouvelles</h2>
            <div class="latest__container">
                <?php if (have_posts()): while (have_posts()): the_post();
                    dw_include('post', ['modifier' => 'index']);
                endwhile; else: ?>
                    <!-- si je n'ai pas d'article, un message qui le signale-->
                    <p>Il n' a pas de post</p>
                <?php endif; ?>
            </div>
        </section>
        
        <section class="layout__trips trips">
            <h2 class="trips__title">Mes derniers voyages</h2>
            <div class="trips__container">
                <!--debut de la boucle-->
                <?php if (($trips = dw_get_trips(3))->have_posts()): while($trips->have_posts()): $trips->the_post();
                    dw_include('trip', ['modifier' => 'index']);
                endwhile; else: ?>
                <p class="trips__empty">Il n'y a pas de voyage a raconter</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

<?php get_footer(); ?>