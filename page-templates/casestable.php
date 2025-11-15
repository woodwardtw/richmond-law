<?php
/**
 * Template Name: Cases Table
 *
 * Template for displaying cases in a filterable table format
 *
 * @package Understrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

$container = get_theme_mod( 'understrap_container_type' );

// Handle Publish All action
$publish_message = '';
if (isset($_POST['publish_all_drafts']) && check_admin_referer('publish_all_cases_action', 'publish_all_cases_nonce')) {
    // Get filter values from POST to match the same query
    $selected_term = isset($_POST['case_term']) ? sanitize_text_field($_POST['case_term']) : '';
    $selected_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    $selected_post_status = isset($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) : '';
    $search_query_post = isset($_POST['case_search']) ? sanitize_text_field($_POST['case_search']) : '';

    // Apply search filter if search query is provided
    if (!empty($search_query_post)) {
        global $cases_search_term;
        $cases_search_term = $search_query_post;
        add_filter('posts_where', 'cases_search_where_filter', 10, 2);
        add_filter('posts_distinct', 'cases_search_distinct', 10, 2);
    }

    // Build args to get ALL drafts in the filtered set (not just current page)
    $publish_args = array(
        'post_type' => 'case',
        'posts_per_page' => -1,
        'post_status' => 'draft',
        'orderby' => 'title',
        'order' => 'ASC',
        'fields' => 'ids',
        'suppress_filters' => false
    );

    // Add search parameter to trigger WordPress search
    if (!empty($search_query_post)) {
        $publish_args['s'] = $search_query_post;
    }

    // Add tax queries if filters are selected
    $publish_tax_query = array();

    if (!empty($selected_term)) {
        $publish_tax_query[] = array(
            'taxonomy' => 'case_terms',
            'field' => 'slug',
            'terms' => $selected_term,
        );
    }

    if (!empty($selected_status)) {
        $publish_tax_query[] = array(
            'taxonomy' => 'status',
            'field' => 'slug',
            'terms' => $selected_status,
        );
    }

    if (!empty($publish_tax_query)) {
        $publish_tax_query['relation'] = 'AND';
        $publish_args['tax_query'] = $publish_tax_query;
    }

    $draft_ids = get_posts($publish_args);

    // Remove the search filters after query is complete
    if (!empty($search_query_post)) {
        remove_filter('posts_where', 'cases_search_where_filter', 10);
        remove_filter('posts_distinct', 'cases_search_distinct', 10);
        unset($GLOBALS['cases_search_term']);
    }

    if (!empty($draft_ids)) {
        $published_count = 0;
        foreach ($draft_ids as $post_id) {
            $result = wp_update_post(array(
                'ID' => $post_id,
                'post_status' => 'publish'
            ));
            if ($result) {
                $published_count++;
            }
        }
        $publish_message = '<div class="alert alert-success"><strong>Success!</strong> Published ' . $published_count . ' draft case(s).</div>';
    } else {
        $publish_message = '<div class="alert alert-info"><strong>Notice:</strong> No draft cases found to publish.</div>';
    }
}

// Get filter values from URL
$selected_term = isset($_GET['case_term']) ? sanitize_text_field($_GET['case_term']) : '';
$selected_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$selected_post_status = isset($_GET['post_status']) ? sanitize_text_field($_GET['post_status']) : '';
$search_query = isset($_GET['case_search']) ? sanitize_text_field($_GET['case_search']) : '';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Define search filter functions (only once)
if (!function_exists('cases_search_where_filter')) {
    function cases_search_where_filter($where, $query) {
        global $wpdb, $cases_search_term;

        // Only add custom search for case queries with our search term
        if (!empty($cases_search_term) && !is_admin() && $query->get('post_type') === 'case') {
            $search_like = '%' . $wpdb->esc_like($cases_search_term) . '%';

            // Add OR condition for postmeta search
            $custom_where = $wpdb->prepare(
                " OR {$wpdb->posts}.ID IN (
                    SELECT post_id FROM {$wpdb->postmeta}
                    WHERE meta_value LIKE %s
                )",
                $search_like
            );

            // Find the last occurrence of WordPress's search closing parens and add our condition
            if (strpos($where, 'post_title LIKE') !== false) {
                // WordPress has added search - extend it
                $where = preg_replace('/(\)\)\))/', $custom_where . '$1', $where, 1);
            }
        }
        return $where;
    }

    function cases_search_distinct($distinct, $query) {
        global $cases_search_term;

        // Make results distinct when searching meta to avoid duplicates
        if (!empty($cases_search_term) && !is_admin() && $query->get('post_type') === 'case') {
            return 'DISTINCT';
        }
        return $distinct;
    }
}

// Apply search filter if search query is provided
if (!empty($search_query)) {
    global $cases_search_term;
    $cases_search_term = $search_query;
    add_filter('posts_where', 'cases_search_where_filter', 10, 2);
    add_filter('posts_distinct', 'cases_search_distinct', 10, 2);
}

// Build query args
$args = array(
    'post_type' => 'case',
    'posts_per_page' => 50,
    'paged' => $paged,
    'post_status' => !empty($selected_post_status) ? $selected_post_status : array('publish', 'draft'),
    'orderby' => 'title',
    'order' => 'ASC',
    'suppress_filters' => false
);

// Add search parameter to trigger WordPress search
if (!empty($search_query)) {
    $args['s'] = $search_query;
}

// Add tax queries if filters are selected
$tax_query = array();

if (!empty($selected_term)) {
    $tax_query[] = array(
        'taxonomy' => 'case_terms',
        'field' => 'slug',
        'terms' => $selected_term,
    );
}

if (!empty($selected_status)) {
    $tax_query[] = array(
        'taxonomy' => 'status',
        'field' => 'slug',
        'terms' => $selected_status,
    );
}

if (!empty($tax_query)) {
    $tax_query['relation'] = 'AND';
    $args['tax_query'] = $tax_query;
}

$cases_query = new WP_Query($args);

// Count drafts in the current filtered set (all pages, not just current)
$draft_count_args = $args;
$draft_count_args['posts_per_page'] = -1;
$draft_count_args['post_status'] = 'draft';
$draft_count_args['fields'] = 'ids';
$draft_count_query = new WP_Query($draft_count_args);
$total_draft_count = $draft_count_query->found_posts;
wp_reset_postdata();

// Remove the search filters after queries are complete
if (!empty($search_query)) {
    remove_filter('posts_where', 'cases_search_where_filter', 10);
    remove_filter('posts_distinct', 'cases_search_distinct', 10);
    unset($GLOBALS['cases_search_term']);
}
?>

<div class="wrapper" id="page-wrapper">

    <div class="<?php echo esc_attr( $container ); ?>" id="content" tabindex="-1">

        <div class="row">

            <main class="site-main col-md-12" id="main">

                <?php while ( have_posts() ) : the_post(); ?>
                    <h1 class="entry-title"><?php the_title(); ?></h1>

                    <?php if ( get_the_content() ) : ?>
                        <div class="entry-content">
                            <?php the_content(); ?>
                        </div>
                    <?php endif; ?>
                <?php endwhile; ?>

                <!-- Publish Message -->
                <?php if (!empty($publish_message)) : ?>
                    <?php echo $publish_message; ?>
                <?php endif; ?>

                <!-- Filter Form -->
                <div class="cases-filters" style="margin: 30px 0; padding: 20px; background: #f5f5f5; border-radius: 5px;">
                    <form method="get" action="<?php echo esc_url(get_permalink()); ?>">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="search" style="font-weight: bold; display: block; margin-bottom: 5px;">Search:</label>
                                <input type="text" name="case_search" id="search" class="form-control" placeholder="Search cases by title or content..." value="<?php echo esc_attr($search_query); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="case_term" style="font-weight: bold; display: block; margin-bottom: 5px;">Term:</label>
                                <select name="case_term" id="case_term" class="form-control">
                                    <option value="">All Terms</option>
                                    <?php
                                    $terms = get_terms(array(
                                        'taxonomy' => 'case_terms',
                                        'hide_empty' => false,
                                    ));
                                    if (!empty($terms) && !is_wp_error($terms)) :
                                        foreach ($terms as $term) :
                                            // Remove "term-" prefix from display name
                                            $display_name = preg_replace('/^term-/i', '', $term->name);
                                            ?>
                                            <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($selected_term, $term->slug); ?>>
                                                <?php echo esc_html($display_name); ?>
                                            </option>
                                        <?php endforeach;
                                    endif;
                                    ?>
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="status" style="font-weight: bold; display: block; margin-bottom: 5px;">Status:</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <?php
                                    $statuses = get_terms(array(
                                        'taxonomy' => 'status',
                                        'hide_empty' => false,
                                    ));
                                    if (!empty($statuses) && !is_wp_error($statuses)) :
                                        foreach ($statuses as $status) :
                                            ?>
                                            <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($selected_status, $status->slug); ?>>
                                                <?php echo esc_html($status->name); ?>
                                            </option>
                                        <?php endforeach;
                                    endif;
                                    ?>
                                </select>
                            </div>
                    <?php if (current_user_can('manage_options')) : ?>                            
                            <div class="col-md-4 mb-3">
                                <label for="post_status" style="font-weight: bold; display: block; margin-bottom: 5px;">Post Status:</label>
                                <select name="post_status" id="post_status" class="form-control">
                                    <option value="">All Statuses (Published & Draft)</option>
                                    <option value="publish" <?php selected($selected_post_status, 'publish'); ?>>Published</option>
                                    <option value="draft" <?php selected($selected_post_status, 'draft'); ?>>Draft</option>
                                    <option value="pending" <?php selected($selected_post_status, 'pending'); ?>>Pending</option>
                                    <option value="private" <?php selected($selected_post_status, 'private'); ?>>Private</option>
                                </select>
                            </div>
                        <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-secondary">Filter Cases</button>
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="btn btn-secondary">Reset Filters</a>
                    </form>
                </div>

                <!-- Results Count and Publish All Button -->
                <div class="cases-count" style="margin: 20px 0; display: flex; justify-content: space-between; align-items: center;">
                    <p style="margin: 0;"><strong>Showing <?php echo $cases_query->post_count; ?> of <?php echo $cases_query->found_posts; ?> cases</strong></p>

                        <form method="post" action="" id="publish-all-form" style="margin: 0;">
                            <?php wp_nonce_field('publish_all_cases_action', 'publish_all_cases_nonce'); ?>
                            <input type="hidden" name="case_term" value="<?php echo esc_attr($selected_term); ?>">
                            <input type="hidden" name="status" value="<?php echo esc_attr($selected_status); ?>">
                            <input type="hidden" name="post_status" value="<?php echo esc_attr($selected_post_status); ?>">
                            <input type="hidden" name="case_search" value="<?php echo esc_attr($search_query); ?>">
                    <?php if ($total_draft_count > 0 && current_user_can('manage_options')) : ?>                            
                            <button type="submit" name="publish_all_drafts" class="btn btn-success btn-secondary" onclick="return confirm('Are you sure you want to publish all <?php echo $total_draft_count; ?> draft case(s) in this filtered set? This action cannot be undone.');">
                                Publish All (<?php echo $total_draft_count; ?> drafts)
                            </button>
                     <?php endif; ?>
                        </form>                   
                </div>

                <!-- Cases Table -->
                <?php if ($cases_query->have_posts()) : ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered cases-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Term</th>
                                    <th>Record Number</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($cases_query->have_posts()) : $cases_query->the_post(); ?>
                                    <tr>
                                        <td>
                                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            <?php if (get_post_status() === 'draft') : ?>
                                                <span class="badge badge-warning" style="margin-left: 5px;">Draft</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $case_terms = get_the_terms(get_the_ID(), 'case_terms');
                                            if ($case_terms && !is_wp_error($case_terms)) {
                                                $terms_array = array();
                                                foreach ($case_terms as $case_term) {
                                                    // Remove "term-" prefix from display name
                                                    $display_name = preg_replace('/^term-/i', '', $case_term->name);
                                                    $terms_array[] = esc_html($display_name);
                                                }
                                                echo implode(', ', $terms_array);
                                            } else {
                                                echo '&mdash;';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $record_number = get_field('record_number');
                                            echo $record_number ? esc_html($record_number) : '&mdash;';
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            $post_statuses = get_the_terms(get_the_ID(), 'status');
                                            if ($post_statuses && !is_wp_error($post_statuses)) {
                                                $status_array = array();
                                                foreach ($post_statuses as $post_status) {
                                                    $status_array[] = esc_html($post_status->name);
                                                }
                                                echo implode(', ', $status_array);
                                            } else {
                                                echo '&mdash;';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="cases-pagination" style="margin: 30px 0;">
                        <?php
                        $pagination_args = array(
                            'total' => $cases_query->max_num_pages,
                            'current' => $paged,
                            'prev_text' => __('&laquo; Previous'),
                            'next_text' => __('Next &raquo;'),
                            'type' => 'list',
                            'add_args' => array(
                                'case_term' => $selected_term,
                                'status' => $selected_status,
                                'post_status' => $selected_post_status,
                                'case_search' => $search_query,
                            ),
                        );
                        echo paginate_links($pagination_args);
                        ?>
                    </div>

                <?php else : ?>
                    <div class="alert alert-info">
                        <p>No cases found matching your criteria.</p>
                    </div>
                <?php endif; ?>

                <?php wp_reset_postdata(); ?>

            </main>

        </div><!-- .row -->

    </div><!-- #content -->

</div><!-- #page-wrapper -->


<?php
get_footer();
