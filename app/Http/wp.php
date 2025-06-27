
/**
 * PLATEFORME ARTISANS - SYSTÈME COMPLET WORDPRESS
 * Inspiré de travaux.com
 * Auteur: Claude AI
 * Version: 1.0
 */

// =============================================================================
// 1. CRÉATION DES TABLES PERSONNALISÉES
// =============================================================================

register_activation_hook(__FILE__, 'create_artisan_platform_tables');

function create_artisan_platform_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Table des catégories de service
    $table_categories = $wpdb->prefix . 'service_categories';
    $sql_categories = "CREATE TABLE $table_categories (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        slug varchar(100) NOT NULL,
        description text,
        icon varchar(50),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY slug (slug)
    ) $charset_collate;";
    
    // Table des métiers
    $table_metiers = $wpdb->prefix . 'metiers';
    $sql_metiers = "CREATE TABLE $table_metiers (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        category_id mediumint(9) NOT NULL,
        name varchar(100) NOT NULL,
        slug varchar(100) NOT NULL,
        description text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY slug (slug),
        KEY category_id (category_id)
    ) $charset_collate;";
    
    // Table des abonnements artisans
    $table_subscriptions = $wpdb->prefix . 'artisan_subscriptions';
    $sql_subscriptions = "CREATE TABLE $table_subscriptions (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        plan_type enum('starter','professional','premium') NOT NULL,
        credits_remaining int(11) DEFAULT 0,
        price decimal(10,2) NOT NULL,
        start_date datetime NOT NULL,
        end_date datetime NOT NULL,
        status enum('active','expired','cancelled') DEFAULT 'active',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";
    
    // Table des crédits clients
    $table_client_credits = $wpdb->prefix . 'client_credits';
    $sql_client_credits = "CREATE TABLE $table_client_credits (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        credits int(11) DEFAULT 0,
        total_purchased int(11) DEFAULT 0,
        total_used int(11) DEFAULT 0,
        last_purchase datetime,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_id (user_id)
    ) $charset_collate;";
    
    // Table des projets/demandes de devis
    $table_projects = $wpdb->prefix . 'client_projects';
    $sql_projects = "CREATE TABLE $table_projects (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        client_id bigint(20) NOT NULL,
        category_id mediumint(9) NOT NULL,
        metier_id mediumint(9) NOT NULL,
        title varchar(200) NOT NULL,
        description text NOT NULL,
        budget_min decimal(10,2),
        budget_max decimal(10,2),
        surface varchar(50),
        urgency enum('normal','urgent','tres_urgent') DEFAULT 'normal',
        location varchar(200),
        zip_code varchar(10),
        status enum('active','closed','completed') DEFAULT 'active',
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY client_id (client_id),
        KEY category_id (category_id)
    ) $charset_collate;";
    
    // Table des profils artisans
    $table_artisan_profiles = $wpdb->prefix . 'artisan_profiles';
    $sql_profiles = "CREATE TABLE $table_artisan_profiles (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        company_name varchar(200) NOT NULL,
        category_id mediumint(9) NOT NULL,
        metier_id mediumint(9) NOT NULL,
        experience_years int(11) DEFAULT 0,
        certifications text,
        description text,
        zone_geographique varchar(200),
        zip_codes text,
        phone varchar(20),
        website varchar(200),
        siret varchar(20),
        rating decimal(3,2) DEFAULT 0.00,
        total_reviews int(11) DEFAULT 0,
        verified tinyint(1) DEFAULT 0,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_id (user_id),
        KEY category_id (category_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_categories);
    dbDelta($sql_metiers);
    dbDelta($sql_subscriptions);
    dbDelta($sql_client_credits);
    dbDelta($sql_projects);
    dbDelta($sql_profiles);
    
    // Insertion des données de base
    insert_default_categories_and_metiers();
}

