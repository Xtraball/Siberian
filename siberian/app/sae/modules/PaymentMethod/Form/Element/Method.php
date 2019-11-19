<?php

namespace PaymentMethod\Form\Element;

use PaymentMethod\Model\Gateway;
use PaymentMethod\Model\GatewayAbstract;
use Siberian_Form_Abstract as FormAbstract;

/**
 * Class Method
 * @package PaymentMethod\Form\Element
 */
class Method extends FormAbstract
{
    /**
     * @param $requiredMethods
     * @return array
     */
    public static function getMethodsFor($requiredMethods)
    {
        $availableMethods = Gateway::all();

        $matchingMethods = [];
        foreach ($availableMethods as $availableMethod) {
            /**
             * @var $tmpClass GatewayAbstract
             */
            $tmpClass = new $availableMethod["class"]();
            foreach ($requiredMethods as $requiredMethod) {
                if ($tmpClass->supports($requiredMethod)) {
                    $matchingMethods[] = $availableMethod;
                    break 1;
                }
            }
        }

        $options = [];
        foreach ($matchingMethods as $matchingMethod) {
            $class = $matchingMethod["class"];
            $label = $matchingMethod["label"];
            /**
             * @var $tmpInstance GatewayAbstract
             */
            $tmpInstance = new $class();
            $isEnabled = $tmpInstance->isSetup() ?
                "<span class=\"pm-enabled\">(" . p__("payment_method", "enabled") . ")</span>":
                "<span class=\"pm-disabled\">(" . p__("payment_method", "disabled") . ")</span>";

            $options[$class] = sprintf("%s %s", $label, $isEnabled);
        }

        return $options;
    }
}