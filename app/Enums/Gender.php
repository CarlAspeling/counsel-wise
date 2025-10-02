<?php

namespace App\Enums;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case PreferNotToSay = 'prefer_not_to_say';
}
