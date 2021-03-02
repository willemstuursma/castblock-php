<?php

namespace WillemStuursma\CastBlock\Tests\ValueObjects;

use PHPUnit\Framework\TestCase;
use WillemStuursma\CastBlock\ValueObjects\ChromeCast;
use WillemStuursma\CastBlock\ValueObjects\Status;

class StatusTest extends TestCase
{
    /**
     * @dataProvider dpGoChromeCastOutputStrings
     */
    public function testCreatedFromGoChromecastOutput(string $output, bool $youtubeIsPlaying): void
    {
        $actual = Status::fromGoChromeCastOutput($output);

        self::assertSame($youtubeIsPlaying, $actual->isPlayingYoutube());
    }

    public function dpGoChromeCastOutputStrings(): array
    {
        return [
            ["Idle (Backdrop), volume=0.78 muted=false\n", false],
            ["[wX0vTXk08Pk] YouTube (PLAYING), title=\"I bought more C64 SID chips from AliExpress! (And some other chips too)\", artist=\"\", time remaining=591s/2636s, volume=1.00, muted=false\n", true],
            ["[Zg_bLK5XChA] YouTube (PAUSED), title=\"The 2020 Audi RSQ8 Is a $140,000 Super SUV\", artist=\"\", time remaining=201s/1336s, volume=0.85, muted=false\n", false],
        ];
    }
}