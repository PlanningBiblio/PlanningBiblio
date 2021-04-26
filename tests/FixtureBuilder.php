<?php

namespace Tests;

use App\Model\Agent;

class FixtureBuilder
{

    private $em;

    public function __construct()
    {

        global $entityManager;
        $this->em = $entityManager;

    }

    public function build($model, $values = array())
    {
        $metadata = $this->em->getClassMetadata($model);
        $entity_fields = $metadata->getFieldNames();

        $entity = new $model();
        foreach ($entity_fields as $field) {
            $type = $metadata->getTypeOfField($field);
            $mapping = $metadata->getFieldMapping($field);

            if (isset($values[$field])) {
                $metadata->setFieldValue($entity, $field, $values[$field]);
                continue;
            }

            // Let DBMS set identifiers.
            if ($metadata->isIdentifier($field)) {
                continue;
            }

            if ($metadata->isNullable($field)) {
                continue;
            }

            $value = $this->random($type, $mapping);
            $metadata->setFieldValue($entity, $field, $value);
        }

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    private function random($type, $mapping)
    {
        $value = '';
        $length = isset($mapping['length']) ? $mapping['length'] : null;

        switch ($type) {
            case 'string':
                $value = $this->random_text($length);
                break;
            case 'text':
                $value = $this->random_text($length);
                break;
            case 'date':
                $value = $this->random_date();
                break;
            case 'json_array':
                $value = $this->random_json_array();
                break;
            case 'datetime':
                $value = $this->random_datetime();
                break;
            case 'float':
                $value = $this->random_float();
                break;
            case 'integer':
                $value = $this->random_integer();
                break;
        }

        return $value;
    }

    private function random_text($length)
    {
        $length ??= 20;
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randstring .= $characters[$index];
        }

        return $randstring;
    }

    private function random_date()
    {
        return new \DateTime(date('Y-m-d'));
    }

    private function random_json_array()
    {
        return array();
    }

    private function random_datetime()
    {
        return new \DateTime(date("Y-m-d H:i:s"));
    }

    private function random_float()
    {
        return rand(0, 55) / 10;
    }

    private function random_integer()
    {
        return rand(0, 30);
    }
}
