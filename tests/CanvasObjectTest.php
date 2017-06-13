<?php

namespace Tests;

use Exception;
use string;
use smtech\CanvasPest\CanvasPest;
use smtech\CanvasPest\CanvasObject;
use smtech\CanvasPest\CanvasObject_Exception;
use PHPUnit\Framework\TestCase;

class CanvasObjectTest extends TestCase
{
    public function testInstantiation()
    {
        $pest = new CanvasPest(
            getenv('CANVASPEST_URL'),
            getenv('CANVASPEST_TOKEN')
        );
        $this->assertInstanceOf(CanvasPest::class, $pest);

        $response = $pest->get('users/self/profile');
        $this->assertInstanceOf(CanvasObject::class, $response);

        return $response;
    }

    /**
     * @depends testInstantiation
     */
    public function testObjectIsset(CanvasObject $obj)
    {
        $this->assertTrue(isset($obj->id));
        $this->assertFalse(isset($obj->foo_bar_baz));
    }

    /**
     * @depends testInstantiation
     */
    public function testObjectGet(CanvasObject $obj)
    {
        $id = $obj->id;
        $this->assertNotEmpty($id);
    }

    /**
     * @depends testInstantiation
     */
    public function testObjectSet(CanvasObject $obj)
    {
        try {
            $obj->name = 'Alice Bob';
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasObject_Exception::class, $e);
            $this->assertEquals(CanvasObject_Exception::IMMUTABLE, $e->getCode());
        }
    }

    /**
     * @depends testInstantiation
     */
    public function testObjectUnset(CanvasObject $obj)
    {
        try {
            unset($obj->name);
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasObject_Exception::class, $e);
            $this->assertEquals(CanvasObject_Exception::IMMUTABLE, $e->getCode());
        }
    }

    /**
     * @depends testInstantiation
     */
    public function testArrayOffsetExists(CanvasObject $obj)
    {
        $this->assertTrue(isset($obj['id']));
        $this->assertFalse(isset($obj['foo_bar_baz']));
    }

    /**
     * @depends testInstantiation
     */
    public function testArrayOffsetGet(CanvasObject $obj)
    {
        $id = $obj['id'];
        $this->assertNotEmpty($id);
    }

    /**
     * @depends testInstantiation
     */
    public function testArrayOffsetSet(CanvasObject $obj)
    {
        try {
            $obj['name'] = 'Alice Bob';
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasObject_Exception::class, $e);
            $this->assertEquals(CanvasObject_Exception::IMMUTABLE, $e->getCode());
        }
    }

    /**
     * @depends testInstantiation
     */
    public function testArrayOffsetUnset(CanvasObject $obj)
    {
        try {
            unset($obj['name']);
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasObject_Exception::class, $e);
            $this->assertEquals(CanvasObject_Exception::IMMUTABLE, $e->getCode());
        }
    }

    /**
     * @depends testInstantiation
     */
    public function testSerialize(CanvasObject $obj)
    {
        $serialized = serialize($obj);
        $this->assertInternalType('string', $serialized);
        $this->assertNotEmpty($serialized);
        return $serialized;
    }

    /**
     * @depends testSerialize
     */
    public function testUnserialize($serialized)
    {
        $obj = unserialize($serialized);
        $this->assertInstanceOf(CanvasObject::class, $obj);
    }

    /**
     * @depends testInstantiation
     */
    public function testGetArrayCopy(CanvasObject $obj)
    {
        $arr = $obj->getArrayCopy();
        $this->assertInternalType('array', $arr);
        $this->assertNotEmpty($arr);
        foreach ($obj as $key => $value) {
            $this->assertEquals($value, $array[$key]);
        }
        foreach ($arr as $key => $value) {
            $this->assertEquals($value, $obj[$key]);
        }
    }
}
