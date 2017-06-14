<?php

namespace Tests;

use Exception;
use smtech\CanvasPest\CanvasPest;
use smtech\CanvasPest\CanvasObject;
use smtech\CanvasPest\CanvasArray;
use smtech\CanvasPest\CanvasArray_Exception;
use Tests\Expectations\ExceptionExpectation;

class CanvasArrayTest extends ExceptionExpectation
{
    private static $items;
    private static $per_page;

    public static function setUpBeforeClass()
    {
        self::$items = getenv('CANVASPEST_ITEMS');
        self::$per_page = (integer) (self::$items / 3);
    }

    public function testInstantiation()
    {
        try {
            $pest = new CanvasPest(
                getenv('CANVASPEST_URL'),
                getenv('CANVASPEST_TOKEN')
            );
        } catch (Exception $e) {
            $this->assertNotException($e);
        }
        $this->assertInstanceOf(CanvasPest::class, $pest);

        try {
            $response = $pest->get(
                'users/' . getenv('CANVASPEST_USER_ID') . '/calendar_events',
                [
                    'start_date' => '2017-06-01',
                    'end_date' => '2017-07-01',
                    'per_page' => self::$per_page
                ]);
        } catch (Exception $e) {
            $this->assertNotException($e);
        }
        $this->assertInstanceOf(CanvasArray::class, $response);

        return $response;
    }

    public function testDynamicPagination()
    {
        $arr = $this->testInstantiation();
        $this->assertTrue(isset($arr[self::$per_page + 1]));

        return $arr;
    }

    public function testDynamicOffsetGet()
    {
        $arr = $this->testInstantiation();
        for ($i = 0; $i < self::$per_page + 1; $i++) {
            $this->assertInstanceOf(CanvasObject::class, $arr[$i]);
        }
    }

    public function testDynamicIteration()
    {
        $arr = $this->testInstantiation();
        $i = 0;
        foreach ($arr as $obj) {
            $i++;
        }
        $this->assertEquals(self::$items, $i);
    }

    /**
     * @depends testInstantiation
     */
    public function testCount(CanvasArray $arr)
    {
        $this->assertEquals(self::$items, $arr->count());
    }

    /**
     * @depends testInstantiation
     */
    public function testGetArrayCopy(CanvasArray $arr)
    {
        $a = $arr->getArrayCopy();
        $this->assertInternalType('array', $a);
        $this->assertNotEmpty($a);

        foreach ($arr as $key => $value) {
            $this->assertEquals($value, $a[$key]);
        }

        foreach ($a as $key => $value) {
            $this->assertTrue(isset($arr[$key]));
        }
    }

    /**
     * @depends testInstantiation
     */
    public function testArrayOffsetExists(CanvasArray $arr)
    {
        $this->assertTrue(isset($arr[self::$per_page]));
        $this->assertFalse(isset($arr[self::$items * 2]));
    }

    /**
     * @depends testInstantiation
     */
    public function testArrayOffsetGet(CanvasArray $arr)
    {
        $obj = $arr[self::$per_page];
        $this->assertInstanceOf(CanvasObject::class, $obj);
    }

    /**
     * @depends testInstantiation
     */
    public function testArrayOffsetSet(CanvasArray $arr)
    {
        try {
            $arr[self::$per_page] = 'foo bar';
            $this->assertException(CanvasArray_Exception::class);
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasArray_Exception::class, $e);
            $this->assertEquals(CanvasArray_Exception::IMMUTABLE, $e->getCode());
        }
    }

    /**
     * @depends testInstantiation
     */
    public function testArrayOffsetUnset(CanvasArray $arr)
    {
        try {
            unset($arr[self::$per_page]);
            $this->assertException(CanvasArray_Exception::class);
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasArray_Exception::class, $e);
            $this->assertEquals(CanvasArray_Exception::IMMUTABLE, $e->getCode());
        }
    }

    /**
     * @depends testInstantiation
     */
    public function testSerialize(CanvasArray $arr)
    {
        $serialized = serialize($arr);
        $this->assertInternalType('string', $serialized);
        $this->assertNotEmpty($serialized);

        return $serialized;
    }

    /**
     * @depends testSerialize
     */
    public function testUnserialize($serialized)
    {
        $arr = unserialize($serialized);
        $this->assertInstanceOf(CanvasArray::class, $arr);
    }
}
