<?php

namespace WillemStuursma\CastBlock;

use MyCLabs\Enum\Enum;

/**
 * @method static self SPONSOR()
 * @method static self INTRO()
 * @method static self OUTRO()
 * @method static self INTERACTION()
 * @method static self SELFPROMO()
 * @method static self MUSIC_OFFTOPIC()
 */
final class SponsorblockCategory extends Enum
{
    private const SPONSOR = "sponsor";
    private const INTRO = "intro";
    private const OUTRO = "outro";
    private const INTERACTION = "interaction";
    private const SELFPROMO = "selfpromo";
    private const MUSIC_OFFTOPIC = "music_offtopic";
}