<?php

namespace app\commands;

use app\helpers\ErrorHelper;
use app\helpers\XmlFileHelper;
use Exception;
use finfo;
use helpers\CommandHelper;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\BaseConsole;

class FeedController extends Controller
{
    const ALREADY_EXIST_WARNING_MESSAGE = "Warning: No new data inserted. Data already exists.";
    const SUCCESS_MESSAGE = "Table and data imported successfully.";
    /**
     * @throws Exception
     * @var string $filePath
     * @return CommandHelper
     */
    public function actionData(string $filePath = ''): CommandHelper
    {

        try {
            if (empty($filePath)) {
                $this->stderr("Error: File path is required.\n", BaseConsole::FG_RED);
                return new CommandHelper(ExitCode::CONFIG, "Error: File path is required.");
            }

            // Determine the file type
            $fileType = $this->detectFileType($filePath);

            switch ($fileType) {
                case 'xml':
                    return $this->handleXmlFile($filePath);
                case 'csv':
                    return $this->handleCsvFile($filePath);
                default:
                    $this->stderr("Error: Unsupported file type.\n", BaseConsole::FG_RED);
                    return new CommandHelper(ExitCode::CONFIG, "Error: Unsupported file type.");
            }
        } catch (\Exception $e) {
            ErrorHelper::handleError($e);
            return new CommandHelper(ExitCode::UNSPECIFIED_ERROR, $e->getMessage());
        }
    }

    /**
     * Check if the file has an XML MIME type.
     *
     * @param string $filePath
     * @return bool
     */
    private function isXmlFile(string $filePath): bool
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($filePath);
        return $mimeType === 'text/xml' || $mimeType === 'application/xml';
    }



    /**
     * Detect the file type based on the file extension.
     *
     * @param string $filePath
     * @return string|null
     */
    private function detectFileType(string $filePath): ?string
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'xml':
                return 'xml';
            case 'csv':
                return 'csv';
            default:
                return null;
        }
    }


    /**
     * Handle XML file.
     *
     * @param string $filePath
     * @return CommandHelper
     * @throws Exception
     */
    private function handleXmlFile(string $filePath): CommandHelper
    {
        $xmlData = XmlFileHelper::loadXmlData($filePath);
        $tableName = XmlFileHelper::getTableNameFromXml($xmlData);
        if (!XmlFileHelper::tableExists($tableName)) {
            XmlFileHelper::createTableFromXml($xmlData, $tableName);
        } else {
            XmlFileHelper::updateTableColumns($xmlData, $tableName);
        }
        $insertedRows = XmlFileHelper::pushDataToDatabase($xmlData, $tableName);
        if ($insertedRows > 0) {
            $this->stdout(self::SUCCESS_MESSAGE . "\n", BaseConsole::FG_GREEN);
            return new CommandHelper(ExitCode::OK, self::SUCCESS_MESSAGE);
        } else {
            $this->stdout(self::ALREADY_EXIST_WARNING_MESSAGE . "\n", BaseConsole::FG_YELLOW);
            return new CommandHelper(ExitCode::OK, self::ALREADY_EXIST_WARNING_MESSAGE);
        }
    }

    /**
     * Handle CSV file.
     *
     * @param string $filePath
     * @return CommandHelper
     * @throws Exception
     */
    private function handleCsvFile(string $filePath): CommandHelper
    {
        // Implement CSV file handling logic here
        // You can use the CsvFileHelper or create a new helper for CSV processing

        // Example:
        // $csvData = CsvFileHelper::loadCsvData($filePath);
        // $tableName = CsvFileHelper::getTableNameFromCsv($csvData);
        // ... (similar handling logic as XML)

        $this->stderr("Error: CSV file handling not implemented yet.\n", BaseConsole::FG_RED);
        return new CommandHelper(ExitCode::CONFIG, "Error: CSV file handling not implemented yet.");
    }
}