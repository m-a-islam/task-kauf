<?php

namespace app\commands;

use helpers\CommandHelper;
use SimpleXMLElement;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Exception;
use yii\db\Migration;
use yii\db\Query;
use yii\helpers\BaseConsole;
use yii\log\Logger;

class FeedController extends Controller
{
    const ALREADY_EXIST_WARNING_MESSAGE = "Warning: No new data inserted. Data already exists.";
    const SUCCESS_MESSAGE = "Table and data imported successfully.";
    /**
     * @throws \Exception
     * @var string $xmlPath
     * @return CommandHelper
     */
    public function actionData(string $xmlPath = ''): CommandHelper
    {
        try {
            if (empty($xmlPath)) {
                $this->stderr("Error: XML path is required.\n", BaseConsole::FG_RED);
                return new CommandHelper(ExitCode::CONFIG, "Error: XML path is required.");
            }
            $xmlData = $this->loadXmlData($xmlPath);
            $tableName = $this->getTableNameFromXml($xmlData);
            if (!$this->tableExists($tableName)) {
                $this->createTableFromXml($xmlData, $tableName);
            } else {
                $this->updateTableColumns($xmlData, $tableName);
            }
            $insertedRows = $this->pushDataToDatabase($xmlData, $tableName);
            if ($insertedRows > 0) {
                $this->stdout(self::SUCCESS_MESSAGE . "\n", BaseConsole::FG_GREEN);
                return new CommandHelper(ExitCode::OK, self::SUCCESS_MESSAGE);
            } else {
                $this->stdout(self::ALREADY_EXIST_WARNING_MESSAGE . "\n", BaseConsole::FG_YELLOW);
                return new CommandHelper(ExitCode::OK, self::ALREADY_EXIST_WARNING_MESSAGE);
            }
        } catch (\Exception $e) {
            $this->handleError($e);
            return new CommandHelper(ExitCode::UNSPECIFIED_ERROR, $e->getMessage());
        }
    }
    protected function handleError(\Exception $e): void
    {
        $errorMessage = "Error: " . $e->getMessage() . "\n" . "File: " . $e->getFile() . "\n" . "Line: " . $e->getLine();

        $logger = Yii::getLogger();
        $logger->log($errorMessage, Logger::LEVEL_ERROR, 'import-errors');
    }

    /**
     * @throws \Exception
     */
    protected function getTableNameFromXml($xmlData): array|string|null
    {
        try{
        $firstTag = $xmlData->getName();

        $tableName = preg_replace('/[^a-zA-Z0-9_]/', '_', $firstTag);
        return $tableName;
        } catch (\Exception $e) {
            $this->handleError($e);
            throw $e; // Re-throw the exception for further handling
        }
    }

    /**
     * @throws \Exception
     */
    protected function loadXmlData($xmlPath): SimpleXMLElement
    {
        $xmlContent = @file_get_contents($xmlPath);

        if ($xmlContent === false) {
            $errorMessage = "Error: Failed to open XML file '{$xmlPath}'.";
            $this->stderr($errorMessage."\n", BaseConsole::FG_RED);
            throw new \Exception($errorMessage);
        }

        return new SimpleXMLElement($xmlContent);
    }


    /**
     * @throws \Exception
     */
    protected function createTableFromXml($xmlData, $tableName): void
    {
        try {
            $migration = new Migration();
            $tableOptions = null;

            $migration->createTable($tableName, [
                'id' => $migration->primaryKey(),
            ], $tableOptions);

            foreach ($xmlData->item[0] as $elementName => $elementValue) {
                $columnName = strtolower($elementName);

                // Define appropriate MySQL data types based on the XML data types
                if (is_int($elementValue)) {
                    $migration->addColumn($tableName, $columnName, $migration->integer());
                } elseif (is_float($elementValue)) {
                    $migration->addColumn($tableName, $columnName, $migration->decimal(10, 4));
                } else {
                    $migration->addColumn($tableName, $columnName, $migration->text());
                }
            }
        } catch (\Exception $e) {
            $this->handleError($e);
            throw $e; // Re-throw the exception for further handling
        }
    }

    /**
     * @throws \Exception
     */
    protected function updateTableColumns($xmlData, $tableName): void
    {
        try{
            $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);

            if ($tableSchema === null) {
                return;
            }

            $existingColumns = array_map('strtolower', $tableSchema->columnNames);
            $expectedColumns = array_keys((array)$xmlData->item[0]);

            $missingColumns = array_diff($expectedColumns, $existingColumns);

            if (!empty($missingColumns)) {
                $migration = new Migration();

                foreach ($missingColumns as $columnName) {

                    if (!in_array(strtolower($columnName), $existingColumns)) {
                        $elementValue = $xmlData->item[0]->$columnName;
                        if (is_int($elementValue)) {
                            $migration->addColumn($tableName, $columnName, $migration->integer());
                        } elseif (is_float($elementValue)) {
                            $migration->addColumn($tableName, $columnName, $migration->decimal(10, 4));
                        } else {
                            $migration->addColumn($tableName, $columnName, $migration->text());
                        }
                    }
                }

                $tableSchema = Yii::$app->db->schema->getTableSchema($tableName);
            }
        }catch (\Exception $e) {
            $this->handleError($e);
            throw $e;
        }

    }


    /**
     * @param $xmlData
     * @param $tableName
     * @return int
     * @throws Exception
     */
    protected function pushDataToDatabase($xmlData, $tableName): int
    {
        try {
            $tableColumns = array_keys(get_object_vars($xmlData->item[0]));
            $insertedRows = 0;
            foreach ($xmlData->item as $itemData) {
                $dataToInsert = array_intersect_key((array)$itemData, array_flip($tableColumns));
                if (!$this->dataExists($tableName, $dataToInsert)) {
                    Yii::$app->db->createCommand()->insert($tableName, $dataToInsert)->execute();
                    $insertedRows++;
                }
            }
            return $insertedRows;
        } catch (\Exception $e) {
            $this->handleError($e);
            throw $e;
        }
    }

    /**
     * Check if the data already exists in the table
     *
     * @param string $tableName
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    protected function dataExists(string $tableName, array $data): bool
    {
        try {
            $query = (new Query())->from($tableName);
            foreach ($data as $column => $value) {
                    $column = strtolower($column);
                    $query->andFilterWhere(['=', $column, $value]);
            }
            return $query->exists(Yii::$app->db);
        }catch (\Exception $e) {
            $this->handleError($e);
            throw $e;
        }

    }

    /**
     * @throws \Exception
     */
    protected function tableExists($tableName): bool
    {
        try {
            $db = Yii::$app->db;
            $schema = $db->schema;

            return in_array($tableName, $schema->getTableNames());
        }catch (\Exception $e) {
            $this->handleError($e);
            throw $e;
        }
    }
}