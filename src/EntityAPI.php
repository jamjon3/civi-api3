<?php

namespace Leanwebstart\CiviApi3;

class EntityAPI
{
    protected $api;
    protected $entity;

    public function __construct($api, $entity)
    {
        $this->api = $api;
        $this->entity = $entity;
    }

    public function __call($action, $args)
    {
        return $this->api->call($this->entity, $action, $args[0]);
    }
}
