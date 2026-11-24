<?php

namespace App\Enums;

enum Role: string
{
    case Admin     = 'admin';
    case Professor = 'professor';
    case Student   = 'student';
}
