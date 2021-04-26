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

            $random_method = "random_$type";
            $value = $this->$random_method();
            $metadata->setFieldValue($entity, $field, $value);
        }

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }

    private function random_text($length = 39)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randstring .= $characters[$index];
        }

        return $randstring;
    }

    private function random_string()
    {
        return $this->random_text(15);
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
