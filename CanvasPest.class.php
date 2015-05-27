<?php

require_once('vendor/autoload.php');

/* the current maximum number of results per page returned by the Canvas API */
define ('CANVASPEST_MAXIMUM_PER_PAGE', '50');

class CanvasPest extends Pest {
	
	protected $headers;
	protected $response = array();
	protected $pagination;
	
	public function __construct($apiInstanceUrl, $apiAuthorizationToken) {
		parent::__construct($apiInstanceUrl);
		$this->setupToken($apiAuthorizationToken);
	}
	
	public function setupToken($token) {
		if (is_string($token) && strlen($token) > 0) {
			$this->headers['Authorization'] = "Bearer $token";
		} else if ($this->throw_exceptions) {
			throw new CanvasPest_Exception('API authorization token must be a non-zero-length string');
		}
	}
	
	protected function preprocessData($data) {
		if (is_array($data) && !array_key_exists('per_page', $data)) {
			$data['per_page'] = CanvasPest_MAXIMUM_PER_PAGE;
		}
		return $data;
	}
	
	protected function prepHeaders($headers) {
		return parent::prepHeaders(array_merge($this->headers, $headers));
	}
	
	protected function postprocessResponse($response) {
		$this->response = json_decode($response, true);
	}
	
	public function getResponse() {
		return $this->response;
	}
	
	protected function parseResponsePages() {
		if (preg_match_all('%<([^>]*)>\s*;\s*rel="([^"]+)"%', $this->lastHeader('link'), $links, PREG_SET_ORDER)) {
			foreach ($links as $link)
			{
				$this->pagination[$link[2]] = str_replace($this->base_url, '', $link[1]);
			}
		} else {
			$this->pagination = array();
		}
	}
	
	public function get($path, $data = array(), $headers = array()) {
		$this->postprocessResponse(parent::get($path, $this->preprocessData($data), $headers));
		$this->parseResponsePages();
		return $this->response;
	}
	
	public function post($path, $data = array(), $headers = array()) {
		$this->postprocessResponse(parent::post($path, $this->preprocessData($data), $headers));
		$this->parseResponsePages();
		return $this->response;
	}
	
	public function put($path, $data = array(), $headers = array()) {
		$this->postprocessResponse(parent::put($path, $this->preprocessData($data), $headers));
		$this->parseResponsePages();
		return $this->response;
	}
	
	public function delete($path, $headers = array()) {
		$this->postprocessResponse(parent::delete($path, $headers));
		$this->parseResponsePages();
		return $this->response;
	}
	
	private function getResponsePage($page) {
		if (array_key_exists($page, $this->pagination)) {
			return $this->get($this->pagination[$page], '');
		}
		return false;
	}
	
	public function nextPage() {
		return $this->getResponsePage('next');
	}
	
	public function prevPage() {
		return $this->getResponsePage('prev');
	}
	
	public function firstPage() {
		return $this->getResponsePage('first');
	}
	
	public function lastPage() {
		return $this->getResponsePage('last');
	}
	
	private function getPageNumber($page) {
		if (array_key_exists($page, $this->pagination)) {
			parse_str(parse_url($this->pagination[$page], PHP_URL_QUERY), $query);
			return $query['page'];
		}
		return false;
	}
	
	public function getNextPageNumber() {
		return $this->getPageNumber('next');
	}
	
	public function getPrevPageNumber() {
		return $this->getPageNumber('prev');
	}
	
	public function getFirstPageNumber() {
		return $this->getPageNumber('first');
	}
	
	public function getLastPageNumber() {
		return $this->getPageNumber('last');
	}
	
	public function getCurrentPageNumber() {
		$next = $this->getNextPageNumber();
		if ($next) {
			return $next - 1;
		} else {
			$prev = $this->getPrevPageNumber();
			if ($prev) {
				return $prev + 1;
			} else {
				return $this->getFirstPageNumber();
			}
		}
	}
	
	public function hasNextPage() {
		return $this->getNextPageNumber() !== false;
	}
	
	public function hasPrevPage() {
		return $this->getPrevPageNumber() !== false;
	}
}

class CanvasPestIterator {
	protected $canvasPest;
	protected $response;
	protected $responseIndex;
	
	public function __construct($canvasPest) {
		$this->canvasPest = $canvasPest;
		$this->response = array_values($CanvasPest->getResponse());
		$this->responseIndex = 0;
	}
	
	public function next() {
		if ($this->responseIndex == (count($this->response) - 1) && $this->canvasPest->hasNextPage()) {
			$this->response = array_values($this->canvasPest->nextPage());
			$this->responseIndex = 0;
		} else {
			$this->responseIndex++;
		}
	}
	
	public function valid() {
		if ($this->responseIndex == (count($this->response) - 1)) {
			return $this->canvasPest->hasNextPage();
		} else {
			return true;
		}
	}
	
	public function current() {
		return $this->response[$this->responseIndex];
	}
}

class CanvasApiProcess extends CanvasPest {}

class CanvasPest_Exception extends Exception {}

?>