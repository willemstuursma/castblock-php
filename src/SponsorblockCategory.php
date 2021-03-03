<?php

namespace WillemStuursma\Castblock;

use MyCLabs\Enum\Enum;

/**
 * @method self SPONSOR()
 * @method self INTRO()
 * @method self OUTRO()
 * @method self INTERACTION()
 * @method self SELFPROMO()
 * @method self MUSIC_OFFTOPIC()
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