<?php
/*
Plugin Name: N8N ChatBot
Plugin URI: https://github.com/jaainil/n8n-chat-plugin-wordpress
Description: ChatGPT-style interface for N8N AI Agent
Version: 1.0
Author: Jainil Prajapati
License: GPL-2.0-or-later
Text Domain: wordpress-n8n-chatbot-main
Domain Path: /languages
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
*/

// Security checks
defined('ABSPATH') or die('No script kiddies please!');

// Define constants
define('OACB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OACB_PLUGIN_URL', plugin_dir_url(__FILE__));

// Register activation hook
register_activation_hook(__FILE__, 'oacb_check_requirements');
function oacb_check_requirements() {
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        wp_die('This plugin requires PHP 7.4 or higher.');
    }
    if (!extension_loaded('curl')) {
        wp_die('This plugin requires cURL extension.');
    }
    
    // Set default options
    add_option('oacb_webhook_url');
    add_option('oacb_bot_name', 'Jainil');
    add_option('oacb_welcome_message', 'Hi! What are you looking for?');
    add_option('oacb_chat_icon', OACB_PLUGIN_URL . 'public/chatbot-icon.png');
    add_option('oacb_bot_thumb', OACB_PLUGIN_URL . 'public/bot-thumb.png');
    add_option('oacb_position', 'bottom-right');
    add_option('oacb_enable_popup', '1');
    add_option('oacb_popup_delay', '3');
    add_option('oacb_popup_size', 'medium');
}

// Admin menu
add_action('admin_menu', 'oacb_admin_menu');
function oacb_admin_menu() {
    add_menu_page(
        'N8N ChatBot Settings',
        'N8N ChatBot',
        'manage_options',
        'oacb-settings',
        'oacb_settings_page',
        'dashicons-chat'
    );
}

