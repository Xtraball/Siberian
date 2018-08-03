<?php
namespace Plesk;

use Plesk\Helper\Xml;

class ListServicePlans extends BaseRequest
{
    /**
     * @var string
     */
    public $xml_packet = <<<EOT
<?xml version="1.0"?>
<packet version="1.6.3.0">
<service-plan>
	<get>
		<filter/>
	</get>
</service-plan>
</packet>
EOT;

    /**
     * @param $xml
     * @return array
     */
    protected function processResponse($xml)
    {
        $result = array();

        for ($i = 0; $i < count($xml->{"service-plan"}->get->result); $i++) {
            $plan = $xml->{"service-plan"}->get->result[$i];

            $hosting = array();
            foreach ($plan->hosting as $host) {
                $hosting[$host->getName()] = Xml::getProperties($host);
            }

            $result[] = array(
                'id' => (string)$plan->id,
                'guid'    =>(string)$plan->guid,
                'status' => (string)$plan->status,
                'name' => (string)$plan->name,
                'limits' => array(
                    'overuse' => (string)$plan->limits->overuse,
                    'max_sites' => Xml::findProperty($plan->limits, 'max_site', 'limit'),
                    'max_subdomains' => Xml::findProperty($plan->limits, 'max_subdom', 'limit'),
                    'max_domain_aliases' => Xml::findProperty($plan->limits, 'max_dom_aliases', 'limit'),
                    'disk_space' => Xml::findProperty($plan->limits, 'disk_space', 'limit'),
                    'max_traffic' => Xml::findProperty($plan->limits, 'max_traffic', 'limit'),
                    'max_web_users' => Xml::findProperty($plan->limits, 'max_wu', 'limit'),
                    'max_subftp_users' => Xml::findProperty($plan->limits, 'max_subftp_users', 'limit'),
                    'max_databases' => Xml::findProperty($plan->limits, 'max_db', 'limit'),
                    'max_mailboxes' => Xml::findProperty($plan->limits, 'max_box', 'limit'),
                    'mailbox_quota' => Xml::findProperty($plan->limits, 'mbox_quota', 'limit'),
                    'max_maillists' => Xml::findProperty($plan->limits, 'max_maillists', 'limit'),
                    'max_webapps' => Xml::findProperty($plan->limits, 'max_webapps', 'limit'),
                    'max_site_builder' => Xml::findProperty($plan->limits, 'max_site_builder', 'limit'),
                    'expiration' => Xml::findProperty($plan->limits, 'expiration', 'limit'),
                ),
                'log_rotation' => array(
                    'on' => (string)$plan->{"log-rotation"}->on->{"log-condition"}->{"log-bytime"},
                    'max_num_files' => (int)$plan->{"log-rotation"}->on->{"log-max-num-files"},
                    'compressed' => (string)$plan->{"log-rotation"}->on->{"log-compress"},
                ),
                'preferences' => array(
                    'stat' => (int)$plan->preferences->stat,
                    'maillists' => (string)$plan->preferences->maillists,
                    'dns_zone_type' => (string)$plan->preferences->dns_zone_type,
                ),
                'hosting' => $hosting,
                'performance' => array(
                    'bandwidth' => (int)$plan->performance->bandwidth,
                    'max_connections' => (int)$plan->performance->max_connections,
                ),
                'permissions' => Xml::getProperties($plan->permissions, 'permission'),
            );
        }

        return $result;
    }
}
