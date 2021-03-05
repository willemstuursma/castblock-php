<?php

namespace WillemStuursma\CastBlock\Commands;

use GuzzleHttp\Exception\ConnectException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use WillemStuursma\CastBlock\ChromeCastConnector;
use WillemStuursma\CastBlock\SponsorBlockApi;
use WillemStuursma\CastBlock\SponsorblockCategory;
use WillemStuursma\CastBlock\ValueObjects\ChromeCast;
use WillemStuursma\CastBlock\ValueObjects\Segment;
use WillemStuursma\CastBlock\ValueObjects\Status;

class RunCommand extends Command
{
    protected static $defaultName = 'app:run';

    /**
     * @var ChromeCast[]
     */
    private $chromeCasts = [];

    /**
     * @var int
     */
    private $listLastUpdated;

    /**
     * @var ChromeCastConnector
     */
    private $connector;

    /**
     * @var SponsorBlockApi
     */
    private $sponsorBlock;

    public function __construct(?string $name = null)
    {
        parent::__construct($name);

        $this->connector = new ChromeCastConnector();
        $this->sponsorBlock = new SponsorBlockApi();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $logger->debug("Starting CastBlock PHP...");

        $categories = [
            SponsorblockCategory::SPONSOR(),
            SponsorblockCategory::INTERACTION(),
        ];

        while (true) {

            $chromeCasts = $this->listChromeCasts($logger);

            if (count($chromeCasts) === 0) {
                /*
                 * No Chromecasts found, idle a bit.
                 */
                $this->sleep(10.0);
                continue;
            }

            foreach ($chromeCasts as $chromeCast) {
                $this->skipSponsors($chromeCast, $categories, $logger);
            }

            $this->sleep(2.5);
        }

        return Command::SUCCESS;
    }


    /**
     * @param ChromeCast $chromeCast
     * @param SponsorblockCategory[] $categories
     */
    private function skipSponsors(ChromeCast $chromeCast, array $categories, LoggerInterface $logger): void
    {
        $status = $this->getChromeCastStatus($chromeCast, $logger);

        if (!$status->isPlayingYoutube()) {
            $logger->debug("{$chromeCast} is not playing Youtube.");
            return;
        }

        try {
            $segments = $this->sponsorBlock->getSegments($status->getVideoId(), $categories);
        } catch (ConnectException $e) {
            /*
             * Cannot retrieve segments from API now.
             */
            $logger->error("Cannot retrieve segments from the Sponsorblock API.");
            return;
        }

        if (count($segments) === 0) {
            $logger->info("No segments found for video {$status->getVideoId()}.");
            return;
        }


        foreach ($segments as $segment) {
            $logger->debug(
                sprintf("Found %.02Fs {$segment->getCategory()} segment from %.02Fs to %.2Fs.",
                    $segment->getEnd() - $segment->getStart(),
                    $segment->getStart(),
                    $segment->getEnd()
                )
            );

            $this->handleSponsorshipSegment($chromeCast, $status, $segment, $logger);
        }
    }

    private function handleSponsorshipSegment(ChromeCast $chromeCast, Status $status, Segment $segment, LoggerInterface $logger): void
    {
        $position = $status->getPosition();

        $start = $segment->getStart();
        $end   = $segment->getEnd();

        $duration = $end - $start;

        if ($duration <= 3) {
            /*
             * Segment is too short to skip.
             */
            $logger->debug("Segment is only {$duration}s, too short to skip.");
            return;
        }

        // e.g. end = 250, position = 249

        if ($position > ($end - 3)) {
            /*
             * We've already passed this segment.
             */
            $logger->debug("Segment already (or almost) over, not skipping.");
            return;
        }

        if ($status->getVideoId() !== $segment->getVideoId()) {
            /*
             * We've somehow changed to a different video, ignore this segment.
             */
            $logger->debug("{$chromeCast} is no longer playing {$segment->getVideoId()}.");
            return;
        }

        $due = $start - $position;

        $logger->info(sprintf("Segment starts in %.02Fs.", $due));

        if ($due > 10) {
            $logger->debug("Not skipping now, coming back later.");
            return;
        }

        if ($due > 0) {
            /*
             * $due could be 0 or we could be in the middle of the segment.
             */
            $logger->info(sprintf("Waiting %.2Fs for segment to start...", $due));
            $this->sleep($due);
        }

        $fastForwardTo = round($end); // We'll accept skipping 0-0.5s of genuine content.

        $logger->info("Fast forwarding to end of segment at {$fastForwardTo}s.");

        $this->connector->seekTo($chromeCast, $fastForwardTo);

        /*
         * Make sure next status call gets a position past the segment.
         */
        $this->sleep(1);
    }

    /**
     * Update the list of Chromecasts in the network, every few seconds or so.
     *
     * @return ChromeCast[]
     */
    private function listChromeCasts(LoggerInterface $logger): array
    {
        if ($this->listLastUpdated + 15 < time()) {
            $this->chromeCasts = $this->connector->listChromeCasts();
            $this->listLastUpdated = time();

            foreach ($this->chromeCasts as $chromeCast) {
                $logger->info("Found {$chromeCast->getDevice()} \"{$chromeCast->getDeviceName()}\" at {$chromeCast->getAddress()}.");
            }
        }

        return $this->chromeCasts;
    }

    private function getChromeCastStatus(ChromeCast $chromeCast, LoggerInterface $logger): Status
    {
        $status = $this->connector->getStatus($chromeCast);

        if ($status->isPlayingYoutube()) {
            $logger->debug(sprintf("{$chromeCast} is playing video {$status->getVideoId()} at position %.02Fs.", $status->getPosition()));
        }

        return $status;
    }

    private function sleep(float $seconds): void
    {
        $microSeconds = round($seconds * 1e6);

        usleep($microSeconds);
    }
}