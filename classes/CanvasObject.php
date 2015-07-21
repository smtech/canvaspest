<?php

/**
 * An object that represents a single Canvas object, providing both object-
 * style access (obj->key) and array-style access (array[key]).
 * CanvasObject objects are immutable, so attempts to change their
 * underlying data will result in exceptions.
 *
 * A CanvasObject is returned from any API request for which the endpoint ends
 * with a specific ID number (e.g. http://example.com/api/v1/accounts/1/users/1).
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class CanvasObject implements ArrayAccess {
	
	protected $pest;
	private $data;
	
	public function __construct($response, $canvasPest) {
		$this->pest = $canvasPest;
		if (is_array($response)) {
			$this->data = $response;
		} else {
			$this->data = json_decode($jsonResponse, true);
		}
	}
	
	/****************************************************************************
	 Object methods */

	public function __isset($key) {
		return isset($this->data[$key]);
	}
	
	public function __get($key) {
		return $this->data[$key];
	}

	public function __set($key, $value) {
		throw new CanvasObject_Exception(
			'Canvas objects are immutable',
			CanvasObject_Exception::IMMUTABLE
		);
	}
	
	public function __unset($key) {
		throw new CanvasObject_Exception(
			'Canvas objects are immutable',
			CanvasObject_Exception::IMMUTABLE
		);
	}
	
	/****************************************************************************/
	
	/****************************************************************************
	 ArrayAccess methods */
	
	public function offsetExists ($offset) {
		return isset($this->data[$offset]);
	}
	
	public function offsetGet ($offset) {
		return $this->data($offset);
	}
	
	public function offsetSet($offset, $value) {
		throw new CanvasObject_Exception(
			'Canvas objects are immutable',
			CanvasObject_Exception::IMMUTABLE
		);
	}
	public function offsetUnset ($offset) {
		throw new CanvasObject_Exception(
			'Canvas objects are immutable',
			CanvasObject_Exception::IMMUTABLE
		);
	}
	
	/****************************************************************************/
}

/**
 * All exceptions thrown by CanvasObject
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class CanvasObject_Exception extends CanvasPest_Exception {
	/**
	 * @const IMMUTABLE Response values are read-only
	 **/
	const IMMUTABLE = 101;
}

?>