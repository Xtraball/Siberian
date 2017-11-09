<?php
namespace Plesk\Helper;

use Plesk\Node;
use Plesk\NodeList;

class MailPreferences
{
    const NONEXISTENT_USER_BOUNCE = 'bounce';
    const NONEXISTENT_USER_FORWARD = 'forward';
    const NONEXISTENT_USER_REJECT = 'reject';

    /**
     * @param array $params
     * @return NodeList
     */
    public function generate(array $params)
    {
        $nodes = array();
        foreach ($params as $key => $value) {
            if ($node = $this->createNode($key, $value)) {
                $nodes[] = $node;
            }
        }

        return new NodeList($nodes);
    }

    /**
     * @param $key
     * @param $value
     * @return null|Node
     */
    protected function createNode($key, $value)
    {
        switch ($key) {
            case 'nonexistent-user':
                if (in_array($value, array(self::NONEXISTENT_USER_BOUNCE, self::NONEXISTENT_USER_FORWARD, self::NONEXISTENT_USER_REJECT))) {
                    return new Node($key, new Node($value));
                }
                break;
            case 'web_mail':
            case 'spam_protect_sign':
            case 'grey_listing':
            case 'mailservice':
                if (is_bool($value)) {
                    return new Node($key, $value);
                }
                break;
        }

        return null;
    }
}