function insert_default_categories_and_metiers() {
    global $wpdb;
    
    $categories_data = [
        ['name' => 'Plomberie', 'slug' => 'plomberie', 'description' => 'Travaux de plomberie et sanitaire', 'icon' => 'wrench'],
        ['name' => 'Électricité', 'slug' => 'electricite', 'description' => 'Installation et réparation électrique', 'icon' => 'bolt'],
        ['name' => 'Maçonnerie', 'slug' => 'maconnerie', 'description' => 'Travaux de maçonnerie et gros œuvre', 'icon' => 'hammer'],
        ['name' => 'Peinture', 'slug' => 'peinture', 'description' => 'Peinture intérieure et extérieure', 'icon' => 'brush'],
        ['name' => 'Menuiserie', 'slug' => 'menuiserie', 'description' => 'Travaux de menuiserie bois et PVC', 'icon' => 'saw'],
        ['name' => 'Couverture', 'slug' => 'couverture', 'description' => 'Toiture et couverture', 'icon' => 'roof'],
        ['name' => 'Chauffage', 'slug' => 'chauffage', 'description' => 'Installation et maintenance chauffage', 'icon' => 'fire'],
        ['name' => 'Jardinage', 'slug' => 'jardinage', 'description' => 'Aménagement paysager et jardinage', 'icon' => 'leaf']
    ];
    
    foreach ($categories_data as $category) {
        $wpdb->insert($wpdb->prefix . 'service_categories', $category);
    }
    
    $metiers_data = [
        // Plomberie
        [1, 'Plombier général', 'plombier-general'],
        [1, 'Chauffagiste', 'chauffagiste'],
        [1, 'Installateur sanitaire', 'installateur-sanitaire'],
        // Électricité
        [2, 'Électricien général', 'electricien-general'],
        [2, 'Domoticien', 'domoticien'],
        [2, 'Installateur photovoltaïque', 'installateur-photovoltaique'],
        // Maçonnerie
        [3, 'Maçon général', 'macon-general'],
        [3, 'Carreleur', 'carreleur'],
        [3, 'Platrier', 'platrier'],
        // Peinture
        [4, 'Peintre en bâtiment', 'peintre-batiment'],
        [4, 'Façadier', 'facadier'],
        // Menuiserie
        [5, 'Menuisier bois', 'menuisier-bois'],
        [5, 'Menuisier PVC', 'menuisier-pvc'],
        [5, 'Poseur de parquet', 'poseur-parquet']
    ];
    
    foreach ($metiers_data as $metier) {
        $wpdb->insert($wpdb->prefix . 'metiers', [
            'category_id' => $metier[0],
            'name' => $metier[1],
            'slug' => $metier[2]
        ]);
    }
}

// =============================================================================
// 2. ENREGISTREMENT DES ENDPOINTS REST API
// =============================================================================

