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
        $response = false;
        try {
            $response = $pest->get('login');
        } catch (Exception $e) {
            $this->assertNoException($e);
        }
        $this->assertNull($response);
    }
}
