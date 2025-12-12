<?php

namespace App\PlanningBiblio\Event;

use Symfony\Contracts\EventDispatcher\Event;

class BaseEvent extends Event
{
    private $response = array();

    private $params = array();

    private $has_response = 0;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function hasResponse()
    {
        if ( $this->has_response == 1 ) {
            return true;
        }

        return false;
    }

    public function setResponse( $response )
    {
        $this->has_response = 1;
        $this->$response = $response;
    }

    public function response()
    {
        return $this->response;
    }

    public function params()
    {
        return $this->params;
    }
}
