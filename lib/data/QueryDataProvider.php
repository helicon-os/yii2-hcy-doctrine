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

use \yii;
use \helicon\hcy\doctrine\orm;

/**
 * Description of DqlDataProvider
 *
 * @author Andreas Prucha, Helicon Software Development
 * 
 * @property-read \Doctrine\ORM\Tools\Pagination\Paginator|\Doctrine\ORM\AbstractQuery $internalDataSource
 */
class QueryDataProvider extends \yii\data\BaseDataProvider
{

    /**
     * Mixed results will not get transformed, thus the result does not contain models, but the 
     * raw associative array returned by doctrine. 
     */
    const MIXED_RESULT_RAW = 0;

    /**
     * Mixed results are encapsulated in bridge objects
     */
    const MIXED_RESULT_BRIDGED = 1;

    /**
     * @var \Doctrine\ORM\Tools\Pagination\Paginator|\Doctrine\ORM\AbstractQuery
     */
    private $dataSource = null;

    /**
     * @var \Doctrine\ORM\Tools\Pagination\Paginator|\Doctrine\ORM\AbstractQuery
     */
    private $internalDataSource = null;

    /**
     * Field or callable to determine the record id
     * 
     * If key is set to null, the DQL feature INDEX BY must be used
     * 
     * @var string|callable|null
     */
    public $key = null;

    /**
     * Indicates if the query returns a mixed or scalar result
     * 
     * @var bool    Set to true if the query returns a mixed or array result 
     */
    public $mixedResult = false;

    /**
     * Specifies how mixed results are handled
     * 
     * <code>{@link MIXED_RESULT_RAW}</code>:       No transformation.
     * <code>{@link MIXED_RESULT_BRIDGED}</code>:   Records are encapsulated into a bridge objects.
     *                                              The class used as bridge can be specified in the property
     *                                              [[$mixedResultBridgeClass]]
     * 
     * @var int 
     */
    public $mixedResultMode = self::MIXED_RESULT_BRIDGED;

    /**
     * Class used as bridge in mixed results
     * 
     * This wrapper class is used if
     * 
     * - The query contains additional (scalar) fields
     * - [[mixedResultMode]] is set to [[self::MIXED_RESULT_BRIDGED]]
     * - *and* [[mixedResult]] is set to TRUE
     * 
     * @var string 
     * @see \helicon\hcy\doctrine\orm\data\MixedResultBrdige 
     */
    public $mixedResultBridgeClass = '\helicon\hcy\doctrine\orm\data\MixedResultBridge';

    /**
     * Class used to wrap doctrine query 
     * 
     * @var false|string Class name or false if the doctrine paginator should not be used. 
     */
    public $doctrinePaginatorClass = '\Doctrine\ORM\Tools\Pagination\Paginator';

    /**
     * Doctrine hydration mode used in [[Doctrine\ORM\AbstractQuery::getResult()]]
     * @var int
     */
    public $hydrationMode = \Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT;

    /**
     * Initializes the DB connection component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     * @throws InvalidConfigException if [[db]] is invalid.
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Sets the data source and 
     * @param \Doctrine\ORM\Tools\Pagination\Paginator| $dataSource
     */
    public function setDataSource($dataSource)
    {
        $this->dataSource = $dataSource;
        $this->models = null;
        $this->internalDataSource = $this->dataSource;
        // Check if pagination is 
        if ($this->pagination !== false &&
                !$this->internalDataSource instanceof \Doctrine\ORM\Tools\Pagination\Paginator) {
            if ($this->doctrinePaginatorClass) {
                // Pagination is requested, but the query is not wrapped into a Doctrine paginator
                $this->internalDataSource = new $this->doctrinePaginatorClass($this->internalDataSource);
            }
        }
    }

    /**
     * @return \Doctrine\ORM\Tools\Pagination\Paginator|\Doctrine\ORM\AbstractQuery|null
     */
    public function getInternalDataSource()
    {
        return $this->internalDataSource;
    }

    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEmInst()
    {
        return Yii::$app->{$this->dc}->getEm($this->em);
    }

