<?php

namespace WillemStuursma\CastBlock\Tests;

use PHPUnit\Framework\TestCase;
use WillemStuursma\CastBlock\SegmentMerger;
use WillemStuursma\CastBlock\ValueObjects\Segment;

class SegmentMergerTest extends TestCase
{
    /**
     * @var SegmentMerger
     */
    private $merger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->merger = new SegmentMerger();
    }

    public function testSegmentsAreMerged()
    {
        /** @var Segment[] $segments */
        $segments = [
            new Segment("IdgCkUJNTYs", 186.517, 194.594),
            new Segment("IdgCkUJNTYs", 33.741, 81.883),
            new Segment("IdgCkUJNTYs", 187.428, 192.944),
        ];

        $merged = $this->merger->merge(...$segments);

        /** @var Segment[] $segments */
        $expected = [
            new Segment("IdgCkUJNTYs", 33.741, 81.883),
            new Segment("IdgCkUJNTYs", 186.517, 194.594),
            // Third item is gone because it overlaps with the 2nd.
        ];

        $this->assertEquals($expected, $merged);
    }
}