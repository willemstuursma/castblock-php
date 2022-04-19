<?php

namespace WillemStuursma\CastBlock;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use WillemStuursma\CastBlock\ValueObjects\ChromeCast;

class ChromecastsFinder
{
    use LoggerAwareTrait;

    /**
     * Check for new Chromecasts every x seconds:
     */
    private const REDISCOVER_TIMEOUT = 15;

    /**
     * @var ChromeCastConnector
     */
    private $castConnector;

    /**
     * @var float
     */
    private $lastUpdated = 0.0;

    /**
     * @var ChromeCast[]
     */
    private $cache = [];

    public function __construct(LoggerInterface $logger, ChromeCastConnector $castConnector)
    {
        $this->castConnector = $castConnector;
        $this->logger = $logger;
    }

    /**
     * Update the list of Chromecasts in the network, every few seconds or so.
     *
     * @return ChromeCast[]
     */
    public function listChromeCasts(): \Generator
    {
        if ($this->lastUpdated + self::REDISCOVER_TIMEOUT < microtime(true)) {

            $this->logger->info("Checking for new Chromecasts in local network...");

            $this->cache = [];
            $this->lastUpdated = \microtime(true);

            $chromecasts = $this->castConnector->listChromeCasts();

            foreach ($chromecasts as $chromecast) {
                $this->cache[] = $chromecast;

                yield $chromecast;
            }

        }

        yield from $this->cache;
    }
}