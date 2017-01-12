<?php
/**
 * Created by PhpStorm.
 * User: qiang
 * Date: 12/01/17
 * Time: 17:25
 */

namespace Fixtures\Alice\Providers;


class DateTime {


    /**
     * @param string $date
     * @return int
     */
    public static function exactDateTime($date = 'now') {
        return new \DateTime( $date );
    }
}
