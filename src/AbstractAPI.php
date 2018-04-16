<?php

namespace Leanwebstart\CiviApi3;

abstract class AbstractAPI
{

    protected $entityApiClassInstances = array();

    abstract protected function apiCall($entity, $action, $params);

    /**
     * Use magic __get to create/call entity-specific API object in the form $thisObject->entity->...
     */
    public function __get($apiEntityToCall)
    {
        if (!isset($this->entityApiClassInstances[$apiEntityToCall])) {
            $this->entityApiClassInstances[$apiEntityToCall] = new EntityAPI($this, $apiEntityToCall);
        }
        return $this->entityApiClassInstances[$apiEntityToCall];
    }

    
    public function call($entity, $action = 'Get', $params = array())
    {

        $params = $this->xformSingleIntegerToId($params);
        
        $result = $this->apiCall($entity, $action, $params);

        return $this->checkResult($result, $entity, $action, $params);
    }


    protected function checkResult($result, $entity, $action, $params)
    {
        if (empty($result) || !is_object($result)) {
            throw new \Exception("The CiviCRM Api3 call return value is empty");
        } elseif (!empty($result->is_error)) {
            $msg = !empty($result->error_message) ? "CiviCRM Api3 error: " . $result->error_message : "CiviCRM Api3 returned an unknown error";
            throw new \Exception($msg);
        } else {
            return $result;
        }
    }

    /**
     * Shorthand call with a single integer, we consider it to be a request for a specific object ID.
     */
    protected function xformSingleIntegerToId($params)
    {

        if (is_int($params)) {
            return ['id' =>  $params];
        }
        return $params;
    }
}
