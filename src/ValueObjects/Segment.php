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
     * @var float
     */
    private $start;

    /**
     * @var float
     */
    private $end;

    public function __construct(string $videoId, float $start, float $end)
    {
        $this->videoId = $videoId;
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * @return Segment[]
     */
    private static function fromDecodedSegments(string $videoId, array $decoded): array
    {
        $return = [];

        foreach ($decoded as $segment) {
            [
                "segment" => [
                    0 => $start,
                    1 => $end,
                ],
            ] = $segment;

            $return[] = new self($videoId, $start, $end);
        }

        return $return;
    }

    public static function fromMultiSponsorBlockResponses(string $videoId, ResponseInterface $response): array
    {
        if ($response->getStatusCode() === 404) {
            return [];
        }

        $responseBody = $response->getBody()->__toString();

        $result = \json_decode($responseBody, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON (" . json_last_error_msg() . "): \"{$responseBody}\"");
        }

        foreach ($result as $item) {
            if ($item["videoID"] !== $videoId) {
                continue;
            }

            return self::fromDecodedSegments($videoId, $item["segments"]);
        }

        return [];
    }

    public static function merge(Segment $first, Segment $second): self
    {
        return new self(
            $first->getVideoId(),
            min($first->getStart(), $second->getStart()),
            max($first->getEnd(), $second->getEnd())
        );
    }

    public function getVideoId(): string
    {
        return $this->videoId;
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