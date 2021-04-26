<?php

namespace Tests\FixtureBuilder;

class Agent
{
    private $defaults = array(
        'supprime' => 0,
        'temps' => '[["09:00:00","13:00:00","14:00:00","17:00:00","1"],["09:00:00","13:00:00","14:00:00","17:00:00","1"],["09:00:00","13:00:00","14:00:00","17:00:00","1"],["09:00:00","13:00:00","14:00:00","17:00:00","1"],["09:00:00","13:00:00","14:00:00","17:00:00","1"],["","","","","1"]]',
        'check_hamac' => 0,
    );

    public function getFor($field)
    {
        if (isset($this->defaults[$field])) {
            return $this->defaults[$field];
        }

        return null;
    }
}