// Settings page
function oacb_settings_page() {
    ?>
    <div class="wrap">
        <h1>N8N ChatBot Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('oacb_settings_group');
            do_settings_sections('oacb-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
add_action('admin_init', 'oacb_register_settings');
function oacb_register_settings() {
    register_setting('oacb_settings_group', 'oacb_webhook_url', 'esc_url_raw');
    register_setting('oacb_settings_group', 'oacb_bot_name', 'sanitize_text_field');
    register_setting('oacb_settings_group', 'oacb_welcome_message', 'sanitize_textarea_field');
    register_setting('oacb_settings_group', 'oacb_chat_icon', 'esc_url_raw');
    register_setting('oacb_settings_group', 'oacb_bot_thumb', 'esc_url_raw');
    register_setting('oacb_settings_group', 'oacb_position', 'sanitize_text_field');
    register_setting('oacb_settings_group', 'oacb_enable_popup', 'sanitize_text_field');
    register_setting('oacb_settings_group', 'oacb_popup_delay', 'absint');
    register_setting('oacb_settings_group', 'oacb_popup_size', 'sanitize_text_field');
    
    // Add settings sections
    add_settings_section('oacb_general_section', 'General Settings', 'oacb_general_section_callback', 'oacb-settings');
    add_settings_section('oacb_appearance_section', 'Appearance Settings', 'oacb_appearance_section_callback', 'oacb-settings');
    
    // Add settings fields
    add_settings_field('oacb_webhook_url', 'N8N Webhook URL', 'oacb_webhook_url_callback', 'oacb-settings', 'oacb_general_section');
    add_settings_field('oacb_bot_name', 'Bot Name', 'oacb_bot_name_callback', 'oacb-settings', 'oacb_general_section');
    add_settings_field('oacb_welcome_message', 'Welcome Message', 'oacb_welcome_message_callback', 'oacb-settings', 'oacb_general_section');
    add_settings_field('oacb_enable_popup', 'Enable Welcome Popup', 'oacb_enable_popup_callback', 'oacb-settings', 'oacb_general_section');
    add_settings_field('oacb_popup_delay', 'Popup Delay (seconds)', 'oacb_popup_delay_callback', 'oacb-settings', 'oacb_general_section');
    add_settings_field('oacb_popup_size', 'Popup Size', 'oacb_popup_size_callback', 'oacb-settings', 'oacb_general_section');
    add_settings_field('oacb_chat_icon', 'Chat Icon', 'oacb_chat_icon_callback', 'oacb-settings', 'oacb_appearance_section');
    add_settings_field('oacb_bot_thumb', 'Bot Thumbnail', 'oacb_bot_thumb_callback', 'oacb-settings', 'oacb_appearance_section');
    add_settings_field('oacb_position', 'Chat Position', 'oacb_position_callback', 'oacb-settings', 'oacb_appearance_section');
}

// Settings section callbacks
function oacb_general_section_callback() {
    echo 'Configure the general settings for your chatbot.';
}

function oacb_appearance_section_callback() {
    echo 'Customize the appearance and position of your chatbot.';
}

// Settings field callbacks
function oacb_webhook_url_callback() {
    $url = get_option('oacb_webhook_url');
    echo '<input type="url" name="oacb_webhook_url" value="' . esc_attr($url) . '" class="regular-text">';
    echo '<p class="description">Enter your N8N webhook URL for the chatbot.</p>';
}

function oacb_bot_name_callback() {
    $name = get_option('oacb_bot_name');
    echo '<input type="text" name="oacb_bot_name" value="' . esc_attr($name) . '" class="regular-text">';
}

function oacb_welcome_message_callback() {
    $message = get_option('oacb_welcome_message');
    echo '<input type="text" name="oacb_welcome_message" value="' . esc_attr($message) . '" class="regular-text">';
    echo '<p class="description">Message shown in the welcome popup.</p>';
}

function oacb_enable_popup_callback() {
    $enabled = get_option('oacb_enable_popup');
    echo '<label><input type="checkbox" name="oacb_enable_popup" value="1" ' . checked($enabled, '1', false) . '> Enable welcome popup</label>';
}

function oacb_popup_delay_callback() {
    $delay = get_option('oacb_popup_delay');
    echo '<input type="number" name="oacb_popup_delay" value="' . esc_attr($delay) . '" min="1" max="60">';
    echo '<p class="description">Delay before showing popup (in seconds).</p>';
}

function oacb_chat_icon_callback() {
    $icon = get_option('oacb_chat_icon');
    echo '<input type="text" name="oacb_chat_icon" value="' . esc_attr($icon) . '" class="regular-text" id="oacb_chat_icon_field">';
    echo '<button type="button" class="button" id="oacb_upload_chat_icon">Upload Icon</button>';
    echo '<p class="description">URL for the chat icon image.</p>';
}

function oacb_bot_thumb_callback() {
    $thumb = get_option('oacb_bot_thumb');
    echo '<input type="text" name="oacb_bot_thumb" value="' . esc_attr($thumb) . '" class="regular-text" id="oacb_bot_thumb_field">';
    echo '<button type="button" class="button" id="oacb_upload_bot_thumb">Upload Thumbnail</button>';
    echo '<p class="description">URL for the bot thumbnail image.</p>';
}

function oacb_position_callback() {
    $position = get_option('oacb_position');
    $positions = [
        'bottom-right' => 'Bottom Right',
        'bottom-left' => 'Bottom Left',
        'top-right' => 'Top Right',
        'top-left' => 'Top Left'
    ];
    echo '<select name="oacb_position">';
    foreach ($positions as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($position, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
}

function oacb_popup_size_callback() {
    $size = get_option('oacb_popup_size');
    $sizes = [
        'small' => 'Small',
        'medium' => 'Medium'
    ];
    echo '<select name="oacb_popup_size">';
    foreach ($sizes as $value => $label) {
        echo '<option value="' . esc_attr($value) . '" ' . selected($size, $value, false) . '>' . esc_html($label) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Choose the size of the welcome popup.</p>';
}

// Enqueue admin scripts
add_action('admin_enqueue_scripts', 'oacb_admin_enqueue_scripts');
function oacb_admin_enqueue_scripts($hook) {
    if ($hook !== 'toplevel_page_oacb-settings') return;
    
    wp_enqueue_media();
    wp_enqueue_script('oacb-admin-script', OACB_PLUGIN_URL . 'admin/admin-script.js', ['jquery'], '1.0', true);
}

// Enqueue assets
add_action('wp_enqueue_scripts', 'oacb_enqueue_assets');
function oacb_enqueue_assets() {
    wp_enqueue_style(
        'oacb-chat-style',
        OACB_PLUGIN_URL . 'public/chatbot-style.css',
        [],
        filemtime(OACB_PLUGIN_DIR . 'public/chatbot-style.css')
    );

    wp_enqueue_script(
        'oacb-chat-script',
        OACB_PLUGIN_URL . 'public/chatbot-script.js',
        ['jquery'],
        filemtime(OACB_PLUGIN_DIR . 'public/chatbot-script.js'),
        true
    );

    // Get settings
    $settings = [
        'apiUrl' => rest_url('oacb/v1/send-message'),
        'nonce' => wp_create_nonce('wp_rest'),
        'assetsUrl' => OACB_PLUGIN_URL . 'public/',
        'chatIcon' => get_option('oacb_chat_icon', OACB_PLUGIN_URL . 'public/chatbot-icon.png'),
        'botThumb' => get_option('oacb_bot_thumb', OACB_PLUGIN_URL . 'public/bot-thumb.png'),
        'botName' => get_option('oacb_bot_name', 'Nina'),
        'welcomeMessage' => get_option('oacb_welcome_message', 'Hi! What are you looking for?'),
        'position' => get_option('oacb_position', 'bottom-right'),
        'enablePopup' => get_option('oacb_enable_popup', '1'),
        'popupDelay' => get_option('oacb_popup_delay', '3'),
        'popupSize' => get_option('oacb_popup_size', 'medium')
    ];

    wp_localize_script('oacb-chat-script', 'oacbConfig', $settings);
}

// Register REST API endpoint
add_action('rest_api_init', 'oacb_register_api_endpoints');
function oacb_register_api_endpoints() {
    register_rest_route('oacb/v1', '/send-message', [
        'methods' => 'POST',
        'callback' => 'oacb_handle_message',
        'permission_callback' => '__return_true',
        'args' => [
            'message' => [
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'nonce' => [
                'required' => true,
                'validate_callback' => function($param) {
                    return wp_verify_nonce($param, 'wp_rest');
                }
            ]
        ]
    ]);
}

// Handle message processing
function oacb_handle_message(WP_REST_Request $request) {
    try {
        $webhook_url = get_option('oacb_webhook_url', 'https://webhooks.aiservers.com.br/webhook/7ca83640-6c8c-4993-9917-0418bbaf4ab0');
        $message = sanitize_text_field($request['message']);

        $response = wp_remote_post($webhook_url, [
            'timeout' => 30,
            'body' => json_encode([
                'message' => $message,
                'session_id' => oacb_generate_session_id()
            ]),
            'headers' => [
                'Content-Type' => 'application/json',
                'X-WP-Referer' => esc_url_raw(home_url())
            ]
        ]);

        if (is_wp_error($response)) {
            throw new Exception(__('Service unavailable', 'wordpress-n8n-chatbot-main'));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return [
            'success' => true,
            'response' => wp_kses_post($data['response'] ?? __('No response received', 'wordpress-n8n-chatbot-main'))
        ];

    } catch (Exception $e) {
        return new WP_Error('api_error', $e->getMessage(), ['status' => 500]);
    }
}

// Generate session ID
function oacb_generate_session_id() {
    $session_id = '';
    if (isset($_COOKIE['oacb_session_id'])) {
        $session_id = sanitize_text_field(wp_unslash($_COOKIE['oacb_session_id']));
    }
    if (empty($session_id)) {
        $session_id = bin2hex(random_bytes(16));
        setcookie('oacb_session_id', $session_id, time() + 3600, '/', '', is_ssl(), true);
    }
    return $session_id;
}

// Add chat interface to footer
add_action('wp_footer', 'oacb_render_chat_interface');
function oacb_render_chat_interface() {
    include OACB_PLUGIN_DIR . 'public/chat-interface.html';
}