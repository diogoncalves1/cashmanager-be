<?php
namespace Modules\DashboardCharts\Core;

class Helpers
{
    public static function calcVarPercentage($oldVal, $curVal)
    {
        if ($oldVal == 0) {
            return number_format(($curVal * 100), 2);
        }

        return number_format((($curVal - $oldVal) / $oldVal) * 100, 2);
    }

    public static function getClasses($percentage, $inverted = 0)
    {
        if (! $inverted) {
            if ($percentage > 0) {
                return ['class' => 'success', 'icon' => 'up'];
            } elseif ($percentage == 0) {
                return ['class' => 'warning', 'icon' => 'left'];
            } else {
                return ['class' => 'error', 'icon' => 'down'];
            }
        } else {
            if ($percentage < 0) {
                return ['class' => 'success', 'icon' => 'down'];
            } elseif ($percentage == 0) {
                return ['class' => 'warning', 'icon' => 'left'];
            } else {
                return ['class' => 'error', 'icon' => 'up'];
            }
        }
    }
}
