<?php

include_once 'snmp_conf.php';

abstract class snmp_eltex extends snmp_conf {
    const MAX_SNMP_SEND_TRIES = 5;
    
    function __construct($ip, $options = NULL) {
        $this->eltex_options = Array(
            'version' => '2c',
            'timeout' => '1',
            'retries' => '1'
        );
        if(! is_null($options))
            copy_options($options, $this->eltex_options);
        parent::__construct($ip, $this->eltex_options);
    }

    protected function set_ont($community = 'private') {
        $this->action = 'snmpset';
        $this->snmp_comm = $community;
        $this->addition = ';echo "$?"';
        $this->make_req();
    }
    
    abstract protected function mac_to_oid($mac);
}

?>
