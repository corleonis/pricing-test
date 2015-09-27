<?php
namespace JustPark\Interval;

use Carbon\Carbon;

class Interval
{
    /**
     * Number of hours offset to the 24 hours clock
     */
    const HOURS_OFFSET = 5;

    /**
     * @var Carbon
     */
    private $from;

    /**
     * @var Carbon
     */
    private $to;

    public function __construct(Carbon $from, Carbon $to) {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @return Carbon
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return Carbon
     */
    public function getTo()
    {
        return $this->to;
    }
}
