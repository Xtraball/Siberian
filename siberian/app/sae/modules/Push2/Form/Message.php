<?php

namespace Push2\Form;

use Push2\Model\Onesignal\Player;
use Push2\Model\Onesignal\Scheduler;
use Push2\Model\Push as Push2;
use \Siberian_Form_Abstract as FormAbstract;

/**
 * Class Message
 */
class Message extends FormAbstract
{
    /**
     * @var mixed|null
     */
    public $application;

    /**
     * @var mixed|null
     */
    public $_features;

    /**
     * @param $options
     * @throws \Zend_Form_Exception
     */
    public function __construct($options = null)
    {
        $this->application = $options['application'] ?? null;
        $this->_features = $options['features'] ?? null;

        parent::__construct($options);
    }

    /**
     * @throws \Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        $individualPush = false;
        if (Push2::individualEnabled() &&
            $this->application &&
            $this->application->getId()) {
            $individualPush = true;
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

        // Loading segments from OneSignal API, or catching if misconfigured
        $configured = true;
        try {
            $segmentSlice = (new Scheduler($this->application))->fetchSegments();
            $segmentsOptions = [];
            foreach ($segmentSlice->getSegments() as $segment) {
                $segmentsOptions[$segment->getName()] = $segment->getName();
            }
        } catch (\Exception $e) {
            $segmentsOptions = [];
            $configured = false;
        }

        if (!$configured) {
            $notConfigured = p__("push2", "You must configure OneSignal API Key and OneSignal App ID before sending a push message.");
            $this->addSimpleHtml('segments_not_configured', <<<HTML
<div class="col-md-12">
    <div class="alert alert-danger">
        <strong>{$notConfigured}</strong>
    </div>
</div>
HTML
            );
            return $this;
        }


        $segment = $this->addSimpleSelect(
            'segment',
            p__('push2', 'Segment'),
            $segmentsOptions
        );
        $segment->setRequired();

        $title = $this->addSimpleText('title', p__('push2', 'Title'));
        $title->setRequired();

        //$this->addSimpleText('subtitle', p__('push2', 'Subtitle'));

        $body = $this->addSimpleTextarea('body', p__('push2', 'Message'));
        $body->setRequired();

        if ($individualPush) {
            $players = (new Player())->findWithCustomers(["player.app_id = ?" => $this->application->getId()]);

            $is_individual = $this->addSimpleCheckbox('is_individual', p__('push2', 'Individual?'));
            $is_individual->setDescription(p__('push2', 'Segment & Geolocation are ignored when individual push is used'));

            $this->addSimpleHtml('individual_table', $this->individualTable($players));
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
            'segment',
            'title',
            //'subtitle',
            'body',
        ], p__('push2', 'Message'));

        $big_picture = $this->addSimpleImage('big_picture', p__('push2', "Upload a cover image"), p__('push2', "Add a cover image"), [
            'width' => 1440,
            'height' => 720,
        ]);

        $big_picture_url = $this->addSimpleText('big_picture_url', p__('push2', "Add a cover image URL"));

        $bigPictureHint = p__("push2", "If both big pictures are set, the URL will take over the uploaded one.");
        $this->addSimpleHtml("big_picture_hint", <<<HTML
<div class="col-md-12">
    <div class="alert alert-info">
        {$bigPictureHint}
    </div>
</div>
HTML
        );

        if ($individualPush) {
            $this->groupElements('players', [
                'is_individual',
                'individual_table',
            ], p__('push2', 'Individual push'));
        }

        // Geolocation
        $this->geolocation();

        // URL
        $open_url = $this->addSimpleCheckbox('open_url', p__('push2', 'Link to an URL?'));
        $this->addSimpleText('feature_url', p__('push2', 'URL: https://...'));
        $this->groupElements('open_url_group', [
            'open_url',
            'feature_url',
        ], p__('push2', 'Link to an URL'));

        // Features
        if (!empty($this->_features)) {
            $open_feature = $this->addSimpleCheckbox('open_feature', p__('push2', 'Link to a page?'));
            $this->addSimpleHtml('features_table', $this->featureTable());
            $this->groupElements('features', [
                'open_feature',
                'features_table',
            ], p__('push2', 'Link to a page'));
        }

        $this->groupElements('the_time', [
            'is_scheduled',
            'picker_send_after',
            'picker_delivery_time_of_day',
            'submit',
        ], p__('push2', 'Scheduling options'));


        $strSearch = p__('push2', 'Search, filter ...');
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

$("table.sb-pager.os-players").sbpager({
    with_search: true,
    items_per_page: 10,
    search_placeholder: "{$strSearch}",
    callback_init: function () {
        checkAllVisible();
    },
    callback_goto_page_after: function () {
        checkAllVisible();
    }
});

$("table.sb-pager.sb-features").sbpager({
    with_search: true,
    items_per_page: 10,
    search_placeholder: "{$strSearch}",
});

// Toggle url/feature
let urlCheckbox = $("#open_url");
let featureCheckbox = $("#open_feature");

let toggleElement = function (element, state) {
    element.prop("checked", state);
};

urlCheckbox.off("click");
urlCheckbox.on("click", () => {
    toggleElement(featureCheckbox, false);
});

featureCheckbox.off("click");
featureCheckbox.on("click", () => {
    toggleElement(urlCheckbox, false);
});

// Location & individual
let useLocation = $("#use_location");
useLocation.on("click", () => {
    if (useLocation.is(":checked")) {
        toggleElement(individualCheckbox, false);
        individualSchedule();
    }
});

individualCheckbox.on("click", () => {
    if (individualCheckbox.is(":checked")) {
        toggleElement(useLocation, false);
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
        $strEmail = p__("push2", "Email");

        $tableHtml = <<<HTML
<div class="col-md-12">
    <table class="table content-white-bkg sb-pager os-players margin-top">
        <thead>
            <tr class="border-grey">
                <th style="width: 40px">
                    <label>
                        <input type="checkbox"
                               id="toggle_all_visible" />
                    </label>
                </th>
                <th class="sortable numeric">{$strId}</th>
                <th class="sortable">{$strUser}</th>
                <th class="sortable">{$strEmail}</th>
            </tr>
        </thead>
        <tbody>
HTML;
        foreach ($players as $player) {
            $tableHtml .= <<<HTML
            <tr class="sb-pager">
                <td style="width: 40px">
                    <input type="checkbox" 
                           name="player_ids[]" 
                           value="{$player->getPlayerId()}" />
                </td>
                <td style="width: 80px">
                    <b>#{$player->getCustomerId()}</b>
                </td>
                <td>
                    <b>{$player->getFirstname()} {$player->getLastname()}</b>
                </td>
                <td>
                    <b>{$player->getEmail()}</b>
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

    public function featureTable()
    {
        $strFeature = p__("push2", "Feature");

        $tableHtml = <<<HTML
<div class="col-md-12">
    <table class="table content-white-bkg sb-pager sb-features margin-top">
        <thead>
            <tr class="border-grey">
                <th style="width: 40px"></th>
                <th class="sortable">{$strFeature}</th>
            </tr>
        </thead>
        <tbody>
HTML;
        foreach ($this->_features as $feature) {
            $tableHtml .= <<<HTML
            <tr class="sb-pager" 
                onclick="$('[name=feature_id]').prop('checked', false);$('#feature_id_{$feature->getId()}').prop('checked', true);">
                <td>
                    <input type="checkbox" 
                           name="feature_id" 
                           id="feature_id_{$feature->getId()}"
                           value="{$feature->getId()}" />
                <td>
                    <b>{$this->getFeatureStr($feature)}</b>
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

    public function geolocation() {
        $googlemaps_key = $this->application->getGooglemapsKey();

        $use_location = $this->addSimplecheckbox('use_location', p__('push2', 'Send to location?'));
        $use_location->setDescription(p__('push2', 'Segment & Individual push are ignored when location is used'));
        $this->addSimpleText('location', p__('push2', 'Location'));
        $radius = $this->addSimpleNumber('radius', p__('push2', 'Radius (in meters)'), 10, 1000000, true, 10);
        $radius->setValue(100);
        $this->addSimpleHidden('latitude');
        $this->addSimpleHidden('longitude');

        $raw = <<<HTML
  <div style="margin: 15px;">
  <div id="push2_map" style="width:100%; height: 500px;"></div>

  <script>
    let map;
    let circle;
    let marker;
    let autocomplete;

    function initMap() {
      map = new google.maps.Map(document.getElementById('push2_map'), {
        center: { lat: 48.856614, lng: 2.3522219 },
        zoom: 12
      });
      const input = document.getElementById('location');
      autocomplete = new google.maps.places.Autocomplete(input);
      autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        if (!place.geometry) {
          return; // No place selected
        }

        // Set hidden input fields with latitude and longitude
        document.getElementById('latitude').value = place.geometry.location.lat();
        document.getElementById('longitude').value = place.geometry.location.lng();

        locateOnMap();
      });
    }

    function locateOnMap() {
      const location = document.getElementById('location').value;
      const radius = parseFloat(document.getElementById('radius').value);

      if (isNaN(radius) || radius <= 0 || !location.trim()) {
        alert('Please enter a valid location and radius.');
        return;
      }

      const geocoder = new google.maps.Geocoder();
      geocoder.geocode({ address: location }, function (results, status) {
        if (status === 'OK') {
          const center = results[0].geometry.location;

          if (circle) {
            circle.setMap(null); // Remove the existing circle if any
          }

          map.setCenter(center);

          circle = new google.maps.Circle({
            strokeColor: '#0099C7',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#0099C7',
            fillOpacity: 0.35,
            map: map,
            center: center,
            radius: radius
          });
          
          // Remove existing marker if any
          if (marker) {
            marker.setMap(null);
          }
    
          // Place a marker at the selected location
          marker = new google.maps.Marker({
            position: center,
            map: map,
            title: 'Selected Location'
          });

          const bounds = circle.getBounds();
          map.fitBounds(bounds); // Fit the map to the circle's bounds
        } else {
          alert('Geocode was not successful for the following reason: ' + status);
        }
      });
    }
    
    if(!$('#gmaps_libraries').length) {
        let script_tag = document.createElement('script');
        script_tag.setAttribute("id", "gmaps_libraries");
        script_tag.setAttribute("type", "text/javascript");
        script_tag.setAttribute("src", "https://maps.google.com/maps/api/js?sensor=false&libraries=places&callback=initMap&key={$googlemaps_key}");
        (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
    } else {
        initMap();
    }
  </script>
  </div>
HTML;

        $this->addSimpleHtml('raw_map', $raw);

        $this->groupElements('group_geolocation', [
            'use_location',
            'location',
            'radius',
            'latitude',
            'longitude',
            'raw_map',
        ], p__('push2', 'Geolocation'));

    }

    public function getFeatureStr($feature) {
        $strFeatureParts = [
            "#{$feature->getId()}",
            $feature->getTabbarName()
        ];
        if (!$feature->getIsVisible()) {
            $strFeatureParts[] = '<span class="text-warning">' . p__('push2', '(hidden from menu)'). '</span>';
        }
        if (!$feature->getIsActive()) {
            $strFeatureParts[] = '<span class="text-danger">' . p__('push2', '(not published)'). '</span>';
        }
        return implode_polyfill(' - ', $strFeatureParts);
    }
}
