<?php


/*require_once(__DIR__ . '/Menus/PrimaryMenuWalker.php');*/
require_once(__DIR__ . '/Menus/PrimaryMenuItem.php');
require_once(__DIR__ . '/Forms/BaseFormController.php');
require_once(__DIR__ . '/Forms/ContactFormController.php');
require_once(__DIR__ . '/Forms/Sanitizers/BaseSanitizer.php');
require_once(__DIR__ . '/Forms/Sanitizers/TextSanitizer.php');
require_once(__DIR__ . '/Forms/Sanitizers/EmailSanitizer.php');
require_once(__DIR__ . '/Forms/Validators/BaseValidator.php');
require_once(__DIR__ . '/Forms/Validators/RequiredValidator.php');
require_once(__DIR__ . '/Forms/Validators/EmailValidator.php');
require_once(__DIR__ . '/Forms/Validators/AcceptedValidator.php');
require_once(__DIR__ . '/Forms/CustomSearchQuery.php');

//lancer la session php

add_action('init', 'dw_boot_theme', 1);

function dw_boot_theme()
{
    load_theme_textdomain('dw', __DIR__ . '/locales');

    if(!session_id()){
        session_start();
    }
}

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
    'rewrite' => ['slug' => 'voyages'],
    'has_archive' => true,
]);

register_post_type('message', [
    'label' => 'Messages de contact',
    'labels' => [ //Ecraser des valeurs par defaut
        'name' => 'Messages de contact',
        'singular_name' => 'Message de contact',
    ],
    'description' => "Les messages envoyés par les utilisateurs via le formulaire de contact",
    'public' => false, //accessible dans l'interface admin (formulaire de contact: false)
    'show_ui' => true,
    'menu_position' => 10,
    'menu_icon' => 'dashicons-buddicons-pm',
    'capabilities' => [
        'create_posts' => false, //enlever le bouton add new
    ],
    'map_meta_cap' => true,
]);

// Enregistrer une taxonomie (facon de classifier des posts) pour les pays où des voyages ont eu lieu
register_taxonomy('country', ['trips'], [
    'labels' => [
        'name' => 'Pays',
        'singular_name' => 'Pays',
    ],
    'description' => 'Pays visités et exploités dans nos récits de voyages',
    'public' => true,
    'hierarchical' => true,
]);

//Récuperer les trips via une requete wordpress
function dw_get_trips($count = 20, $search = null)
{
    // 1. on instancie l'objet WP_QUERY
    $trips = new WP_Query([
        'post_type' => 'trips',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => $count,
        's' => strlen($search) ? $search : null,
    ]);

    // 2. on retourne l'objet WP_QUERY
    return $trips;
}

//Enregistrer les menus de navigation
register_nav_menu('primary', 'Emplacement de la navigation principale de haut de page');
register_nav_menu('footer', 'Emplacement de la navigation secondaire de pied de page');

// Définition de la fonction retournant un menu de navigation sous forme d'un tableau de lien niveau 0

function dw_get_menu_items($location)
{
    // Récuperer le menu qui correspond a l'emplacement souhaité

    $items = [];

    $locations = get_nav_menu_locations(); //fonction retourne un tableau de plusieurs emplacement

    if(!$locations[$location] ?? false){
        return $items;
    }

    $menu = $locations[$location];

    // Récuperer tous les éléments du menu en question
    $posts = wp_get_nav_menu_items($menu);

    // Traiter chaque éléments du menu pour les transformer en objet
    foreach ($posts as $post){
        // Créer une instance d'un objet personnalisé à partir de $post

        $item = new PrimaryMenuItem($post);

        // Ajouter cette instance soit à $items (s'il s'agit d'un element de niveau 0), soit en tant que sous élément d'un item déja existant

        if(!$item->isSubItems()){
            $items[] = $item;
            continue;
        }

        //ajouter l'einstance comme enfant d'un item existant
        foreach ($items as $existing){
            if(!$existing->isParentFor($item)) continue;

            $existing->addSubItem($item);
        }
    }

    // Retourner les éléments de niveau 0
    return $items;
}

