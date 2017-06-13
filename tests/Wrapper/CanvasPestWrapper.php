<?php

namespace Tests\Wrapper;

use smtech\CanvasPest\CanvasPest;
use smtech\CanvasPest\CanvasObject;

class CanvasPestWrapper
{
    public $pest;
    public $event;

    public function __construct(CanvasPest $pest, CanvasObject $event)
    {
        $this->pest = $pest;
        $this->event = $event;
    }
}
