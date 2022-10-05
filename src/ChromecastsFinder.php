<?php

namespace WillemStuursma\CastBlock;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
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
     * @return \Generator<ChromeCast>
     */
    public function listChromeCasts(): \Generator
    {
        if ($this->lastUpdated + self::REDISCOVER_TIMEOUT < microtime(true)) {

            $this->logger->info("Checking for new Chromecasts in local network...");

            $devices = $this->castConnector->listChromeCasts();

            $oldCache = $this->cache;

            $this->cache = [];
            $this->lastUpdated = \microtime(true);

            try {
                foreach ($devices as $device) {
                    /*
                     * yield one by one as there can be some time between discovering multiple Chromecasts.
                     */
                    $this->cache[] = $device;
                    yield $device;
                }
            } catch (ProcessTimedOutException $exception) {
                /*
                 * Process timed out, log exception and continue.
                 */
                $this->logger->error("Failed listing Chromecasts: {$exception->getMessage()}", ["exception" => $exception]);
                yield from $oldCache;
            }

            return;
        }

        yield from $this->cache;
    }
}