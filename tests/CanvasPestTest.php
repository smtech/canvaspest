<?php

namespace Tests;

use Exception;
use Battis\Educoder;
use smtech\CanvasPest\CanvasPest;
use smtech\CanvasPest\CanvasObject;
use smtech\CanvasPest\CanvasArray;
use smtech\CanvasPest\CanvasPest_Exception;
use Tests\Wrappers\CanvasPestWrapper;
use PHPUnit\Framework\TestCase;

class CanvasPestTest extends TestCase
{
    /**
     * FIXME not the right way to do this --
     * https://phpunit.de/manual/current/en/extending-phpunit.html#idp1310704
     */
    protected function assertNoException(Exception $e)
    {
        $this->assertFalse(true, 'No exception expected, ' . get_class($e) . ' thrown with code ' . $e->getCode());
    }

    public function testInstantiation()
    {
        try {
            $pest = new CanvasPest(
                getenv('CANVASPEST_URL'),
                getenv('CANVASPEST_TOKEN')
            );
        } catch (CanvasPest_Exception $e) {
            $this->assertFalse(true);
        }
        $this->assertInstanceOf(CanvasPest::class, $pest);

        return $pest;
    }

    /**
     * @depends testInstantiation
     */
    public function testGetObject(CanvasPest $pest)
    {
        try {
            $response = $pest->get('users/self/profile');
        } catch (Exception $e) {
            $this->assertNoException($e);
        }
        $this->assertInstanceOf(CanvasObject::class, $response);
    }

    /**
     * @depends testInstantiation
     */
    public function testGetArray(CanvasPest $pest)
    {
        try {
            $response = $pest->get('users/self/courses');
        } catch (Exception $e) {
            $this->assertNoException($e);
        }
        $this->assertInstanceOf(CanvasArray::class, $response);
    }

    /**
     * @depends testInstantiation
     */
    public function testPostObject(CanvasPest $pest)
    {
        try {
            $response = $pest->post('calendar_events', [
                'calendar_event[context_code]' => 'user_' . getenv('CANVASPEST_USER_ID'),
                'calendar_event[title]' => 'CanvasPest testPostObject'
            ]);
        } catch (Exception $e) {
            $this->assertNoException($e);
        }
        $this->assertInstanceOf(CanvasObject::class, $response);

        return new CanvasPestWrapper($pest, $response);
    }

    /**
     * @depends testPostObject
     */
    public function testPutObject(CanvasPestWrapper $wrapper)
    {
        try {
            $response = $wrapper->pest->put('calendar_events/' . $wrapper->response['id'], [
                'calendar_event[description]' => 'CanvasPest testPutObject'
            ]);
        } catch (Exception $e) {
            $this->assertNoException($e);
        }
        $this->assertInstanceOf(CanvasObject::class, $response);
        $wrapper->response = $response;
        return $wrapper;
    }

    /**
     * @depends testPutObject
     */
    public function testDeleteObject(CanvasPestWrapper $wrapper, $paramPath = '')
    {
        try {
            $response = $wrapper->pest->delete('calendar_events/' . $wrapper->response['id'] . $paramPath, [
                'cancel_reason' => 'testDeleteObject'
            ]);
        } catch (Exception $e) {
            $this->assertNoException($e);
        }
        $this->assertInstanceOf(CanvasObject::class, $response);
    }

    /**
     * @depends testInstantiation
     */
    public function testPostObjectDuplicate(CanvasPest $pest)
    {
        return $this->testPostObject($pest);
    }

    /**
     * @depends testPostObjectDuplicate
     */
    public function testDeleteObjectWithParamsPath(CanvasPestWrapper $wrapper)
    {
        return $this->testDeleteObject($wrapper, '?foo=bar');
    }

    /**
     * @depends testInstantiation
     */
    public function testPatchFails(CanvasPest $pest)
    {
        try {
            $response = $pest->patch('foo/bar');
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasPest_Exception::class, $e);
            $this->assertEquals(CanvasPest_Exception::UNSUPPORTED_METHOD, $e->getCode());
        }
    }

    public function testInvalidJsonResponse()
    {
        $pest = new CanvasPest(substr(getenv('CANVASPEST_URL'), 0, -7), getenv('CANVASPEST_TOKEN'));
        $response = false;
        try {
            $response = $pest->get('login');
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasPest_Exception::class, $e);
            $this->assertEquals(CanvasPest_Exception::INVALID_JSON_RESPONSE, $e->getCode());
        }
    }

    public function testBadToken()
    {
        $pest = new CanvasPest(getenv('CANVASPEST_URL'));
        try {
            $pest->setupToken("");
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasPest_Exception::class, $e);
            $this->assertEquals(CanvasPest_Exception::INVALID_TOKEN, $e->getCode());
        }
    }
}
