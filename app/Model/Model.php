<?php namespace App\Model;

use DateTime;

/**
 * Class Model
 *
 */
class Model extends \Illuminate\Database\Eloquent\Model
{
    /**
     * @param DateTime $date
     * @return string
     */
    protected function serializeDate(DateTime $date)
    {
        return $date->format(DATE_ISO8601);
    }

    protected function getArrayableRelations()
    {
        return [];
    }
}
