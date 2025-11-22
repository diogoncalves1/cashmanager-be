<?php

namespace Modules\Friends\Core;

use DateTime;

class Helpers
{
    public static function getOldDate($daysToReduce = 0, $monthsToReduce = 0, $yearsToReduce = 0): DateTime
    {
        $date = new DateTime();

        $oldDate = $date->modify("-{$daysToReduce} days");
        $oldDate = $oldDate->modify("-{$monthsToReduce} months");
        $oldDate = $oldDate->modify("-{$yearsToReduce} years");

        return $oldDate;
    }
}
