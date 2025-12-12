<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

class PLBEntity {

    public function __call($name, $arguments) {

        if (!property_exists($this, $name)) {
            throw new \Exception("Unknown method $name");
        }

        if (!isset($arguments[0])) {
            return $this->$name;
        }

        $this->$name = $arguments[0];

        return $this;
    }

    public function disable() {
        if (!property_exists($this, 'supprime')) {
            throw new \Exception("This entity cannot be disabled");
        }
            $this->supprime(new \DateTime());
    }

    public function enable() {
        if (!property_exists($this, 'supprime')) {
            throw new \Exception("This entity cannot be enabled");
        }

        $this->supprime = null;
    }
}
