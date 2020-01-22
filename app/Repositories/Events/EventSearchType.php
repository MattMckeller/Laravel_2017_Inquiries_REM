<?php
namespace App\Repositories\Events;

Abstract Class EventSearchType
{
    var $limit;
    var $eventType;
    var $eventName;
    /**
     * @return array of events
     */
    abstract function getEvents();

    function setLimit($val){
        $this->limit = $val;
    }
    function getLimit(){
        return $this->limit;
    }
    function getEventType(){
        return $this->eventType;
    }
    function getEventName(){
        return $this->eventName;
    }
}