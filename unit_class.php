<?php

/**
 * Creates a unit object to Get/Set usermeta
 * @since WPRPG 1.0.18
 */
class wpRPG_Unit {

    protected $self = array();

    public function __construct($id = '') {
        global $wpdb;
        $sql = "Select * FROM " . $wpdb->base_prefix . "rpg_army_units WHERE id=$id";
        $data = $wpdb->get_results($sql);
        foreach ($data as $key => $val) {
            foreach ($val as $valkey => $valval) {
                $this->self[$valkey] = $valval;
            }
        }
        $this->get_bonuses($this->id);
    }

    public function __get($name = null) {
        if (array_key_exists($name, $this->self)) {
            return $this->self[$name];
        } else {
            return false;
        }
    }

    public function __set($name = null, $value = null) {
        return $this->self[$name] = $value;
    }

    public function get_bonuses($id) {
        global $wpdb;
        $sql = "SELECT * FROM " . $wpdb->base_prefix . "rpg_unit_bonus WHERE unit_id = $id";
        $data = $wpdb->get_results($sql);
        foreach ($data as $key => $val) {
            foreach ($val as $k => $v) {
                $this->$k = $v;
            }
        }
    }

    public function save_bonuses($name = null, $value = null) {
        global $wpdb;
        $wpdb->update("rpg_unit_bonuses", array($name => $value), array('unit_id' => $this->id), array("%d"));
        return true;
    }

}
