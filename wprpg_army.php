<?php

/*
  Plugin Name: WP RPG Army
  Plugin URI: http://wordpress.org/extend/plugins/wprpg_army/
  Version: 1.0.4
  wpRPG: 1.0.18
  Author: <a href="http://tagsolutions.tk">Tim G.</a>
  Description: RPG Army elements
  Text Domain: wprpg_army
  License: GPL2
 */
/*
  Globals
 */
global $wpdb;

/*
  Definitions
  @since 1.0.0
 */
define('WPRPG_Army_Plugin_File', plugin_basename(__FILE__));
define('WPRPG_Army_Version', '1.0.4');

/*
  WPRPG Class Loader
  @since 1.0.0
 */

function wpRPG_Army_wpRPGCheck() {
    global $wpdb;
    if (class_exists('wpRPG')) {
        if (!class_exists('wpRPG_Army')) {
            include(__DIR__ . '/wprpg-army-class.php');
        }
        $rpgArmy = new wpRPG_Army;
        include ( __DIR__ . '/wprpg-army-library.php');
        $sql = "SELECT * FROM " . $wpdb->base_prefix . "users";
        $ids = $wpdb->get_results($sql);
        foreach ($ids as $key => $val) {
            $player = new wpRPG_Player($val->ID);
            if (!array_key_exists('citizen',$player)) {
                $player->update_meta('citizen', 0);
            }
        }
    }
}

add_action('plugins_loaded', 'wpRPG_Army_wpRPGcheck');
/*
  Plugin Activations / Uninstall
  @since 1.0.0
 */

function wpRPG_Army_Activate() {
    add_option('Activated_Plugin', 'wpRPG-Army');
}

register_activation_hook(__FILE__, 'wpRPG_Army_Activate');
register_deactivation_hook(__FILE__, 'wpRPG_Army_on_deactivation');
register_uninstall_hook(__FILE__, 'wpRPG_Army_on_uninstall');

function wpRPG_Army_on_deactivation() {
    if (!current_user_can('activate_plugins'))
        return;
    $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
    update_option('WPRPG_Army_installed', 0);
}

function wpRPG_Army_on_uninstall() {
    global $wpdb;
    if (!current_user_can('activate_plugins'))
        return;

    check_admin_referer('bulk-plugins');

    if (__FILE__ != WP_UNINSTALL_PLUGIN)
        return;
}

?>