<?php

//////////////////////////
/// Run initialization ///
//////////////////////////

/*
 * Don't start on every page, the plugin page is enough.
 */

if (is_admin()) {
    add_action('admin_init', 'RegisterSettings');
}

/**
 * Test current system for the features the plugin needs.
 *
 * @return array Errors or empty array
 */


function wpRPG_Attack_on_activation() {
	global $rpgBankCP;
    if (!current_user_can('activate_plugins'))
        return;
	
		$plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
		check_admin_referer("activate-plugin_{$plugin}");

		# Uncomment the following line to see the function in action
		//exit( var_dump( $_GET ) );
		$rpgBankCP->check_tables();

}

function wpRPG_Attack_on_deactivation() {
    if (!current_user_can('activate_plugins'))
        return;
    $plugin = isset($_REQUEST['plugin']) ? $_REQUEST['plugin'] : '';
	update_option('WPRPG_BankCP_installed', 0);
    # Uncomment the following line to see the function in action
    # exit( var_dump( $_GET ) );
}

function wpRPG_Attack_on_uninstall() {
    global $wpdb;
    if (!current_user_can('activate_plugins'))
        return;
		
    check_admin_referer('bulk-plugins');

    // Important: Check if the file is the one
    // that was registered during the uninstall hook.
    if (__FILE__ != WP_UNINSTALL_PLUGIN)
        return;
    # Uncomment the following line to see the function in action
    # exit( var_dump( $_GET ) );
}


//////////////////////////
/// End initialization ///
//////////////////////////
/////////////////////////
/// Install Functions ///
/////////////////////////





?>