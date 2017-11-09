<?php
$name = "Calendar";
$category = "events";

# Install icons
$icons = array(
    '/calendar/calendar1.png',
    '/calendar/calendar2.png',
    '/calendar/calendar3.png'
);

$result = Siberian_Feature::installIcons($name, $icons);

# Install the Feature
$data = array(
    'library_id'                    => $result["library_id"],
    'icon_id'                       => $result["icon_id"],
    'code'                          => 'calendar',
    'name'                          => $name,
    'model'                         => 'Event_Model_Event',
    'desktop_uri'                   => 'event/application/',
    'mobile_uri'                    => 'event/mobile_list/',
    'mobile_view_uri'               => 'event/mobile_view/',
    'mobile_view_uri_parameter'     => 'event_id',
    'only_once'                     => 0,
    'is_ajax'                       => 1,
    'position'                      => 200,
    'social_sharing_is_available'   => 0
);

$option = Siberian_Feature::install($category, $data, array('code'));


# Icons Flat
$icons = array(
    '/calendar/calendar1-flat.png',
    '/calendar/calendar2-flat.png',
    '/calendar/calendar3-flat.png',
);

Siberian_Feature::installIcons("{$name}-flat", $icons);
