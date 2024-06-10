<?php
/*
Plugin Name: Datenator
Description: A plugin to dynamically update the published date for all existing posts.
Version: 1.0
Author: Driving Theory Test
Author URI: https://drivingtheorytest.info/
*/

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add admin menu
function upd_add_admin_menu() {
    add_options_page(
        'Update Published Date Settings',
        'Update Published Date',
        'manage_options',
        'upd-settings',
        'upd_settings_page'
    );
}
add_action( 'admin_menu', 'upd_add_admin_menu' );

// Settings page content
function upd_settings_page() {
    ?>
    <div class="wrap">
        <h1>Update Published Date Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'upd_settings_group' );
            do_settings_sections( 'upd-settings' );
            submit_button();
            ?>
        </form>
        <form method="post" action="">
            <input type="hidden" name="upd_update_posts" value="1" />
            <?php submit_button('Start Updating Post Dates'); ?>
        </form>
    </div>
    <?php
}

// Register settings
function upd_register_settings() {
    register_setting( 'upd_settings_group', 'upd_post_date' );

    add_settings_section(
        'upd_settings_section',
        'Date Settings',
        'upd_settings_section_callback',
        'upd-settings'
    );

    add_settings_field(
        'upd_post_date',
        'New Post Date',
        'upd_post_date_callback',
        'upd-settings',
        'upd_settings_section'
    );
}
add_action( 'admin_init', 'upd_register_settings' );

// Settings section callback
function upd_settings_section_callback() {
    echo 'Set the new date for all published posts.';
}

// Date field callback
function upd_post_date_callback() {
    $date = get_option( 'upd_post_date', '' );
    echo '<input type="datetime-local" id="upd_post_date" name="upd_post_date" value="' . esc_attr( $date ) . '" />';
}

// Update post dates function
function upd_update_post_dates() {
    if ( isset( $_POST['upd_update_posts'] ) && $_POST['upd_update_posts'] == '1' ) {
        $new_date = get_option( 'upd_post_date', date( 'Y-m-d H:i:s' ) );

        $args = array(
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        );

        $query = new WP_Query( $args );

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $current_post_id = get_the_ID();
                wp_update_post( array(
                    'ID' => $current_post_id,
                    'post_date' => $new_date,
                    'post_date_gmt' => get_gmt_from_date( $new_date ),
                ) );
            }
            wp_reset_postdata();
        }
    }
}
add_action( 'admin_init', 'upd_update_post_dates' );
?>
