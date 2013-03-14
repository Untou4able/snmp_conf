<?php
include_once 'snmp_eltex.php';
include_once 'iSnmp_eltex.php';

class eltex_nte extends snmp_eltex implements iSnmp_eltex {
    protected $mac = '', $eltex_options;
    
    protected $oids = Array(
        'id' => '1.3.6.1.4.1.35265.1.21.16.1.1.8.6%deced_mac% s %value%',
        'desc' => '1.3.6.1.4.1.35265.1.21.16.1.1.2.6%deced_mac% u %value%',
        'rule' => '1.3.6.1.4.1.35265.1.21.16.1.1.3.6%deced_mac% u %value%',
        'ipmc' => '1.3.6.1.4.1.35265.1.21.16.1.1.4.6%deced_mac% u %value%',
        'port_p' => '1.3.6.1.4.1.35265.1.21.16.1.1.6.6%deced_mac% u %value%',
        'type' => '1.3.6.1.4.1.35265.1.21.16.1.1.10.6%deced_mac% i %value%',
        'ktv' => '1.3.6.1.4.1.35265.1.21.16.1.1.25.6%deced_mac% i %value%',
        'commit' => '1.3.6.1.4.1.35265.1.21.16.1.1.21.6%deced_mac% u 1'
    );
    
    protected $oids_save = Array(
        '1.3.6.1.4.1.35265.1.21.45.0 u 1'
    );
    
    protected $del_oids = Array(
           '1.3.6.1.4.1.35265.1.21.16.1.1.21.6%deced_mac% u 1', '1.3.6.1.4.1.35265.1.21.16.1.1.20.6%deced_mac% u 1', '1.3.6.1.4.1.35265.1.21.45.0 u 1'
    );
    
    public function __construct($ip, $options = NULL) {
        $this->eltex_options = Array(
            'version' => '2c',
            'timeout' => '1',
            'retries' => '2'
        );
        if(! is_null($options))
            copy_options($options, $this->eltex_options);
        parent::__construct($ip, $this->eltex_options);
    }
    
    private function del_mac($mac) {
        $this->mac_to_oid($mac);
        $this->del_oids = preg_replace('/%deced_mac%/', $this->mac, $this->del_oids);
    }
    
    private function add_mac($mac) {
        $this->mac_to_oid($mac);
        $this->oids = preg_replace('/%deced_mac%/', $this->mac, $this->oids);
    }
    
    protected function mac_to_oid($mac) {
        foreach(preg_split('/:/', $mac) as $mac_octet) {
            $this->mac .= '.'.hexdec($mac_octet);
        }
    }
    
    public function save_ont() {
        foreach($this->oids_save as $oid) {
            $this->object_id .= $oid;
        }
        $this->send();
    }
    
    public function add_ont($mac) {
        $snmp_send_counter = 0;
        $this->add_mac($mac);
        foreach($this->oids as $oid) {
            $this->object_id .= $oid.' ';
        }
        $this->set_ont();
        do {
            $this->send();
            $snmp_send_counter++;
        } while($this->snmp_return != 0 && $snmp_send_counter < parent::MAX_SNMP_SEND_TRIES );
        if($snmp_send_counter >= parent::MAX_SNMP_SEND_TRIES ) {
            $this->snmp_return = '2';
        } else {
            $this->save_ont();
        }
        return $this->snmp_return;
    }

    public function del_ont($mac) {
        echo 'in del_ont func';
        $this->del_mac($mac);
        foreach($this->del_oids as $oid) {
            $this->object_id .= $oid.' ';
        }
        $this->set_ont();
        $this->send();
        $this->save_ont();
    }
    
    public function set_ont_property($prop, $value) {
        if(array_key_exists($prop, $this->oids)) {
            $this->oids[$prop] = preg_replace('/%value%/', $value, $this->oids[$prop]);
        }
    }
}
?>