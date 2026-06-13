<?php
/**
 * Plugin Name: LOE Woo Messages
 * Author: Loeion
 * Author URI: https://www.loeion.com
 * Description: LOE Woo Messages - Interaction with users and vendors. Discussion Enquiry [loe-frontend-discussions]
 * Plugin URI: https://www.loeion.com/loe-woo-messages
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * License: GPL2
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// ─────────────────────────────────────────────
// CONSTANTS
// ─────────────────────────────────────────────
define( 'LOE_WOO_MSG_VERSION', '1.0.0' );
define( 'LOE_WOO_MSG_FILE',    __FILE__ );
define( 'LOE_WOO_MSG_DIR',     plugin_dir_path( __FILE__ ) );
define( 'LOE_WOO_MSG_URL',     plugin_dir_url( __FILE__ ) );
define( 'LOE_WOO_MSG_TABLE',   'loe_woo_messages' );
define( 'LOE_FROM_EMAIL',      'no-reply@loeion.com' );
define( 'LOE_FROM_NAME',       'Loeion' );

// ─────────────────────────────────────────────
// ADMIN MENU
// ─────────────────────────────────────────────
function loe_woo_message_admin_menu() {
    add_menu_page(
        'Woo Messages',
        'Woo Messages',
        'manage_options',          // Changed from 'read' — only admins should see this
        LOE_WOO_MSG_FILE,
        'loe_woo_messages_page',
        'dashicons-chat',
        7
    );
}
add_action( 'admin_menu', 'loe_woo_message_admin_menu' );

function loe_woo_messages_page() {
    include LOE_WOO_MSG_DIR . 'inc/loe-woo-messages.php';
}

// ─────────────────────────────────────────────
// INSTALL — Create DB Table on Activation
// ─────────────────────────────────────────────
function loe_woo_message_install() {
    global $wpdb;

    $table_name      = $wpdb->prefix . LOE_WOO_MSG_TABLE;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        `id`             int(11)      NOT NULL AUTO_INCREMENT,
        `vendor_id`      varchar(56)  NOT NULL DEFAULT '',
        `userid`         varchar(255) NOT NULL DEFAULT '',
        `user_email`     varchar(255) NOT NULL DEFAULT '',
        `title`          varchar(255) NOT NULL DEFAULT '',
        `message`        longtext     NOT NULL,
        `product_id`     varchar(11)  NOT NULL DEFAULT '',
        `status`         varchar(255) NOT NULL DEFAULT '',
        `chat_unique_id` varchar(255) NOT NULL DEFAULT '',
        `replyto`        varchar(255) NOT NULL DEFAULT '',
        `user_type`      varchar(50)  NOT NULL DEFAULT '',
        `last_update`    timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY chat_unique_id (chat_unique_id),
        KEY replyto (replyto)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'loe_woo_message_install' );

// ─────────────────────────────────────────────
// FRONT-END — Enquiry Form below Add to Cart
// ─────────────────────────────────────────────
function loe_woo_message_form() {
    include LOE_WOO_MSG_DIR . 'templates/loe-WooMessage.php';
}
add_action( 'woocommerce_after_add_to_cart_button', 'loe_woo_message_form' );

// ─────────────────────────────────────────────
// HELPER — Get administrator email safely
// ─────────────────────────────────────────────
function loe_get_administrator_email() {
    $admins = get_users( array( 'role' => 'Administrator', 'number' => 1 ) );
    if ( ! empty( $admins ) ) {
        return $admins[0]->user_email;
    }
    return get_option( 'admin_email' ); // Fallback to WordPress site admin email
}

// ─────────────────────────────────────────────
// HELPER — Shared email headers
// ─────────────────────────────────────────────
function loe_email_headers() {
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= 'From: ' . LOE_FROM_NAME . ' <' . LOE_FROM_EMAIL . '>';
    return $headers;
}

// ─────────────────────────────────────────────
// AJAX — Send New Enquiry
// ─────────────────────────────────────────────
add_action( 'wp_ajax_nopriv_loe_send_woo_message', 'loe_send_woo_message' );
add_action( 'wp_ajax_loe_send_woo_message',        'loe_send_woo_message' );

function loe_send_woo_message() {

    // Nonce verification
    check_ajax_referer( 'loe_woo_msg_nonce', 'nonce' );

    global $wpdb;
    $table_name = $wpdb->prefix . LOE_WOO_MSG_TABLE;

    // Sanitize all inputs
    $user_id        = sanitize_text_field( $_REQUEST['userId'] ?? '' );
    $title          = sanitize_text_field( $_REQUEST['title'] ?? '' );
    $vendor_id      = absint( $_REQUEST['vendor_id'] ?? 0 );
    $product_id     = absint( $_REQUEST['product_id'] ?? 0 );
    $message        = sanitize_textarea_field( $_REQUEST['message'] ?? '' );
    $user_email     = sanitize_email( $_REQUEST['user_email'] ?? '' );
    $chat_unique_id = sanitize_text_field( $_REQUEST['chat_unique_id'] ?? '' );

    // Basic validation
    if ( empty( $user_email ) || ! is_email( $user_email ) ) {
        wp_send_json_error( 'Invalid email address.' );
    }
    if ( empty( $message ) || empty( $title ) ) {
        wp_send_json_error( 'Message and title are required.' );
    }

    // ── Email to User ──
    $discussion_url = get_permalink( get_option( 'loe_discussion_page_id', 0 ) );
    $user_subject   = 'Discussion Unique ID for Your Enquiry';
    $user_msg       = 'We have received your enquiry.<br><br>'
                    . '<strong>Your Message:</strong> ' . esc_html( $message ) . '<br><br>'
                    . 'Your unique discussion ID is: <strong>' . esc_html( $chat_unique_id ) . '</strong><br>'
                    . '<a href="' . esc_url( $discussion_url ) . '" target="_blank">Start Conversation</a>';

    wp_mail( $user_email, $user_subject, $user_msg, loe_email_headers() );

    // ── Email to Admin ──
    $vendor         = get_user_by( 'ID', $vendor_id );
    $vendor_name    = $vendor ? $vendor->display_name : 'Unknown Vendor';
    $product_name   = get_the_title( $product_id );

    $admin_subject  = 'New Enquiry Received';
    $admin_msg      = 'A new enquiry has been received.<br><br>'
                    . '<strong>Vendor:</strong> '         . esc_html( $vendor_name )    . '<br>'
                    . '<strong>Product:</strong> '        . esc_html( $product_name )   . '<br>'
                    . '<strong>Enquiry By:</strong> '     . esc_html( $user_id )        . '<br>'
                    . '<strong>Email:</strong> '          . esc_html( $user_email )     . '<br>'
                    . '<strong>Title:</strong> '          . esc_html( $title )          . '<br>'
                    . '<strong>Message:</strong> '        . esc_html( $message )        . '<br><br>'
                    . '<strong>Discussion ID:</strong> '  . esc_html( $chat_unique_id ) . '<br>'
                    . '<a href="' . esc_url( $discussion_url ) . '" target="_blank">View Conversation</a>';

    $email_sent = wp_mail( loe_get_administrator_email(), $admin_subject, $admin_msg, loe_email_headers() );

    // ── Insert into DB ──
    $inserted = $wpdb->insert(
        $table_name,
        array(
            'userid'         => $user_id,
            'title'          => $title,
            'vendor_id'      => $vendor_id,
            'product_id'     => $product_id,
            'message'        => $message,
            'chat_unique_id' => $chat_unique_id,
            'user_email'     => $user_email,
            'user_type'      => 'user',
            'last_update'    => current_time( 'mysql' ),
        ),
        array( '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
    );

    if ( $inserted && $email_sent ) {
        wp_send_json_success( 'Enquiry sent successfully.' );
    } else {
        wp_send_json_error( 'Failed to save enquiry. Please try again.' );
    }
}

// ─────────────────────────────────────────────
// AJAX — Delete Message Thread
// ─────────────────────────────────────────────
add_action( 'wp_ajax_loe_woo_message_delete', 'loe_woo_message_delete' );

function loe_woo_message_delete() {

    // Only admins can delete
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Unauthorized.' );
    }

    check_ajax_referer( 'loe_woo_msg_nonce', 'nonce' );

    global $wpdb;
    $table_name = $wpdb->prefix . LOE_WOO_MSG_TABLE;
    $del_id     = absint( $_REQUEST['delId'] ?? 0 );

    if ( empty( $del_id ) ) {
        wp_send_json_error( 'Invalid ID.' );
    }

    // Get the chat_unique_id for this record first
    $chat_unique_id = $wpdb->get_var(
        $wpdb->prepare( "SELECT chat_unique_id FROM $table_name WHERE id = %d", $del_id )
    );

    if ( $chat_unique_id ) {
        // Delete entire thread by chat_unique_id
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table_name WHERE id = %d OR chat_unique_id = %s",
                $del_id,
                $chat_unique_id
            )
        );
        wp_send_json_success( 'Thread deleted.' );
    } else {
        wp_send_json_error( 'Record not found.' );
    }
}

// ─────────────────────────────────────────────
// AJAX — Admin/Vendor Send Reply
// ─────────────────────────────────────────────
add_action( 'wp_ajax_nopriv_loe_woo_send_reply', 'loe_woo_send_reply' );
add_action( 'wp_ajax_loe_woo_send_reply',        'loe_woo_send_reply' );

function loe_woo_send_reply() {

    check_ajax_referer( 'loe_woo_msg_nonce', 'nonce' );

    global $wpdb;
    $table_name = $wpdb->prefix . LOE_WOO_MSG_TABLE;

    $chat_unique_id  = sanitize_text_field( $_REQUEST['chatUniqueId']    ?? '' );
    $reply_message   = sanitize_textarea_field( $_REQUEST['replyMessage'] ?? '' );
    $reply_title     = sanitize_text_field( $_REQUEST['replyTitle']       ?? '' );
    $reply_username  = sanitize_text_field( $_REQUEST['replyUserName']    ?? '' );
    $reply_vendor    = absint( $_REQUEST['replyVendor']                   ?? 0 );
    $reply_product   = absint( $_REQUEST['replyProductId']                ?? 0 );

    if ( empty( $chat_unique_id ) || empty( $reply_message ) ) {
        wp_send_json_error( 'Missing required fields.' );
    }

    // Get user email from existing thread
    $user_email = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT user_email FROM $table_name WHERE chat_unique_id = %s LIMIT 1",
            $chat_unique_id
        )
    );

    if ( empty( $user_email ) ) {
        wp_send_json_error( 'Thread not found.' );
    }

    // ── Email Reply to User ──
    $email_sent = wp_mail(
        $user_email,
        'Reply to Your Enquiry',
        wp_kses_post( $reply_message ),
        loe_email_headers()
    );

    // ── Insert Reply into DB ──
    $wpdb->insert(
        $table_name,
        array(
            'userid'         => $reply_username,
            'title'          => $reply_title,
            'vendor_id'      => $reply_vendor,
            'product_id'     => $reply_product,
            'message'        => $reply_message,
            'replyto'        => $chat_unique_id,
            'user_email'     => $user_email,
            'user_type'      => 'admin',
            'last_update'    => current_time( 'mysql' ),
        ),
        array( '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
    );

    if ( $email_sent ) {
        wp_send_json_success( 'Reply sent successfully.' );
    } else {
        wp_send_json_error( 'Reply saved but email failed.' );
    }
}

// ─────────────────────────────────────────────
// SHORTCODE — Frontend Discussion Thread
// ─────────────────────────────────────────────
function loe_frontend_discussion() {
    include LOE_WOO_MSG_DIR . 'templates/loe-WooDiscussion.php';
}
add_shortcode( 'loe-frontend-discussions', 'loe_frontend_discussion' );

// ─────────────────────────────────────────────
// AJAX — Check / Load Enquiry Thread
// ─────────────────────────────────────────────
add_action( 'wp_ajax_nopriv_loe_check_enquiry', 'loe_check_enquiry' );
add_action( 'wp_ajax_loe_check_enquiry',        'loe_check_enquiry' );

function loe_check_enquiry() {

    check_ajax_referer( 'loe_woo_msg_nonce', 'nonce' );

    global $wpdb;
    $table_name  = $wpdb->prefix . LOE_WOO_MSG_TABLE;
    $enquiry_id  = sanitize_text_field( $_REQUEST['enquiryId'] ?? '' );

    if ( empty( $enquiry_id ) ) {
        wp_send_json_error( 'Invalid enquiry ID.' );
    }

    $data = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE chat_unique_id = %s
             UNION
             SELECT * FROM $table_name WHERE replyto = %s",
            $enquiry_id,
            $enquiry_id
        ),
        OBJECT
    );

    wp_send_json_success( $data );
}

// ─────────────────────────────────────────────
// AJAX — User Reply to Thread
// ─────────────────────────────────────────────
add_action( 'wp_ajax_nopriv_loe_users_reply', 'loe_users_reply' );
add_action( 'wp_ajax_loe_users_reply',        'loe_users_reply' );

function loe_users_reply() {

    check_ajax_referer( 'loe_woo_msg_nonce', 'nonce' );

    global $wpdb;
    $table_name    = $wpdb->prefix . LOE_WOO_MSG_TABLE;
    $enquiry_id    = sanitize_text_field( $_REQUEST['thread_id'] ?? '' );
    $reply_message = sanitize_textarea_field( $_REQUEST['message'] ?? '' );

    if ( empty( $enquiry_id ) || empty( $reply_message ) ) {
        wp_send_json_error( 'Missing required fields.' );
    }

    // Get original enquiry details
    $enquiry = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE chat_unique_id = %s LIMIT 1",
            $enquiry_id
        ),
        OBJECT
    );

    if ( empty( $enquiry ) ) {
        wp_send_json_error( 'Enquiry not found.' );
    }

    $user_id    = $enquiry->userid;
    $title      = $enquiry->title;
    $vendor_id  = $enquiry->vendor_id;
    $product_id = $enquiry->product_id;
    $user_email = $enquiry->user_email;

    // ── Insert User Reply ──
    $wpdb->insert(
        $table_name,
        array(
            'userid'         => $user_id,
            'title'          => $title,
            'vendor_id'      => $vendor_id,
            'product_id'     => $product_id,
            'message'        => $reply_message,
            'replyto'        => $enquiry_id,
            'user_email'     => $user_email,
            'user_type'      => 'user',
            'last_update'    => current_time( 'mysql' ),
        ),
        array( '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
    );

    // ── Email to Vendor ──
    $vendor       = get_user_by( 'id', $vendor_id );
    $vendor_email = $vendor ? $vendor->user_email : '';

    if ( $vendor_email ) {
        wp_mail(
            $vendor_email,
            'Reply from ' . esc_html( $user_id ),
            wp_kses_post( $reply_message ),
            loe_email_headers()
        );
    }

    // ── Email to Admin ──
    $product_name = get_the_title( $product_id );
    $vendor_name  = $vendor ? $vendor->display_name : 'Unknown';

    $discussion_url = get_permalink( get_option( 'loe_discussion_page_id', 0 ) );
    $admin_msg = 'A user has replied to an enquiry.<br><br>'
               . '<strong>Vendor:</strong> '      . esc_html( $vendor_name )  . '<br>'
               . '<strong>Product:</strong> '     . esc_html( $product_name ) . '<br>'
               . '<strong>User:</strong> '        . esc_html( $user_id )      . '<br>'
               . '<strong>Email:</strong> '       . esc_html( $user_email )   . '<br>'
               . '<strong>Title:</strong> '       . esc_html( $title )        . '<br>'
               . '<strong>Message:</strong> '     . esc_html( $reply_message ). '<br><br>'
               . '<strong>Discussion ID:</strong> ' . esc_html( $enquiry_id ) . '<br>'
               . '<a href="' . esc_url( $discussion_url ) . '" target="_blank">View Conversation</a>';

    wp_mail( loe_get_administrator_email(), 'Enquiry Thread Reply', $admin_msg, loe_email_headers() );

    wp_send_json_success( 'Reply submitted.' );
}

// ─────────────────────────────────────────────
// AJAX — Get Thread Main Info
// ─────────────────────────────────────────────
add_action( 'wp_ajax_nopriv_loe_get_thread_main', 'loe_get_thread_main' );
add_action( 'wp_ajax_loe_get_thread_main',        'loe_get_thread_main' );

function loe_get_thread_main() {

    check_ajax_referer( 'loe_woo_msg_nonce', 'nonce' );

    global $wpdb;
    $table_name = $wpdb->prefix . LOE_WOO_MSG_TABLE;
    $enquiry_id = sanitize_text_field( $_REQUEST['enquiryId'] ?? '' );

    if ( empty( $enquiry_id ) ) {
        wp_send_json_error( 'Invalid enquiry ID.' );
    }

    $enquiry = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE chat_unique_id = %s LIMIT 1",
            $enquiry_id
        ),
        OBJECT
    );

    if ( empty( $enquiry ) ) {
        wp_send_json_error( 'Thread not found.' );
    }

    $vendor       = get_user_by( 'ID', $enquiry->vendor_id );
    $vendor_name  = $vendor ? $vendor->display_name : 'Unknown';
    $product_name = get_the_title( $enquiry->product_id );

    wp_send_json_success( array(
        'product_title'  => $product_name,
        'vendor_name'    => $vendor_name,
        'enquiry_title'  => $enquiry->title,
    ) );
}
