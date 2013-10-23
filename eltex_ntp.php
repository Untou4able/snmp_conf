<?php
include_once 'snmp_eltex.php';
include_once 'iSnmp_eltex.php';

/*
 * 
 *  the functions of this class are used for both NTP and MA4000 configurations
 * 
 * 
 * 
 */

class eltex_ntp extends snmp_eltex implements iSnmp_eltex {
    protected $mac = '', $eltex_options, $slot, $rg = FALSE, $rg_oids = Array();
    
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
        ),
        'ntp_rg' => Array(
            'ppp_l' => '1.3.6.1.4.1.35265.1.22.3.15.1.11.8.69.76.84.88%deced_mac% s %value%',
            'ppp_p' => '1.3.6.1.4.1.35265.1.22.3.15.1.12.8.69.76.84.88%deced_mac% s %value%',
            'sip_s' => '1.3.6.1.4.1.35265.1.22.3.15.1.10.8.69.76.84.88%deced_mac% s 172.31.254.34',
            'voip1_e' => '1.3.6.1.4.1.35265.1.22.3.15.1.4.8.69.76.84.88%deced_mac% s "Enabled"',
            'voip1_l' => '1.3.6.1.4.1.35265.1.22.3.15.1.5.8.69.76.84.88%deced_mac% s "%value%"',
            'voip1_p' => '1.3.6.1.4.1.35265.1.22.3.15.1.6.8.69.76.84.88%deced_mac% s "%value%"',
            'voip2_e' => '1.3.6.1.4.1.35265.1.22.3.15.1.7.8.69.76.84.88%deced_mac% s "Enabled"',
            'voip2_l' => '1.3.6.1.4.1.35265.1.22.3.15.1.8.8.69.76.84.88%deced_mac% s "%value%"',
            'voip2_p' => '1.3.6.1.4.1.35265.1.22.3.15.1.9.8.69.76.84.88%deced_mac% s "%value%"',
            'inner_vlan' => '1.3.6.1.4.1.35265.1.22.3.5.1.5.2.%slot%.8.69.76.84.88%deced_mac% u %value%',
            'vpn_e' => '1.3.6.1.4.1.35265.1.22.3.5.1.4.2.%slot%.8.69.76.84.88%deced_mac% i 1',
            'vpn_cfg' => Array(
                '1.3.6.1.4.1.35265.1.22.3.60.1.5.8.69.76.84.88%deced_mac%.65536 i 4',
                '1.3.6.1.4.1.35265.1.22.3.60.1.3.8.69.76.84.88%deced_mac%.65536 s InternetGatewayDevice.WANDevice.5.WANConnectionDevice.2.WANIPConnection.1.X_BROADCOM_COM_VlanMuxID',
                '1.3.6.1.4.1.35265.1.22.3.60.1.4.8.69.76.84.88%deced_mac%.65536 s %value%'
            )
        )
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
    
    private function add_rg_mac() {
        if(array_key_exists('voip1_l', $this->rg_oids)) {
            $this->rg_oids['sip_s'] = $this->oids['ntp_rg']['sip_s'];
            $this->rg_oids['voip1_e'] = $this->oids['ntp_rg']['voip1_e'];
        }
        if(array_key_exists('voip2_l', $this->rg_oids)) {
            $this->rg_oids['voip2_e'] = $this->oids['ntp_rg']['voip2_e'];
        }
        if(array_key_exists('inner_vlan', $this->rg_oids)) {
            $this->rg_oids['vpn_e'] = $this->oids['ntp_rg']['vpn_e'];
            $this->oids['ntp_rg']['vpn_cfg'] = preg_replace('/%deced_mac%/', $this->mac, $this->oids['ntp_rg']['vpn_cfg']);
        }
        $this->rg_oids = preg_replace('/%deced_mac%/', $this->mac, $this->rg_oids);
    }
    
    private function add_rg() {
        foreach($this->rg_oids as $oid) {
            $this->object_id = $oid;
            $this->set_ont();
            $this->send();
        }
        if(array_key_exists('inner_vlan', $this->rg_oids)) {
            $this->object_id = '';
            foreach($this->oids['ntp_rg']['vpn_cfg'] as $oid) {
                $this->object_id .= $oid.' ';
            }
            $this->set_ont();
            $this->send();
        }
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
        if($this->rg && !empty($this->rg_oids)) {
            $this->rg_oids = preg_replace('/%slot%/', $this->slot, $this->rg_oids);
        }
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
        if($this->rg && !empty($this->rg_oids)) {
            $this->add_rg_mac();
        }
    }
    
    protected function mac_to_oid($mac) {
        $mac = preg_replace('/^eltx/i', '', $mac);
        $mac_c = strlen($mac);
        for($i = 0;$i < $mac_c; $i += 2) {
            $this->mac .= '.'.hexdec(substr($mac, $i, 2));
        }
    }

    public function add_ont($mac) {
        $this->add_mac($mac);
        $this->add_slot();
        $this->create_pon();
        #echo $this->snmp_req;
        #echo "<br>";
        $this->create_acs($mac);
        $this->object_id = '';
        foreach($this->oids['pon'] as $oid) {
            $this->object_id .= $oid.' ';
        }
        #echo $this->object_id;
        $this->set_ont();
        #echo $this->snmp_req;
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
        if($this->rg) {
            $this->add_rg();
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
        $this->bunch_sender();
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
        } elseif(array_key_exists($prop, $this->oids['ntp_rg']) && !empty($value)) {
            $this->rg = TRUE;
            $this->rg_oids[$prop] = preg_replace('/%value%/', $value, $this->oids['ntp_rg'][$prop]);
            if($prop == 'inner_vlan') {
                $this->oids['ntp_rg']['vpn_cfg'][2] = preg_replace('/%value%/', $value, $this->oids['ntp_rg']['vpn_cfg'][2]);
            }
        }
    }
}
?>