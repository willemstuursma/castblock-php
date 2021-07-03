<?php

namespace WillemStuursma\CastBlock\ValueObjects;

use Psr\Http\Message\ResponseInterface;

final class Segment
{
    /**
     * @var string
     */
    private $videoId;

    /**
     * @var string
     */
    private $category;

    /**
     * @var float
     */
    private $start;

    /**
     * @var float
     */
    private $end;

    /**
     * @return self[]
     */
    public static function fromSponsorBlockResponse(string $videoId, ResponseInterface $response): array
    {
        if ($response->getStatusCode() === 404) {
            return [];
        }

        $decoded = \json_decode($response->getBody(), true);

        return self::fromDecodedSegments($videoId, $decoded);
    }

    /**
     * @return Segment[]
     */
    private static function fromDecodedSegments(string $videoId, array $decoded): array
{
        $return = [];

        foreach ($decoded as $segment) {

            $instance = new self();
            $instance->videoId = $videoId;

            [
                "category" => $instance->category,
                "segment" => [
                    0 => $instance->start,
                    1 => $instance->end,
                ],
            ] = $segment;

            $return[] = $instance;
        }

        return $return;
    }

    public static function fromMultiSponsorBlockResponses(string $videoId, ResponseInterface $response): array
    {
        if ($response->getStatusCode() === 404) {
            return [];
        }

        $result = \json_decode($response->getBody(), true);

        foreach ($result as $item) {
            if ($item["videoID"] !== $videoId) {
                continue;
            }

            return self::fromDecodedSegments($videoId, $item["segments"]);
        }

        return [];
    }

    public function getVideoId(): string
    {
        return $this->videoId;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getStart(): float
    {
        return $this->start;
    }

    public function getEnd(): float
    {
        return $this->end;
    }
}