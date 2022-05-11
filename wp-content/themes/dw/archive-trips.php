<main class="layout">
    <section class="layout__trips trips">
        <h2 class="trips__title"> <?= __('Tous mes voyages', 'dw') ?></h2>
        <nav class="trips__filters">
            <h3 class="sro"><?= __('Filtrer les résultats', 'dw'); ?></h3>
            <?php foreach (get_terms(['taxonomy' => 'country', 'hide_empty' => true]) as $country): ?>
                <a href="?country=<?= $country->slug ?>"><?= $country->name ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="trips__container">
            <!--debut de la boucle-->
            <?php if (have_posts()): while(have_posts()): the_post();
                dw_include('trip', ['modifier' => 'index']);
            endwhile; else: ?>
                <p class="trips__empty"> <?= __('Il n\'y a pas de voyages', 'dw') ?></p>
            <?php endif; ?>
        </div>
    </section>
</main>