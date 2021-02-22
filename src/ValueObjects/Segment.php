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
     * Identifier (from Sponsorblock).
     *
     * @var string
     */
    private $uuid;

    /**
     * @return self[]
     */
    public static function fromSponsorBlockResponse(string $videoId, ResponseInterface $response): array
    {
        if ($response->getStatusCode() === 404) {
            return [];
        }

        //[{"category":"sponsor","segment":[362.213504,424.946576],"UUID":"0541dea82f2c2a26756eecfed7df45aad5e32d6c1c5e2f788dfbe743ee548df2"}]

        $decoded = json_decode($response->getBody(), true);

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
                "UUID" => $instance->uuid
            ] = $segment;

            $return[] = $instance;
        }

        return $return;
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