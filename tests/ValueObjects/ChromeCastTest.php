<?php

namespace WillemStuursma\CastBlock\Tests\ValueObjects;

use PHPUnit\Framework\TestCase;
use WillemStuursma\CastBlock\ValueObjects\ChromeCast;

class ChromeCastTest extends TestCase
{
    /**
     * @dataProvider dpGoChromeCastOutputStrings
     */
    public function testCreatedFromGoChromecastOutput(string $output, array $expected): void
    {
        $actual = ChromeCast::fromGoChromeCastOutput($output);

        self::assertEquals($actual, $expected);
    }

    public function dpGoChromeCastOutputStrings(): array
    {
        return [
            ["1) device=\"Chromecast\" device_name=\"TV woonkamer\" address=\"192.168.2.148:8009\" uuid=\"fb536d23e0256b514e2feac06dd3c20a\"\n", [new ChromeCast("Chromecast", "TV woonkamer", "192.168.2.148:8009", "fb536d23e0256b514e2feac06dd3c20a")]],
        ];
    }
}