<?php
class snmp_conf {
    protected $ip, $snmp_comm, $snmp_return, $snmp_req, $object_id, $type, $value;
    protected $version = '1';
    protected $redirect = '2>&1';
    protected $timeout = '1';
    protected $retries = '5';
    protected $output = '-Oqv';
    public function __construct($ip, $options = NULL) {
        $this->ip = $ip;
        if(array_key_exists('version', $options)){ $this->version = $options['version']; }
        if(array_key_exists('output', $options)){ $this->output = $options['output']; }
        if(array_key_exists('timeout', $options)){ $this->timeout = $options['timeout']; }
        if(array_key_exists('retries', $options)){ $this->retries = $options['retries']; }
    }

    protected function make_req() {
        $this->snmp_req = '/usr/local/bin/'. $this->action.' -t '.$this->timeout.' -r '.$this->retries.' -v '. $this->version.' -c '. $this->snmp_comm.' '. $this->output.' '. $this->ip.' '.$this->object_id.' '.$this->type.' '.$this->value.' '. $this->redirect;
    }

    public function get($object_id, $community = 'public') {
        $this->action = 'snmpwalk';
        $this->snmp_comm = $community;
        $this->object_id = $object_id;
        $this->make_req();
    }

    public function set($object_id, $type, $value, $community = 'private') {
        $this->action = 'snmpset';
        $this->snmp_comm = $community;
        $this->type = $type;
        $this->value = $value;
        $this->object_id = $object_id;
        $this->redirect = ';echo "$?"';
        $this->make_req();
    }

    public function send() {
        $this->snmp_return = exec($this->snmp_req);
        return $this->snmp_return;
    }
}

class snmp_eltex extends snmp_conf {
    const MAX_SNMP_SEND_TRIES = 8;
    protected $eltex_options;
    protected $mac = '';
    protected $oids_save = Array(
        'nte' => '1.3.6.1.4.1.35265.1.21.45.0 u 1'
        );
    protected $oids = Array(
        'nte' => Array(
            'id' => '1.3.6.1.4.1.35265.1.21.16.1.1.8.6%deced_mac% s %value%',
            'desc' => '1.3.6.1.4.1.35265.1.21.16.1.1.2.6%deced_mac% u %value%',
            'rule' => '1.3.6.1.4.1.35265.1.21.16.1.1.3.6%deced_mac% u %value%',
            'ipmc' => '1.3.6.1.4.1.35265.1.21.16.1.1.4.6%deced_mac% u %value%',
            'port_p' => '1.3.6.1.4.1.35265.1.21.16.1.1.6.6%deced_mac% u %value%',
            'type' => '1.3.6.1.4.1.35265.1.21.16.1.1.10.6%deced_mac% i %value%',
            'ktv' => '1.3.6.1.4.1.35265.1.21.16.1.1.25.6%deced_mac% i %value%',
            'commit' => '1.3.6.1.4.1.35265.1.21.16.1.1.21.6%deced_mac% u 1'
        )
    );
    protected $del_oids = Array(
        'nte' => Array(
           '1.3.6.1.4.1.35265.1.21.16.1.1.21.6%deced_mac% u 1', '1.3.6.1.4.1.35265.1.21.16.1.1.20.6%deced_mac% u 1', '1.3.6.1.4.1.35265.1.21.45.0 u 1'
        )
    );

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

    private function set_nte($community = 'private') {
        $this->action = 'snmpset';
        $this->snmp_comm = $community;
        $this->redirect = ';echo "$?"';
        $this->make_req();
    }

    private function mac_to_oid($mac) {
        foreach(preg_split('/:/', $mac) as $mac_octet) {
            $this->mac .= '.'.hexdec($mac_octet);
        }
    }

    private function add_mac($mac) {
        $this->mac_to_oid($mac);
        $this->oids['nte'] = preg_replace('/%deced_mac%/', $this->mac, $this->oids['nte']);
    }

    private function del_mac($mac) {
        $this->mac_to_oid($mac);
        $this->del_oids['nte'] = preg_replace('/%deced_mac%/', $this->mac, $this->del_oids['nte']);
    }

    private function save_nte() {
        $this->object_id = $this->oids_save['nte'];
        $this->send();
    }

    public function add_nte($mac) {
        $snmp_send_counter = 0;
        $this->add_mac($mac);
        foreach($this->oids['nte'] as $oid) {
            $this->object_id .= $oid.' ';
        }
        $this->set_nte();
        do {
            $this->send();
            $snmp_send_counter++;
        } while($this->snmp_return != 0 && $snmp_sent_counder < self::MAX_SNMP_SEND_TRIES );
        if($snmp_send_counter >= self::MAX_SNMP_SEND_TRIES ) {
            $this->snmp_return = '2';
        } else {
            $this->save_nte();
        }
        return $this->snmp_return;
    }

    public function del_nte($mac) {
        $this->del_mac($mac);
        foreach($this->del_oids['nte'] as $oid) {
            $this->object_id .= $oid.' ';
        }
        $this->set_nte();
        $this->send();
    }
    public function set_nte_property($prop, $value) {
        if(array_key_exists($prop, $this->oids['nte'])) {
            $this->oids['nte'][$prop] = preg_replace('/%value%/', $value, $this->oids['nte'][$prop]);
        }
    }

}

function copy_options($from, &$to) {
    foreach($from as $option => $value) {
        $to[$option] = $value;
    }
}
?>