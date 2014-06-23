<?php

class wpRPG_Army extends wpRPG {

    protected $file = __FILE__;
    protected $plugslug = '';

    function __construct() {
        parent::__construct();
        global $wp_rewrite;
        $this->plugslug = basename(dirname(__FILE__));
        $this->version = WPRPG_Army_Version;
        add_filter('wpRPG_add_crons', array(
            $this,
            'add_mycrons'
        ));
        add_action('wp_ajax_train', array(
            $this,
            'trainCallback'
        ));
        add_action('wp_ajax_nopriv_train', array(
            $this,
            'trainCallback'
        ));
    }

    function add_mycrons($crons) {
        $my_crons = array(
            '1Day_CitizenGain' => array('class' => 'wpRPG_Army', 'func' => 'increase_citizen', 'duration' => 86400),
            '1Day_Miners' => array('class' => 'wpRPG_Army', 'func' => 'miners_mine', 'duration' => 86400)
        );
        return array_merge($crons, $my_crons);
    }

    function miners_mine() {
        global $wpdb;
        $wpdb->show_errors();
        $sql = "SELECT * FROM " . $wpdb->base_prefix . "rpg_unit_bonus WHERE bonus_name = 'mining'";
        $res = $wpdb->get_results($sql);
        $sql2 = 'SELECT * FROM ' . $wpdb->base_prefix . 'users';
        $res2 = $wpdb->get_results($sql2);
        foreach ($res2 as $key => $val) {
            $addgold = 0;
            $player = new wpRPG_Player($val->ID);
            foreach ($res as $rkey => $rval) {
                $unit = new wpRPG_Unit($rval->unit_id);
                $name = $unit->name;
                $addgold += ($unit->bonus_amt * $player->$name);
            }
            $player->update_meta('gold', ($player->gold + $addgold));
        }
    }

    function increase_citizen() {
        global $wpdb;
        $sql = "SELECT * FROM " . $wpdb->base_prefix . "users";
        $ids = $wpdb->get_results($sql);
        foreach ($ids as $key => $val) {
            $player = new wpRPG_Player($val->ID);
            $player->update_meta('citizen', ($player->citizen + 1));
        }
    }

    function add_page_settings($pages) {
        $setting = array(
            'army' => array('name' => 'Army', 'shortcode' => '[wprpg_Army]')
        );
        return array_merge($pages, $setting);
    }

