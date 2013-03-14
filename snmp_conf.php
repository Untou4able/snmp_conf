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
?>