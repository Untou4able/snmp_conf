<?php
include_once 'snmp_eltex.php';
include_once 'iSnmp_eltex.php';

class eltex_ntp extends snmp_eltex implements iSnmp_eltex {
    protected $mac = '', $eltex_options, $slot;
    
    protected $del_oids = Array(
        'pon' => Array(
            '1.3.6.1.4.1.35265.1.22.3.4.1.20.%slot%.8.69.76.84.88%deced_mac% i 6'
        ),
        'acs' => Array(
            '1.3.6.1.4.1.35265.1.22.3.15.1.20.8.69.76.84.88%deced_mac% u 1'
        )
    );


    protected $oids_save = Array(
        '1.3.6.1.4.1.35265.1.22.1.50.0 u 1'
    );
    protected $create_oids = Array(
        'pon' => '1.3.6.1.4.1.35265.1.22.3.4.1.20.%slot%.8.69.76.84.88%deced_mac%',
        'acs' => '1.3.6.1.4.1.35265.1.22.3.15.1.2.8.69.76.84.88%deced_mac%'
    );
    protected $oids = Array(
        'pon' => Array(
            'desc' => '1.3.6.1.4.1.35265.1.22.3.4.1.8.%slot%.8.69.76.84.88%deced_mac% s %value%',
            'mgm_prof' => '1.3.6.1.4.1.35265.1.22.3.4.1.9.%slot%.8.69.76.84.88%deced_mac% u %value%',
            'service_prof' => '1.3.6.1.4.1.35265.1.22.3.4.1.5.%slot%.8.69.76.84.88%deced_mac% u %value%',
            'mcast_prof' => '1.3.6.1.4.1.35265.1.22.3.4.1.10.%slot%.8.69.76.84.88%deced_mac% u %value%',
            'pon_port' => '1.3.6.1.4.1.35265.1.22.3.4.1.3.%slot%.8.69.76.84.88%deced_mac% u %value%',
            'ont_id' => '1.3.6.1.4.1.35265.1.22.3.4.1.4.%slot%.8.69.76.84.88%deced_mac% u %value%'
        ),
        'acs' => Array(
            'acs_service_prof' => '1.3.6.1.4.1.35265.1.22.3.15.1.3.8.69.76.84.88%deced_mac% s %value%'
        )
        /*
        'ntp_rg' => Array(
            'ppp' => Array(
                'ppp_l' => '1.3.6.1.4.1.35265.1.22.3.15.1.11.8.69.76.84.88%deced_mac% s %value%',
                'ppp_p' => '1.3.6.1.4.1.35265.1.22.3.15.1.12.8.69.76.84.88%deced_mac% s %value%'
            ),
            'voip1' => Array(
                'sip_s' => '1.3.6.1.4.1.35265.1.22.3.15.1.10.8.69.76.84.88%deced_mac% s %value%',
                'voip1_e' => '1.3.6.1.4.1.35265.1.22.3.15.1.4.8.69.76.84.88%deced_mac% s %value%',
                'viop1_l' => '1.3.6.1.4.1.35265.1.22.3.15.1.5.8.69.76.84.88%deced_mac% s %value%',
                'voip1_p' => '1.3.6.1.4.1.35265.1.22.3.15.1.6.8.69.76.84.88%deced_mac% s %value%'
            ),
            'voip2' => Array(
                'voi2_e' => '1.3.6.1.4.1.35265.1.22.3.15.1.7.8.69.76.84.88%deced_mac% s %value%',
                'voip2_l' => '1.3.6.1.4.1.35265.1.22.3.15.1.8.8.69.76.84.88%deced_mac% s %value%',
                'voip2_p' => '1.3.6.1.4.1.35265.1.22.3.15.1.9.8.69.76.84.88%deced_mac% s %value%'
            )
        )
        */
    );
    
    public function __construct($ip, $slot = 1, $options = NULL) {
        $this->slot = $slot;
        $this->eltex_options = Array(
            'version' => '2c',
            'timeout' => '1',
            'retries' => '2'
        );
        if(! is_null($options))
            copy_options($options, $this->eltex_options);
        parent::__construct($ip, $this->eltex_options);
    }
    