add_action('rest_api_init', function() {
    
    // CORS Headers
    add_action('rest_api_init', function() {
        remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
        add_filter('rest_pre_serve_request', function($value) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: Authorization, Content-Type');
            return $value;
        });
    }, 15);
    
    // =========================================================================
    // GESTION DES CATÉGORIES DE SERVICE
    // =========================================================================
    
    // Créer une catégorie
    register_rest_route('artisan/v1', '/categories', [
        'methods' => 'POST',
        'callback' => 'create_service_category',
        'permission_callback' => 'check_admin_permission',
        'args' => [
            'name' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'description' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field'],
            'icon' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field']
        ]
    ]);
    
    // Lister les catégories
    register_rest_route('artisan/v1', '/categories', [
        'methods' => 'GET',
        'callback' => 'get_service_categories',
        'permission_callback' => '__return_true'
    ]);
    
    // Modifier une catégorie
    register_rest_route('artisan/v1', '/categories/(?P<id>\d+)', [
        'methods' => 'PUT',
        'callback' => 'update_service_category',
        'permission_callback' => 'check_admin_permission',
        'args' => [
            'name' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'description' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field'],
            'icon' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field']
        ]
    ]);
    
    // Supprimer une catégorie
    register_rest_route('artisan/v1', '/categories/(?P<id>\d+)', [
        'methods' => 'DELETE',
        'callback' => 'delete_service_category',
        'permission_callback' => 'check_admin_permission'
    ]);
    
     // =========================================================================
    // INSCRIPTION ET CONNEXION
    // =========================================================================
    
    // Inscription artisan
    register_rest_route('artisan/v1', '/register/artisan', [
        'methods' => 'POST',
        'callback' => 'register_artisan',
        'permission_callback' => '__return_true',
        'args' => [
            'email' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_email'],
            'password' => ['required' => true, 'type' => 'string'],
            'company_name' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'category_id' => ['required' => true, 'type' => 'integer'],
            'metier_id' => ['required' => true, 'type' => 'integer'],
            'phone' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'zone_geographique' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field']
        ]
    ]);

    // =========================================================================
    // GESTION DES MÉTIERS
    // =========================================================================
    
    // Créer un métier
    register_rest_route('artisan/v1', '/metiers', [
        'methods' => 'POST',
        'callback' => 'create_metier',
        'permission_callback' => 'check_admin_permission',
        'args' => [
            'category_id' => ['required' => true, 'type' => 'integer'],
            'name' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'description' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field']
        ]
    ]);
    
    // Lister les métiers par catégorie
    register_rest_route('artisan/v1', '/categories/(?P<category_id>\d+)/metiers', [
        'methods' => 'GET',
        'callback' => 'get_metiers_by_category',
        'permission_callback' => '__return_true'
    ]);
    
    // =========================================================================
    // INSCRIPTION ET CONNEXION
    // =========================================================================
    
    // Inscription artisan
    register_rest_route('artisan/v1', '/register/artisan', [
        'methods' => 'POST',
        'callback' => 'register_artisan',
        'permission_callback' => '__return_true',
        'args' => [
            'email' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_email'],
            'password' => ['required' => true, 'type' => 'string'],
            'company_name' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'category_id' => ['required' => true, 'type' => 'integer'],
            'metier_id' => ['required' => true, 'type' => 'integer'],
            'phone' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'zone_geographique' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field']
        ]
    ]);
    
    // Inscription client
    register_rest_route('artisan/v1', '/register/client', [
        'methods' => 'POST',
        'callback' => 'register_client',
        'permission_callback' => '__return_true',
        'args' => [
            'email' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_email'],
            'password' => ['required' => true, 'type' => 'string'],
            'first_name' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'last_name' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'phone' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field']
        ]
    ]);
    
    // Connexion
    register_rest_route('artisan/v1', '/login', [
        'methods' => 'POST',
        'callback' => 'user_login',
        'permission_callback' => '__return_true',
        'args' => [
            'email' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_email'],
            'password' => ['required' => true, 'type' => 'string']
        ]
    ]);
    
    // =========================================================================
    // GESTION DES ABONNEMENTS ARTISANS
    // =========================================================================
    
    // Créer un abonnement artisan
    register_rest_route('artisan/v1', '/subscription/artisan', [
        'methods' => 'POST',
        'callback' => 'create_artisan_subscription',
        'permission_callback' => 'check_artisan_permission',
        'args' => [
            'plan_type' => ['required' => true, 'type' => 'string', 'enum' => ['starter', 'professional', 'premium']],
            'duration' => ['required' => true, 'type' => 'integer'] // en mois
        ]
    ]);
    
    // =========================================================================
    // GESTION DES CRÉDITS CLIENTS
    // =========================================================================
    
    // Acheter des crédits
    register_rest_route('artisan/v1', '/credits/purchase', [
        'methods' => 'POST',
        'callback' => 'purchase_client_credits',
        'permission_callback' => 'check_client_permission',
        'args' => [
            'credits' => ['required' => true, 'type' => 'integer'],
            'payment_method' => ['required' => true, 'type' => 'string']
        ]
    ]);
    
    // =========================================================================
    // PROJETS / DEMANDES DE DEVIS
    // =========================================================================
    
    // Créer un projet
    register_rest_route('artisan/v1', '/projects', [
        'methods' => 'POST',
        'callback' => 'create_client_project',
        'permission_callback' => 'check_client_permission',
        'args' => [
            'category_id' => ['required' => true, 'type' => 'integer'],
            'metier_id' => ['required' => true, 'type' => 'integer'],
            'title' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'description' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field'],
            'budget_min' => ['required' => false, 'type' => 'number'],
            'budget_max' => ['required' => false, 'type' => 'number'],
            'surface' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'urgency' => ['required' => false, 'type' => 'string', 'enum' => ['normal', 'urgent', 'tres_urgent']],
            'location' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'zip_code' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field']
        ]
    ]);
    
    // Lister les projets pour artisans
    register_rest_route('artisan/v1', '/projects/artisan', [
        'methods' => 'GET',
        'callback' => 'get_projects_for_artisan',
        'permission_callback' => 'check_artisan_permission'
    ]);
    
    // =========================================================================
    // GÉNÉRATION IA - PROFILS ARTISANS
    // =========================================================================
    
    register_rest_route('artisan/v1', '/generate/profile', [
        'methods' => 'POST',
        'callback' => 'generate_artisan_profile_ai',
        'permission_callback' => 'check_artisan_permission',
        'args' => [
            'category_service' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'metier' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'zone_geographique' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'experience_years' => ['required' => true, 'type' => 'integer'],
            'specialites' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'certifications' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field']
        ]
    ]);
    
    // =========================================================================
    // GÉNÉRATION IA - DESCRIPTIONS DE PROJETS
    // =========================================================================
    
    register_rest_route('artisan/v1', '/generate/project', [
        'methods' => 'POST',
        'callback' => 'generate_project_description_ai',
        'permission_callback' => 'check_client_permission',
        'args' => [
            'type_travaux' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'budget_range' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'surface' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'urgence' => ['required' => false, 'type' => 'string', 'default' => 'normal', 'sanitize_callback' => 'sanitize_text_field'],
            'details_specifiques' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_textarea_field']
        ]
    ]);
    
    // =========================================================================
    // GÉNÉRATION IA - DEVIS AUTOMATIQUES
    // =========================================================================
    
    register_rest_route('artisan/v1', '/generate/devis', [
        'methods' => 'POST',
        'callback' => 'generate_devis_ai',
        'permission_callback' => 'check_artisan_permission',
        'args' => [
            'project_id' => ['required' => true, 'type' => 'integer'],
            'materiaux_type' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'gamme_prix' => ['required' => true, 'type' => 'string', 'enum' => ['economique', 'standard', 'premium']],
            'delai_souhaite' => ['required' => true, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
            'garanties' => ['required' => false, 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field']
        ]
    ]);
});

// =============================================================================
// 3. FONCTIONS CALLBACK DES ENDPOINTS
// =============================================================================

// =========================================================================
// GESTION DES CATÉGORIES
// =========================================================================

function create_service_category($request) {
    global $wpdb;
    
    $name = $request->get_param('name');
    $description = $request->get_param('description');
    $icon = $request->get_param('icon');
    $slug = sanitize_title($name);
    
    $result = $wpdb->insert(
        $wpdb->prefix . 'service_categories',
        [
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'icon' => $icon
        ]
    );
    
    if ($result === false) {
        return new WP_Error('db_error', 'Erreur lors de la création de la catégorie', ['status' => 500]);
    }
    
    return [
        'success' => true,
        'message' => 'Catégorie créée avec succès',
        'category_id' => $wpdb->insert_id
    ];
}



function delete_service_category($request) {
    global $wpdb;
    
    $category_id = $request->get_param('id');
    
    // Vérifier s'il y a des métiers associés
    $metiers_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}metiers WHERE category_id = %d",
        $category_id
    ));
    
    if ($metiers_count > 0) {
        return new WP_Error('has_metiers', 'Impossible de supprimer une catégorie qui contient des métiers', ['status' => 400]);
    }
    
    $wpdb->delete(
        $wpdb->prefix . 'service_categories',
        ['id' => $category_id]
    );
    
    return [
        'success' => true,
        'message' => 'Catégorie supprimée avec succès'
    ];
}

