<?php
namespace functional;
use app\commands\FeedController;
use Exception;
use FunctionalTester;
use Yii;
use yii\console\ExitCode;

class ProcessDataCommandCest
{
    /**
     * this class is to test the file type xml
     */
    private string $xmlPath;

    public function _before(FunctionalTester $I): void
    {
        $this->xmlPath = codecept_data_dir('test_feed.xml');
        if (!file_exists($this->xmlPath)) {
            throw new \RuntimeException("Sample XML file not found: $this->xmlPath");
        }

    }

    public function _after(FunctionalTester $I): void
    {
        $this->rollbackDatabase($I);
    }

    private function rollbackDatabase(FunctionalTester $I): void
    {
        $xmlData = simplexml_load_file($this->xmlPath);
        $tableName = $this->getTableNameFromXml($xmlData);

        try {
            $schema = Yii::$app->db->schema;
            if ($schema->getTableSchema($tableName) !== null) {
                Yii::$app->db->createCommand()->dropTable($tableName)->execute();
                $I->comment("Table '$tableName' has been deleted.");
            }
        } catch (Exception $e) {
            $I->comment("Error deleting table '$tableName': " . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function testActionDataCreatesTable(FunctionalTester $I): void
    {
        // Run the actionData function
        $feedController = new FeedController('feed', Yii::$app);
        $result = $feedController->actionData($this->xmlPath);
        $I->assertEquals(ExitCode::OK, $result->exitCode);
        $I->assertStringContainsString(FeedController::SUCCESS_MESSAGE, $result->message);

        $xmlData = simplexml_load_file($this->xmlPath);
        $tableName = $this->getTableNameFromXml($xmlData);

        $tableExists = $this->tableExists($tableName);

        $I->assertTrue($tableExists, "Table $tableName should exist.");
        $this->rollbackDatabase($I);
    }

    /**
     * @throws Exception
     */
    public function testCheckWarningMessage(FunctionalTester $I): void
    {
        $expectedMessage = FeedController::ALREADY_EXIST_WARNING_MESSAGE;

        $feedController = new FeedController('feed', Yii::$app);
        $result = $feedController->actionData($this->xmlPath);
        $result = $feedController->actionData($this->xmlPath);
        $I->assertEquals(ExitCode::OK, $result->exitCode);
        $I->assertStringContainsString($expectedMessage, $result->message);
    }

    /**
     * @throws Exception
     */
    public function testMissingColumnInDatabase(FunctionalTester $I): void
    {
        $missedColumn = strtolower('description');
        $missedColumnXmlPath = $this->missingColumnXmlContent();
        $this->runFeedControllerWithMissedColumnXmlFile($I, $missedColumnXmlPath);

        $xmlDataWithOutMissingColumn = simplexml_load_file($this->xmlPath);
        $tableNameFromXmlDataWithoutMissingColumn = $this->getTableNameFromXml($xmlDataWithOutMissingColumn);

        $tableSchema = Yii::$app->db->schema->getTableSchema($tableNameFromXmlDataWithoutMissingColumn);

        $I->assertFalse(
            in_array(strtolower($missedColumn), array_map('strtolower', $tableSchema->columnNames)),
            "Column '$missedColumn' should not exist in the database."
        );
        $this->rollbackDatabase($I);
    }

    /**
     * @throws Exception
     */
    private function runFeedControllerWithMissedColumnXmlFile(FunctionalTester $I, $missedColumnXmlPath): void
    {
        $feedController = new FeedController('feed', Yii::$app);
        $result = $feedController->actionData($missedColumnXmlPath);
        $I->assertEquals(ExitCode::OK, $result->exitCode);
    }
    private function missingColumnXmlContent(): string
    {
        //intentionally missed description columns/tags in the xml file.
        $xmlContent = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<catalog>
    <item>
		<entity_id>340</entity_id>
		<CategoryName>
			<![CDATA[Green Mountain Ground Coffee]]>
		</CategoryName>
		<sku>20</sku>
		<name>
			<![CDATA[Green Mountain Coffee French Roast Ground Coffee 24 2.2oz Bag]]>
		</name>
		<shortdesc>
			<![CDATA[Green Mountain Coffee French Roast Ground Coffee 24 2.2oz Bag steeps cup after cup of smoky-sweet, complex dark roast coffee from Green Mountain Ground Coffee.]]>
		</shortdesc>
		<price>41.6000</price>
		<link>http://www.coffeeforless.com/green-mountain-coffee-french-roast-ground-coffee-24-2-2oz-bag.html</link>
		<image>http://mcdn.coffeeforless.com/media/catalog/product/images/uploads/intro/frac_box.jpg</image>
		<Brand>
			<![CDATA[Green Mountain Coffee]]>
		</Brand>
		<Rating>0</Rating>
		<CaffeineType>Caffeinated</CaffeineType>
		<Count>24</Count>
		<Flavored>No</Flavored>
		<Seasonal>No</Seasonal>
		<Instock>Yes</Instock>
		<Facebook>1</Facebook>
		<IsKCup>0</IsKCup>
	</item>
</catalog>
XML;
        $missedColumnXmlSample = codecept_data_dir('missed_column.xml');
        file_put_contents($missedColumnXmlSample, $xmlContent);
        return $missedColumnXmlSample;
    }


    protected function getTableNameFromXml($xmlData): string
    {
        $firstTag = $xmlData->getName();

        return preg_replace('/[^a-zA-Z0-9_]/', '_', $firstTag);
    }

    protected function tableExists($tableName): bool
    {
        $db = Yii::$app->db;
        $schema = $db->schema;
        return in_array($tableName, $schema->getTableNames());
    }
}
