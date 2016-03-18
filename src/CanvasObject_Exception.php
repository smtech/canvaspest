<?php

/** smtech\CanvasPest\CanvasObject_Exception */
	
namespace smtech\CanvasPest;

/**
 * All exceptions thrown by CanvasObject
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class CanvasObject_Exception extends CanvasPest_Exception {
	/** Response values are read-only */
	const IMMUTABLE = 101;
}