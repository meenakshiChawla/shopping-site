<?php
/**
 * Template Name: Custom Contact Form
 */

// Create custom table if not exists (runs only once)
global $wpdb;
$table = $wpdb->prefix . 'custom_template_contacts';
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

get_header(); ?>

<div class="container">
    <h2>Contact Us</h2>
    <form id="custom-contact-form" method="post">
        <input type="text" name="cc_name" placeholder="Your Name" required><br><br>
        <input type="email" name="cc_email" placeholder="Your Email" required><br><br>
        <input type="text" name="cc_phone" placeholder="Phone"><br><br>
        <textarea name="cc_message" placeholder="Your Message" required></textarea><br><br>
        <button type="submit" id="submit-btn">Submit</button>
        <span id="form-loader" style="display:none;">
            ⏳ Sending...
        </span>
        <div id="cc-response" style="margin-top:10px;color:green;"></div>
    </form>
</div>

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
        body: new URLSearchParams([...formData, ['action', 'handle_custom_contact_form_inline']])
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

<?php get_footer(); ?>
