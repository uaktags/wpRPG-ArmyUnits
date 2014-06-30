<?php
global $current_user;
$wpRPG = new wpRPG;
$wpdb->show_errors();
if(is_user_logged_in()){
		$current_user = wp_get_current_user();
	
    $player = new wpRPG_Player($current_user->ID);
    global $message;
    ?>

    <div id="rpg_area">
        Total Citizens: <?php echo $player->citizen ?>
        <?php
        $sql = "SELECT * FROM ". $wpdb->base_prefix ."rpg_army_cats";
        $cats = $wpdb->get_results($sql);
		
        foreach ($cats as $key => $val) {
            $colsql = "SELECT DISTINCT bonus_name FROM ".$wpdb->base_prefix ."rpg_unit_bonus as bonus JOIN ".$wpdb->base_prefix ."rpg_army_units as units On bonus.unit_id = units.id Join ".$wpdb->base_prefix ."rpg_army_cats as cats on cats.id = units.catID WHERE cats.ID=".$val->id;
			$cols = $wpdb->get_results($colsql);
			echo $val->title ?>
            <table>
			<form name="form_unit_"<?php $val->id ?>">
                    <thead><td>Title</td>
					<?php foreach($cols as $k => $v)
					{
						echo "<td>".$v->bonus_name."</td>";
					}?>
					<td>Cost</td><td>Trained</td><td>Quantity</td></thead>
                    <tbody>
                        <?php
                        $sql = "SELECT id FROM wp_rpg_army_units WHERE catID = " . $val->id;
                        $units = $wpdb->get_results($sql);
                        foreach ($units as $unit => $data) {
                            $unit = new wpRPG_Unit($data->id);
                		?>
                            <tr>	
                                <td><?php echo $unit->title ?></td>
                                <?php 
								$bonsql = "SELECT DISTINCT bonus_amt FROM ".$wpdb->base_prefix ."rpg_unit_bonus as bonus JOIN ".$wpdb->base_prefix ."rpg_army_units as units On bonus.unit_id = units.id WHERE units.id=".$unit->id;
								$bons = $wpdb->get_results($bonsql);
								foreach($bons as $k => $v)
								{ ?>
									<td><?php echo ($v->bonus_amt?$v->bonus_amt:0) ?></td>
                                <?php } ?>
								<td><?php echo $unit->cost ?></td>
                                <td><?php echo ($player->__get($unit->name) ? $player->__get($unit->name) : 0); ?></td>
                                <td><input name="unit_<?php echo $unit->name ?>" type="text" id="<?php echo $unit->name ?>" style="width:60px;" /></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <button id=train<?php echo $val->id ?> >Train</button><br/>
            </form>
            <?php }
        ?>
    </div>
    <br/>
    <br/>
    <?php
} else {
    ?>
    <h1>Army</h1>
    <strong>You must be logged in to view this page!</strong>
<?php } ?>