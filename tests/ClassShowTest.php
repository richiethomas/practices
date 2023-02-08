<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ClassShowTest extends TestCase
{
    public function testDbFunctionsWhereLoaded() : void
    {
        $this->assertTrue(function_exists('DB\get_connection'), 'DB functions did not loaded properly.');
        $this->assertTrue(function_exists('DB\pdo_query'), 'DB functions did not loaded properly.');
        $this->assertTrue(function_exists('DB\interpolateQuery'), 'DB functions did not loaded properly.');

    }

    public function testDBbConnects() : void
    {
        $db = DB\get_connection();
        $this->assertTrue(!is_null($db), 'DB is not connecting.');
    }

    public function testHasExpectedProperties(): void
    {
        $classShow = new ClassShow();
        $this->assertInstanceOf('ClassShow', $classShow);
        $this->assertEquals($classShow->tablename, 'shows');
    }

}
