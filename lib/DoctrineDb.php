<?php

namespace helicon\hcy\doctrine\orm;

use \Yii;

/**
 * 
 * @property    array   $types    Array of custom Doctrine Types to register. See {@link setTypes()} for details.
 *  
 */
class DoctrineDb extends \yii\base\Component
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $ormEntityManagers;

    /**
     * @var 
     */
    protected $cacheInstances = array();
    public $connectionParams;

    /**
     * Orm Configurations
     * 
     * Note: If just one configuration is used, it should be named 'default'
     * 
     * Each configuration may contain the following items:
     * 
     * <ul>
     *   <li>'proxyDir': * string = '(a)runtime/doctrine/proxies'  Directory where proxies are generated. Yii-Aliases are allowed</li>
     *   <li>'proxyNamespace': * string = '__yii2hcydoctrine__proxies': Namespace </li>
     *   <li>'autoGenerateProxyClasses': bool = true: Generate proxy classes automatically;</li>
     *   <li>'metadataCache': string = {@link $defaultCache}: Id of the cache defined in $caches</li>
     *   <li>'resultCache': string = {@link $defaultCache}: Id of the cache defined in $caches</li>
     *   <li>'queryCache': string = {@link $defaultCache}: Id of the cache defined in $caches</li>
     *   <li>'customFunctions': array = []: Associative Array of function name => Associative array of function definitions.
     *       Each item must contain the following fields:
     *      <ul>
     *        <li>'type': * string: Must be <b>string</b>, <b>numeric</b> or <b>datetime</b></li>
     *        <li>'class': * string: Class name of the user defined function</li>
     *      </ul>
     *   </li>
     *   <li>'metadataDriver': *
     *    Each entry may contain the following items:
     *    <ul>
     *      <li>'type': * string = 'simpleAnnotation'
     *        <ul>
     *          <li>annotation: PhpDoc-Anntotations (uses class \Doctrine\ORM\Mapping\Driver\AnnotationDriver)</li>
     *          <li>simpleAnnotation: simplified PhpDoc-Anntotations (\Doctrine\ORM\Mapping\Driver\AnnotationDriver) </li>
     *          <li>xml: Xml (\Doctrine\ORM\Mapping\Driver\XmlDriver)</li>
     *          <li>yaml: Yaml (\Doctrine\ORM\Mapping\Driver\YamlDriver)</li>
     *          <li>chain: Multiple Mapping drivers (\Doctrine\ORM\Mapping\Driver\DriverChain). </li>
     *        </ul>
     *      </li>
     *      <li>
     *        'path': ? array|string: Directory or directories used for this mapping. Yii-Aliases are resolved.
     *        This property is used for xml, yaml, annotation and simpleAnnotation.    
     *      </li>  
     *      <li>
     *        'mappings': ? array: Array of mappings. Required if type is 'chain'. Important: Use the namespace as key.
     *      </li>  
     *    </ul>
     *   </li>
     * </ul>
     * 
     * @var array Associative array of Doctrine Configurations
     */
    public $ormConfigurations = array();
    public $proxyDir = null;

    /**
     * @var array Associatve array of cache-configurations.
     * 
     * Note: If just one cache is defined, the key 'default' should be used. 
     * 
     * Each configuration may contain the following items:
     * <ul>
     *   <li>'type': array|apc|filesystem. (Default: 'array')</li>
     *   <li>'directory': Type filesystem only: Location of the cache files (Default: '@runtime/doctrine/cache)</li>
     *   <li>'extension': Type filesystem only: Extension of the cache files (Default: Doctrine-Default)</li>
     * </ul>
     * 
     * Example:
     * <code>
     *   [
     *      'default' => [
     *        'type' => 'filesystem',
     *        'directory' => '@runtime/doctrine/cachex'
     *      ]
     *   ]
     * </code>
     * 
     */
    public $caches = array();

    /**
     *  Configuration of Connections
     * 
     *  Associative array of connection configurations. If just one connection is defined, it should be called 'default'.
     *  Each connection configuration may contain the following items:
     * 
     *  <ul>
     *    <li>'params': * Array of parameters passed to {@link \Doctrine\DBAL\DriverManager::getConnection()}
     *        Common parameters are:
     *      <ul>
     *        <li>'driver': string: Name of the driver, e.g. 'pdo_mysql'. Use this, if 'driverClass' is not specified.</li>
     *        <li>'driverClass': string: Class name of the driver. Use this, if 'driver' is not specified.</li>
     *        <li>'dbname': Name of the db</li>
     *        <li>'host': Host</li>
     *        <li>'user': Username</li>
     *        <li>'password': Password</li>
     *        <li>'charset': Character Set</li>
     *      </ul>
     *      See Doctrine documentation for details
     *    </li>
     *    <li>'dbalConfiguration': Id of the dbal configuration. If not specified, {@link $defaultDbalConfiguration} is used.
     *    <li>'eventManager': Id of the event manager. If not specified, {@link $defaultEventManager} is used.
     *  </ul>
     * 
     *  @var Array 
     */
    public $connections = array();

    /**
     * @var array ssociative array of Doctrine dbal-configurations.
     * 
     * This Configuration is optional.
     */
    public $dbalConfigurations = array();

    /**
     * Entity Manger Configurations
     * 
     * Associative array of entity manager configurations. 
     * 
     * Each entity-manager declaration may contain the following configuration-items
     * 
     * <ul>
     *   <li>'connection': string = {@link $defaultConnection}: Id of the connection declared in $connections.
     *   <li>'ormConfiguration': string = {@link $defaultOrmConfiguration}: Id of the Doctrine Orm configuration declared in $ormConfigurations.
     * </ul>
     * 
     * @var array Associative array of entity-manager-configurations
     */
    public $entityManagers = array();

    /**
     * Associative array of event manger configurations
     * 
     * Configuration of one or more event managers. If just one event manager is defined, it should be called 'default'.
     * 
     * @var array 
     */
    public $eventManagers = array();

    /**
     * @var string Id of the standard Dbal Configuration  
     */
    public $defaultDbalConfiguration = 'default';

    /**
     * @var string key of the standard entity manager delared in $entityManagers
     */
    public $defaultEntityManager = 'default';

    /**
     * @var string Id of the default event manager 
     */
    public $defaultEventManager = 'default';

    /**
     * @var string Id of the default Orm Configuration
     */
    public $defaultOrmConfiguration = 'default';

    /**
     * @var string Id of the default connection
     */
    public $defaultConnection = 'default';

    /**
     * @var string Id of the default cache provider
     */
    public $defaultCache = 'default';

    /**
     * @var bool  Enable automatic validation of mappings. NOTE: The validation of mappings is time consuming, 
     *            thus should not be done in production-environments
     */
    public $autoValidateMappings = false;

    /**
     * @var \Doctrine\DBAL\Configuration[] Array of Dbal Configuration objects
     */
    protected $dbalConfigurationObjects = array();

    /**
     * @var \Doctrine\ORM\Configuration[] Array of Orm Configuration objects
     */
    protected $ormConfigurationObjects = array();

    /**
     * @var \Doctrine\DBAL\Connection[] Array of Dbal-Connections
     */
    protected $dbalConnectionObjects = array();

    /**
     *
     * @var \Doctrine\Common\Annotations\SimpleAnnotationReader
     */
    protected $mappingInstances = array();

    /**
     * @var array Associative array of mapping configurations.
     * 
     * The array may contain one or more named mapping configurations. If just one mapping is defined,
     * it should be named 'default'.
     * 
     * Each entry may contain the following items:
     * <ul>
     *   <li>'type': * string: One of the following mapping standard driver types or a fully qualified class name
     *     <ul>
     *       <li>annotation: PhpDoc-Anntotations (uses class \Doctrine\ORM\Mapping\Driver\AnnotationDriver)</li>
     *       <li>simpleAnnotation: simplified PhpDoc-Anntotations (\Doctrine\ORM\Mapping\Driver\AnnotationDriver) </li>
     *       <li>xml: Xml (\Doctrine\ORM\Mapping\Driver\XmlDriver)</li>
     *       <li>yaml: Yaml (\Doctrine\ORM\Mapping\Driver\YamlDriver)</li>
     *       <li>chain: Multiple Mapping drivers (\Doctrine\ORM\Mapping\Driver\DriverChain). </li>
     *     </ul>
     *   </li>
     *   <li>
     *     'path': ? array|string: Directory or directories used for this mapping. Yii-Aliases are resolved.
     *     This property is used for xml and yaml.    
     *   </li>  
     *   <li>
     *     'mappings': ? array: Array of mappings. Required if type is 'chain'. Important: Use the namespace as key.
     *   </li>  
     * </ul>
     */
    public $mappings = array();

    /**
     * @var array Configuration of the message translation source of the category yii-hcy-doctrine
     * 
     * @see \yii\i18n\I18N::$translations
     * @see configureMessageSource()
     */
    public $messageTranslationSource = null;

    /**
     * @var array Array of Doctrine types to register
     */
    protected $_types = array();

    /**
     * @var bool Indicates if types have been registered
     */
    protected $typesRegistered = false;

    /**
     * @var \Doctrine\Common\EventManager[] Array of EventManager-Instances
     */
    protected $dtEventManagerObjects = array();

    public static function getArrayValue($aKey, $aArray, $aDefault)
    {
        return \array_key_exists($aKey, $aArray) ? $aArray[$aKey] : $aDefault;
    }

    public static function setObjectProperties($aObject, array $aPropertyArray = [], array $aExcludeList = [])
    {
        foreach ($aPropertyArray as $p => $v) {
            if (!in_array($p, $aExcludeList)) {
                if (property_exists($aObject, $p)) {
                    $aObject->$p = $v;
                } else {
                    $setter = 'set' . ucfirst($p);
                    if (method_exists($aObject, $setter)) {
                        $aObject->$setter($v);
                    }
                }
            }
        }
    }

    /**
     * Registers messages source of the component
     */
    protected function configureMessageSource($aMessageTranslationSourceConfig)
    {
        if (empty($aMessageTranslationSourceConfig)) {
            $aMessageTranslationSourceConfig = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en-US',
                'basePath' => __DIR__ . '/messages'
            ];
        }
        \Yii::$app->i18n->translations['yii2-hcy-doctrine*'] = $aMessageTranslationSourceConfig;
    }

    /**
     * Initialization of the component
     */
    public function init()
    {
        parent::init();

        $this->configureMessageSource($this->messageTranslationSource);

        if ($this->autoValidateMappings) {
            $this->validateMappings('*', true);
        }
    }

    /**
     * Checks the config array
     * 
     * @param type $aConfigPath
     * @param type $aConfigArray
     * @param array $validators
     * @param type $allowNull
     * @throws \helicon\hcy\doctrine\Exception
     */
    protected function checkConfig($aConfigPath, $aConfigArray, array $validators)
    {
        foreach ($validators as $k => $rules) {
            if (!\array_key_exists($k, $aConfigArray)) {
                if (isset($rules['required']) && $rules['required']) {
                    $error = 'Missing proprety ' . $k . ' in ' . $aConfigPath . (isset($rules['requiredHint']) ? ': ' . $rules['requiredHint'] : '');
                    throw new Exception($error);
                }
            } else {
                if (isset($rules['type'])) {
                    $itemType = \strtolower(gettype($aConfigArray[$k]));
                    if ((\strpos(\strtolower($rules['type']), $itemType) === false) &&
                            (\strpos(\strtolower($rules['type']), 'mixed') === false)) {
                        throw Exception::newError('Doctrine config entry {id} has type {foundType}, but {expectedType} expected',
                                array('id' => $aConfigPath.'.'.$k,
                                    'foundType' => $itemType,
                                    'expectedType' => $rules['type']
                                ));
                    }
                }
            }
        }
    }

    /**
     * Registers all type defined in {@link $types} to doctrine and sets {@link $typesRegistered} to true;
     * 
     * @param bool $reRegister Re-Register types
     */
    public function registerTypes($reRegister = false)
    {
        if ($reRegister || !$this->typesRegistered) {
            foreach ($this->types as $typeName => $typeClass) {
                if (\Doctrine\DBAL\Types\Type::hasType($typeName)) {
                    \Doctrine\DBAL\Types\Type::overrideType($typeName, $typeClass);
                } else {
                    \Doctrine\DBAL\Types\Type::addType($typeName, $typeClass);
                }
            }
        }
        $this->typesRegistered = true;
    }

    /**
     * Sets the {@link $type} property
     * 
     * <b>Example:</b>
     * 
     * <code><pre>
     *  ['MyMoney' => '\foo\bar\MyMoneyType',
     *   'MyDateTime = > '\foo\bar\MyDateTimeType];
     * </pre></code>
     * 
     * @param string[] $aValue Associative array of TypeName => TypeClass - Pairs.
     * @see $types
     * @see getTypes()
     */
    public function setTypes($aValue)
    {
        $this->_types = $aValue;
        //
        // Re-Register types if types have already been registered
        // We do not do it during the first assignment because registerTypes is called
        // when connections are opened
        //
    if ($this->typesRegistered) {
            $this->registerTypes(true);
        }
    }

    /**
     * Returns the value of the {@link $type} property
     * 
     * @return array
     * @see $types
     * @see setTypes()
     */
    public function getTypes()
    {
        return $this->_types;
    }

    /**
     * Creates a new \Doctrine\DBAL\Configuration instance
     * 
     * @param array $aConfigArray
     * @param string $aIdHint
     * @return \Doctrine\DBAL\Configuration
     */
    protected function newDbalConfigurationObject(array $aConfigArray = [], $aIdHint = '?')
    {
        $result = new \Doctrine\DBAL\Configuration();
        $result->setAutoCommit(isset($aConfigArray['autoCommit']) && $aConfigArray['autoCommit']);
        return $result;
    }

    /**
     * Returns the Dbal-Configuration by Id
     * @param type $aId
     * @return \Doctrine\DBAL\Configuration
     */
    public function getDbalConfigurationObject($aId)
    {
        //
        // Check if instance exists and return 
        //
    
        if (isset($this->dbalConfigurationObjects[$aId])) {
            return $this->dbalConfigurationObjects[$aId]; // Found ===> RETURN
        }

        //
        // Create new Instance
        //
    
        $this->dbalConfigurationObjects[$aId] = $this->newDbalConfigurationObject(isset($this->dbalConfigurations[$aId]) ? $this->dbalConfigurations[$aId] : array(), $aId);

        return $this->dbalConfigurationObjects[$aId];
    }

    /**
     * Creates a Cache-Object instance
     * 
     * @param array $aConfig
     */
    protected
            function newDtCacheObject(array $aConfig = array(), $aIdHint = '?')
    {
        $aConfig = array_merge(['disabled' => false], $aConfig);

        $this->checkConfig('configs[' . $aIdHint . ']', $aConfig, [
            'type' => ['required' => true, 'type' => 'string', 'hint' => 'Type of cache must be specified'],
            'disabled' => ['required' => false, 'type' => 'boolean', 'hint' => 'Set to true in order to disable this cache']
        ]);

        if (!$aConfig['disabled']) {

            switch ($aConfig['type']) {
                case 'array': {
                        $result = new \Doctrine\Common\Cache\ArrayCache();
                        break;
                    }
                case 'apc': {
                        $result = new \Doctrine\Common\Cache\ApcCache();
                        break;
                    }
                case 'filesystem':
                case 'php': {
                        $aConfig = array_merge(
                                array('directory' => '@runtime/doctrine/cache',
                            'extension' => null), $aConfig);
                        $aConfig['directory'] = \Yii::getAlias($aConfig['directory']);
                        switch ($aConfig['type']) {
                            case 'filesystem': {
                                    $result = new \Doctrine\Common\Cache\FilesystemCache($aConfig['directory'], $aConfig['extension'] ? $aConfig['extension'] : \Doctrine\Common\Cache\FilesystemCache::EXTENSION);
                                    break;
                                }
                            case 'php': {
                                    $result = new \Doctrine\Common\Cache\FilesystemCache($aConfig['directory'], $aConfig['extension'] ? $aConfig['extension'] : \Doctrine\Common\Cache\PhpFileCache::EXTENSION);
                                    break;
                                }
                        }
                    }
            }
            return $result;
        } else {
            return null;
        }
    }

    /**
     * 
     * @param \Doctrine\Common\Cache $aId
     */
    protected function getDtCacheObject($aId = 'default')
    {

        //
        // Return if cache exists
        //
    
    if (isset($this->cacheInstances[$aId])) {
            return $this->cacheInstances[$aId];  // Found ==> RETURN
        }

        //
        // Create Cache
        //
    
    $this->cacheInstances[$aId] = $this->newDtCacheObject(
                isset($this->caches[$aId]) ? $this->caches[$aId] : array('type' => 'array'), 'caches[' . $aId . ']');

        return $this->cacheInstances[$aId];
    }

    /**
     * Validates mappings
     * 
     * @param \Doctrine\ORM\EntityManager|\Doctrine\ORM\EntityManager[]|string|string[] $aEntityManager Entity-Manager(s)
     * @return array List of errors
     */
    public function validateMappings($aEntityManager, $throwExceptionOnError = false)
    {
        $result = array();
        if ($aEntityManager === '*') {
            $aEntityManager = array_keys($this->entityManagers);
        }
        if (is_array($aEntityManager)) {
            foreach ($aEntityManager as $em) {
                $result = array_merge($result, $this->validateMappings($em));
            }
        } else {
            if (!$aEntityManager instanceof \Doctrine\ORM\EntityManagerInterface) {
                $aEntityManager = $this->getEm($aEntityManager);
            }
            $validator = new \Doctrine\ORM\Tools\SchemaValidator($aEntityManager);
            return $validator->validateMapping();
        }

        if (!empty($result) && $throwExceptionOnError) {
            throw new Exception(\Yii::t('yii2-hcy-doctrine', "The following Doctrine mapping errors have been found: \n {mappingErrors}", ['{mappingErrors}' => implode("\n", $result)]));
        }

        return $result;
    }

    public function validateSchema($aEntityManager = 'default', $throwExceptionOnError = FALSE)
    {
        $errors = parent::validateSchema($aEntityManager);

        if ($throwExceptionOnError && !empty($errors)) {
            $msg = '';
            foreach ($errors as $m) {
                $msg .= print_r($m, true) . "\n";
            }
            throw new Exception($msg);
        }

        return $errors;
    }

    /**
     * Generates all proxies for the given entity manager(s)
     * 
     * @param \Doctrine\ORM\EntityManager|\Doctrine\ORM\EntityManager[]|string|string[] $aEntityManager One or array of EntityManager-Ids or EntityManager object
     * @return array List of errors
     */
    public function generateAllProxies(\Doctrine\ORM\EntityManager $aEntityManager)
    {
        $result = array();
        if (is_array($aEntityManager)) {
            foreach ($aEntityManager as $em) {
                $result = array_merge($result, $this->generateAllProxies($em));
            }
        } else {
            if (!$aEntityManager instanceof \Doctrine\ORM\EntityManagerInterface) {
                $aEntityManager = $this->getEm($aEntityManager);
            }

            $cmf = $this->em->getMetadataFactory();
            $classes = $cmf->getAllMetadata();

            $aEntityManager->getProxyFactory()->generateProxyClasses($classes);
        }
        return $result;
    }

    /**
     * Creates a new mapping driver object for an orm configuration
     * 
     * @param \Doctrine\ORM\Configuration $aOrmConfiguration
     * @param array $aConfig
     * @param string $aIdHint
     */
    protected function newMetadataDriverObjectForOrmConfigurationObject(\Doctrine\ORM\Configuration $aOrmConfiguration, array $aConfig = array(), $aIdHint = '?')
    {
        //
        // Check type and translate into class if necesary
        //
    
    $this->checkConfig($aIdHint, $aConfig, [
            'type' => ['required' => true, 'type' => 'string'],
            'path' => ['required' => false, 'type' => 'string|array']
        ]);
        $typeLower = \strtolower($aConfig['type']);

        //
        // Resolve Aliases if path is set
        //
    if (isset($aConfig['path'])) {
            if (is_array($aConfig['path'])) {
                foreach ($aConfig['path'] as $k => $v) {
                    $aConfig['path'][$k] = \Yii::getAlias($v);
                }
            } else {
                $aConfig['path'] = \Yii::getAlias($aConfig['path']);
            }
        }

        //
        // Resolve Aliases if prefixes is set
        //
    if (isset($aConfig['prefixes'])) {
            $newValues = array();
            foreach ($aConfig['prefixes'] as $k => $v) {
                $newValues[\Yii::getAlias($k)] = $v;
            }
        }

        switch ($typeLower) {
            case 'annotation':
            case 'simpleannotation': {
                    $this->checkConfig($aIdHint, $aConfig, ['path' => ['required' => true]]);
                    $result = $aOrmConfiguration->newDefaultAnnotationDriver($aConfig['path'], $typeLower == 'simpleannotation');
                    break;
                }
            case 'chain': {
                    if (!isset($aConfig['mappings'])) {
                        throw new \helicon\hcy\doctrine\Exception(
                        \Yii::t('yii2-hcy-doctrine', 'Item \'mappings\' not specified for chain-mapping {$aId}', ['{$aId}' => $aIdHint]));
                    }

                    $result = new \Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain();

                    foreach ($aConfig['mappings'] as $namespace => $nestedConfig) {
                        $nestedDriver = $this->newMappingDriverObjectForOrmConfigurationObject($aOrmConfiguration, $namespace, $nestedConfig);
                        $result->addDriver($nestedDriver, $namespace);
                    }
                    break;
                }
            case 'xml':
            case '\Doctrine\Common\Persistence\Mapping\Driver\XmlDriver': {
                    $result = new \Doctrine\ORM\Mapping\Driver\XmlDriver($aConfig['path'], \Doctrine\ORM\Mapping\Driver\XmlDriver::DEFAULT_FILE_EXTENSION);
                    break;
                }
            case 'yaml':
            case '\Doctrine\Common\Persistence\Mapping\Driver\YamlDriver': {
                    $this->checkConfig($aIdHint, $aConfig, ['path' => ['required' => true]]);
                    $result = new \Doctrine\ORM\Mapping\Driver\YamlDriver($aConfig['path'], \Doctrine\ORM\Mapping\Driver\YamlDriver::DEFAULT_FILE_EXTENSION);
                    break;
                }
            case 'simplifiedxml':
            case '\Doctrine\Common\Persistence\Mapping\Driver\SimplifiedXmlDriver': {
                    $this->checkConfig($aIdHint, $aConfig, ['prefixes' => ['required' => true]]);
                    $result = new \Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver($aConfig['prefixes'], \Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver::DEFAULT_FILE_EXTENSION);
                    break;
                }
            case 'simplifiedyaml':
            case '\Doctrine\Common\Persistence\Mapping\Driver\SimplifiedYamlDriver': {
                    $this->checkConfig($aIdHint, $aConfig, ['prefixes' => ['required' => true]]);
                    $result = new \Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver($aConfig['prefixes'], \Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver::DEFAULT_FILE_EXTENSION);
                    break;
                }
            default: {
                    $reflector = new \ReflectionClass($aConfig['type']);
                    $result = $reflector->newInstanceArgs($aConfig['params']);
                }
        }

        return $result;
    }

    protected function newSqlLoggerObject($aLoggerConfig, $aConfigIdHint = '?')
    {

        if (empty($aLoggerConfig))
            return null;

        $this->checkConfig($aConfigIdHint, $aLoggerConfig, [
            'class' => ['required' => true, 'type' => 'string'],
        ]);
        switch ($aLoggerConfig['class']) {
            case 'chain':;
            case '\Doctrine\DBAL\Logging\LoggerChain': {
                    $this->checkConfig($aConfigIdHint, $aLoggerConfig, [
                        'loggers' => ['required' => true, 'type' => 'array'],
                    ]);
                    $result = new \Doctrine\DBAL\Logging\LoggerChain();
                    foreach ($aLoggerConfig['loggers'] as $i => $subLoggerConfig) {
                        $subLogger = $this->newSqlLoggerObject($subLoggerConfig, $aConfigIdHint . '.sqlLoggers.' . $i);
                        $result->addLogger($subLogger);
                    }
                    break;
                }
            case 'echo':;
            case '\Doctrine\DBAL\Logging\EchoSQLLogger': {
                    $result = new \Doctrine\DBAL\Logging\EchoSQLLogger();
                    break;
                }
            case 'yii':;
            case '\helicon\hcy\doctrine\orm\doctrine\loggers\YiiSqlLogger': {
                    $result = new \helicon\hcy\doctrine\orm\doctrine\loggers\YiiSqlLogger();
                    self::setObjectProperties($result, $aLoggerConfig, ['class']);
                    break;
                }
            default: {
                    $result = new $aLoggerConfig['class']();
                    self::setObjectProperties($result, $aLoggerConfig, ['class']);
                    break;
                }
        }
    }

    /**
     * Creates a new Orm configuration object
     * @param string $aConfigId
     * @return \Doctrine\ORM\Configuration
     */
    protected function newOrmConfigurationObject($aConfiguration, $aConfigIdHint = '?')
    {
        //
        // Configuration obviously does not exist - Create it
        //
        
        $this->checkConfig($aConfigIdHint, $aConfiguration, [
            'customFunctions' => ['required' => false, 'type' => 'array'],
        ]);

        // Create working copy of config array and manipulate entries where necessary

        $c = array_merge(
                array(
            'proxyDir' => '@runtime/doctrine/proxies',
            'proxyNamespace' => '__yii2hcydoctrine__proxies',
            'autoGenerateProxyClasses' => true,
            'metadataCache' => $this->defaultCache,
            'resultCache' => $this->defaultCache,
            'queryCache' => $this->defaultCache,
                ), $aConfiguration);  // Create working copy of configuration array

        $c['proxyDir'] = \Yii::getAlias($c['proxyDir']);

        $c['metadataCacheImpl'] = $this->getDtCacheObject($c['metadataCache']);
        unset($c['metadataCache']);

        $c['queryCacheImpl'] = $this->getDtCacheObject($c['queryCache']);
        unset($c['queryCache']);

        $c['resultCacheImpl'] = $this->getDtCacheObject($c['resultCache']);
        unset($c['resultCache']);

        if (isset($c['namingConvention'])) {
            $c['resultCacheImpl'] = $this->initOrGetCacheInstance($c['resultCache']);
        }

        if (isset($c['sqlLogger'])) {
            $c['sqlLogger'] = $this->newSqlLoggerObject($c['sqlLogger']);
        }

        $customFunctions = (isset($c['customFunctions'])) ? $c['customFunctions'] : [];
        unset($c['customFunctions']);

        $result = new \Doctrine\ORM\Configuration();

        if (isset($c['metadataDriver'])) {
            $c['metadataDriverImpl'] = $this->newMetadataDriverObjectForOrmConfigurationObject($result, $c['metadataDriver'], $aConfigIdHint . '[metadataDriver]');
            unset($c['metadataDriver']);
        }

        foreach ($c as $ck => $cv) {
            $setter = 'set' . \ucfirst($ck);
            $result->$setter($cv);
        }

        if (!empty($customFunctions)) {
            foreach ($customFunctions as $fn => $fd) {
                switch (strtolower(isset($fd['type']) ? $fd['type'] : '?')) {
                    case 'string': {
                            $result->addCustomStringFunction($fn, $fd['class']);
                            break;
                        }
                    case 'numeric': {
                            $result->addCustomNumericFunction($fn, $fd['class']);
                            break;
                        }
                    case 'datetime': {
                            $result->addCustomDatetimeFunction($fn, $fd['class']);
                            break;
                        }
                    default: {
                            throw new Exception('customFunction type not set or unknown: ' . $fd['type']);
                        }
                }
            }
        }

        return $result;
    }

    /**
     * Returns an OrmConfigurationObject by id
     * @param type $aId
     * @return \Doctrine\ORM\Configuration
     * @throws Exception
     */
    public function getOrmConfigurationObject($aId)
    {
        ($aId) || $aId = $this->defaultOrmConfiguration;

        //
        // Return configuration if it already exists
        //
    
        if (isset($this->ormConfigurationObjects[$aId])) {
            return $this->ormConfigurationObjects[$aId];
        }

        //
        // Object does not exist - create it
        //
    
        if (!isset($this->ormConfigurations[$aId]) || !is_array($this->ormConfigurations[$aId])) {
            throw new Exception(Yii::t('yii2-hcy-doctrine', 'ormConfiguration {id} is invalid or des not exist', ['{id}' => $aId]));
        }

        $this->ormConfigurationObjects[$aId] = $this->newOrmConfigurationObject($this->ormConfigurations[$aId], $aId);

        return $this->ormConfigurationObjects[$aId];
    }

    protected function initOrGetMapping($aId = null)
    {
        ($aId) || $aId = 'default';

        if (isset($this->mappingInstances[$aId])) {
            return $this->mappingInstances[$aId];  // Found ===> RETURN
        }
    }

    /**
     * Creates a new Doctrine Event Manager Instance
     * 
     * @param array $aConnectionConfig
     * @param string $aIdHint
     * @return \Doctrine\Common\EventManager
     */
    protected function newDtEventManagerObject(array $aConnectionConfig = [], $aIdHint = '?')
    {
        if (isset($aConnectionConfig['class'])) {
            return new $aConnectionConfig['class']();
        } else {
            return new \Doctrine\Common\EventManager();
        }
    }

    public function getDtEventManagerObject($aId)
    {
        if (isset($this->dtEventManagerObjects[$aId])) {
            return $this->dtEventManagerObjects[$aId]; // Found ===> RETURN
        }


        $this->dtEventManagerObjects[$aId] = $this->newDtEventManagerObject(
                isset($this->eventManagers[$this->defaultEventManager]) ? $this->eventManagers[$this->defaultEventManagerId] : array(), $aId);
    }

    /**
     * Creates a Dbal Conneciton Object 
     * 
     * @param type $aId
     * @param type $aConnectionConfig
     * @return type
     */
    protected function newDbalConnectionObject($aId, $aConnectionConfig)
    {
        $this->checkConfig('connections.' . $aId, $aConnectionConfig, [
            'params' => ['required' => true]]);

        $dbalConfig = $this->getDbalConfigurationObject(self::getArrayValue('dbalConfiguration', $aConnectionConfig, $this->defaultDbalConfiguration));
        $eventManager = $this->getDtEventManagerObject(self::getArrayValue('eventManager', $aConnectionConfig, $this->defaultEventManager));

        $this->registerTypes(false); // Register global type mappings if necessary

        $result = \Doctrine\DBAL\DriverManager::getConnection($aConnectionConfig['params'], $dbalConfig, $eventManager);

        if (isset($aConnectionConfig['typeMappings'])) {
            foreach ($aConnectionConfig['typeMappings'] as $dbType => $doctrineType) {
                if (!$result->getDatabasePlatform()->hasDoctrineTypeMappingFor($dbType)) {
                    $result->getDatabasePlatform()->registerDoctrineTypeMapping($dbType, $doctrineType);
                }
            }
        }

        return $result;
    }

    /**
     * Returns a Doctrine connection object by Id
     * 
     * @param string $aId Id of the connection
     * @result Doctrine\DBAL\Connection
     */
    public function getDbalConnectionObject($aId)
    {
        //
        // Return Connection object if it exists
        //
    if (isset($this->dbalConnectionObjects[$aId])) {
            return $this->dbalConnectionObjects[$aId]; // ===> Exists, RETURN
        }

        //
        // Create new connection based on configuration
        //
    
    if (!isset($this->connections[$aId])) {
            throw new \helicon\hcy\doctrine\Exception(
            \Yii::t('yii2-hcy-doctrine', 'Connection {id} is not configured', ['{id}' => $aId]));
        }

        $this->dbalConnectionObjects[$aId] = $this->newDbalConnectionObject($aId, $this->connections[$aId]);
        return $this->dbalConnectionObjects[$aId];
    }

    /**
     * Creates a new Entity manager instance
     * 
     * @param array $aConfig
     * @param string $aIdHint 
     */
    protected function newOrmEntityManager(array $aConfig = [], $aIdHint = '?')
    {
        $c = array_merge(array(
            'connection' => $this->defaultConnection,
            'ormConfiguration' => $this->defaultOrmConfiguration
                ), $aConfig);

        $ormConfigurationObject = $this->getOrmConfigurationObject($c['ormConfiguration']);
        $dbalConnectionObject = $this->getDbalConnectionObject($c['connection']);

        return \Doctrine\ORM\EntityManager::create(
                        $dbalConnectionObject, $ormConfigurationObject);
    }

    /**
     * Returns the assigned entity manager
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm($aEmId = null)
    {
        // Determine the default Em

        ($aEmId) || $aEmId = $this->defaultEntityManager;

        // Check if the entity manager has been initialized and return if possible

        if (isset($this->ormEntityManagers[$aEmId])) {
            return $this->ormEntityManagers[$aEmId]; // found ==> RETURN
        }

        //
        // Create the entity manager
        //
    
        $this->ormEntityManagers[$aEmId] = $this->newOrmEntityManager(
                isset($this->entityManagers[$aEmId]) ?
                        $this->entityManagers[$aEmId] :
                        array(
                    'connection' => $this->defaultConnection,
                    'ormConfiguration' => $this->defaultOrmConfiguration
                        ), $aEmId);
        //
        // Return the entity manager instance
        //
    
        return $this->ormEntityManagers[$aEmId];
    }

    /**
     * Returns the key of the current entity manager
     * @return string
     */
    public function getDefaultEntityManager()
    {
        return $this->defaultEntityManager;
    }

    /**
     * Sets the key of the default entity manager
     * @param string $defaultEntityManager Key of the default entity manager (Default: 'default);
     * @return type
     */
    public function setDefaultEntityManager($defaultEntityManager = 'default')
    {
        $this->defaultEntityManager = $defaultEntityManager;
    }

}