    private function bunch_sender() {
        $snmp_send_counter = 0;
        do {
            $this->send();
            $snmp_send_counter++;
        } while($this->snmp_return != 0 && $snmp_send_counter < parent::MAX_SNMP_SEND_TRIES );
        if($snmp_send_counter >= parent::MAX_SNMP_SEND_TRIES )
            $this->snmp_return = '2';
    }
    private function create_acs($mac) {
        $this->set($this->create_oids['acs'], 's', $mac);
        $this->send();
        $this->type = '';
        $this->value = '';
    }

    private function create_pon() {
        $this->set($this->create_oids['pon'], 'i', '4');
        $this->send();
        $this->type = '';
        $this->value = '';
    }

    private function add_slot() {
        $this->oids['pon'] = preg_replace('/%slot%/', $this->slot, $this->oids['pon']);
        $this->create_oids['pon'] = preg_replace('/%slot%/', $this->slot, $this->create_oids['pon']);
    }
    
    private function del_slot() {
        $this->del_oids['pon'] = preg_replace('/%slot%/', $this->slot, $this->del_oids['pon']);
    }

    private function del_mac($mac) {
        $this->mac_to_oid($mac);
        $this->del_oids['pon'] = preg_replace('/%deced_mac%/', $this->mac, $this->del_oids['pon']);
        $this->del_oids['acs'] = preg_replace('/%deced_mac%/', $this->mac, $this->del_oids['acs']);
    }
    
    private function add_mac($mac) {
        $this->mac_to_oid($mac);
        $this->oids['pon'] = preg_replace('/%deced_mac%/', $this->mac, $this->oids['pon']);
        $this->oids['acs'] = preg_replace('/%deced_mac%/', $this->mac, $this->oids['acs']);
        $this->create_oids = preg_replace('/%deced_mac%/', $this->mac, $this->create_oids);
    }
    
    protected function mac_to_oid($mac) {
        $mac = preg_replace('/[^0-9]+/i', '', $mac);
        $mac_c = strlen($mac);
        for($i = 0;$i < $mac_c; $i += 2) {
            $this->mac .= '.'.hexdec(substr($mac, $i, 2));
        }
    }

    public function add_ont($mac) {
        $this->add_mac($mac);
        $this->add_slot();
        $this->create_pon();
        $this->create_acs($mac);
        $this->object_id = '';
        foreach($this->oids['pon'] as $oid) {
            $this->object_id .= $oid.' ';
        }
        $this->set_ont();
        $this->bunch_sender();
        if($this->snmp_return == '0') {
            $this->object_id = '';
            foreach($this->oids['acs'] as $oid) {
                $this->object_id .= $oid.' ';
            }
            $this->set_ont();
            $this->bunch_sender();
            if($this->snmp_return == '0')
                $this->save_ont();
        }
        return $this->snmp_return;
    }
    
    public function del_ont($mac) {
        $this->del_mac($mac);
        $this->del_slot();
        foreach($this->del_oids['acs'] as $oid) {
            $this->object_id .= $oid.' ';
        }
        $this->set_ont();
        $this->send();
        $this->object_id = '';
        foreach($this->del_oids['pon'] as $oid) {
            $this->object_id .= $oid.' ';
        }
        $this->set_ont();
        $this->send();
        $this->save_ont();
    }
    
    public function save_ont() {
        $this->object_id = '';
        foreach($this->oids_save as $oid) {
            $this->object_id .= $oid;
        }
        $this->send();
    }
    
    public function set_ont_property($prop, $value) {
        if(array_key_exists($prop, $this->oids['pon'])) {
            $this->oids['pon'][$prop] = preg_replace('/%value%/', $value, $this->oids['pon'][$prop]);
        } elseif (array_key_exists($prop, $this->oids['acs'])) {
            $this->oids['acs'][$prop] = preg_replace('/%value%/', $value, $this->oids['acs'][$prop]);
        }
    }
}
?>
