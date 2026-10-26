<?php
/**
 * #296: the iCal calendar cropped descriptions, lost the location and broke on events without end. The reader is a
 * plain class, it is exercised with the shapes real producers (Google, Outlook, Apple) send.
 */
$siberian = dirname(__DIR__, 3) . '/siberian';

if (!class_exists('Ical_Reader', false)) {
    require_once $siberian . '/lib/Ical/Reader.php';
}

$ics = function (array $lines) {
    return implode("\r\n", array_merge(['BEGIN:VCALENDAR', 'VERSION:2.0'], $lines, ['END:VCALENDAR', '']));
};

return [
    '296 a folded description stays in one piece, even when a fold lands before a colon' => function () use ($ics) {
        $reader = new Ical_Reader($ics([
            'BEGIN:VEVENT',
            'DTSTART:20990101T100000Z',
            'DESCRIPTION:Bring your badge and read the programme at the ne',
            ' w address https://example.com/programme before the day',
            '  starts',
            'LOCATION:Main hall',
            'END:VEVENT',
        ]));
        $event = $reader->events()[0];
        t_equal(
            'Bring your badge and read the programme at the new address https://example.com/programme before the day starts',
            $event['DESCRIPTION'],
            'whole description'
        );
        t_equal('Main hall', $event['LOCATION'], 'the next property is still read');
        t_equal(['DTSTART', 'DESCRIPTION', 'LOCATION'], array_keys($event), 'no stray key from the url');
    },

    '296 properties with parameters are read under their plain name' => function () use ($ics) {
        $reader = new Ical_Reader($ics([
            'BEGIN:VEVENT',
            'DTSTART;TZID=Europe/Paris:20990102T100000',
            'DTEND;TZID=Europe/Paris:20990102T110000',
            'SUMMARY;LANGUAGE=en-US:Outlook style',
            'DESCRIPTION;LANGUAGE=en-US:Outlook description',
            'LOCATION;LANGUAGE=en-US:Outlook room',
            'END:VEVENT',
        ]));
        $event = $reader->events()[0];
        t_equal('Outlook style', $event['SUMMARY'], 'summary');
        t_equal('Outlook description', $event['DESCRIPTION'], 'description');
        t_equal('Outlook room', $event['LOCATION'], 'location');
        t_equal('20990102T100000', $event['DTSTART'], 'start');
        t_equal('20990102T110000', $event['DTEND'], 'end');
    },

    '296 text escaping is removed' => function () use ($ics) {
        $reader = new Ical_Reader($ics([
            'BEGIN:VEVENT',
            'DTSTART:20990101T100000Z',
            'SUMMARY:Tea\, cake\; and a path C:\\\\temp',
            'DESCRIPTION:Line one\nLine two\NLine three',
            'LOCATION:Room 1\, Main street',
            'END:VEVENT',
        ]));
        $event = $reader->events()[0];
        t_equal('Tea, cake; and a path C:\\temp', $event['SUMMARY'], 'summary');
        t_equal("Line one\nLine two\nLine three", $event['DESCRIPTION'], 'new lines');
        t_equal('Room 1, Main street', $event['LOCATION'], 'location');
    },

    '296 a reminder inside the event does not replace the event description' => function () use ($ics) {
        $reader = new Ical_Reader($ics([
            'BEGIN:VEVENT',
            'DTSTART:20990101T100000Z',
            'SUMMARY:Meeting',
            'DESCRIPTION:The real description',
            'BEGIN:VALARM',
            'ACTION:DISPLAY',
            'DESCRIPTION:This is an event reminder',
            'END:VALARM',
            'LOCATION:After the reminder',
            'END:VEVENT',
            'BEGIN:VEVENT',
            'DTSTART:20990102T100000Z',
            'SUMMARY:Second',
            'END:VEVENT',
        ]));
        $events = $reader->events();
        t_equal(2, count($events), 'two events');
        t_equal('The real description', $events[0]['DESCRIPTION'], 'description kept');
        t_equal('After the reminder', $events[0]['LOCATION'], 'properties after the alarm belong to the event');
        t_equal('Second', $events[1]['SUMMARY'], 'next event');
    },

    '296 a calendar without events gives an empty list and no warning' => function () use ($ics) {
        $reader = new Ical_Reader($ics(['X-WR-TIMEZONE:Europe/Paris']));
        t_equal([], $reader->events(), 'no events');
        t_equal('Europe/Paris', $reader->timezone(), 'timezone');
    },

    '296 a recurring event without end repeats instead of failing' => function () use ($ics) {
        $reader = new Ical_Reader($ics([
            'BEGIN:VEVENT',
            'DTSTART:20990101T100000',
            'RRULE:FREQ=DAILY;UNTIL=20990104T235959',
            'SUMMARY:Daily',
            'END:VEVENT',
        ]));
        t_equal(4, count($reader->events()), 'the event and three repetitions');
    },

    '296 the agenda tolerates events without end, summary or description' => function () use ($siberian) {
        $code = file_get_contents($siberian . '/app/sae/modules/Event/Model/Event.php');
        t_true(strpos($code, "\$event['DTEND'] = \$event['DTEND'] ?? \$event['DTSTART'];") !== false, 'end defaults to start');
        t_true(strpos($code, "\"name\" => \$event['SUMMARY'] ?? ''") !== false, 'summary is optional');
        t_true(strpos($code, "\$event['DESCRIPTION'] ?? ''") !== false, 'description is optional');
        t_true(strpos($code, '"location_label" => ') !== false, 'the location is shown in the list');
    },
];
