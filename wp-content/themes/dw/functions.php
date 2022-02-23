<?php

// Désactiver l'éditeur "Gutenberg" de Wordpress
add_filter('use_block_editor_for_post', '__return_false');

// Activer les images sur les articles
add_theme_support('post-thumbnails');

// Enregistrer un "type de ressource" (custom post type) pour les voyage
register_post_type('trips', [
   'label' => 'Voyages',
   'labels' => [ //Ecraser des valeurs par defaut
       'name' => 'Voyages',
       'singular_name' => 'Voyage',
   ],
    'description' => "La ressource permettant de gérer les voyages qui ont été effectués",
    'public' => true, //accessible dans l'interface admin (formulaire de contact: false)
    'menu_position' => 5,
    'menu_icon' => 'dashicons-palmtree',
    'supports' => ['title', 'editor', 'thumbnail'],
]);