<?php

namespace Tests;

use Exception;
use smtech\CanvasPest\CanvasPestImmutable;
use smtech\CanvasPest\CanvasObject;
use smtech\CanvasPest\CanvasArray;
use smtech\CanvasPest\CanvasPestImmutable_Exception;
use Tests\Expectations\ExceptionExpectation;

class CanvasPestImmutableTest extends ExceptionExpectation
{
    public function assertImmutableException(Exception $e)
    {
        $this->assertInstanceOf(CanvasPestImmutable_Exception::class, $e);
        $this->assertEquals(CanvasPestImmutable_Exception::IMMUTABLE, $e->getCode());
    }

    public function testInstantiation()
    {
        $pest = new CanvasPestImmutable(
            getenv('CANVASPEST_URL'),
            getenv('CANVASPEST_TOKEN')
        );
        $this->assertInstanceOf(CanvasPestImmutable::class, $pest);
        return $pest;
    }

    /**
     * @depends testInstantiation
     */
    public function testPostObject(CanvasPestImmutable $pest)
    {
        try {
            $response = $pest->post('calendar_events', [
                'calendar_event[context_code]' => 'user_' . getenv('CANVASPEST_USER_ID'),
                'calendar_event[title]' => 'CanvasPest testPostObject'
            ]);
            $this->assertException(CanvasPestImmutable::class);
        } catch (Exception $e) {
            $this->assertImmutableException($e);
        }
    }

    /**
     * @depends testInstantiation
     */
    public function testPutObject(CanvasPestImmutable $pest)
    {
        try {
            $response = $pest->put('calendar_events/123', [
                'calendar_event[description]' => 'CanvasPest testPutObject'
            ]);
            $this->assertException(CanvasPestImmutable::class);
        } catch (Exception $e) {
            $this->assertImmutableException($e);
        }
    }

    /**
     * @depends testInstantiation
     */
    public function testDeleteObject(CanvasPestImmutable $pest)
    {
        try {
            $response = $pest->delete('calendar_events/123', [
                'cancel_reason' => 'testDeleteObject'
            ]);
            $this->assertException(CanvasPestImmutable::class);
        } catch (Exception $e) {
            $this->assertImmutableException($e);
        }
    }
}