    /**
     * @inheritDoc
     * 
     * @param type $models
     * @return type
     */
    protected function prepareKeys($models)
    {
        $keys = [];
        if ($this->key !== null) {
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }
            return $keys;
        } else {
            return array_keys($models);
        }
    }

    protected function addOrderBy(\Doctrine\ORM\AbstractQuery $query, array $sortOrder = [])
    {
        if (!empty($sortOrder)) {
            $orderItems = [];
            foreach ($sortOrder as $fn => $fo) {
                $sortOrder[] = $fn . ' ' . fo;
            }
            $query->setDQL($quey->getDQL() . ' order by ' . implode(', ', $sortOrder));
        }
    }

    /**
     * @return \Doctrine\ORM\AbstractQuery
     */
    protected function getInternalDataSourceQuery()
    {
        $result = $this->getInternalDataSource();
        if ($result instanceof \Doctrine\ORM\Tools\Pagination\Paginator) {
            return $result->getQuery(); // Data Source is a Paginator ===> Return the associated Query
        }
        return $result;
    }

    /**
     * Transforms the result array into bridged models
     * 
     * @param array $models
     * @return void
     */
    protected function transformModelsBridged(array &$models)
    {
        // Check if we really have a mixed or scalar result
        if (!is_array(reset($models))) {
            return false; // Nothing to transform - obviously not a mixed or scalar result
        }
        // Transform the array
        foreach ($models as $i => &$v) {
            $models[$i] = new $this->mixedResultBridgeClass($v);
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function prepareModels()
    {
        $ds = $this->getInternalDataSource();
        $result = [];

        if ($ds instanceof \Doctrine\ORM\Tools\Pagination\Paginator) {
            // The data source is a doctrine Pagnator object. Use it if 
            // pagination is enabled.
            $dsq = $ds->getQuery();
            if (($sort = $this->getSort()) !== false) {
                $this->addOrderBy($ds->getQuery(), $this->getSort()->orders);
            }

            if ($this->pagination instanceof \yii\data\Pagination) {
                $this->pagination->totalCount = $ds->count();
                $first = $this->pagination->getPage() * $this->pagination->getPageSize();
                $limit = $this->pagination->getPageSize();
            } else {
                $this->setTotalCount(null); // Number will be determined by array
                $first = null;
                $limit = null;
            }

            $dsq->setFirstResult($first);
            $dsq->setMaxResults($limit);
            $result = $dsq->getResult($this->hydrationMode);
        } else {
            // Query is not encapulated in a Pagination object
            // Try to simulate pagination if necessary

            $dsq = $ds->getQuery();
            $dsq->setFirstResult($first);
            $dsq->setMaxResults($limit);

            $result = $dsq->getResult();
            $this->pagination->totalCount = count($result);

            if ($this->pagination instanceof \yii\data\Pagination) {
                $result = $dsq->getResult();
                $first = $this->pagination->getPage() * $this->pagination->getPageSize();
                $limit = $this->pagination->getPageSize();
                $result = array_slice($result, $first, $limit, true);
            }
        }

        if (!empty($result) && $this->mixedResult && $this->mixedResultMode) {
            switch ($this->mixedResultMode) {
                case self::MIXED_RESULT_BRIDGED: {
                        $this->transformModelsBridged($result);
                    }
            }
        }

        return $result;
    }

    protected function prepareTotalCount()
    {
        $ds = $this->getDataSource();
        return $ds->count();
    }

    /**
     * @inheritdoc
     */
    public function setSort($value)
    {
        parent::setSort($value);
        $query = $this->getInternalDataSourceQuery();
        $ast = $query->getAST();
        $parse = new \Doctrine\ORM\Query\Parser($query);
        $parse->parse();
        $sort = $this->getSort();
        if ($sort !== false) {
            
        }
        /* @var $model Model */
        /*
          if (($sort = $this->getSort()) !== false && $this->query instanceof ActiveQueryInterface) {
          $model = new $this->query->modelClass;
          if (empty($sort->attributes)) {
          foreach ($model->attributes() as $attribute) {
          $sort->attributes[$attribute] = [
          'asc' => [$attribute => SORT_ASC],
          'desc' => [$attribute => SORT_DESC],
          'label' => $model->getAttributeLabel($attribute),
          ];
          }
          } else {
          foreach($sort->attributes as $attribute => $config) {
          if (!isset($config['label'])) {
          $sort->attributes[$attribute]['label'] = $model->getAttributeLabel($attribute);
          }
          }
          }
          }
         */
    }

}
