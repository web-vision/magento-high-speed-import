<?php

/**
 * web-vision GmbH
 *
 * NOTICE OF LICENSE
 *
 * <!--LICENSETEXT-->
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.web-vision.de for more information.
 *
 * @category    WebVision
 * @package     Fci
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Fci_Model
{
    /**
     * @var Fci_Helper_Config
     */
    protected $_config;

    /**
     * @var string
     */
    protected $_profile;

    /**
     * @var \Cemes\Pdo\Mysql
     */
    protected $_mysql;

    /**
     * @var array
     */
    public static $csvFields;

    /**
     * @var Fci_Objects_Cache
     */
    protected $_cache;

    /**
     * @var
     */
    protected $_csvFile;

    /**
     * @var string
     */
    protected $_productIdentifier;

    /**
     * @var int[]
     */
    protected $_processedIds = [];

    /**
     * Initializes the logger and registers it in the registry.
     */
    public function initLogger()
    {
        if (Cemes::isCli()) {
            $logger = new Fci_Log_CliLogger();
        } else {
            $logger = new Fci_Log_HtmlLogger();
        }

        Cemes_Registry::register('logger', $logger);
    }

    /**
     * Initializes the timers for insert and update and registers them in the registry.
     */
    public function initTimers()
    {
        Cemes_Registry::register('insert_timer', new Cemes_Helper_Timer());
        Cemes_Registry::register('update_timer', new Cemes_Helper_Timer());
    }

    /**
     * Loads the fci config and the magento mysql config.
     *
     * @param string $configFile
     */
    public function loadConfig($configFile)
    {
        if (!file_exists(ROOT_BASEDIR . '/bin/' . $configFile)) {
            Cemes_Registry::get('logger')->critical('Config file {file} not present.', ['file' => $configFile]);
        }
        $this->_profile = $configFile;
        $this->_config = Fci_Helper_Config::getInstance();
        $this->_config->load($configFile);
    }

    /**
     * Initializes all scripts for their event.
     */
    public function initEvents()
    {
        $eventHandler = Cemes_EventHandler::getInstance();

        foreach ($this->_config->getScripts() as $script) {
            try {
                $eventHandler->registerForEvent($script->getEventName(), [$script, 'execute']);
            } catch (Exception $e) {
                Cemes_Registry::get('logger')->error($e->getMessage());
            }
        }
    }

    /**
     * Checks if .lock file exists. If not check if the csv file exists.
     */
    public function checkCsvFile()
    {
        $this->_executeEvent('before_csv_check');

        $isLocked = glob(dirname(ROOT_BASEDIR . $this->_config->getFilePath()) . CDS . '*.lock');
        if (is_array($isLocked) && count($isLocked) !== 0) {
            Cemes_Registry::get('logger')->critical('lock Datei existiert. Abbruch.');
        }

        $files = glob(ROOT_BASEDIR . $this->_config->getFilePath(), GLOB_BRACE);
        if ($files === false || (is_array($files) && empty($files))) {
            $context = ['path' => $this->_config->getFilePath()];
            Cemes_Registry::get('logger')->critical(
                'CSV Datei nicht gefunden. Pfad "{path}" könnte falsch sein.',
                $context
            );
        }

        $this->_csvFile = array_pop($files);

        $this->_executeEvent('after_csv_check');
    }

    /**
     * Connects to the database.
     */
    public function connectToDatabase()
    {
        $conf = $this->_config->get('mysql');

        try {
            $this->_mysql = new \Cemes\Pdo\Mysql();
            $this->_mysql->connect($conf);
            Cemes_Registry::register('db', $this->_mysql);
        } catch (Exception $e) {
            Cemes_Registry::get('logger')->critical('Verbindung zur Datenbank konnte nicht aufgebaut werden.');
        }

        $this->_executeEvent('after_database_initialization');
    }

    /**
     * Initialise the cache.
     */
    public function loadCache()
    {
        $this->_cache = Fci_Objects_Cache::getInstance($this->_config->getReloadCache());

        $this->_executeEvent('after_cache_initialization');
    }

    public function checkIdentifier()
    {
        $this->_productIdentifier = $this->_config->getProductIdentifier();
        if ($this->_config->getMode() === 'products' && $this->_productIdentifier !== 'sku') {
            $attr = $this->_cache->getProductAttribute($this->_productIdentifier);
            $context = ['productIdentifier' => $this->_productIdentifier];
            if ($attr->isEmpty()) {
                Cemes_Registry::get('logger')->critical(
                    '{productIdentifier} is not a product attribute and can not be used as a product identifier.',
                    $context
                );
            }
            if ($attr->getBackendType() === 'static') {
                Cemes_Registry::get('logger')->critical(
                    '{productIdentifier} is a static attribute and can not be used as a product identifier.',
                    $context
                );
            }
        }
    }

    /**
     * Reads the csv file.
     */
    public function getDataFromFile()
    {
        if (array_key_exists('startAt', $_GET) && $_GET['startAt'] !== '') {
            $startAt = (int)$_GET['startAt'];
        } else {
            $startAt = -1;
        }

        $fileInfo = pathinfo($this->_csvFile);
        $csvReader = new Fci_Helper_Io_CsvReader();
        $csvReader->setPath($fileInfo['dirname']);
        $csvReader->setFile($fileInfo['basename']);
        $csvReader->setDelimiter($this->_config->getFileDelimiter());
        $csvReader->setEnclosure($this->_config->getFileEnclosure());
        // set to no headline as we process the headline manually for mapping
        $csvReader->setHasHeadline(false);

        try {
            $csvReader->init();
        } catch (Exception $e) {
            Cemes_Registry::get('logger')->critical('CSV Datei ist nicht lesbar.');
        }

        $context = ['file' => basename($this->_csvFile, '.lock')];
        Cemes_Registry::get('logger')->success('Auslesen der Datei {file} begonnen.', $context);
        $entities = [];
        /** @var array $rowArr */
        while (($rowArr = $csvReader->getLine()) !== false) {
            // fist line contains field names
            if ($csvReader->getLinesRead() === 1) {
                foreach ($rowArr as $col) {
                    static::$csvFields[] = $col;
                }
                Fci_Helper_MandatoryCsv::getInstance()->writeFieldNames(static::$csvFields);
                if ($mappings = $this->_config->getMappings()) {
                    foreach (static::$csvFields as $id => $fileColumn) {
                        $normalizedColumnName = str_replace(' ', '_', $fileColumn);
                        if (array_key_exists($normalizedColumnName, $mappings)) {
                            static::$csvFields[$id] = $mappings[$normalizedColumnName];
                        }
                    }
                }
                $this->_checkRequiredCsvFields();
                if ($this->_config->getImportType() === 'none') {
                    return;
                }
                continue;
            }

            if ($startAt === -1 || $csvReader->getLinesRead() - 1 >= $startAt) {
                // trim values and replace field id with field name
                $entity = [];
                foreach ($rowArr as $colNr => $colValue) {
                    $entity[static::$csvFields[$colNr]] = $colValue;
                }
                if ($this->_hasRequiredFields($entity)) {
                    $entities[] = $entity;
                }

                if (($csvReader->getLinesRead() - 1) % $this->_config->getLinesToCache() === 0) {
                    $this->_upOrIn($entities);
                }
            }
        }// end: while
        $this->_upOrIn($entities);
    }

    /**
     * Checks if a product should be updated or inserted.
     *
     * @param $entities array
     */
    private function _upOrIn(&$entities)
    {
        // search if product is already inserted
        foreach ($entities as $entity) {
            new Fci_Objects_PreProcessor($entity, $this->_profile);
            $entity_id = $this->_getEntityId($entity);
            $entity = Fci_Helper_Factory_EntityFactory::createEntity($entity, $entity_id);
            new Fci_Objects_PostProcessor($entity, $this->_profile);
            Fci_Helper_Factory_MagentoFactory::getMagento()->postProcessEntity($entity);

            try {
                $this->_mysql->beginTransaction();
                switch ($this->_config->getImportType()) {
                    case 'both':
                        $entity->save();
                        break;
                    case 'insert':
                        if ($entity->isNew()) {
                            $entity->save();
                        }
                        break;
                    case 'update':
                        if (!$entity->isNew()) {
                            $entity->save();
                        }
                        break;
                }
                $this->_mysql->commit();
            } catch (Exception $e) {
                Cemes_Registry::get('logger')->error($e->getMessage());
                $this->_mysql->rollBack();
            }

            if ($this->_config->getDeleteProductsNotInCsv()) {
                $this->_processedIds[] = $entity->getId();
            }

            ob_flush();
            flush();
        }
        set_time_limit(0);
        // clear cached products
        $entities = [];
    }

    /**
     * Do things which should be done before update or insert is done.
     */
    public function before()
    {
        date_default_timezone_set('Europe/Berlin');
        $this->_executeEvent('before_csv_processing');
        $this->_lockCsv();
        set_time_limit(0);
        ob_start();
        ob_flush();
        flush();
        if ($this->_config->getDisableProducts()) {
            $this->_disableProducts();
        }

        if ($this->_config->getDeleteAllSpecialPrices()) {
            $this->_deleteAllSpecialPrices();
        }
    }

    /**
     * Do things which should be done after update or insert is done.
     */
    public function after()
    {
        if ($this->_config->getDeleteDisabledProducts()) {
            $this->_deleteDisabledProducts();
        }

        if ($this->_config->getDeleteProductsNotInCsv() && count($this->_processedIds)) {
            $this->_deleteProductsNotInCsv();
        }

        // rewrite cache if it has been modified
        if ($this->_cache->hasChanges()) {
            $this->_cache->save();
        }

        $this->_executeEvent('after_csv_processing');

        if (Fci_Model_Resource_AbstractResource::getProcessedEntities()) {
            // set index to require_reindex
            $magento = Fci_Helper_Factory_MagentoFactory::getMagento();
            $this->_mysql->update($magento->getTable('index'))
                ->set('status', $magento->getIndexState())
                ->execute();
        }

        $this->_archiveCsv();

        $insertTime = Cemes_Registry::get('insert_timer')->getElapsed();
        $updateTime = Cemes_Registry::get('update_timer')->getElapsed();
        $context = [
            'processed'   => Fci_Model_Resource_AbstractResource::getProcessedEntities(),
            'inserted'    => Fci_Model_Resource_AbstractResource::getInsertedEntities(),
            'updated'     => Fci_Model_Resource_AbstractResource::getUpdatedEntities(),
            'total_time'  => Cemes_StdLib::humanReadableTime($insertTime + $updateTime),
            'insert_time' => Cemes_StdLib::humanReadableTime($insertTime),
            'update_time' => Cemes_StdLib::humanReadableTime($updateTime),
        ];
        $message = 'Alle Produkte wurden erfolgreich importiert.' . PHP_EOL . PHP_EOL;
        $message .= 'Es wurden {processed} verarbeitet.' . PHP_EOL;
        $message .= 'Davon wurden:' . PHP_EOL;
        $message .= '{inserted} importiert' . PHP_EOL;
        $message .= '{updated} geupdated' . PHP_EOL . PHP_EOL;
        $message .= 'Importdauer: {total_time}' . PHP_EOL;
        $message .= 'Davon wurde gebraucht für:' . PHP_EOL;
        $message .= 'Import: {insert_time}' . PHP_EOL;
        $message .= 'Update: {update_time}';
        Cemes_Registry::get('logger')->success($message, $context);

        ob_end_flush();
        flush();
    }

    protected function _executeEvent($eventName)
    {
        try {
            Cemes_EventHandler::getInstance()->dispatchEvent($eventName);
        } catch (Exception $e) {
            Cemes_Registry::get('logger')->error($e->getMessage());
            $originalException = $e->getPrevious();
            if ($originalException instanceof Fci_Exceptions_CriticalScriptException) {
                Cemes_Registry::get('logger')->critical($originalException->getMessage());
            }
        }
    }

    /**
     * Disables all products in database.
     */
    protected function _disableProducts()
    {
        $update = $this->_mysql->update('catalog_product_entity_int')
            ->set('catalog_product_entity_int.value', 2)
            ->where(
                'catalog_product_entity_int.attribute_id',
                $this->_cache->getData('productAttributes/status/attribute_id')
            );

        $conditions = $this->_config->getDisableConditions();

        $tables = ['catalog_product_entity_int'];

        /** @var array $data */
        foreach ($conditions as $field => $data) {
            if (is_array($data) && !array_key_exists('@attributes', $data)) {
                foreach ($data as $subData) {
                    $this->_addSqlCondition($field, $subData, $tables, $update);
                }
            } else {
                $this->_addSqlCondition($field, $data, $tables, $update);
            }
        }

        $update->execute();

        Cemes_Registry::get('logger')->notice('Produkte wurden deaktiviert');
    }

    /**
     * Clear all special prices in the database.
     */
    protected function _deleteAllSpecialPrices()
    {
        $this->_mysql->delete('catalog_product_entity_decimal')
            ->where('attribute_id', $this->_cache->getData('productAttributes/special_price/attribute_id'))
            ->execute();

        $this->_mysql->delete('catalog_product_entity_datetime')
            ->where('attribute_id', array(
                $this->_cache->getData('productAttributes/special_from_date/attribute_id'),
                $this->_cache->getData('productAttributes/special_to_date/attribute_id')), 'in')
            ->execute();

        Cemes_Registry::get('logger')->notice('All special prices are deleted.');
    }

    /**
     * Adds a condition from a config array to the current sql query.
     *
     * @param string $field
     * @param array  $data
     * @param array  $tables
     * @param \Cemes\Pdo\Statement
     */
    protected function _addSqlCondition($field, $data, &$tables, \Cemes\Pdo\Statement $update)
    {
        if (!array_key_exists('@attributes', $data) || !array_key_exists('table', $data['@attributes'])) {
            return;
        }

        $table = 'catalog_product_' . $data['@attributes']['table'];
        $value = $data['@value'];
        $operator = 'eq';
        $next = null;
        $group = 0;
        if (array_key_exists('operator', $data['@attributes'])) {
            $operator = $data['@attributes']['operator'];
        }
        if (array_key_exists('next', $data['@attributes'])) {
            $next = $data['@attributes']['next'];
        }
        if (array_key_exists('group', $data['@attributes'])) {
            $group = $data['@attributes']['group'];
        }

        if (!in_array($table, $tables, true)) {
            $tables[] = $table;
            $update->leftJoin($table, 'catalog_product_entity_int.entity_id = ' . $table . '.entity_id');
        }

        if (null === $next || strtoupper($next) === 'AND') {
            $update->where($table . '.' . $field, $value, $operator, $group);
        } else {
            $update->orWhere($table . '.' . $field, $value, $operator, $group);
        }
    }

    /**
     * Deletes all products that are deactivated.
     */
    protected function _deleteDisabledProducts()
    {
        $this->_mysql->delete('catalog_product_entity')
            ->leftJoin(
                'catalog_product_entity_int',
                'catalog_product_entity.entity_id = catalog_product_entity_int.entity_id'
            )
            ->where('attribute_id', $this->_cache->getData('productAttributes/status/attribute_id'))
            ->where('value', 2)
            ->execute();
        Cemes_Registry::get('logger')->notice('Alle deaktivierten Produkte wurden gelöscht.');
    }

    /**
     * Deletes all products that where not in the csv.
     */
    protected function _deleteProductsNotInCsv()
    {
        $this->_mysql->delete('catalog_product_entity')
            ->where('entity_id', $this->_processedIds, 'nin')
            ->execute();

        Cemes_Registry::get('logger')->notice('Alle Produkte die nicht in der CSV waren wurden gelöscht.');
    }

    /**
     * Checks if all required fields are present in the CSV.
     */
    protected function _checkRequiredCsvFields()
    {
        if ($this->_config->getImportType() !== 'update') {
            // check if all required fields for import are in the csv
            $missing = $this->_checkRequiredFields('insert');
            if ($missing) {
                $this->_handleMissingFields(
                    'Einfügen von neuen {entity} abgebrochen. Folgende Felder fehlen: {missing}.',
                    $missing
                );
                $this->_config->setImportType($this->_config->getImportType() === 'both' ? 'update' : 'none');
            }
        }
        if ($this->_config->getImportType() !== 'insert'
            && $this->_config->getImportType() !== 'none') {
            // check if all required fields for update are in the csv
            $missing = $this->_checkRequiredFields('update');
            if ($missing) {
                $this->_handleMissingFields(
                    'Update von alten {entity} abgebrochen. Folgende Felder fehlen: {missing}.',
                    $missing
                );
                $this->_config->setImportType($this->_config->getImportType() === 'both' ? 'insert' : 'none');
            }
        }

        /** @noinspection SuspiciousAssignmentsInspection */
        $missing = $this->_checkRequiredFields('mandatory');
        if ($missing) {
            $this->_handleMissingFields('Import abgebrochen. Folgende Pflicht-Felder fehlen: {missing}.', $missing);
            $this->_config->setImportType('none');
        }

        if ($this->_config->getMode() === 'products'
            && array_key_exists($this->_productIdentifier, array_flip(static::$csvFields)) !== true) {
            $this->_handleMissingFields(
                'Import abgebrochen. Produkt Identifier fehlt: {missing}.',
                [$this->_productIdentifier]
            );
            $this->_config->setImportType('none');
        }
    }

    /**
     * Checks the required fields and returns the fields that are missing as a comma separated string.
     *
     * @param string $importType
     *
     * @return array
     */
    protected function _checkRequiredFields($importType)
    {
        $requiredFields = $this->_getRequiredFieldsToCheck($importType);

        if (!$requiredFields) {
            return null;
        }

        $missing = Cemes_StdLib::array_keys_exists($requiredFields, array_flip(static::$csvFields));

        return is_array($missing) ? $missing : null;
    }

    /**
     * Returns the required fields based on the import type and the import mode.
     *
     * @param string $importType
     *
     * @return array
     */
    protected function _getRequiredFieldsToCheck($importType)
    {
        switch ($importType) {
            case 'insert':
                if ($this->_config->getMode() === 'products') {
                    return ['sku', 'name', 'price'];
                }

                if ($this->_config->getMode() === 'categories') {
                    return ['path', 'name'];
                }
                break;
            case 'update':
                if ($this->_config->getMode() === 'products') {
                    return ['sku'];
                }

                if ($this->_config->getMode() === 'categories') {
                    return ['path'];
                }
                break;
            case 'mandatory':
                return $this->_config->getMandatoryFields();
        }

        return null;
    }

    /**
     * Maps the missing fields if needed and writes the given error message with the current logger.
     *
     * @param string $message
     * @param array  $missing
     */
    protected function _handleMissingFields($message, $missing)
    {
        $mappings = $this->_config->getMappings();
        if ($mappings) {
            foreach ($missing as $id => $field) {
                if (in_array($field, $mappings, true)) {
                    $missing[$id] = str_replace('_', ' ', array_search($field, $mappings, true));
                }
            }
        }
        $context = [
            'entity'  => $this->_config->getMode() === 'categories' ? 'Kategorien' : 'Produkten',
            'missing' => implode(',', $missing),
        ];
        Cemes_Registry::get('logger')->error($message, $context);
    }

    /**
     * Checks if all mandatory fields are filled except for entities that are defined as a mandatory exception.
     *
     * @param array $entity
     *
     * @return bool
     */
    protected function _hasRequiredFields($entity)
    {
        $mandatoryFields = $this->_config->getMandatoryFields();
        if ($mandatoryFields && !$this->_isMandatoryException($entity)) {
            $missing = [];
            $mappings = $this->_config->getMappings();
            foreach ($mandatoryFields as $mandatory) {
                if (trim($entity[$mandatory]) === '') {
                    if (in_array($mandatory, $mappings, true)) {
                        $key = array_search($mandatory, $mappings, true);
                        $missing[] = str_replace('_', ' ', $key);
                    } else {
                        $missing[] = $mandatory;
                    }
                }
            }

            if (!empty($missing)) {
                Fci_Helper_MandatoryCsv::getInstance()->writeLine($entity, $missing);

                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the given entity is a mandatory exception.
     *
     * @param array $entity
     *
     * @return bool
     */
    protected function _isMandatoryException($entity)
    {
        $isException = false;
        foreach ($this->_config->getMandatoryExceptions() as $exception) {
            $isException |= $exception->parse($entity);
        }

        return $isException;
    }

    /**
     * Moves the csv file to *.lock.
     */
    private function _lockCsv()
    {
        rename($this->_csvFile, $this->_csvFile . '.lock');
        $this->_csvFile .= '.lock';
    }

    /**
     * Moves the lock file into the archive folder.
     */
    private function _archiveCsv()
    {
        if ($this->_config->getArchiveWithDateTime()) {
            $fileName = pathinfo(basename($this->_csvFile, '.lock'));
            $dateTime = new DateTime();
            $fileName = $fileName['filename'] . $dateTime->format('-Y-m-d_H-i.') . $fileName['extension'];
        } else {
            $fileName = basename($this->_csvFile, '.lock');
        }
        $newFile = dirname($this->_csvFile) . CDS . 'archive' . CDS . $fileName;
        if (!@mkdir(dirname($newFile), 0755, true) && !is_dir(dirname($newFile))) {
            Cemes_Registry::get('logger')->error('Archive Ordner konnte nicht erstellt werden.');
        }
        if (!rename($this->_csvFile, $newFile)) {
            Cemes_Registry::get('logger')->error('CSV konnte nicht ins Archive verschoben werden.');
        }
    }

    /**
     * Returns the id of the given entity.
     *
     * @param array $entity
     *
     * @return int
     */
    protected function _getEntityId($entity)
    {
        switch (Fci_Helper_Config::getInstance()->getMode()) {
            case 'categories':
                return false;
            case 'products':
            default:
                return $this->_getProductEntityId($entity);
        }
    }

    /**
     * Returns the entity id of a product.
     *
     * @param array $product
     *
     * @return int
     */
    protected function _getProductEntityId($product)
    {
        if ($this->_productIdentifier === 'sku') {
            return (int)$this->_mysql
                ->select('catalog_product_entity', ['entity_id'])
                ->where($this->_productIdentifier, $product[$this->_productIdentifier])
                ->fetchEntry('entity_id');
        }

        $attr = $this->_cache->getProductAttribute($this->_productIdentifier);
        if (array_key_exists($this->_productIdentifier, $product) && $product[$this->_productIdentifier]) {
            return (int)$this->_mysql->select('catalog_product_entity_' . $attr->getBackendType(), ['entity_id'])
                ->where('value', $product[$this->_productIdentifier])
                ->fetchEntry('entity_id');
        }

        Cemes_Registry::get('logger')->error('The product doesn\'t has a value for the specified identifier.');

        return 0;
    }
}