// =========================================================================
// GESTION DES MÉTIERS
// =========================================================================

function create_metier($request) {
    global $wpdb;
    
    $category_id = $request->get_param('category_id');
    $name = $request->get_param('name');
    $description = $request->get_param('description');
    $slug = sanitize_title($name);
    
    $result = $wpdb->insert(
        $wpdb->prefix . 'metiers',
        [
            'category_id' => $category_id,
            'name' => $name,
            'slug' => $slug,
            'description' => $description
        ]
    );
    
    if ($result === false) {
        return new WP_Error('db_error', 'Erreur lors de la création du métier', ['status' => 500]);
    }
    
    return [
        'success' => true,
        'message' => 'Métier créé avec succès',
        'metier_id' => $wpdb->insert_id
    ];
}

function get_metiers_by_category($request) {
    global $wpdb;
    
    $category_id = $request->get_param('category_id');
    
    $metiers = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}metiers WHERE category_id = %d ORDER BY name ASC",
        $category_id
    ));
    
    return [
        'success' => true,
        'metiers' => $metiers
    ];
}

// =========================================================================
// INSCRIPTION ET CONNEXION
// =========================================================================

function register_artisan($request) {
    $email = $request->get_param('email');
    $password = $request->get_param('password');
    $company_name = $request->get_param('company_name');
    $category_id = $request->get_param('category_id');
    $metier_id = $request->get_param('metier_id');
    $phone = $request->get_param('phone');
    $zone_geographique = $request->get_param('zone_geographique');
    
    // Créer l'utilisateur WordPress
    $user_id = wp_create_user($email, $password, $email);
    
    if (is_wp_error($user_id)) {
        return new WP_Error('registration_failed', $user_id->get_error_message(), ['status' => 400]);
    }
    
    // Ajouter le rôle artisan
    $user = new WP_User($user_id);
    $user->set_role('artisan');
    
    // Créer le profil artisan
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'artisan_profiles',
        [
            'user_id' => $user_id,
            'company_name' => $company_name,
            'category_id' => $category_id,
            'metier_id' => $metier_id,
            'phone' => $phone,
            'zone_geographique' => $zone_geographique
        ]
    );
    
    return [
        'success' => true,
        'message' => 'Artisan inscrit avec succès',
        'user_id' => $user_id
    ];
}

