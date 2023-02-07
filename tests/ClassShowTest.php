<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

error_reporting(0);
error_reporting(E_ERROR);
require(__DIR__ . '/../libs/wbh_webkit.php');
// require(__DIR__ . '/../libs/db_pdo.php');
// require(__DIR__ . '/../lib-master.php');

spl_autoload_register(function ($className) {
  $className = str_replace('\\', DIRECTORY_SEPARATOR, $className); // for subdirectories in 'oclasses'
  $file = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . "oclasses" . DIRECTORY_SEPARATOR . "{$className}.class.php";
  if (is_readable($file)) require_once $file;
});

define('DEFAULT_TIME_ZONE', 'America/Los_Angeles');

final class ClassShowTest extends TestCase
{
  public function testHasExpectedProperties(): void
  {
    $classShow = new ClassShow();
    $this->assertInstanceOf('ClassShow', $classShow);
    $this->assertEquals($classShow->tablename, 'shows');
  }

  public function testSetWorkshops(): void
  {
    $classShow = new ClassShow();
    $fields = array(
      'id' => '1'
    );
    $classShow->fields = $fields;
    // $this->assertInstanceOf('ClassShow', $classShow);
    // $this->assertEquals($classShow->tablename, 'shows');
    $this->assertTrue($classShow->set_workshops());
  }
}
