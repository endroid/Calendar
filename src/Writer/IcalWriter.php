<?php

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Calendar\Writer;

use DateTimeZone;
use Endroid\Calendar\Entity\Calendar;

class IcalWriter
{
    public function writeToString(Calendar $calendar)
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'X-WR-CALNAME:'.$calendar->getTitle(),
            'PRODID:-//hacksw/handcal//NONSGML v1.0//EN',
            'CALSCALE:GREGORIAN'
        ];

        $dateTimeZoneUTC = new DateTimeZone('UTC');

        foreach ($calendar->getEvents() as $event) {
            $lines = array_merge($lines, [
                'BEGIN:VEVENT',
                'SUMMARY:'.$event->getTitle(),
                'DESCRIPTION:'.$event->getDescription(),
            ]);

            $dateStart = $event->getDateStart()->setTimezone($dateTimeZoneUTC);
            $dateEnd = $event->getDateEnd()->setTimezone($dateTimeZoneUTC);

            if ($event->isAllDay()) {
                $lines = array_merge($lines, [
                    'DTSTART;VALUE=DATE:'.$dateStart->format('Ymd\THis\Z'),
                    'DTEND;VALUE=DATE:'.$dateEnd->format('Ymd\THis\Z'),
                ]);
            } else {
                $lines = array_merge($lines, [
                    'DTSTART:'.$dateStart->format('Ymd\THis\Z'),
                    'DTEND:'.$dateEnd->format('Ymd\THis\Z'),
                ]);
            }

            $lines = array_merge($lines, [
                'UID:'.sha1($event->getTitle()), // @todo think of better generic method
                'DTSTAMP:'.$dateStart->format('U'),
                'END:VEVENT'
            ]);
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\n", $lines);
    }
}