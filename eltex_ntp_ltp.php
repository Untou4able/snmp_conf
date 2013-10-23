<?php

include_once 'eltex_ntp.php';

class eltex_ntp_ltp extends eltex_ntp {
    
    public function __construct($ip, $slot = 1, $options = NULL) {
        $this->slot = $slot;
        $this->eltex_options = Array(
            'version' => '2c',
            'timeout' => '1',
            'retries' => '2'
        );
        if(! is_null($options))
            copy_options($options, $this->eltex_options);
        parent::__construct($ip, $this->slot, $this->eltex_options);
        $this->oids_save = Array(
            '1.3.6.1.4.1.35265.1.22.1.50.0 u 1'
        );
    }
    
    public function save_ont() {
        $this->object_id = '';
        foreach($this->oids_save as $oid) {
            $this->object_id .= $oid;
        }
        $this->send();
    }
}

?>
