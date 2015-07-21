<?php
	
/**
 * Enforce read-only access to the API on the client-side (since the server
 * can't/won't do so)
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class CanvasPest_ReadOnly extends CanvasPest {
	/**
	 * @deprecated CanvasPest_ReadOnly only supports GET calls to the API
	 * @throws CanvasPest_ReadOnly_Exception ATTEMPTED_WRITE_REQUEST All calls to this method will cause an exception
	 **/
	public function put($path, $data = array(), $headers = array()) {
		throw new CanvasPest_ReadOnly_Exception(
			'Only GET calls to the API are allowed from CanvasPest_ReadOnly.',
			CanvasPest_ReadOnly_Exception::ATTEMPTED_WRITE_REQUEST
		);
	}

	/**
	 * @deprecated CanvasPest_ReadOnly only supports GET calls to the API
	 * @throws CanvasPest_ReadOnly_Exception ATTEMPTED_WRITE_REQUEST All calls to this method will cause an exception
	 **/
	public function post($path, $data = array(), $headers = array()) {
		throw new CanvasPest_ReadOnly_Exception(
			'Only GET calls to the API are allowed from CanvasPest_ReadOnly.',
			CanvasPest_ReadOnly_Exception::ATTEMPTED_WRITE_REQUEST
		);
	}

	/**
	 * @deprecated CanvasPest_ReadOnly only supports GET calls to the API
	 * @throws CanvasPest_ReadOnly_Exception ATTEMPTED_WRITE_REQUEST All calls to this method will cause an exception
	 **/
	public function delete($path, $headers = array()) {
		throw new CanvasPest_ReadOnly_Exception(
			'Only GET calls to the API are allowed from CanvasPest_ReadOnly.',
			CanvasPest_ReadOnly_Exception::ATTEMPTED_WRITE_REQUEST
		);
	}
}

/**
 * All exceptions thrown by CanvasPest_ReadOnly
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class CanvasPest_ReadOnly_Exception extends CanvasPest_Exception {
	/**
	 * @const ATTEMPTED_WRITE_REQUEST A request to the API that would change data was attempted
	 **/
	const ATTEMPTED_WRITE_REQUEST = 1;
}

?>