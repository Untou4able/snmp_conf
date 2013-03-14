<?php
include_once 'snmp_eltex.php';

class eltex_ntp extends snmp_eltex {
    protected $eltex_options;
    protected $mac = '';
    
    protected $oids_save = Array(
        'ltp' => '1.3.6.1.4.1.35265.1.22.1.50.0 u 1'
    );
    protected $oids = Array(
        'ntp' => Array(
            'pon' => Array(
                'mac' => '1.3.6.1.4.1.35265.1.22.3.4.1.20.%slot%.8.69.76.84.88%mac_dec% i %value%',
                'desc' => '1.3.6.1.4.1.35265.1.22.3.4.1.8.%slot%.8.69.76.84.88%mac_dec% s %value%',
                'mgm_prof' => '1.3.6.1.4.1.35265.1.22.3.4.1.9.%slot%.8.69.76.84.88%mac_dec% u %value%',
                'service_prof' => '1.3.6.1.4.1.35265.1.22.3.4.1.5.%slot%.8.69.76.84.88%mac_dec% u %value%',
                'mcast_prif' => '1.3.6.1.4.1.35265.1.22.3.4.1.10.%slot%.8.69.76.84.88%mac_dec% u %value%',
                'pon_port' => '1.3.6.1.4.1.35265.1.22.3.4.1.3.%slot%.8.69.76.84.88%mac_dec% u %value%',
                'ont_id' => '1.3.6.1.4.1.35265.1.22.3.4.1.4.%slot%.8.69.76.84.88%mac_dec% u %value%'
            ),
            'acs' => Array(
                'mac_acs' => '1.3.6.1.4.1.35265.1.22.3.15.1.2.8.69.76.84.88%mac_dec% s %value%',
                'acs_service_prof' => '1.3.6.1.4.1.35265.1.22.3.15.1.3.8.69.76.84.88%mac_dec% s %value%'
            )
        ),
        'ntp_rg' => Array(
            'ppp' => Array(
                'ppp_l' => '1.3.6.1.4.1.35265.1.22.3.15.1.11.8.69.76.84.88%mac_dec% s %value%',
                'ppp_p' => '1.3.6.1.4.1.35265.1.22.3.15.1.12.8.69.76.84.88%mac_dec% s %value%'
            ),
            'voip1' => Array(
                'sip_s' => '1.3.6.1.4.1.35265.1.22.3.15.1.10.8.69.76.84.88%mac_dec% s %value%',
                'voip1_e' => '1.3.6.1.4.1.35265.1.22.3.15.1.4.8.69.76.84.88%mac_dec% s %value%',
                'viop1_l' => '1.3.6.1.4.1.35265.1.22.3.15.1.5.8.69.76.84.88%mac_dec% s %value%',
                'voip1_p' => '1.3.6.1.4.1.35265.1.22.3.15.1.6.8.69.76.84.88%mac_dec% s %value%'
            ),
            'voip2' => Array(
                'voi2_e' => '1.3.6.1.4.1.35265.1.22.3.15.1.7.8.69.76.84.88%mac_dec% s %value%',
                'voip2_l' => '1.3.6.1.4.1.35265.1.22.3.15.1.8.8.69.76.84.88%mac_dec% s %value%',
                'voip2_p' => '1.3.6.1.4.1.35265.1.22.3.15.1.9.8.69.76.84.88%mac_dec% s %value%'
            )
        )
    );
    
}
?>
