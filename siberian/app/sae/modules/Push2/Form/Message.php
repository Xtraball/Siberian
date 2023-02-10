<?php

namespace Push2\Form;

use \Siberian_Form_Abstract as FormAbstract;

/**
 * Class Message
 */
class Message extends FormAbstract
{
    /**
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $this
            ->setAction(__path('/push2/application/send-message'))
            ->setAttrib('id', 'push2-form-message');

        /** Bind as a create form */
        self::addClass('create', $this);

        //$target_devices = $this->addSimpleSelect(
        //    'target_devices',
        //    p__('push2', 'Target devices'),
        //    [
        //        'all' => p__('push2', 'All devices'),
        //        'ios' => p__('push2', 'iOS'),
        //        'android' => p__('push2', 'Android'),
        //    ]
        //);

        $title = $this->addSimpleText('title', p__('push2', 'Title'));
        $title->setRequired();

        //$this->addSimpleText('subtitle', p__('push2', 'Subtitle'));

        $body = $this->addSimpleTextarea('body', p__('push2', 'Message'));
        $body->setRequired();

        $this->groupElements('the_message', [
            'title',
            //'subtitle',
            'body',
        ], p__('push2', 'Message'));

        $is_scheduled = $this->addSimpleCheckbox('is_scheduled', p__('push2', 'Schedule?'));

        $send_after = $this->addSimpleDatetimepickerv2('picker_send_after', p__('push2', 'Send after'));
        $send_after->setAttrib('data-moment-format', 'LL');

        $delivery_time_of_day = $this->addSimpleDatetimepickerv2(
            'picker_delivery_time_of_day',
            p__('push2', 'Delivery time of day'),
            false,
            \Siberian_Form_Abstract::TIMEPICKER);
        $delivery_time_of_day->setAttrib('data-moment-format', 'LT');
        $this->groupElements('the_time', [
            'is_scheduled',
            'picker_send_after',
            'picker_delivery_time_of_day',
        ], p__('push2', 'Scheduling options'));

        // Hidden for now, will be used later
        // $this->addSimpleText('latitude', p__('push2', 'Latitude'));
        // $this->addSimpleText('longitude', p__('push2', 'Longitude'));
        // $this->addSimpleText('radius', p__('push2', 'Radius'));

        // $this->addSimpleCheckbox('is_silent', p__('push2', 'Silent'));

        $this->addSimpleHidden('send_after');
        $this->addSimpleHidden('delivery_time_of_day');

        $valueId = $this->addSimpleHidden('value_id');
        $valueId
            ->setRequired(true);

        $submit = $this->addSubmit(p__('push2', 'Send message'));
        $submit->addClass('pull-right');
    }
}
