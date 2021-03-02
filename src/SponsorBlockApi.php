<?php

namespace WillemStuursma\CastBlock;

use Cache\Adapter\PHPArray\ArrayCachePool;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\RequestOptions;
use Psr\SimpleCache\CacheInterface;
use WillemStuursma\CastBlock\ValueObjects\Segment;

class SponsorBlockApi
{
    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(ClientInterface $guzzle = null, CacheInterface $cache = null)
    {
        if ($guzzle === null) {
            $guzzle = new Client([
                RequestOptions::CONNECT_TIMEOUT => 5,
                RequestOptions::TIMEOUT => 5,
            ]);
        }

        $this->guzzle = $guzzle;

        if ($cache === null) {
            /*
             * Store max 1,000 items in the cache pool to prevent memory filling up.
             */
            $cache = new ArrayCachePool(1000);
        }

        $this->cache = $cache;
    }

    /**
     * @throws ConnectException
     *
     * @return Segment[]
     */
    public function getSegments(string $videoId): array
    {
        if (!$this->cache->has($videoId)) {

            $sha256hash = substr(hash("sha256", $videoId), 0, 4);

            $url = "https://sponsor.ajay.app/api/skipSegments/{$sha256hash}";

            $response = $this->guzzle->get(
                $url,
                [
                    RequestOptions::HTTP_ERRORS => false,
                ]
            );


            $segments = Segment::fromMultiSponsorBlockResponses($videoId, $response);

            $this->cache->set($videoId, $segments);

            return $segments;
        }

        return $this->cache->get($videoId);
    }
}