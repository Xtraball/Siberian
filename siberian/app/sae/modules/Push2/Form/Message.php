<?php

namespace Push2\Form;

use Push2\Model\Onesignal\Player;
use Push2\Model\Push;
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

        $individualPush = false;
        if (Push::individualEnabled()) {
            $application = $this->getApplication();
            if ($application && $application->getId()) {
                $individualPush = true;
            }
        }

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

        if ($individualPush) {
            $players = (new Player())->findAll(["app_id = ?" => $application->getId()]);

            $is_individual = $this->addSimpleCheckbox('is_individual', p__('push2', 'Individual?'));

            $this->addSimpleHtml('individual_table', $this->individualTable($players));

            $strSearch = p__('push2', 'Search, filter ...');
        }


        $is_scheduled = $this->addSimpleCheckbox('is_scheduled', p__('push2', 'Schedule?'));

        $send_after = $this->addSimpleDatetimepickerv2('picker_send_after', p__('push2', 'Send after'));
        $send_after->setAttrib('data-moment-format', 'LL');

        $delivery_time_of_day = $this->addSimpleDatetimepickerv2(
            'picker_delivery_time_of_day',
            p__('push2', 'Delivery time of day'),
            false,
            \Siberian_Form_Abstract::TIMEPICKER);
        $delivery_time_of_day->setAttrib('data-moment-format', 'LT');



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

        $submit = $this->addSubmit(p__('push2', 'Send message'), "submit");
        $submit->addClass('pull-right');

        $this->groupElements('the_message', [
            'title',
            //'subtitle',
            'body',
        ], p__('push2', 'Message'));

        if ($individualPush) {
            $this->groupElements('players', [
                'is_individual',
                'individual_table',
            ], p__('push2', 'Individual push'));
        }

        $this->groupElements('the_time', [
            'is_scheduled',
            'picker_send_after',
            'picker_delivery_time_of_day',
            'submit',
        ], p__('push2', 'Scheduling options'));


        $dynamicJs = <<<JS
<script type="text/javascript">
let individualCheckbox = $("#is_individual");
let individualTable = $("#individual_table");
let individualSchedule = function () {
    if (individualCheckbox.is(":checked")) {
        individualTable.parents(".sb-form-line").show();
    } else {
        individualTable.parents(".sb-form-line").hide();
    }
};

individualCheckbox.off("click");
individualCheckbox.on("click", individualSchedule);

individualSchedule();

let toggleAllVisibleCheckbox = $("#toggle_all_visible");
let toggleAllVisible = function () {
    let isChecked = toggleAllVisibleCheckbox.is(":checked");
    let checkboxes = individualTable.find("tbody tr:visible input[type=checkbox]");
    checkboxes.prop("checked", isChecked);
};

let checkAllVisible = function () {
    let checkboxes = individualTable.find("tbody tr:visible input[type=checkbox]");
    let checkboxesChecked = individualTable.find("tbody tr:visible input[type=checkbox]:checked");
    toggleAllVisibleCheckbox.prop("checked", checkboxes.length === checkboxesChecked.length);
};

toggleAllVisibleCheckbox.off("click");
toggleAllVisibleCheckbox.on("click", toggleAllVisible);

let playerIdCheckbox = $("input[name='player_ids[]']");

playerIdCheckbox.off("click");
playerIdCheckbox.on("click", checkAllVisible);


$("table.sb-pager").sbpager({
    with_search: true,
    items_per_page: 20,
    search_placeholder: "{$strSearch}",
    callback_init: function () {
        checkAllVisible();
    },
    callback_goto_page_after: function () {
        checkAllVisible();
    }
});

</script>
JS;

        $this->addMarkup($dynamicJs);
    }

    public function individualTable($players)
    {
        $strId = p__("push2", "ID");
        $strUser = p__("push2", "User");

        $tableHtml = <<<HTML
<div class="col-md-12">
    <table class="table content-white-bkg sb-pager margin-top">
        <thead>
            <tr class="border-grey">
                <th>
                    <label>
                        <input type="checkbox"
                               id="toggle_all_visible" />
                               Toggle all visible
                    </label>
                </th>
                <th class="sortable numeric">{$strId}</th>
                <th class="sortable">{$strUser}</th>
            </tr>
        </thead>
        <tbody>
HTML;
        foreach ($players as $player) {
            $tableHtml .= <<<HTML
            <tr class="sb-pager">
                <td>
                    <input type="checkbox" 
                           name="player_ids[]" 
                           value="{$player->getPlayerId()}" />
                <td>
                    <b>#{$player->getId()}</b>
                </td>
                <td>
                    <b>{$player->getPlayerId()}</b>
                </td>
            </tr>
HTML;
        }
        $tableHtml .= <<<HTML
        </tbody>
    </table>
</div>
HTML;
        return $tableHtml;
    }
}
