<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<header class="header">
    <h1 class="header__title"><?= get_bloginfo('name') ?></h1>
    <p class="header__tagline"><?= get_bloginfo('description') ?></p>

    <nav class="header__nav nav">
        <h2 class="nav__title">
            Navigation principale
        </h2>
        <?php /*wp_nav_menu([
            'theme_location' => 'primary',
            'menu_class' => 'nav__links',
            'container_class' => 'nav__container',
            'walker' => new PrimaryMenuWalker(),
        ]) */?>
        <ul class="nav__container">
            <?php foreach (dw_get_menu_items('primary') as $link): ?>  <!--la fonction renvoie un tableau d'item-->
                <li class="<?= $link->getBemClasses('nav__item') ?>">
                    <a href="<?= $link->url ?>" class="nav__link"><?= $link->label ?></a>
                    <?php if($link->hasSubItems()): ?>
                        <ul class="nav__subitems">
                            <?php foreach ($link->subitems as $sub): ?>
                                <li class="<?= $link->getBemClasses('nav__subitem') ?>">
                                    <a href="<?= $sub->url ?>" class="nav__link"><?= $sub->label ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
</header>
