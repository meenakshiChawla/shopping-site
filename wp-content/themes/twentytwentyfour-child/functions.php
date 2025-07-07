<?php
add_filter('xmlrpc_enabled', '__return_false');
// Enqueue parent and child styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_uri(), ['parent-style']);

    // Enqueue custom JS
    wp_enqueue_script('custom-js', get_stylesheet_directory_uri() . '/custom.js', [], null, true);
});


function enqueue_project_ajax_filter() {
    wp_enqueue_script('project-filter', get_stylesheet_directory_uri() . '/js/project-filter.js', ['jquery'], null, true);

    wp_localize_script('project-filter', 'project_ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_project_ajax_filter');


function filter_projects_by_taxonomy_ajax() {
  $project_type = isset($_POST['project_type']) ? sanitize_text_field($_POST['project_type']) : '';

  $args = [
    'post_type' => 'projects',
    'posts_per_page' => -1,
  ];

  if (!empty($project_type)) {
    $args['tax_query'] = [
      [
        'taxonomy' => 'project_type',
        'field' => 'slug',
        'terms' => $project_type,
      ]
    ];
  }

  $query = new WP_Query($args);

  if ($query->have_posts()) :
    while ($query->have_posts()) : $query->the_post(); ?>
      <div class="project-item">
        <h2><?php the_title(); ?></h2>
        <?php the_excerpt(); ?>
      </div>
    <?php endwhile;
  else :
    echo '<p>No projects found.</p>';
  endif;

  wp_die();
}
add_action('wp_ajax_filter_projects', 'filter_projects_by_taxonomy_ajax');
add_action('wp_ajax_nopriv_filter_projects', 'filter_projects_by_taxonomy_ajax');


// ✅ Register "Projects" Custom Post Type
function register_projects_cpt() {
    $labels = [
        'name'               => __('Projects'),
        'singular_name'      => __('Project'),
        'add_new'            => __('Add New Project'),
        'add_new_item'       => __('Add New Project'),
        'edit_item'          => __('Edit Project'),
        'new_item'           => __('New Project'),
        'view_item'          => __('View Project'),
        'search_items'       => __('Search Projects'),
        'not_found'          => __('No projects found'),
        'not_found_in_trash' => __('No projects found in Trash'),
        'all_items'          => __('All Projects'),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'has_archive'        => true,
        'rewrite'            => ['slug' => 'projects'],
        'supports'           => ['title', 'editor', 'excerpt', 'thumbnail'],
        'show_in_rest'       => true, // Enables Gutenberg and REST API
        'menu_icon'          => 'dashicons-portfolio', // Optional icon
    ];

    register_post_type('projects', $args);
}
add_action('init', 'register_projects_cpt');

// ✅ Register "Project Type" Taxonomy for Projects CPT
function register_project_type_taxonomy() {
    $labels = [
        'name'              => __('Project Types'),
        'singular_name'     => __('Project Type'),
        'search_items'      => __('Search Project Types'),
        'all_items'         => __('All Project Types'),
        'edit_item'         => __('Edit Project Type'),
        'update_item'       => __('Update Project Type'),
        'add_new_item'      => __('Add New Project Type'),
        'new_item_name'     => __('New Project Type Name'),
        'menu_name'         => __('Project Types'),
    ];

    $args = [
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_admin_column' => true,
        'rewrite'           => ['slug' => 'project-type'],
        'show_in_rest'      => true,
    ];

    register_taxonomy('project_type', ['projects'], $args);
}
add_action('init', 'register_project_type_taxonomy');

// Register the custom post type: team_member
function register_team_member_cpt() {
    register_post_type('team_member', [
        'label' => 'Team Members',
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'team-members'],
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'register_team_member_cpt');

// Register the custom taxonomy: department
function register_team_member_taxonomy() {
    register_taxonomy('department', 'team_member', [
        'label'        => 'Departments',
        'public'       => true,
        'hierarchical' => true,
        'rewrite'      => ['slug' => 'department'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'register_team_member_taxonomy');

// Shortcode to display team members
function shortcode_team_members($atts) {
    $atts = shortcode_atts([
        'department'     => '',
        'orderby'        => 'title',
        'order'          => 'ASC',
        'posts_per_page' => 10,
    ], $atts, 'team_members');

    // Generate a unique cache key
    $cache_key = 'team_members_' . md5(json_encode($atts));
    $output = get_transient($cache_key);

    if ($output === false) {
        $args = [
            'post_type'      => 'team_member',
            'posts_per_page' => intval($atts['posts_per_page']),
            'orderby'        => sanitize_key($atts['orderby']),
            'order'          => sanitize_key($atts['order']),
        ];

        // Add taxonomy filter if specified
        if (!empty($atts['department'])) {
            $args['tax_query'] = [[
                'taxonomy' => 'department',
                'field'    => 'slug',
                'terms'    => sanitize_text_field($atts['department']),
            ]];
        }

        $query = new WP_Query($args);

        ob_start();

        if ($query->have_posts()) {
            echo '<div class="team-members">';
            while ($query->have_posts()) {
                $query->the_post();
                echo '<div class="team-member">';
                if (has_post_thumbnail()) {
                    the_post_thumbnail('medium');
                }
                echo '<h3>' . get_the_title() . '</h3>';
                echo '<div class="bio">' . get_the_excerpt() . '</div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p>No team members found.</p>';
        }

        wp_reset_postdata();

        $output = ob_get_clean();
        // Save output to cache for 12 hours
        set_transient($cache_key, $output, 12 * HOUR_IN_SECONDS);
    }

    return $output;
}
add_shortcode('team_members', 'shortcode_team_members');
function clear_team_members_cache() {
    global $wpdb;
    $transients = $wpdb->get_col(
        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_team_members_%'"
    );
    foreach ($transients as $transient) {
        $key = str_replace('_transient_', '', $transient);
        delete_transient($key);
    }
}
add_action('save_post_team_member', 'clear_team_members_cache');

// Show custom checkout fields
add_action('woocommerce_checkout_after_customer_details', 'ttfc_add_custom_checkout_fields');

function ttfc_add_custom_checkout_fields($checkout = null) {
    // Fallback if $checkout not passed
    if (empty($checkout) || !is_object($checkout)) {
        $checkout = WC()->checkout(); // ← this is the fix
    }

    echo '<div id="ttfc_custom_fields"><h3>' . __('Additional Information') . '</h3>';

    // How did you hear about us
    woocommerce_form_field('how_heard', array(
        'type'     => 'select',
        'class'    => array('form-row-wide'),
        'label'    => __('How did you hear about us?'),
        'required' => true,
        'options'  => array(
            '' => 'Select one',
            'google' => 'Google',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'friend' => 'Friend',
            'other' => 'Other'
        )
    ), $checkout->get_value('how_heard'));

    // GDPR checkbox
    woocommerce_form_field('gdpr_consent', array(
        'type'     => 'checkbox',
        'class'    => array('form-row-wide'),
        'label'    => __('I agree to the processing of my data in accordance with the <a href="/privacy-policy" target="_blank">privacy policy</a>.'),
        'required' => true,
    ), $checkout->get_value('gdpr_consent'));

    echo '</div>';
}

// Validate custom fields
add_action('woocommerce_checkout_process', 'ttfc_validate_custom_checkout_fields');
function ttfc_validate_custom_checkout_fields() {
    if (empty($_POST['how_heard'])) {
        wc_add_notice(__('Please tell us how you heard about us.'), 'error');
    }
    if (empty($_POST['gdpr_consent'])) {
        wc_add_notice(__('Please agree to the privacy policy.'), 'error');
    }
}

// Save custom field values to order meta
add_action('woocommerce_checkout_update_order_meta', 'ttfc_save_custom_checkout_fields');
function ttfc_save_custom_checkout_fields($order_id) {
    if (isset($_POST['how_heard'])) {
        update_post_meta($order_id, '_how_heard', sanitize_text_field($_POST['how_heard']));
    }
    if (isset($_POST['gdpr_consent'])) {
        update_post_meta($order_id, '_gdpr_consent', 'Yes');
    }
}

// Display field values in admin
add_action('woocommerce_admin_order_data_after_billing_address', 'ttfc_show_custom_checkout_fields_admin', 10, 1);
function ttfc_show_custom_checkout_fields_admin($order) {
    echo '<p><strong>' . __('How Heard:') . '</strong> ' . esc_html(get_post_meta($order->get_id(), '_how_heard', true)) . '</p>';
    echo '<p><strong>' . __('GDPR Consent:') . '</strong> ' . esc_html(get_post_meta($order->get_id(), '_gdpr_consent', true)) . '</p>';
}

add_action('wp_enqueue_scripts', 'ttfc_enqueue_woocommerce_scripts', 20);
function ttfc_enqueue_woocommerce_scripts() {
    if (function_exists('is_woocommerce') && (is_cart() || is_checkout())) {
        wp_enqueue_script('wc-cart');
        wp_enqueue_script('wc-checkout');
    }
}

// 1. Register custom cron schedule (hourly)
add_filter('cron_schedules', 'register_custom_cron_intervals');
function register_custom_cron_intervals($schedules) {
    $schedules['every_hour'] = array(
        'interval' => 3600,
        'display'  => __('Every Hour')
    );
    return $schedules;
}

// 2. Schedule cron event if not already scheduled
add_action('init', 'schedule_expired_post_cron');
function schedule_expired_post_cron() {
    if (!wp_next_scheduled('auto_draft_expired_posts')) {
        wp_schedule_event(time(), 'every_hour', 'auto_draft_expired_posts');
    }
}

// 3. Define the cron task callback
add_action('auto_draft_expired_posts', 'handle_expired_posts_to_draft');
function handle_expired_posts_to_draft() {
    $now = current_time('mysql'); // Format: Y-m-d H:i:s

    $args = array(
        'post_type'      => 'post', // Change to your custom post type if needed
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'     => 'expiry_date',
                'value'   => $now,
                'compare' => '<=',
                'type'    => 'DATETIME'
            )
        ),
        'posts_per_page' => -1
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            wp_update_post(array(
                'ID'          => $post->ID,
                'post_status' => 'draft'
            ));
        }
    }

    wp_reset_postdata();
}

/*add_action('shutdown', function() {
    $timestamp = wp_next_scheduled('auto_draft_expired_posts');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'auto_draft_expired_posts');
    }
}); for remove cron job*/


add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/latest-posts', array(
        'methods'  => 'GET',
        'callback' => 'get_latest_five_posts',
        'permission_callback' => '__return_true'
    ));
});

