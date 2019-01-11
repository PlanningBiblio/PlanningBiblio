<?php

namespace Model;

use Doctrine\ORM\Mapping as ORM;

class Entity {

    public function __call($name, $arguments) {

        if (!property_exists($this, $name)) {
            trigger_error("Unknown method $name");
        }

        if (!isset($arguments[0])) {
            return $this->$name;
        }

        $this->$name = $arguments[0];

        return $this;
    }
}
