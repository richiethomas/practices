<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class WBHObjectTest extends TestCase
{

    public function testSetError() : void
    {
      $wbho = new WBHObject();
      $wbho->setError("Oops");
      $this->assertEquals($wbho->error, "Oops");
    }

    public function testSetMessage() : void
    {
      $wbho = new WBHObject();
      $wbho->setMessage("Hello World");
      $this->assertEquals($wbho->message, "Hello World");
    }

    public function testSetIntoFields() : void
    {
      $wbho = new WBHObject();
      $fields = [
        'id' => '5',
        'foo' => 'bar'
      ];
      $wbho->set_into_fields($fields);
      $this->assertSame($wbho->fields['id'], 5);
      $this->assertEquals($wbho->fields['foo'], 'bar');
    }

    public function testReplaceFields() : void
    {
      $wbho = new WBHObject();
      $fields = [
        'id' => '5',
        'foo' => 'bar'
      ];

      $new_fields = [
        'id' => '6',
        'bazz' => 'buzz'
      ];
      $wbho->set_into_fields($fields);
      $wbho->replace_fields($new_fields);
      $this->assertSame($wbho->fields['id'], 6);
      $this->assertEquals($wbho->fields['bazz'], 'buzz');
      $this->assertFalse(array_key_exists('foo', $wbho->fields));
    }

    public function testSetMysqlDatetimeField() : void
    {
      $wbho = new WBHObject();
      $wbho->set_mysql_datetime_field('created_at', "2023-01-01");

      $this->assertEquals($wbho->fields['created_at'], '2023-01-01 00:00:00');
    }

    public function testSetMysqlDateField() : void
    {
      $wbho = new WBHObject();
      $wbho->set_mysql_date_field('created_at', "2023-01-01");

      $this->assertEquals($wbho->fields['created_at'], '2023-01-01');
    }

    // public function testDbFunctionsWhereLoaded() : void
    // {}

}
