<?php

namespace WillemStuursma\CastBlock;

use GuzzleHttp\Exception\ConnectException;
use Symfony\Component\Console\Output\OutputInterface;
use WillemStuursma\CastBlock\ValueObjects\ChromeCast;
use WillemStuursma\CastBlock\ValueObjects\Segment;
use WillemStuursma\CastBlock\ValueObjects\Status;

class Worker
{
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
     * @var OutputInterface
     */
    private $output;

    /**
     * @var SponsorBlockApi
     */
    private $sponsorBlock;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->connector = new ChromeCastConnector();
        $this->sponsorBlock = new SponsorBlockApi();
    }

    public function run()
    {
        $this->output->writeln("Starting castblock...");

        $categories = [
            SponsorblockCategory::SPONSOR(),
            SponsorblockCategory::INTERACTION(),
        ];

        while (true) {

            $chromeCasts = $this->listChromeCasts();

            if (count($chromeCasts) === 0) {
                /*
                 * No Chromecasts found, idle a bit.
                 */
                $this->sleep(10.0);
                continue;
            }

            foreach ($chromeCasts as $chromeCast) {
                $this->skipSponsors($chromeCast, $categories);
            }

            $this->sleep(2.5);
        }
    }

    /**
     * @param ChromeCast $chromeCast
     * @param SponsorblockCategory[] $categories
     */
    private function skipSponsors(ChromeCast $chromeCast, array $categories): void
    {
        $status = $this->getChromeCastStatus($chromeCast);

        if (!$status->isPlayingYoutube()) {
            $this->output->writeln("{$chromeCast} is not playing Youtube.");
            return;
        }

        try {
            $segments = $this->sponsorBlock->getSegments($status->getVideoId(), $categories);
        } catch (ConnectException $e) {
            /*
             * Cannot retrieve segments from API now.
             */
            $this->output->writeln("[WARNING] Cannot retrieve segments from the API.");
            return;
        }

        foreach ($segments as $segment) {
            $this->output->writeln(
                sprintf("Found %.02Fs {$segment->getCategory()} segment from %.02Fs to %.2Fs.",
                $segment->getEnd() - $segment->getStart(),
                $segment->getStart(),
                $segment->getEnd()
                )
            );

            $this->handleSponsorshipSegment($chromeCast, $status, $segment);
        }
    }

    private function handleSponsorshipSegment(ChromeCast $chromeCast, Status $status, Segment $segment): void
    {
        $position = $status->getPosition();

        $start = $segment->getStart();
        $end   = $segment->getEnd();

        $duration = $end - $start;

        if ($duration <= 3) {
            /*
             * Segment is too short to skip.
             */
            $this->output->writeln("Segment is only {$duration}s, too short to skip.");
            return;
        }

        // e.g. end = 250, position = 249

        if ($position > ($end - 3)) {
            /*
             * We've already passed this segment.
             */
            $this->output->writeln("Segment already (or almost) over, not skipping.");
            return;
        }

        if ($status->getVideoId() !== $segment->getVideoId()) {
            /*
             * We've somehow changed to a different video, ignore this segment.
             */
            $this->output->writeln("{$chromeCast} is no longer playing {$segment->getVideoId()}.");
            return;
        }

        $due = $start - $position;

        $this->output->writeln(sprintf("Segment starts in %.02Fs.", $due));

        if ($due > 10) {
            $this->output->writeln("Not skipping now, coming back later.", $due);
            return;
        }

        if ($due > 0) {
            /*
             * $due could be 0 or we could be in the middle of the segment.
             */
            $this->output->writeln(sprintf("Waiting %.2Fs for segment to start...", $due));
            $this->sleep($due);
        }

        $fastForwardTo = round($end); // We'll accept skipping 0-0.5s of genuine content.

        $this->output->writeln("Fast forwarding to {$fastForwardTo}s.");
        
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
    private function listChromeCasts(): array
    {
        if ($this->listLastUpdated + 15 < time()) {
            $this->chromeCasts = $this->connector->listChromeCasts();
            $this->listLastUpdated = time();

            foreach ($this->chromeCasts as $chromeCast) {
                $this->output->writeln("Found {$chromeCast->getDevice()} \"{$chromeCast->getDeviceName()}\" at {$chromeCast->getAddress()}.");
            }
        }

        return $this->chromeCasts;
    }

    private function getChromeCastStatus(ChromeCast $chromeCast): Status
    {
        $status = $this->connector->getStatus($chromeCast);

        if ($status->isPlayingYoutube()) {
            $this->output->writeln(sprintf("{$chromeCast} is playing video {$status->getVideoId()} at position %.02Fs.", $status->getPosition()));
        }

        return $status;
    }

    private function sleep(float $seconds): void
    {
        $microSeconds = round($seconds * 1e6);

        usleep($microSeconds);
    }
}
