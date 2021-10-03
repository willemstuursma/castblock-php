<?php

namespace WillemStuursma\CastBlock\ValueObjects;

use WillemStuursma\CastBlock\Exception;

class Status
{
    private $isPlayingYoutube = false;

    /**
     * The time the video started playing.
     *
     * @var float
     */
    private $start;

    /**
     * @var string|null
     */
    private $identifier;

    /**
     * @throws Exception
     */
    public static function fromGoChromeCastOutput(string $output): self
    {
        $instance = new self();

        $instance->isPlayingYoutube = strpos($output, "YouTube (PLAYING)") !== false;

        if (!$instance->isPlayingYoutube()) {
            return $instance;
        }

        if (0 === preg_match("!^\\[(?P<identifier>.[^\\]]+)\\]!m", $output, $matches)) {
            throw new Exception("Failed retrieving identifier from go-chromecast output: {$output}");
        }

        $instance->identifier = $matches["identifier"];

        if (preg_match('!\\\\"currentTime\\\\":([\\d.]+),!', $output, $matches)) {
            /* Debug output was used */
            $position = floatval($matches[1]);
        } else {
            preg_match("!time remaining=(?P<position>\\d+)s/(?P<remaining>\\d+)s!", $output, $matches);
            $position = (int)$matches["position"];
        }

        $instance->start = microtime(true) - $position;

        return $instance;
    }

    public function isPlayingYoutube(): bool
    {
        return $this->isPlayingYoutube;
    }

    /**
     * Get the number of seconds we are in the Youtube video.
     */
    public function getPosition(): float
    {
        return microtime(true) - $this->start;
    }

    /**
     * Get the identifier of the Youtube video that is playing.
     */
    public function getVideoId(): ?string
    {
        return $this->identifier;
    }
}