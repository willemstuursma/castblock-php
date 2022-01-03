<?php

namespace WillemStuursma\CastBlock;

use WillemStuursma\CastBlock\ValueObjects\Segment;

/**
 * Segments retrieved from the SponsorBlock API can overlap, for example
 * parts of a segments can be marked as well in another category. Hence, we need
 * to merge segments that overlap to prevent excessive skipping.
 *
 * @todo handle segments that are very close together
 */
class SegmentMerger
{
    public function merge(Segment ...$segments): array
    {
        if (count($segments) === 1) {
            return $segments;
        }

        // Sort segments by start time
        usort($segments, function(Segment $a, Segment $b): int {
            return $a->getStart() <=> $b->getStart();
        });

        // Re-index into numeric array.
        $segments = array_values($segments);

        // See if any segment has its start time before the previous segment's end time.
        for ($i = 1; $i < count($segments); $i++) {
            if ($segments[$i]->getStart() <= $segments[$i - 1]->getEnd()) {
                // We must merge this segment with the previous one.
                $segments[$i - 1] = Segment::merge($segments[$i - 1], $segments[$i]);
                $segments[$i] = null;

                return $this->merge(...array_filter($segments));
            }
        }

        return $segments;
    }
}