function register_client($request) {
    $email = $request->get_param('email');
    $password = $request->get_param('password');
    $first_name = $request->get_param('first_name');
    $last_name = $request->get_param('last_name');
    $phone = $request->get_param('phone');
    
    // Créer l'utilisateur WordPress
    $user_id = wp_create_user($email, $password, $email);
    
    if (is_wp_error($user_id)) {
        return new WP_Error('registration_failed', $user_id->get_error_message(), ['status' => 400]);
    }
    
    // Ajouter le rôle client
    $user = new WP_User($user_id);
    $user->set_role('client');
    
    // Mettre à jour les métadonnées
    update_user_meta($user_id, 'first_name', $first_name);
    update_user_meta($user_id, 'last_name', $last_name);
    if ($phone) update_user_meta($user_id, 'phone', $phone);
    
    // Initialiser les crédits client
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'client_credits',
        [
            'user_id' => $user_id,
            'credits' => 0
        ]
    );
    
    return [
        'success' => true,
        'message' => 'Client inscrit avec succès',
        'user_id' => $user_id
    ];
}

function create_client_project($request) {
    global $wpdb;
    
    $client_id = get_current_user_id();
    $category_id = $request->get_param('category_id');
    $metier_id = $request->get_param('metier_id');
    $title = $request->get_param('title');
    $description = $request->get_param('description');
    $budget_min = $request->get_param('budget_min');
    $budget_max = $request->get_param('budget_max');
    $surface = $request->get_param('surface');
    $urgency = $request->get_param('urgency') ?: 'normal';
    $location = $request->get_param('location');
    $zip_code = $request->get_param('zip_code');
    
    $result = $wpdb->insert(
        $wpdb->prefix . 'client_projects',
        [
            'client_id' => $client_id,
            'category_id' => $category_id,
            'metier_id' => $metier_id,
            'title' => $title,
            'description' => $description,
            'budget_min' => $budget_min,
            'budget_max' => $budget_max,
            'surface' => $surface,
            'urgency' => $urgency,
            'location' => $location,
            'zip_code' => $zip_code
        ]
    );
    
    if ($result === false) {
        return new WP_Error('project_creation_failed', 'Erreur lors de la création du projet', ['status' => 500]);
    }
    
    return [
        'success' => true,
        'message' => 'Projet créé avec succès',
        'project_id' => $wpdb->insert_id
    ];
}

