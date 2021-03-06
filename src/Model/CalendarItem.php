<?php

declare(strict_types=1);

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\Calendar\Model;

class CalendarItem
{
    /** @var string */
    private $id;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /** @var \DateTimeImmutable */
    private $dateStart;

    /** @var \DateTimeImmutable */
    private $dateEnd;

    /** @var ?\DateInterval */
    private $repeatInterval;

    /** @var array<string> */
    private $repeatDays = [];

    /** @var array<\DateTimeImmutable> */
    private $repeatExceptions = [];

    /** @var int */
    private $repeatCount = 0;

    /** @var ?\DateTimeImmutable */
    private $repeatEndDate;

    /** @var ?\DateTimeImmutable */
    private $originalDate;

    public function __construct(string $id, string $title, string $description, \DateTimeImmutable $dateStart, \DateTimeImmutable $dateEnd)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->dateStart = $dateStart;
        $this->dateEnd = $dateEnd;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDateStart(): \DateTimeImmutable
    {
        return $this->dateStart;
    }

    public function getDateEnd(): \DateTimeImmutable
    {
        return $this->dateEnd;
    }

    public function setRepeatInterval(?\DateInterval $repeatInterval): void
    {
        $this->repeatInterval = $repeatInterval;
    }

    public function getRepeatInterval(): ?\DateInterval
    {
        return $this->repeatInterval;
    }

    /** @param array<string> $repeatDays */
    public function setRepeatDays(array $repeatDays): void
    {
        $this->repeatDays = $repeatDays;
    }

    /** @return array<string> */
    public function getRepeatDays(): array
    {
        return $this->repeatDays;
    }

    /** @param array<\DateTimeImmutable> $repeatExceptions */
    public function setRepeatExceptions(array $repeatExceptions): void
    {
        $this->repeatExceptions = $repeatExceptions;
    }

    public function addRepeatException(\DateTimeImmutable $repeatException): void
    {
        $this->repeatExceptions[] = $repeatException;
    }

    public function isRepeatException(\DateTimeImmutable $date): bool
    {
        foreach ($this->repeatExceptions as $repeatException) {
            if ($date == $repeatException) {
                return true;
            }
        }

        return false;
    }

    /** @return array<\DateTimeImmutable> */
    public function getRepeatExceptions(): array
    {
        return $this->repeatExceptions;
    }

    public function setRepeatCount(int $repeatCount): void
    {
        $this->repeatCount = $repeatCount;
    }

    public function getRepeatCount(): int
    {
        return $this->repeatCount;
    }

    public function setRepeatEndDate(?\DateTimeImmutable $repeatEndDate): void
    {
        $this->repeatEndDate = $repeatEndDate;
    }

    public function getRepeatEndDate(): ?\DateTimeImmutable
    {
        return $this->repeatEndDate;
    }

    public function setOriginalDate(\DateTimeImmutable $originalDate): void
    {
        $this->originalDate = $originalDate;
    }

    public function getOriginalDate(): ?\DateTimeImmutable
    {
        return $this->originalDate;
    }

    /** @return array<Event> */
    public function getEvents(\DateTimeImmutable $dateStart, \DateTimeImmutable $dateEnd): array
    {
        $events = [];

        if ($this->repeatEndDate instanceof \DateTimeImmutable && $this->repeatEndDate < $dateEnd) {
            $dateEnd = $this->repeatEndDate;
        }

        $repeatDates = $this->getRepeatDates();

        for ($count = 0; true; ++$count) {
            if ($this->repeatCount > 0 && $count >= $this->repeatCount) {
                break;
            }
            foreach ($repeatDates as &$repeatDate) {
                if ($repeatDate['start'] <= $dateEnd && $repeatDate['end'] >= $dateStart && !$this->isRepeatException($repeatDate['start'])) {
                    $events[] = new Event($this->title, $this->description, $repeatDate['start'], $repeatDate['end']);
                }
                if (!$this->repeatInterval || $repeatDate['start'] > $dateEnd) {
                    break 2;
                }
                $repeatDate['start'] = $repeatDate['start']->add($this->repeatInterval);
                $repeatDate['end'] = $repeatDate['end']->add($this->repeatInterval);
            }
        }

        return $events;
    }

    /** @return array<array<\DateTimeImmutable>> */
    public function getRepeatDates(): array
    {
        $repeatDateStart = $this->getDateStart();
        $repeatDateEnd = $this->getDateEnd();
        $repeatDates = [['start' => $repeatDateStart, 'end' => $repeatDateEnd]];
        $repeatDays = $this->getRepeatDays();

        $dayInterval = new \DateInterval('P1D');
        for ($i = 0; $i < 6; ++$i) {
            $repeatDateStart = $repeatDateStart->add($dayInterval);
            $repeatDateEnd = $repeatDateEnd->add($dayInterval);
            if (in_array($repeatDateStart->format('w'), $repeatDays)) {
                $repeatDates[] = ['start' => $repeatDateStart, 'end' => $repeatDateEnd];
            }
        }

        return $repeatDates;
    }
}
