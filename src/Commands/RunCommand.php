<?php

namespace WillemStuursma\CastBlock\Commands;

use GuzzleHttp\Exception\ConnectException;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use WillemStuursma\CastBlock\ChromeCastConnector;
use WillemStuursma\CastBlock\ChromecastsFinder;
use WillemStuursma\CastBlock\SponsorBlockApi;
use WillemStuursma\CastBlock\SponsorblockCategory;
use WillemStuursma\CastBlock\ValueObjects\ChromeCast;
use WillemStuursma\CastBlock\ValueObjects\Segment;
use WillemStuursma\CastBlock\ValueObjects\Status;

class RunCommand extends Command
{
    use LoggerAwareTrait;

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

    /**
     * @var ChromecastsFinder
     */
    private $castsFinder;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->connector = new ChromeCastConnector();
        $this->sponsorBlock = new SponsorBlockApi();
        $this->logger = new ConsoleLogger($output);
        $this->castsFinder = new ChromecastsFinder($this->logger, $this->connector);
    }

    protected function configure()
    {
        $this
            ->setDescription("Run CastBlock PHP")
            ->setHelp("Runs CastBlock PHP. Any sponsorship segments on Chromecasts will be skipped automatically.")
            ->addOption(
                "category",
                "c",
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                "Which categories do you want to automatically skip?",
                [SponsorblockCategory::INTERACTION()->getValue(), SponsorblockCategory::SPONSOR()->getValue()]
            )
        ;
    }

    /**
     * @return SponsorblockCategory[]
     */
    private function getCategories(InputInterface $input): array
    {
        $selectedCategories = $input->getOption("category");

        $categories = [];

        foreach ($selectedCategories as $selectedCategory) {
            $categories[] = SponsorblockCategory::from($selectedCategory);
        }

        return $categories;

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->debug("Starting CastBlock PHP...");

        $categories = $this->getCategories($input);

        while (true) {

            $chromeCasts = $this->castsFinder->listChromeCasts();

            $found = 0;

            foreach ($chromeCasts as $chromeCast) {
                $found++;
                $this->logger->info("Found {$chromeCast->getDevice()} \"{$chromeCast->getDeviceName()}\" at {$chromeCast->getAddress()}.");
                $this->skipSponsors($chromeCast, $categories);
            }

            $this->sleep($found > 0 ? 2.5 : 10.0);
        }

        return Command::SUCCESS;
    }


    /**
     * @param ChromeCast $chromeCast
     * @param SponsorblockCategory[] $categories
     */
    private function skipSponsors(ChromeCast $chromeCast, array $categories): void
    {
        $status = $this->getChromeCastStatus($chromeCast);

        if (!$status->isPlayingYoutube()) {
            $this->logger->debug("{$chromeCast} is not playing Youtube.");
            return;
        }

        try {
            $segments = $this->sponsorBlock->getSegments($status->getVideoId(), $categories);
        } catch (ConnectException $e) {
            /*
             * Cannot retrieve segments from API now.
             */
            $this->logger->error("Cannot retrieve segments from the Sponsorblock API.");
            return;
        }

        if (count($segments) === 0) {
            $this->logger->info("No segments found for video {$status->getVideoId()}.");
            return;
        }


        foreach ($segments as $segment) {
            $this->logger->debug(
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
            $this->logger->debug("Segment is only {$duration}s, too short to skip.");
            return;
        }

        // e.g. end = 250, position = 249

        if ($position > ($end - 3)) {
            /*
             * We've already passed this segment.
             */
            $this->logger->debug("Segment already (or almost) over, not skipping.");
            return;
        }

        if ($status->getVideoId() !== $segment->getVideoId()) {
            /*
             * We've somehow changed to a different video, ignore this segment.
             */
            $this->logger->debug("{$chromeCast} is no longer playing {$segment->getVideoId()}.");
            return;
        }

        $due = $start - $position;

        $this->logger->info(sprintf("Segment starts in %.02Fs.", $due));

        if ($due > 10) {
            $this->logger->debug("Not skipping now, coming back later.");
            return;
        }

        if ($due > 0) {
            /*
             * $due could be 0 or we could be in the middle of the segment.
             */
            $this->logger->info(sprintf("Waiting %.2Fs for segment to start...", $due));
            $this->sleep($due);
        }

        $fastForwardTo = round($end); // We'll accept skipping 0-0.5s of genuine content.

        $this->logger->info("Fast forwarding to end of segment at {$fastForwardTo}s.");

        $this->connector->seekTo($chromeCast, $fastForwardTo);

        /*
         * Make sure next status call gets a position past the segment.
         */
        $this->sleep(1);
    }

    private function getChromeCastStatus(ChromeCast $chromeCast): Status
    {
        $status = $this->connector->getStatus($chromeCast);

        if ($status->isPlayingYoutube()) {
            $this->logger->debug(sprintf("{$chromeCast} is playing video {$status->getVideoId()} at position %.02Fs.", $status->getPosition()));
        }

        return $status;
    }

    private function sleep(float $seconds): void
    {
        $microSeconds = round($seconds * 1e6);

        usleep($microSeconds);
    }
}