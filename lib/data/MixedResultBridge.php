<?php

/*
 * Copyright (c) 2015, Andreas Prucha, Helicon Software Development
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace helicon\hcy\doctrine\orm\data;

/**
 * Wrapper for mixed results 
 * 
 * If a DQL query containing entity objects *and* scalar fields (e.g. calculated fields) is performed, 
 * doctrine returns an array of associative arrays.
 * 
 * Element 0 always contains the entity object. Additinal fields are returned as field name => value pairs.
 * {@link http://doctrine-orm.readthedocs.org/en/latest/reference/dql-doctrine-query-language.html#pure-and-mixed-results See doctrine documentation)
 * 
 * This class builds a wrapper around the entity object and the additinal fields making all fields accessible as properties.
 *
 * @author Andreas Prucha, Helicon Software Development
 */
class MixedResultBridge extends \yii\base\Object
{

    /**
     * Raw mixed result returned by doctrine
     * 
     * @var array 
     */
    public $rawRecordData = array();
    
    public function __construct ($data) {
        $this->rawRecordData = $data;
    }

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->rawRecordData)) {
            return $this->rawRecordData[$name];
        } else {
            $entityObject = $this->getMainEntityObject();
            if (is_object($entityObject)) {
                return $entityObject->$name;
            }
            else
            {
                return parent::__get($name);
            }
            
        }
    }

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->rawRecordData)) {
            $this->rawRecordData[$name] = $value;
        } else {
            $entityObject = $this->getMainEntityObject();
            if (is_object($entityObject)) {
                $entityObject->$name = $value;
            }
            else
            {
                parent::__set($name, $value);
            }
            
        }
    }

    /**
     * @inheritDoc
     */
    public function __isset($name)
    {
        if (array_key_exists($name, $this->rawRecordData)) {
            return isset($this->rawRecordData[$name]);
        } else {
            $entityObject = $this->getMainEntityObject();
            if (is_object($entityObject) && isset($this->$name)) {
                return true;  // Is set in entity ===> Return TRUE
            }
            else
            {
                return  parent::__isset($name, $value);
            }
        }
    }

    /**
     * @inheritDoc;
     */
    public function __unset($name)
    {
        unset($this->rawRecordData[$name]);
        $entityObject = $this->getMainEntityObject();
        if (is_object($entityObject)) {
            if (isset($entityObject->$name))
                unset($entityObject->$name);
        }
    }

    /**
     * @inheritDoc
     */
    public function __call($name, $params)
    {
        $entityObject = $this->getMainEntityObject();
        if (is_object($entityObject)) {
            return call_user_method_array($name, $entityObject, $params);
        } else {
            parent::__call($name, $params);
        }
    }

    /**
     * @inheritDoc
     */
    public function hasProperty($name, $checkVars = true)
    {
        $result = parent::hasProperty($name, $checkVars);
        if (!$result) {
            $entityObject = $this->getMainEntityObject();
            if ($entityObject instanceof \yii\base\Object) {
                return $entityObject->hasProperty($name, $checkVars);
            } else {
                return property_exists($entityObject, $name);
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        $result = parent::canGetProperty($name, $checkVars);
        if (!$result) {
            $entityObject = $this->getMainEntityObject();
            if ($entityObject instanceof \yii\base\Object) {
                return $entityObject->canGetProperty($name, $checkVars);
            } else {
                return property_exists($entityObject, $name);
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        $result = parent::canSetProperty($name, $checkVars);
        if (!$result) {
            $entityObject = $this->getMainEntityObject();
            if ($entityObject instanceof \yii\base\Object) {
                return $entityObject->canSetProperty($name, $checkVars);
            } else {
                return property_exists($entityObject, $name);
            }
        }
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function hasMethod($name)
    {
        $result = parent::hasMethod($name, $checkVars);
        if (!$result) {
            $entityObject = $this->getMainEntityObject();
            if ($entityObject instanceof \yii\base\Object) {
                return $entityObject->hasMethod($name, $checkVars);
            } else {
                return method_exists($entityObject, $name);
            }
        }
        return $result;
    }

    /**
     * Returns the entity instance 
     * 
     * @return object|null
     */
    public function getMainEntityObject()
    {
        return (isset($this->rawRecordData[0]) && is_object($this->rawRecordData[0])) ? $this->rawRecordData[0] : null;
    }

}
