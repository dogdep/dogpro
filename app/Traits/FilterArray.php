<?php namespace App\Traits;

/**
 * Class FilterArray
 */
trait FilterArray
{
    /**
     * @param array $arr
     * @return array
     */
    protected function filterArray(array $arr)
    {
        foreach ($arr as $k=>$v) {
            if (is_array($v)) {
                $arr[$k] = $this->filterArray($v);
            }

            if (empty($v)) {
                unset($arr[$k]);
            }
        }

        return $arr;
    }
}
