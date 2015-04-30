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

namespace helicon\hcyii2\doctrine\orm\doctrine\loggers;

/**
 * Doctrine SQL Logger to Yii Logger Bridge
 *
 * @author Andreas Prucha, Helicon Software Development
 */
class YiiSqlLogger implements \Doctrine\DBAL\Logging\SQLLogger
{

    public $logLevel = \yii\log\Logger::LEVEL_TRACE;
    public $logCategory = 'doctrine';
    protected $_stack = array();
    protected $_stackCounter = 0;

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $msg = $sql .
                "\n" .
                'params: ' . print_r($params, true) .
                "\n" .
                'types: ' . print_r($types, true);

        $this->_stack[$this->_stackCounter] = array(
            'msg' => $msg,
            'start' => microtime(true),
        );
        $this->_stackCounter++;
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        if ($this->_stackCounter > 0) {
            $this->_stackCounter--;
            \Yii::getLogger()->log('(Duration: ' . (microtime(true) - $this->_stack[$this->_stackCounter]['start']) . ': ' .
                    $this->_stack[$this->_stackCounter]['msg'], $this->logLevel, $this->logCategory);
        }
    }

}
