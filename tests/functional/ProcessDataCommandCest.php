<?php
namespace functional;
use app\commands\FeedController;
use Codeception\Util\Stub;
use FunctionalTester;
use Yii;
use yii\console\ExitCode;
use yii\helpers\FileHelper;

class ProcessDataCommandCest
{


    private $xmlPath;

    public function _before(FunctionalTester $I)
    {
        // Copy the sample XML file to the tests directory
        $this->xmlPath = codecept_data_dir('test_feed.xml');

        // Check if the sample XML file exists before copying
        if (!file_exists($this->xmlPath)) {
            throw new \RuntimeException("Sample XML file not found: $this->xmlPath");
        }

    }

    public function _after(FunctionalTester $I)
    {
        $xmlData = simplexml_load_file($this->xmlPath);
        $tableName = $this->getTableNameFromXml($xmlData);

        try {
            $schema = Yii::$app->db->schema;
            // Check if the table exists before attempting to delete
            if ($schema->getTableSchema($tableName) !== null) {
                Yii::$app->db->createCommand()->dropTable($tableName)->execute();
                $I->comment("Table '$tableName' has been deleted.");
            }
        } catch (\Exception $e) {
            $I->comment("Error deleting table '$tableName': " . $e->getMessage());
        }
    }

    /**
     * @throws \Exception
     */
    public function testActionDataCreatesTable(FunctionalTester $I)
    {
        // Run the actionData function
        $feedController = new FeedController('feed', Yii::$app);
        $exitCode = $feedController->actionData($this->xmlPath);
        // Check if the actionData function executed successfully (ExitCode::OK)
        $I->assertEquals(ExitCode::OK, $exitCode, 'ActionData did not execute successfully');

        //$I->assertStringContainsString('Table and data imported successfully', $output);

        // Get the table name from the XML file
        $xmlData = simplexml_load_file($this->xmlPath);
        $tableName = $this->getTableNameFromXml($xmlData);

        // Check if the table exists in the database
        $tableExists = $this->tableExists($tableName);

        // Assert that the table exists
        $I->assertTrue($tableExists, "Table $tableName should exist.");
    }

    protected function getTableNameFromXml($xmlData): string
    {
        // Get the first tag name from the XML data
        $firstTag = $xmlData->getName();

        // Sanitize the tag name to make it a valid table name
        return preg_replace('/[^a-zA-Z0-9_]/', '_', $firstTag);
    }

    protected function tableExists($tableName): bool
    {
        // Check if the table exists in the database
        $db = Yii::$app->db;
        $schema = $db->schema;
        return in_array($tableName, $schema->getTableNames());
    }
}
