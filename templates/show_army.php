<?php
global $current_user;
$wpRPG = new wpRPG;
$wpdb->show_errors();
if ($current_user->ID != 0) {
    $player = new wpRPG_Player($current_user->ID);
    global $message;
    ?>

    <div id="rpg_area">
        Total Citizens: <?php echo $player->citizen ?>
        <?php
        $sql = "SELECT * FROM wp_rpg_army_cats";
        $cats = $wpdb->get_results($sql);
        foreach ($cats as $key => $val) {
            ?>
            <?php echo $val->title; ?>
            <form name="form_unit_"<?php $val->id ?>">
                <table >
                    <thead><td>Title</td><td>Strength</td><td>Defense</td><td>Cost</td><td>Trained</td><td>Quantity</td></thead>
                    <tbody>
                        <?php
                        $sql = "SELECT id FROM wp_rpg_army_units WHERE catID = " . $val->id;
                        $units = $wpdb->get_results($sql);
                        foreach ($units as $unit => $data) {
                            $unit = new wpRPG_Unit($data->id);
                            ?>
                            <tr>	
                                <td><?php echo $unit->title ?></td>
                                <td><?php echo $unit->offense ?></td>
                                <td><?php echo $unit->defense ?></td>
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