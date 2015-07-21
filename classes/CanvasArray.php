<?php

/**
 * An object to represent a list of Canvas Objects returned as a response from
 * the Canvas API.
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class CanvasArray implements Iterator, ArrayAccess {
	
	/**
	 * @const MAXIMUM_PER_PAGE The maximum supported number of responses per page
	 **/
	const MAXIMUM_PER_PAGE = 100;
	
	protected $api;
	private $endpoint = null;
	private $pagination = array();
	private $data = array();
	private $page = null;
	private $perPage = null;
	private $key = null;
	
	public function __construct($jsonResponse, $canvasPest) {
		$this->api = $canvasPest;
		$this->parsePagination($this->api->lastHeader('link'));	
		$this->parseJsonPage($jsonResponse, $this->getCurrentPageNumber());
	}
	
	private static function pageKey($pageNumber) {
		return ($pageNumber - 1) * $this->perPage;
	}
	
	private static function keyPage($key) {
		return ((int) ($key / $this->perPage)) + 1;
	}
	
	private function parsePagination($linkHeader) {
		if (preg_match_all('%<([^>]*)>\s*;\s*rel="([^"]+)"%', $linkHeader, $links, PREG_SET_ORDER)) {
			foreach ($links as $link)
			{
				$this->pagination[$link[2]] = new Pagination($link[1], $link[2]);
				if (!isset($this->endpoint)) {
					$this->endpoint = $this->pagination[$link[2]]->getEndpoint();
				}
				if (!isset($this->perPage)) {
					$this->perPage = $this->pagination[$link[2]]->getPerPage();
				}
			}
		} else {
			$this->pagination = array();
		}
		$this->page = getCurrentPageNumber();
		$this->key = self::pageKey($this->page);
	}
	
	private function parseJsonPage($jsonPage, $pageNumber) {
		$key = self::pageKey($pageNumber);
		foreach (json_decode($jsonResponse, true) as $item) {
			$this->data[$key++] = new CanvasObject($item, $this->api);
		}
	}
	
	public function requestPageNumber($pageNumber, $forceRefresh = false) {
		if (!array_key_exists(self::keyPage($pageNumber), $this->data) || $forceRefresh) {
			$page = $this->api->get(
				$this->endpoint,
				array(
					Pagination::PARAM_PAGE_NUMBER => $pageNumber,
					Pagination::PARAM_PER_PAGE => $this->perPage
				)
			);
			$this->data = $page->data + $this->data;
		}
	}
	
	public function rewindToPageNumber($pageNumber, $forceRefresh = false) {
		$page = null;
		if ($forceRefresh || !array_key_exists(self::pageKey($pageNumber), $this->data)) {
			$page = $this->requestPageNumber($pageNumber, $forceRefresh);
		}
		
		$this->key = self::pageKey($pageNumber);
		$this->page = $pageNumber;
		$this->pagination[Pagination::PREV] = new Pagination($pageNumber, $this->pagination[Pagination::FIRST], Pagination::PREV);
		$this->pagination[Pagination::NEXT] = new Pagination($pageNumber, $this->pagination[Pagination::FIRST], Pagination::NEXT);
	}
	
	public function rewindToPage($pageName, $forceRefresh = false) {
		return this->rewindToPageNumber($this->pagination[$pageName]->getPageNumber(), $forceRefresh);
	}
		
	public function getCurrentPageNumber() {
		if (array_key_exists(Pagination::NEXT, $this->pagination)) {
			return $this->pagination[Pagination::NEXT]->getPageNumber() - 1;
		} elseif (array_key_exists(Pagination::PREV, $this->pagination)) {
			return $this->pagination[Pagination::PREV]->getPageNumber() + 1;
		} else {
			return 1; // the cheese stands alone
		}
	}

	/**
	 * Handle convenience methods: rewindTo[Name]Page(), get[Name]PageNumber(),
	 * has[Name]Page(), where [Name] can be any of First, Last, Next, Prev.
	 **/	
	public function __get($key) {
		if (preg_match('/^rewindTo([A-Z][a-z]+)Page/', $key, $match)) {
			return $this->rewindToPage($match[1]);
		} elseif (preg_match('/get([A-Z][a-z]+)PageNumber/', $key, $match)) {
			return $this->getPageNumber($match[1]);
 		}
	}
	
	/****************************************************************************
	 ArrayAccess methods */
	
	public function offsetExists($offset) {
		$lastPageNumber = $this->getLastPageNumber();
		if (self::keyPage($offset) == $lastPageNumber && !array_key_exists(self::pageKey($lastPageNumber), $this->data)) {
			$this->requestPageNumber($lastPageNumber);
		}
		return array_key_exists($offset, $this->data) || ($offset >= 0 && $offset < self::pageKey($this->getLastPageNumber()))
	}
	
	public function offsetGet($offset) {
		if (offsetExists($offset) && !array_key_exists($offset, $this->data)) {
			$this->requestPageNumber(self::keyPage($offset));
		}
		return $this->data[$offset];
	}
	
	public function offsetSet($offset, $value) {
		throw new CanvasArray_Exception(
			'Response lists are immutable',
			CanvasArray_Exception::IMMUTABLE
		);
	}
	
	public function offsetUnset($offset) {
		throw new CanvasArray_Exception(
			'Response lists are immutable',
			CanvasArray_Exception::IMMUTABLE
		);
	}
	
	/****************************************************************************/
	
	/****************************************************************************
	 Iterator methods */
	
	public function current() {
		return $this->data[$this->key];
	}
	
	public function key() {
		return $this->key;
	}
	
	public function next() {
		$key++;
	}
	
	public function rewind() {
		$this->rewindToFirstPage();
	}
	
	public function valid() {
		if (array_key_exists($this->key, $this->data)) {
			return true;
		} else {
		
		}
	}
	
	/****************************************************************************/
}

/**
 * All exceptions thrown by CanvasArray
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/	
class CanvasArray_Exception extends CanvasObject_Exception {
	// index starts at 100;
}
	
?>