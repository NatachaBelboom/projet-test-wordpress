<?php

/*require_once(__DIR__ . '/Menus/PrimaryMenuWalker.php');*/
require_once(__DIR__ . '/Menus/PrimaryMenuItem.php');

//lancer la session php

add_action('init', 'dw_init_php_session', 1);

function dw_init_php_session()
{
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
    'rewrite' => ['slug' => 'voyages']
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

//Récuperer les trips via une requete wordpress
function dw_get_trips($count = 20)
{
    // 1. on instancie l'objet WP_QUERY
    $trips = new WP_Query([
        'post_type' => 'trips',
        'orderby' => 'date',
        'order' => 'DESC',
        'posts_per_page' => $count,
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
    if(! dw_verify_contact_form_nonce()){
        // TODO: afficher un message d'erreur unauthorized
        return;
    }

    $data = dw_sanitize_contact_form_data();

    if($errors = dw_validate_contact_form_data($data)){
        $_SESSION['feedback_contact_form'] = [
            'success' => false,
            'data' => $data,
            'errors' => $errors
        ];

        return wp_redirect($_POST['_wp_http_referer']); //revenir a la page ou on etait
    }

    // stocker en base de données
    $id = wp_insert_post([
        'post_type' => 'message',
        'post_title' => 'Message de ' . $data['firstname'] . ' ' . $data['lastname'],
        'post_content' => $data['message'],
        'post_status' => 'publish',
    ]);
    // envoyer un mail
    $content = "Bonjour, un nouveau message de contact a été envoyé. <br />";
    $content .= "Pour les visualiser: " . get_edit_post_link($id);
    wp_mail('natacha.belboom@student.hepl.be', 'Nouveau message', $content);



}

function dw_verify_contact_form_nonce()
{
    $nonce = $_POST['_wpnonce']; //recuperer la valeur qui nous vient du post
    return wp_verify_nonce($nonce, 'nonce_check_contact_form');
}

function dw_sanitize_contact_form_data()
{
    return [
        'firstname' => sanitize_text_field($_POST['firstname'] ?? null),  //Sanitize pour "nettoyer" les données envoyées
        'lastname' => sanitize_text_field($_POST['lastname'] ?? null),
        'email' => sanitize_email($_POST['email'] ?? null),
        'phone' => sanitize_text_field($_POST['phone'] ?? null),
        'message' => sanitize_text_field($_POST['message'] ?? null),
        'rules' => $_POST['rules'] ?? null,
    ];
}

function dw_validate_contact_form_data($data)
{
    $errors = [];

    $required = ['firstname', 'lastname', 'email', 'message'];
    $email = ['email'];
    $accepted = ['rules'];

    foreach ($data as $key => $value){
        if(in_array($key, $required) && !$value){
            $errors[$key] = 'required';
            continue;
        }
        if(in_array($key, $email) && !filter_var($value, FILTER_VALIDATE_EMAIL)){
            $errors[$key] = 'email';
            continue;
        }
        if(in_array($key, $accepted) && !$value != '1'){
            $errors[$key] = 'accepted';
            continue;
        }
    }

    return $errors ? $errors : false;
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