// enregistrer le traitement du formulaire de contact personnalisé
add_action('admin_post_submit_contact_form', 'dw_handle_submit_contact_form');

function dw_handle_submit_contact_form()
{
    $form = new ContactFormController($_POST);
}

function dw_get_contact_field_value($field){

    if(!isset($_SESSION['feedback_contact_form'])) {
        return '';
    }

    return $_SESSION['feedback_contact_form']['data'][$field] ?? '';
}

function dw_get_contact_field_error($field)
{
    if(! isset($_SESSION['feedback_contact_form'])) {
        return '';
    }

    if(! ($_SESSION['feedback_contact_form']['errors'][$field] ?? null)) {
        return '';
    }

    return '<p class="form__error">Problème : ' . $_SESSION['feedback_contact_form']['errors'][$field] . '</p>';
}

//Utilitaire poir charger un fichier compile par mix, avec cache bursting
function dw_mix($path)
{
    $path = '/' . ltrim($path, '/');

    // Checker si le fichier demandé existe bien, sinon retourner NULL
    if(! realpath(__DIR__ . '/public' . $path)) {
        return;
    }

    // Check si le fichier mix-manifest existe bien, sinon retourner le fichier sans cache-bursting
    if(! ($manifest = realpath(__DIR__ . '/public/mix-manifest.json'))) {
        return get_stylesheet_directory_uri() . '/public' . $path;
    }

    // Ouvrir le fichier mix-manifest et lire le JSON
    $manifest = json_decode(file_get_contents($manifest), true);

    // Check si le fichier demandé est bien présent dans le mix manifest, sinon retourner le fichier sans cache-bursting
    if(! array_key_exists($path, $manifest)) {
        return get_stylesheet_directory_uri() . '/public' . $path;
    }

    // C'est OK, on génère l'URL vers la ressource sur base du nom de fichier avec cache-bursting.
    return get_stylesheet_directory_uri() . '/public' . $manifest[$path];
}

// On va se plugger dans l'exécution de la requete de recherche pour la contraindre à chercher dans les articles uniquement.
function dw_configure_search_query($query) //obligé de mettre des if pour voir si on en a besoin car la focntion va s'executer tout le temps
{
    if($query->is_search && !is_admin() && !is_a($query, DW_CustomSearchQuery::class)){ //mettre !is_admin car sinon on casse l'admin
        $query->set('post_type', ['post']);
    }

    /*//Faire un système de filtres "custom" (sans passer par la méthode WP) :
    if(is_archive() && isset($_GET['filter-country'])){
        $query->set('tax_query', [
            ['taxonomy' => 'country', 'field' => 'slug', 'terms' => explode(',', $_GET['filter-country'])]
        ]);
    }*/

    return $query;
}

add_filter('pre_get_posts', 'dw_configure_search_query');

//fonction permettant d'inclure des composants et d'y injecter des variables locales (scope de l'appel de fonction)
function dw_include(string $partial, array $variables = [])
{
    //$partial = 'post'
    // =>/Utilisateurs/.../wp-content/dw/partials/post.php

    extract($variables);
    include(__DIR__ . '/partials/' . $partial . '.php');
}

// Fonction permettant de récupérer la premiere page appartenant à un template donné
function dw_get_template_page(string $template)
{
    // 1. creer une wp-query
    $query = new WP_Query([
        'post_type' => 'page', // 2. filtrer sur le post type de type 'page'
        'post_status' => 'publish', // 3. Uniquement les pages publiées
        'meta_query' => [
            ['key' => '_wp_page_template', 'value' => $template . '.php'] // 4. Filtrer sur le type de template utilisé
        ]
    ]);

    return $query->posts[0] ?? null;    // 5. Retourner la premiere occurence pour cette requete (ou null)
}