<?php

namespace Tests;

use smtech\CanvasPest\CanvasPest;
use Tests\Wrappers\CanvasPestWrapper;

class CanvasPestNoExceptionsTest extends CanvasPestTest
{
    public function testInstantiation()
    {
        $pest = parent::testInstantiation();
        $pest->throw_exceptions = false;
        return $pest;
    }

    public function testInvalidJsonResponse()
    {
        $pest = new CanvasPest(substr(getenv('CANVASPEST_URL'), 0, -7), getenv('CANVASPEST_TOKEN'));
        $pest->throw_exceptions = false;
        $response = null;
        try {
            $response = $pest->get('login');
        } catch (Exception $e) {
            $this->assertNotException($e);
        }
        $this->assertFalse($response);
    }

    public function testBadToken()
    {
        $pest = new CanvasPest(getenv('CANVASPEST_URL'));
        $pest->throw_exceptions = false;
        $response = null;
        try {
            $response = $pest->setupToken("");
        } catch (Exception $e) {
            $this->assertNotException($e);
        }
        $this->assertFalse($response);
    }

    /**
     * @depends testInstantiation
     */
    public function testPatchFails(CanvasPest $pest)
    {
        $response = null;
        try {
            $response = $pest->patch('foo/bar');
        } catch (Exception $e) {
            $this->assertNotException($e);
        }
        $this->assertFalse($response);
    }
}
