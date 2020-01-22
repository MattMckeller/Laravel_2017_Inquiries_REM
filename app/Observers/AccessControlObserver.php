<?php
/**
 * Created by PhpStorm.
 * User: Matt
 * Date: 3/8/17
 * Time: 4:23 PM
 */

namespace app\Observers;

class AccessControlObserver
{
    /**
     * Listen to the User created event.
     *
     * @param  Inquiry  $inquiry
     * @return void
     */
    public function saving($model)
    {
//        echo "Generic saving";
    }
    /**
     * Listen to the User created event.
     *
     * @param  Inquiry  $inquiry
     * @return void
     */
    public function saved($model)
    {
//        echo "Generic saved";
//        echo_newline();
//        echo gettype($model);
//        echo_newline();
//        echo get_class($model);
//        echo_newline();
//        echo $model;
//        echo_newline();
    }
}