function get_projects_for_artisan($request) {
    global $wpdb;
    
    $user_id = get_current_user_id();
    
    // Récupérer le profil de l'artisan
    $artisan_profile = $wpdb->get_row($wpdb->prepare(
        "SELECT category_id, metier_id, zone_geographique FROM {$wpdb->prefix}artisan_profiles WHERE user_id = %d",
        $user_id
    ));
    
    if (!$artisan_profile) {
        return new WP_Error('profile_not_found', 'Profil artisan non trouvé', ['status' => 404]);
    }
    
    // Récupérer les projets correspondants
    $projects = $wpdb->get_results($wpdb->prepare(
        "SELECT p.*, c.name as category_name, m.name as metier_name, 
                u.user_email as client_email
         FROM {$wpdb->prefix}client_projects p
         JOIN {$wpdb->prefix}service_categories c ON p.category_id = c.id
         JOIN {$wpdb->prefix}metiers m ON p.metier_id = m.id
         JOIN {$wpdb->users} u ON p.client_id = u.ID
         WHERE p.category_id = %d 
         AND p.metier_id = %d 
         AND p.status = 'active'
         ORDER BY p.created_at DESC
         LIMIT 20",
        $artisan_profile->category_id,
        $artisan_profile->metier_id
    ));
    
    return [
        'success' => true,
        'projects' => $projects
    ];
}

// =============================================================================
// 4. FONCTIONS IA - GÉNÉRATION DE CONTENU AVEC OPENAI
// =============================================================================

function call_openai_api($prompt, $max_tokens = 1000, $return_json = false) {
    $api_key = get_option('openai_api_key', ''); // À configurer dans les options WordPress
    
    if (empty($api_key)) {
        return new WP_Error('api_key_missing', 'Clé API OpenAI non configurée', ['status' => 500]);
    }
    
    $url = 'https://api.openai.com/v1/chat/completions';
    
    $body_data = [
        'model' => 'gpt-4o-mini',
        'messages' => [['role' => 'user', 'content' => $prompt]],
        'max_tokens' => $max_tokens,
        'temperature' => 0.7
    ];
    
    if ($return_json) {
        $body_data['response_format'] = ['type' => 'json_object'];
    }
    
    $response = wp_remote_post($url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key
        ],
        'body' => json_encode($body_data),
        'timeout' => 120
    ]);
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    if (isset($body['error'])) {
        return new WP_Error('openai_error', $body['error']['message'], ['status' => 500]);
    }
    
    $content = $body['choices'][0]['message']['content'];
    
    return $return_json ? json_decode($content, true) : trim($content);
}

// =========================================================================
// GÉNÉRATION DE PROFILS ARTISANS
// =========================================================================

