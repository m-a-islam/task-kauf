<?php

namespace app\helpers;

use Exception;
use SimpleXMLElement;
use Yii;
use yii\console\Controller;
use yii\db\Migration;
use yii\db\Query;

class XmlFileHelper extends Controller
{
    /**
     * @throws Exception
     */
    public static function loadXmlData($xmlPath): SimpleXMLElement
    {
        $xmlContent = @file_get_contents($xmlPath);

        if ($xmlContent === false) {
            $errorMessage = "Error: Failed to open XML file '{$xmlPath}'.";
            throw new Exception($errorMessage);
        }

        return new SimpleXMLElement($xmlContent);
    }

    /**
     * @throws Exception
     */
    public static function getTableNameFromXml($xmlData): array|string|null
    {
        try{
            $firstTag = $xmlData->getName();
            $tableName = preg_replace('/[^a-zA-Z0-9_]/', '_', $firstTag);
            return $tableName;
        } catch (\Exception $e) {
            ErrorHelper::handleError($e);
            throw $e;
        }
    }


    /**
     * @throws Exception
     */
    public static function tableExists($tableName): bool
    {
        try {
            $db = Yii::$app->db;
            $schema = $db->schema;
            return in_array($tableName, $schema->getTableNames());
        }catch (\Exception $e) {
            ErrorHelper::handleError($e);
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public static function createTableFromXml($xmlData, $tableName): void
    {
        try {
            $migration = new Migration();
            $tableOptions = null;

            $migration->createTable($tableName, [
                'id' => $migration->primaryKey(),
            ], $tableOptions);

            foreach ($xmlData->item[0] as $elementName => $elementValue) {
                $columnName = strtolower($elementName);

                if (is_int($elementValue)) {
                    $migration->addColumn($tableName, $columnName, $migration->integer());
                } elseif (is_float($elementValue)) {
                    $migration->addColumn($tableName, $columnName, $migration->decimal(10, 4));
                } else {
                    $migration->addColumn($tableName, $columnName, $migration->text());
                }
            }
        } catch (\Exception $e) {
            ErrorHelper::handleError($e);
            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    public static function updateTableColumns($xmlData, $tableName): void
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
            ErrorHelper::handleError($e);
            throw $e;
        }
    }
    /**
     * @param $xmlData
     * @param $tableName
     * @return int
     * @throws \yii\db\Exception
     */
    public static function pushDataToDatabase($xmlData, $tableName): int
    {
        try {
            $tableColumns = array_keys(get_object_vars($xmlData->item[0]));
            $insertedRows = 0;
            foreach ($xmlData->item as $itemData) {
                $dataToInsert = array_intersect_key((array)$itemData, array_flip($tableColumns));
                if (!self::dataExists($tableName, $dataToInsert)) {
                    Yii::$app->db->createCommand()->insert($tableName, $dataToInsert)->execute();
                    $insertedRows++;
                }
            }
            return $insertedRows;
        } catch (\Exception $e) {
            ErrorHelper::handleError($e);
            throw $e;
        }
    }

    /**
     * Check if the data already exists in the table
     *
     * @param string $tableName
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public static function dataExists(string $tableName, array $data): bool
    {
        try {
            $query = (new Query())->from($tableName);
            foreach ($data as $column => $value) {
                $column = strtolower($column);
                $query->andFilterWhere(['=', $column, $value]);
            }
            return $query->exists(Yii::$app->db);
        }catch (\Exception $e) {
            ErrorHelper::handleError($e);
            throw $e;
        }
    }
}