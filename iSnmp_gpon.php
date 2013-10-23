<?php

interface iSnmp_gpon {
    public function add_ont($mac);
    public function del_ont($mac);
    public function set_ont_property($prop, $value);
    public function save_ont();
}

?>
