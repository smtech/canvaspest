<?php

namespace Tests;

use smtech\CanvasPest\CanvasPest;
use smtech\CanvasPest\CanvasObject;
use smtech\CanvasPest\CanvasArray;
use smtech\CanvasPest\CanvasPest_Exception;
use PHPUnit\Framework\TestCase;

class CanvasPestTest extends TestCase
{
    public function testInstantiation()
    {
        $pest = new CanvasPest(
            getenv('CANVASPEST_URL'),
            getenv('CANVASPEST_TOKEN')
        );
        $this->assertInstanceOf(CanvasPest::class, $pest);

        return $pest;
    }

    /**
     * @depends testInstantiation
     */
    public function testGetUserProfileObject(CanvasPest $pest)
    {
        try {
            $response = $pest->get('users/self/profile');
        } catch (CanvasPest_Exception $e) {
            $this->assertFalse(true);
        }
        $this->assertInstanceOf(CanvasObject::class, $response);
    }

    /**
     * @depends testInstantiation
     */
    public function testGetUserEnrollmentsArray(CanvasPest $pest)
    {
        $response = $pest->get('users/self/courses');
        $this->assertInstanceOf(CanvasArray::class, $response);
    }
}
