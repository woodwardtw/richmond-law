<?php
/**
 * Understrap Custom post types and taxonomies
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


//case custom post type

// Register Custom Post Type case
// Post Type Key: case

function create_case_cpt() {

  $labels = array(
    'name' => __( 'Cases', 'Post Type General Name', 'textdomain' ),
    'singular_name' => __( 'Case', 'Post Type Singular Name', 'textdomain' ),
    'menu_name' => __( 'Case', 'textdomain' ),
    'name_admin_bar' => __( 'Case', 'textdomain' ),
    'archives' => __( 'Case Archives', 'textdomain' ),
    'attributes' => __( 'Case Attributes', 'textdomain' ),
    'parent_item_colon' => __( 'Case:', 'textdomain' ),
    'all_items' => __( 'All Cases', 'textdomain' ),
    'add_new_item' => __( 'Add New Case', 'textdomain' ),
    'add_new' => __( 'Add New', 'textdomain' ),
    'new_item' => __( 'New Case', 'textdomain' ),
    'edit_item' => __( 'Edit Case', 'textdomain' ),
    'update_item' => __( 'Update Case', 'textdomain' ),
    'view_item' => __( 'View Case', 'textdomain' ),
    'view_items' => __( 'View Cases', 'textdomain' ),
    'search_items' => __( 'Search Cases', 'textdomain' ),
    'not_found' => __( 'Not found', 'textdomain' ),
    'not_found_in_trash' => __( 'Not found in Trash', 'textdomain' ),
    'featured_image' => __( 'Featured Image', 'textdomain' ),
    'set_featured_image' => __( 'Set featured image', 'textdomain' ),
    'remove_featured_image' => __( 'Remove featured image', 'textdomain' ),
    'use_featured_image' => __( 'Use as featured image', 'textdomain' ),
    'insert_into_item' => __( 'Insert into case', 'textdomain' ),
    'uploaded_to_this_item' => __( 'Uploaded to this case', 'textdomain' ),
    'items_list' => __( 'Case list', 'textdomain' ),
    'items_list_navigation' => __( 'Case list navigation', 'textdomain' ),
    'filter_items_list' => __( 'Filter Case list', 'textdomain' ),
  );
  $args = array(
    'label' => __( 'case', 'textdomain' ),
    'description' => __( '', 'textdomain' ),
    'labels' => $labels,
    'menu_icon' => '',
    'supports' => array('title', 'editor', 'revisions', 'author', 'trackbacks', 'custom-fields', 'thumbnail',),
    'taxonomies' => array('category', 'post_tag', 'case_terms', 'status'),
    'public' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'menu_position' => 5,
    'show_in_admin_bar' => true,
    'show_in_nav_menus' => true,
    'can_export' => true,
    'has_archive' => true,
    'hierarchical' => false,
    'exclude_from_search' => false,
    'show_in_rest' => true,
    'publicly_queryable' => true,
    'capability_type' => 'post',
    'menu_icon' => 'dashicons-welcome-learn-more',
  );
  register_post_type( 'case', $args );
  
}
add_action( 'init', 'create_case_cpt', 0 );

// Flush once on activation (plugin) or after theme switch:
register_activation_hook(__FILE__, function () {
    create_case_cpt();
    flush_rewrite_rules();
});
// If this lives in a theme, use:
add_action('after_switch_theme', function () {
    create_case_cpt();
    flush_rewrite_rules();
});

add_action( 'init', 'create_status_taxonomies', 0 );
function create_status_taxonomies()
{
  // Add new taxonomy, NOT hierarchical (like tags)
  $labels = array(
    'name' => _x( 'Status', 'taxonomy general name' ),
    'singular_name' => _x( 'status', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Statuses' ),
    'popular_items' => __( 'Popular Statuses' ),
    'all_items' => __( 'All Statuss' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit Statuses' ),
    'update_item' => __( 'Update status' ),
    'add_new_item' => __( 'Add New status' ),
    'new_item_name' => __( 'New status' ),
    'add_or_remove_items' => __( 'Add or remove Statuses' ),
    'choose_from_most_used' => __( 'Choose from the most used Statuses' ),
    'menu_name' => __( 'Status' ),
  );

//registers taxonomy specific post types - default is just post
  register_taxonomy('status', array('case'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array( 'slug' => 'status' ),
    'show_in_rest'          => true,
    'rest_base'             => 'case-status',
    'rest_controller_class' => 'WP_REST_Terms_Controller',
    'show_in_nav_menus' => true,    
  ));
}

add_action( 'init', 'create_case_term_taxonomies', 0 );
function create_case_term_taxonomies()
{
  // Add new taxonomy, NOT hierarchical (like tags)
  $labels = array(
    'name' => _x( 'Terms', 'taxonomy general name' ),
    'singular_name' => _x( 'Term', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Terms' ),
    'popular_items' => __( 'Popular Terms' ),
    'all_items' => __( 'All Terms' ),
    'parent_item' => true,
    'parent_item_colon' => true,
    'edit_item' => __( 'Edit Terms' ),
    'update_item' => __( 'Update Term' ),
    'add_new_item' => __( 'Add New Term' ),
    'new_item_name' => __( 'New Term' ),
    'add_or_remove_items' => __( 'Add or remove Terms' ),
    'choose_from_most_used' => __( 'Choose from the most used Terms' ),
    'menu_name' => __( 'Term' ),
  );

//registers taxonomy specific post types - default is just post
  register_taxonomy('case_terms', array('case'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'update_count_callback' => '_update_post_term_count',
    'query_var' => true,
    'rewrite' => array( 
        'slug' => 'case-term',
        'with_front' => false
       ),
    'show_in_rest'          => true,
    'rest_base'             => 'case_term',
    'rest_controller_class' => 'WP_REST_Terms_Controller',
    'show_in_nav_menus' => true,    
  ));
}

add_filter('wp_unique_term_slug', function($slug, $term) {
    if (is_numeric($slug) && isset($term->taxonomy) && $term->taxonomy === 'case_terms') {
        return 'term-' . $slug;
    }
    return $slug;
}, 10, 2);

add_action('admin_notices', function() {
    $post = get_post(2024);
    if ($post) {
        echo '<div class="notice notice-error"><p>Post ID 2024 exists: ' . $post->post_title . '</p></div>';
    }
});


/**
 * add 'case' post type to all archives
 */
add_action('pre_get_posts', function (WP_Query $query) {
    // Front-end main archive queries only
    if (is_admin() || !$query->is_main_query() || !$query->is_archive()) {
        return;
    }

    // Don't override the 'case' archive itself
    if ($query->is_post_type_archive('case')) {
        return;
    }

    // Respect queries that already explicitly set post_type to something non-default
    $pt = $query->get('post_type');

    if (empty($pt)) {
        // Default archive queries look only for 'post'
        $query->set('post_type', ['post', 'case']);
        return;
    }

    // Normalize to array and add 'case'
    $pt = (array) $pt;
    $pt[] = 'case';
    $pt = array_values(array_unique($pt));

    $query->set('post_type', $pt);
});