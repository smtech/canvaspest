<?php

namespace Tests\Wrappers;

use smtech\CanvasPest\CanvasPest;
use smtech\CanvasPest\CanvasObject;

class CanvasPestWrapper
{
    public $pest;
    public $response;

    public function __construct(CanvasPest $pest, $response)
    {
        $this->pest = $pest;
        $this->response = $response;
    }
}
