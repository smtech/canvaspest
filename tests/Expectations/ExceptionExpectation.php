<?php

namespace Tests\Expectations;

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * FIXME probably not the right way to do this --
 * https://phpunit.de/manual/current/en/extending-phpunit.html#idp1310704
 */
class ExceptionExpectation extends TestCase
{
    protected function assertNotException(Exception $e)
    {
        $this->assertTrue(false, 'No exception expected, ' . get_class($e) . ' thrown with code ' . $e->getCode());
    }

    protected function assertException($type = Exception::class)
    {
        $this->assertTrue(false, "$type should have been thrown");
    }
}
