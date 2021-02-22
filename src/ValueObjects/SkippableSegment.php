<?php

namespace WillemStuursma\CastBlock\ValueObjects;

class SkippableSegment
{
    /**
     * @var ChromeCast
     */
    private $chromeCast;
    /**
     * @var Segment
     */
    private $segment;

    public function __construct(ChromeCast $chromeCast, Segment $segment)
    {
        $this->chromeCast = $chromeCast;
        $this->segment = $segment;
    }

    public function getChromeCast(): ChromeCast
    {
        return $this->chromeCast;
    }

    public function getSegment(): Segment
    {
        return $this->segment;
    }
}