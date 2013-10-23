<?php
include_once 'snmp_eltex.php';
include_once 'iSnmp_eltex.php';

class eltex_nte extends snmp_eltex implements iSnmp_eltex {
    protected $mac = '', $eltex_options;
    
    protected $vpn_oids = Array(
        'innervlan' => '1.3.6.1.4.1.35265.1.21.16.1.1.14.6%deced_mac% u %value%'
    );


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
//        '1.3.6.1.4.1.35265.1.21.16.1.1.21.6%deced_mac% u 1',
        '1.3.6.1.4.1.35265.1.21.16.1.1.20.6%deced_mac% u 1',
        '1.3.6.1.4.1.35265.1.21.45.0 u 1'
    );
    
    public function __construct($ip, $options = NULL) {
        $this->eltex_options = Array(
            'version' => '2c',
            'timeout' => '2',
            'retries' => '2'
        );
        if(! is_null($options))
            copy_options($options, $this->eltex_options);
        parent::__construct($ip, $this->eltex_options);
    }
    
    public function add_tv_rev_B($ip, $mac) {
        $time = time();
        $fname = "/home/scripts/pon/run/".$time.".pon";
        if(($out_file = fopen($fname,'w')) === FALSE)
            die('Failed to open file for writing! Failed GPON configuration!!!');
        $str = "#!/bin/sh\n/home/scripts/pon/NTE-rg_rev_B_IPTV.exp ".$ip." ".$mac."\n";
        fwrite($out_file, $str);
        fclose($out_file);
        chmod($fname, 0755);
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
        $this->object_id = '';
        foreach($this->oids_save as $oid) {
            $this->object_id .= $oid;
        }
        $this->send();
    }
    
    public function add_ont($mac) {
        $snmp_send_counter = 0;
        $this->add_mac($mac);
        #echo $this->snmp_req;
        #echo '<br>';
        foreach($this->oids as $oid) {
            $this->object_id .= $oid.' ';
        }
        #echo $this->snmp_req;
        #echo '<br>';
        foreach($this->oids_save as $oid) {
            $this->object_id .= $oid;
        }
        #echo $this->snmp_req;
        #echo '<br>';
        $this->set_ont();
        #echo $this->snmp_req;
        #echo '<br>';
        do {
            $this->send();
            $snmp_send_counter++;
            usleep(300000);
        } while($this->snmp_return != 0 && $snmp_send_counter < parent::MAX_SNMP_SEND_TRIES );
        if($snmp_send_counter >= parent::MAX_SNMP_SEND_TRIES ) {
            $this->snmp_return = '2';
        }
        return $this->snmp_return;
    }

    public function del_ont($mac) {
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
        } elseif($prop == 'innervlan') {
            $this->oids['innervlan'] = preg_replace('/%value%/', $value, $this->vpn_oids['innervlan']);
        }
    }
}
?>
