<?php

namespace App\Ejma;

enum LineStatus: string
{
    case Pass = 'pass';
    case Fail = 'fail';
    case NotEvaluated = 'not_evaluated';
}
