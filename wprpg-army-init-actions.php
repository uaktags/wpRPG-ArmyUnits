<?php

$rpgArmy = new wpRPG_Army;
add_action('admin_init', array($rpgArmy, 'wpRPG_Army_load_plugin'));
add_action('admin_init', array($rpgArmy, 'RegisterSettings'));
add_filter('wpRPG_add_admin_tab_header', array($rpgArmy, 'add_Army_admin_tab_header'));
add_filter('wpRPG_add_admin_tabs', array($rpgArmy, 'add_Army_admin_tab'));
add_shortcode('wprpg_Army', 'wpRPG_Army_showArmy');
add_filter('wpRPG_add_pages_settings', array($rpgArmy, 'add_page_settings'));
add_filter('wpRPG_add_plugin_code', array($rpgArmy, 'Army_Jquery_Code'));
if (is_admin()) {
    add_action('admin_init', 'wpRPG_Army_register_settings');
}

function wpRPG_Army_register_settings() {
    if (!get_option('wpRPG_Army_Page')) {
        add_option('wpRPG_Army_Page', 'army', "", "yes");
    }
    register_setting('rpg_settings', 'wpRPG_Army_Page');
}

function wpRPG_Army_showArmy() {
    global $wpdb;
    $result = '';
    if (file_exists(get_template_directory() . 'templates/wprpg/show_Army.php')) {
        include_once (get_template_directory() . 'templates/wprpg/show_Army.php');
    } else {
        include_once (__DIR__ . '/templates/show_Army.php');
    }
    if (get_option('show_wpRPG_Version_footer')) {
        $result .= '<footer style="display:block;margin: 0 2%;border-top: 1px solid #ddd;padding: 20px 0;font-size: 12px;text-align: center;color: #999;">';
        $result .= 'Powered by <a href="http://tagsolutions.tk/wordpress-rpg/">wpRPG ' . WPRPG_VERSION . '</a></footer>';
    }
    return $result;
}
