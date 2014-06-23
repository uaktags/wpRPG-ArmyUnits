<?php
class wpRPG_Army extends wpRPG
{
	protected $file = __FILE__;
	protected $plugslug = '';
	function __construct()
	{
		parent::__construct();
		global $wp_rewrite;
		$this->plugslug = basename(dirname(__FILE__));
		$this->version = WPRPG_Army_Version;
		add_filter( 'wpRPG_add_crons', array(
			 $this,
			'add_mycrons' 
		) );
		add_action( 'wp_ajax_train', array(
			 $this,
			'trainCallback' 
		) );
		add_action( 'wp_ajax_nopriv_train', array(
			 $this,
			'trainCallback' 
		) );
	}

	function add_mycrons( $crons )
	{
		$my_crons = array(
			 '1Day_CitizenGain'=>array('class'=>'wpRPG_Army', 'func'=>'increase_citizen', 'duration'=>86400),
			 '1Day_Miners'=>array('class'=>'wpRPG_Army', 'func'=>'miners_mine', 'duration'=>120)
		);
		return array_merge( $crons, $my_crons );
	}
	
	function miners_mine(){
		global $wpdb;
		$wpdb->show_errors();
		$sql = "SELECT * FROM ". $wpdb->base_prefix ."rpg_unit_bonus WHERE bonus_name = 'mining'";
		$res = $wpdb->get_results($sql);
		$sql2 = 'SELECT * FROM '. $wpdb->base_prefix .'users';
		$res2 = $wpdb->get_results($sql2);
		foreach ( $res2 as $key => $val ) {
			$addgold = 0;
			$player = new wpRPG_Player($val->ID);
			foreach($res as $rkey => $rval) {
				$unit = new wpRPG_Unit($rval->unit_id);
				$name = $unit->name;
				$addgold += ($unit->bonus_amt * $player->$name);
			}
				$player->update_meta('gold', ($player->gold + $addgold));
		}
	}
	
	function increase_citizen(){
		global $wpdb;
		$sql = "SELECT * FROM ". $wpdb->base_prefix . "users";
		$ids = $wpdb->get_results($sql);
		foreach($ids as $key => $val) {
			$player = new wpRPG_Player($val->ID);
			$player->update_meta('citizen', ($player->citizen + 1));
		}
	}
	
	function add_page_settings( $pages ) {
			$setting = array(
				'army'=> array('name'=>'Army', 'shortcode'=>'[wprpg_Army]')
			);
			return array_merge( $pages, $setting );
		}
	
	function Army_Jquery_Code($code)
	{
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
						var user = ".$current_user->ID." ;
						$.ajax({
							method: 'post',
							url: '". site_url( 'wp-admin/admin-ajax.php' ) ."',
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
	
	function add_Army_admin_tab($tabs)
	{
		$tab_page = array('Army'=>$this->Army_options(1));
		return array_merge($tabs, $tab_page);
	}
	
	function add_Army_admin_tab_header($tabs)
	{
		$attack_tabs = array('Army'=>'Army Settings');
		return array_merge($tabs, $attack_tabs);
	}

	 function train(){
		global $current_user, $wpdb;
		$player = new wpRPG_Player($current_user->ID);
		$citizens = $player->citizen;
		$params = array();
		parse_str($_POST['qty'], $params);
		$qty = 0;
		foreach($params as $key => $val){
			$qty += $val;
		}
		if ($citizens > 0 && $citizens >= $qty)
		{
			foreach($params as $key => $val){
				$new = $player->$key + $val;
				update_user_meta($current_user->ID, $key, $new);
			}
			$rem = $citizens - $qty;
			update_user_meta($current_user->ID, 'citizen', $rem );
			echo 'You\'ve trained ' . $qty .' citizens for your army!<br /><a href="#" onclick="location.reload(true); return false;">Reload Bank</a>';
		}else{
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
		$html .= "<tr><td><span class='description'>Version: ".$this->version."</span></td></tr>";
		if(!$opt)
			echo $html;
		else
			return $html;
	}

	function wpRPG_Army_load_plugin() 
	{ 
		if ( ! current_user_can( 'activate_plugins' ) ) 
			return; 
		if(is_admin()&&get_option('Activated_Plugin')=='wpRPG-Army') 
		{ 
			delete_option('Activated_Plugin'); 
			//add_action( 'admin_notices', array($this,'wpRPG_Army_check_admin_notices'), 0 ); 
		}
	}

	function check_tables() 
	{
		global $wpdb;
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
	
	function WpRPG_Army_check_plugin_requirements() 
	{
		$errors = array();
		if (!class_exists('wpRPG')) {
			$errors[] = "WPRPG must be installed!<br />";
			deactivate_plugins(WPRPG_Army_Plugin_File);
		}elseif (!get_option('WPRPG_Army_installed')) {
			 if ($this->check_tables() != FALSE) {
				update_option('WPRPG_Army_installed', "1");
			} else {
				$errors[] = "You had an error occur!<br />";
			}
		}else{
			//die(get_option('WPRPG_rpg_installed'));
		}
		return $errors;
	}

	function RegisterSettings() 
	{
		// Add options to database if they don't already exist
		add_option('WPRPG_Army_installed', "", "", "yes");
		
		// Register settings that this form is allowed to update
		register_setting('rpg_settings', 'WPRPG_Army_installed');
		
	}
	

}
?>