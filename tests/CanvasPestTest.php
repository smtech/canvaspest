<?php

namespace Tests;

use Exception;
use Battis\Educoder;
use smtech\CanvasPest\CanvasPest;
use smtech\CanvasPest\CanvasObject;
use smtech\CanvasPest\CanvasArray;
use smtech\CanvasPest\CanvasPest_Exception;
use Tests\Wrappers\CanvasPestWrapper;
use Tests\Expectations\ExceptionExpectation;

class CanvasPestTest extends ExceptionExpectation
{
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
            $this->assertNotException($e);
        }
        $this->assertInstanceOf(CanvasObject::class, $response);
    }

    /**
     * @depends testInstantiation
     */
    public function testGetArray(CanvasPest $pest)
    {
        try {
            $response = $pest->get(
                'users/' . getenv('CANVASPEST_USER_ID') . '/calendar_events',
                [
                    'start_date' => '2017-06-01',
                    'end_date' => '2017-07-01'
                ]);
        } catch (Exception $e) {
            $this->assertNotException($e);
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
            $this->assertNotException($e);
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
            $this->assertNotException($e);
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
            $this->assertNotException($e);
        }
        $this->assertInstanceOf(CanvasObject::class, $response);
    }

    public function testDeleteObjectWithParamsPath()
    {
        return $this->testDeleteObject(
            $this->testPostObject(
                $this->testInstantiation()
            ),
            '?foo=bar'
        );
    }

    /**
     * @depends testInstantiation
     */
    public function testPatchFails(CanvasPest $pest)
    {
        try {
            $response = $pest->patch('foo/bar');
            $this->assertException(CanvasPest_Exception::class);
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasPest_Exception::class, $e);
            $this->assertEquals(CanvasPest_Exception::UNSUPPORTED_METHOD, $e->getCode());
        }
    }

    public function testInvalidJsonResponse()
    {
        try {
            $pest = new CanvasPest(
                substr(getenv('CANVASPEST_URL'), 0, -7),
                getenv('CANVASPEST_TOKEN')
            );
        } catch (Exception $e) {
            $this->assertNotException($e);
        }

        $response = false;
        try {
            $response = $pest->get('login');
            $this->assertException(CanvasPest_Exception::class);
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
            $this->assertException(CanvasPest_Exception::class);
        } catch (Exception $e) {
            $this->assertInstanceOf(CanvasPest_Exception::class, $e);
            $this->assertEquals(CanvasPest_Exception::INVALID_TOKEN, $e->getCode());
        }
    }
}
