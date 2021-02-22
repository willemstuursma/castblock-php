<?php

namespace WillemStuursma\CastBlock;

use Cache\Adapter\PHPArray\ArrayCachePool;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
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
            $guzzle = new Client();
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
     * @return Segment[]
     */
    public function getSegments(string $videoId): array
    {
        if (!$this->cache->has($videoId)) {

            $url = "https://sponsor.ajay.app/api/skipSegments?videoID=".urlencode($videoId);

            $response = $this->guzzle->get(
                $url,
                [
                    RequestOptions::HTTP_ERRORS => false,
                ]
            );

            $value = Segment::fromSponsorBlockResponse($videoId, $response);

            $this->cache->set($videoId, $value);
        }

        return $this->cache->get($videoId);
    }
}