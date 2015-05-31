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

namespace helicon\hcy\tests\doctrine\orm\unit;

/**
 * Description of DoctrineComponentTest
 *
 * @author Andreas Prucha, Helicon Software Development
 */
class AbstractTestCase extends \helicon\hcy\phpunit\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        if (!\Yii::$app)
            $this->initMockApplication ();
    }
    
    protected function initMockApplication()
    {
        return $this->mockConsoleApplication([
            'components' => [
                'dc' => [
                    'class' => 'helicon\\hcyii2\\doctrine\\orm\\DoctrineDb',
                    'connections' => [
                        'default' => [
                            'params' => [
                                'driver' => $GLOBALS['db_type'],
                                'dbname' => $GLOBALS['db_name'],
                                'host' => $GLOBALS['db_host'],
                                'user' => $GLOBALS['db_username'],
                                'password' => $GLOBALS['db_password']],
                        ]
                    ],
                    'ormConfigurations' => [
                        'default' => [
                            'proxyDir' => '@runtime/proxies',
                            'metadataDriver' => [
                                'type' => 'simpleAnnotation',
                                'path' => ['@app/data/entities']
                            ]
                        ]
                    ]
                ]
        ]]);
    }
    
    /**
     * Helper function returning <code>$this->app->dc</code>
     * 
     * @return \helicon\hcy\doctrine\orm\DoctrineDb
     */
    protected function getAppDc()
    {
        return self::$app->dc;
    }
            
    protected function newApplicationMock()
    {
    }

}
