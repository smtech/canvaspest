<?php

namespace Tests;

use Exception;
use smtech\CanvasPest\CanvasPageLink;
use smtech\CanvasPest\CanvasPageLink_Exception;
use Tests\Expectations\ExceptionExpectation;

class CanvasPageLinkTest extends ExceptionExpectation
{
    public function testInstantiation()
    {
        try {
            $link = new CanvasPageLink('', 'current');
            $this->assertException(CanvasPageLink_Exception::class);
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasPageLink_Exception::class, $e);
            $this->assertEquals(CanvasPageLink_Exception::INVALID_CONSTRUCTOR, $e->getCode());
        }

        try {
            $link = new CanvasPageLink('https://canvas.instructure.com/api/v1/users/12345/courses?page=1&per_page=10', '');
            $this->assertException(CanvasPageLink_Exception::class);
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasPageLink_Exception::class, $e);
            $this->assertEquals(CanvasPageLink_Exception::INVALID_CONSTRUCTOR, $e->getCode());
        }

        try {
            $link = new CanvasPageLink('', '');
            $this->assertException(CanvasPageLink_Exception::class);
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasPageLink_Exception::class, $e);
            $this->assertEquals(CanvasPageLink_Exception::INVALID_CONSTRUCTOR, $e->getCode());
        }

        $link = new CanvasPageLink('https://canvas.instructure.com/api/v1/users/12345/courses?page=1&per_page=10', 'current');
        $this->assertInstanceOf(CanvasPageLink::class, $link);

        return $link;
    }

    /**
     * @depends testInstantiation
     */
    public function testGetName(CanvasPageLink $link)
    {
        $this->assertNotEmpty($link->getName());
    }

    /**
     * @depends testInstantiation
     */
    public function testGetEndpoint(CanvasPageLink $link)
    {
        $this->assertNotEmpty($link->getEndpoint());
    }

    /**
     * @depends testInstantiation
     */
    public function testGetParams(CanvasPageLink $link)
    {
        $this->assertInternalType('array', $link->getParams());
    }

    /**
     * @depends testInstantiation
     */
    public function testGetPageNumber(CanvasPageLink $link)
    {
        $this->assertNotEmpty($link->getPageNumber());
    }

    /**
     * @depends testInstantiation
     */
    public function testGetPerPage(CanvasPageLink $link)
    {
        $this->assertNotEmpty($link->getPerPage());
    }
}