function get_latest_five_posts() {
    $args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 5
    );

    $query = new WP_Query($args);
    $posts = [];

    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            $post_id = $post->ID;

            $posts[] = [
                'title'          => get_the_title($post_id),
                'excerpt'        => get_the_excerpt($post_id),
                'featured_image' => get_the_post_thumbnail_url($post_id, 'medium'),
                'categories'     => get_the_category_names($post_id)
            ];
        }
    }

    return $posts;
}

function get_the_category_names($post_id) {
    $categories = get_the_category($post_id);
    if (empty($categories)) return [];
    return wp_list_pluck($categories, 'name');
}

//contactus form code and properly sanitize user input
// Create custom table if not exists (runs only once)
global $wpdb;
$table = $wpdb->prefix . 'custom_contacts';
if ($wpdb->get_var("SHOW TABLES LIKE '$table'") !== $table) {
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        phone varchar(20),
        message text NOT NULL,
        submitted_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}


add_shortcode('custom_contact_form', 'render_custom_contact_form');
function render_custom_contact_form() {
    ob_start(); ?>
    
    <form id="custom-contact-form" method="post">
        <input type="text" name="cc_name" placeholder="Your Name" required><br/><br/>
        <input type="email" name="cc_email" placeholder="Your Email" required><br/><br/>
        <input type="text" name="cc_phone" placeholder="Phone"><br/><br/>
        <textarea name="cc_message" placeholder="Your Message" required></textarea><br/><br/>
        <button type="submit" id="submit-btn">Submit</button>
		  <span id="form-loader" style="display:none;">
            ⏳ Sending...
        </span>
        <div id="cc-response" style="margin-top:10px;color:green;"></div>
    </form>
<script>
document.getElementById("custom-contact-form").addEventListener("submit", function(e){
    e.preventDefault();

    let form = e.target;
    let formData = new FormData(form);

    // Show loader, disable button
    document.getElementById("form-loader").style.display = "inline";
    document.getElementById("submit-btn").disabled = true;

    fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
        method: "POST",
        body: new URLSearchParams([...formData, ['action', 'handle_custom_contact_form']])
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById("cc-response").innerText = data;
        form.reset();
    })
    .catch(() => {
        document.getElementById("cc-response").innerText = "Something went wrong. Please try again.";
    })
    .finally(() => {
        // Hide loader, re-enable button
        document.getElementById("form-loader").style.display = "none";
        document.getElementById("submit-btn").disabled = false;
    });
});
</script>

    <?php return ob_get_clean();
}

add_action('wp_ajax_handle_custom_contact_form', 'handle_custom_contact_form');
add_action('wp_ajax_nopriv_handle_custom_contact_form', 'handle_custom_contact_form');

function handle_custom_contact_form() {
    global $wpdb;
    $table = $wpdb->prefix . 'custom_contacts';

    $name    = sanitize_text_field($_POST['cc_name'] ?? '');
    $email   = sanitize_email($_POST['cc_email'] ?? '');
    $phone   = sanitize_text_field($_POST['cc_phone'] ?? '');
    $message = sanitize_textarea_field($_POST['cc_message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        echo 'Please fill in all required fields.';
        wp_die();
    }

    // Save to DB
    $wpdb->insert($table, [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'message' => $message,
    ]);

    // Send email
    $admin_email = 'chawla.meenakshi19@gmail.com';
    $subject = "New Contact Message from $name";
    $body = "Name: $name\nEmail: $email\nPhone: $phone\n\nMessage:\n$message";
    $headers = ['Content-Type: text/plain; charset=UTF-8'];

    wp_mail($admin_email, $subject, $body, $headers);

    echo 'Thank you! Your message has been sent.';
    wp_die();
}