function generate_artisan_profile_ai($request) {
    $category_service = $request->get_param('category_service');
    $metier = $request->get_param('metier');
    $zone_geographique = $request->get_param('zone_geographique');
    $experience_years = $request->get_param('experience_years');
    $specialites = $request->get_param('specialites') ?: '';
    $certifications = $request->get_param('certifications') ?: '';
    
    $prompt = "Génère une description professionnelle détaillée pour un profil d'artisan avec les caractéristiques suivantes :

Catégorie de service : $category_service
Métier : $metier
Zone géographique : $zone_geographique
Années d'expérience : $experience_years ans
Spécialités : $specialites
Certifications : $certifications

La description doit inclure :
1. Une présentation de l'entreprise (2-3 phrases)
2. Les services proposés détaillés
3. Les points forts et garanties
4. L'approche client et valeurs
5. Les certifications et assurances si mentionnées

Réponds au format JSON avec les clés :
- company_description (description de l'entreprise)
- services_offered (liste des services)
- strengths (points forts)
- guarantees (garanties proposées)
- certifications_text (texte sur les certifications)
- call_to_action (phrase d'accroche finale)";
    
    $result = call_openai_api($prompt, 1500, true);
    
    if (is_wp_error($result)) {
        return $result;
    }
    
    return [
        'success' => true,
        'generated_profile' => $result,
        'parameters_used' => [
            'category_service' => $category_service,
            'metier' => $metier,
            'zone_geographique' => $zone_geographique,
            'experience_years' => $experience_years
        ]
    ];
}

// =========================================================================
// GÉNÉRATION DE DESCRIPTIONS DE PROJETS
// =========================================================================

function generate_project_description_ai($request) {
    $type_travaux = $request->get_param('type_travaux');
    $budget_range = $request->get_param('budget_range');
    $surface = $request->get_param('surface') ?: '';
    $urgence = $request->get_param('urgence');
    $details_specifiques = $request->get_param('details_specifiques') ?: '';
    
    $urgence_text = [
        'normal' => 'dans les délais normaux',
        'urgent' => 'dans un délai rapide (sous 2 semaines)',
        'tres_urgent' => 'en urgence (sous 1 semaine)'
    ];
    
    $prompt = "Génère une description complète et professionnelle pour une demande de devis de travaux avec les éléments suivants :

Type de travaux : $type_travaux
Budget envisagé : $budget_range
Surface concernée : $surface
Urgence : {$urgence_text[$urgence]}
Détails spécifiques : $details_specifiques

La description doit :
1. Présenter clairement le projet
2. Détailler les travaux souhaités
3. Mentionner les contraintes techniques si applicable
4. Préciser les attentes qualité
5. Indiquer la timeline souhaitée

Réponds au format JSON avec :
- title (titre accrocheur du projet)
- detailed_description (description complète)
- technical_requirements (exigences techniques)
- timeline (délais souhaités)
- budget_explanation (justification du budget)
- additional_info (informations complémentaires)";
    
    $result = call_openai_api($prompt, 1200, true);
    
    if (is_wp_error($result)) {
        return $result;
    }
    
    return [
        'success' => true,
        'generated_project' => $result,
        'parameters_used' => [
            'type_travaux' => $type_travaux,
            'budget_range' => $budget_range,
            'urgence' => $urgence
        ]
    ];
}

// =========================================================================
// GÉNÉRATION DE DEVIS AUTOMATIQUES
// =========================================================================

function generate_devis_ai($request) {
    global $wpdb;
    
    $project_id = $request->get_param('project_id');
    $materiaux_type = $request->get_param('materiaux_type');
    $gamme_prix = $request->get_param('gamme_prix');
    $delai_souhaite = $request->get_param('delai_souhaite');
    $garanties = $request->get_param('garanties') ?: '';
    
    // Récupérer les détails du projet
    $project = $wpdb->get_row($wpdb->prepare(
        "SELECT p.*, c.name as category_name, m.name as metier_name 
         FROM {$wpdb->prefix}client_projects p
         JOIN {$wpdb->prefix}service_categories c ON p.category_id = c.id
         JOIN {$wpdb->prefix}metiers m ON p.metier_id = m.id
         WHERE p.id = %d",
        $project_id
    ));
    
    if (!$project) {
        return new WP_Error('project_not_found', 'Projet non trouvé', ['status' => 404]);
    }
    
    $prix_modifiers = [
        'economique' => 0.8,
        'standard' => 1.0,
        'premium' => 1.3
    ];
    
    $prompt = "Génère un devis détaillé pour ce projet de {$project->metier_name} :

PROJET :
Titre : {$project->title}
Description : {$project->description}
Surface : {$project->surface}
Budget indicatif : {$project->budget_min}€ - {$project->budget_max}€
Localisation : {$project->location}

PARAMÈTRES DU DEVIS :
Matériaux : $materiaux_type
Gamme de prix : $gamme_prix
Délai souhaité : $delai_souhaite
Garanties : $garanties

Génère un devis professionnel avec :
1. Décomposition détaillée des coûts (matériaux, main d'œuvre, etc.)
2. Prix adaptés à la gamme $gamme_prix
3. Planning des travaux
4. Conditions et garanties
5. Validité du devis

Réponds au format JSON avec :
- reference (numéro de devis)
- breakdown (décomposition des coûts avec quantités et prix unitaires)
- total_ht (total HT)
- total_ttc (total TTC)
- planning (planning détaillé)
- guarantees (garanties proposées)
- validity (validité du devis)
- payment_terms (conditions de paiement)
- notes (notes importantes)";
    
    $result = call_openai_api($prompt, 2000, true);
    
    if (is_wp_error($result)) {
        return $result;
    }
    
    return [
        'success' => true,
        'generated_devis' => $result,
        'project_info' => [
            'title' => $project->title,
            'category' => $project->category_name,
            'metier' => $project->metier_name
        ],
        'parameters_used' => [
            'materiaux_type' => $materiaux_type,
            'gamme_prix' => $gamme_prix,
            'delai_souhaite' => $delai_souhaite
        ]
    ];
}

// =============================================================================
// 5. FONCTIONS DE PERMISSIONS
// =============================================================================


function check_artisan_permission() {
    return current_user_can('artisan') || current_user_can('administrator');
}

function check_client_permission() {
    return current_user_can('client') || current_user_can('administrator');
}

// =============================================================================
// 6. CRÉATION DES RÔLES PERSONNALISÉS
// =============================================================================

register_activation_hook(__FILE__, 'create_custom_roles');

function create_custom_roles() {
    // Rôle Artisan
    add_role('artisan', 'Artisan', [
        'read' => true,
        'create_devis' => true,
        'manage_profile' => true,
        'view_projects' => true
    ]);
    
    // Rôle Client
    add_role('client', 'Client', [
        'read' => true,
        'create_projects' => true,
        'purchase_credits' => true,
        'contact_artisans' => true
    ]);
}

// =============================================================================
// 7. HOOKS ET ACTIONS SUPPLÉMENTAIRES
// =============================================================================

// Hook pour gérer l'utilisation des crédits lors du contact d'un artisan
add_action('wp_ajax_contact_artisan', 'handle_contact_artisan');
add_action('wp_ajax_nopriv_contact_artisan', 'handle_contact_artisan');

function handle_contact_artisan() {
    global $wpdb;
    
    $client_id = get_current_user_id();
    $artisan_id = intval($_POST['artisan_id']);
    
    if (!$client_id) {
        wp_die('Vous devez être connecté');
    }
    
    // Vérifier les crédits du client
    $client_credits = $wpdb->get_var($wpdb->prepare(
        "SELECT credits FROM {$wpdb->prefix}client_credits WHERE user_id = %d",
        $client_id
    ));
    
    if ($client_credits < 1) {
        wp_die('Crédits insuffisants. Veuillez acheter des crédits.');
    }
    
    // Décrémenter les crédits
    $wpdb->update(
        $wpdb->prefix . 'client_credits',
        [
            'credits' => $client_credits - 1,
            'total_used' => $wpdb->get_var($wpdb->prepare(
                "SELECT total_used FROM {$wpdb->prefix}client_credits WHERE user_id = %d",
                $client_id
            )) + 1
        ],
        ['user_id' => $client_id]
    );
    
    // Récupérer les informations de contact de l'artisan
    $artisan_info = $wpdb->get_row($wpdb->prepare(
        "SELECT ap.phone, ap.company_name, u.user_email
         FROM {$wpdb->prefix}artisan_profiles ap
         JOIN {$wpdb->users} u ON ap.user_id = u.ID
         WHERE ap.user_id = %d",
        $artisan_id
    ));
    
    wp_send_json_success([
        'contact_info' => $artisan_info,
        'remaining_credits' => $client_credits - 1
    ]);
}

// Configuration des options par défaut
register_activation_hook(__FILE__, 'set_default_options');

function set_default_options() {
    add_option('artisan_platform_version', '1.0');
    add_option('credit_price_per_unit', 3.00);
    add_option('subscription_prices', json_encode([
        'starter' => 29.99,
        'professional' => 59.99,
        'premium' => 99.99
    ]));
}



function get_service_categories() {
    global $wpdb;
    
    $categories = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}service_categories ORDER BY name ASC"
    );
    
    return [
        'success' => true,
        'categories' => $categories
    ];
}

function update_service_category($request) {
    global $wpdb;
    
    $category_id = $request->get_param('id');
    $data = [];
    
    if ($request->get_param('name')) {
        $data['name'] = $request->get_param('name');
        $data['slug'] = sanitize_title($data['name']);
    }
    if ($request->get_param('description')) $data['description'] = $request->get_param('description');
    if ($request->get_param('icon')) $data['icon'] = $request->get_param('icon');
    
    $result = $wpdb->update(
        $wpdb->prefix . 'service_categories',
        $data,
        ['id' => $category_id]
    );
    
    return [
        'success' => true,
        'message' => 'Catégorie mise à jour avec succès'
    ];
}