    function Army_Jquery_Code($code) {
        if (!is_array($code))
            $code = array();
        global $current_user;
        $ArmyCode = array(
            "	$('[id^=train]').on('click', function(event) {
						event.preventDefault();
						$('[name^=unit_]').each(function (){
							if($(this).html().trim()){
								$(this).val(0);
							}
						})
						var qty = $('[name^=form_unit_]').serialize();
						qty = qty.replace(/\unit_/g, '');
						var user = " . $current_user->ID . " ;
						$.ajax({
							method: 'post',
							url: '" . site_url('wp-admin/admin-ajax.php') . "',
							data: {
								'action': 'train',
								'user': user,
								'qty': qty,
								'ajax': true
							},
							success: function(data) {
								$('#rpg_area').empty();
								$('#rpg_area').html(data);
								//location.reload(true); return false;
								
							}
						});
					});"
        );
        return array_merge($code, $ArmyCode);
    }

    function add_Army_admin_tab($tabs) {
        $tab_page = array('Army' => $this->Army_options(1));
        return array_merge($tabs, $tab_page);
    }

    function add_Army_admin_tab_header($tabs) {
        $attack_tabs = array('Army' => 'Army Settings');
        return array_merge($tabs, $attack_tabs);
    }

    function train() {
        global $current_user, $wpdb;
        $player = new wpRPG_Player($current_user->ID);
        $citizens = $player->citizen;
        $params = array();
        parse_str($_POST['qty'], $params);
        $qty = 0;
        foreach ($params as $key => $val) {
            $qty += $val;
        }
        if ($citizens > 0 && $citizens >= $qty) {
            foreach ($params as $key => $val) {
                $new = $player->$key + $val;
                update_user_meta($current_user->ID, $key, $new);
            }
            $rem = $citizens - $qty;
            update_user_meta($current_user->ID, 'citizen', $rem);
            echo 'You\'ve trained ' . $qty . ' citizens for your army!<br /><a href="#" onclick="location.reload(true); return false;">Reload Bank</a>';
        } else {
            echo 'You\'ve tried to train more citizens than you currently have at your disposal!<br /><a href="#" onclick="location.reload(true); return false;">Reload Bank</a>';
        }
    }

    function trainCallback() {
        $this->train();
        //$_POST = '';
        die();
    }

    function Army_options($opt = 0) {
        $html = "<tr>";
        $html .= "<td>";
        $html .= "<h3>Welcome to Wordpress RPG Army Module!</h3>";
        $html .= "</td>";
        $html .= "</tr>";
        $html .= "<br />";
        $html .= "<tr>";
        $html .= "<td>";
        $html .= "<table border=1><tr><th>Setting Name</th><th>Setting</th></tr>";
        $html .= "</table>";
        $html .= "</td>";
        $html .= "</tr>";
        $html .= "<tr><td><span class='description'>Version: " . $this->version . "</span></td></tr>";
        if (!$opt)
            echo $html;
        else
            return $html;
    }

    function wpRPG_Army_check_admin_notices() {
        $errors = $this->WpRPG_Army_check_plugin_requirements();
        if (empty($errors))
            return;

        // Suppress "Plugin activated" notice.
        unset($_GET['activate']);

        // this plugin's name
        $name = get_file_data(__FILE__, array('Plugin Name'), 'plugin');

        printf(
                '<div class="error"><p>%1$s</p>
			<p><i>%2$s</i> has been deactivated.</p></div>', join('</p><p>', $errors), $name[0]
        );
        deactivate_plugins(plugin_basename(__FILE__));
    }

    function wpRPG_Army_load_plugin() {
        if (!current_user_can('activate_plugins'))
            return;
        if (is_admin() && get_option('Activated_Plugin') == 'wpRPG-Army') {
            delete_option('Activated_Plugin');
            add_action('admin_notices', array($this, 'wpRPG_Army_check_admin_notices'), 0);
        }
    }

    function check_tables() {
        global $wpdb;
        $sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "rpg_army_cats` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `title` varchar(50) NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `id` (`id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
        $wpdb->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "rpg_army_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `catID` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `cost` int(11) NOT NULL DEFAULT '0',
  `minXP` int(11) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
        $wpdb->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->base_prefix . "rpg_unit_bonus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `bonus_name` varchar(50) NOT NULL,
  `bonus_amt` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
        $wpdb->query($sql);

        $sql = "INSERT INTO `" . $wpdb->base_prefix . "rpg_unit_bonus` (`id`, `unit_id`, `bonus_name`, `bonus_amt`) VALUES
(1, 1, 'mining', 10),
(2, 2, 'mining', 50);";
        $wpdb->query($sql);

        $sql = "INSERT INTO `" . $wpdb->base_prefix . "rpg_army_units` (`id`, `catID`, `title`, `cost`, `minXP`, `name`) VALUES
(1, 1, 'Miner Lvl 1', 100, 0, 'miner1'),
(2, 1, 'Miner Lvl 2', 500, 500, 'miner2'),
(3, 2, 'Soldier Lvl 1', 100, 100, 'soldier1'),
(4, 2, 'Soldier Lvl 2', 500, 2000, 'soldier2');";
        $wpdb->query($sql);

        $sql = "INSERT INTO `" . $wpdb->base_prefix . "rpg_army_cats` (`id`, `title`) VALUES
(1, 'Worker'),
(2, 'Offense'),
(3, 'Defense');";
        $wpdb->query($sql);
        return true;
    }

    function check_column($table, $col_name) {
        global $wpdb;
        if ($table != null) {
            $results = $wpdb->get_results("DESC $table");
            if ($results != null) {
                foreach ($results as $row) {
                    if ($row->Field == $col_name) {
                        return true;
                    }
                }
                return false;
            }
            return false;
        }
        return false;
    }

    function WpRPG_Army_check_plugin_requirements() {
        $errors = array();
        if (!class_exists('wpRPG')) {
            $errors[] = "WPRPG must be installed!<br />";
            deactivate_plugins(WPRPG_Army_Plugin_File);
        } elseif (!get_option('WPRPG_Army_installed')) {
            if ($this->check_tables() != FALSE) {
                update_option('WPRPG_Army_installed', "1");
            } else {
                $errors[] = "You had an error occur!<br />";
            }
        } else {
            //die(get_option('WPRPG_rpg_installed'));
        }
        return $errors;
    }

    function RegisterSettings() {
        // Add options to database if they don't already exist
        add_option('WPRPG_Army_installed', "", "", "yes");

        // Register settings that this form is allowed to update
        register_setting('rpg_settings', 'WPRPG_Army_installed');
    }

}

?>