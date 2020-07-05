<?php
/**
 * Created by : PhpStorm
 * User: yuansai chen
 * Date: 2020/6/12
 * Time: 11:41
 */

namespace App\TraitInterface;
use DateTimeInterface;

trait ModelDataFormat
{
    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
