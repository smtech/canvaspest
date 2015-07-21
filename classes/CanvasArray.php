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

		/* parse Canvas page links */
		if (preg_match_all('%<([^>]*)>\s*;\s*rel="([^"]+)"%', $this->api->lastHeader('link'), $links, PREG_SET_ORDER)) {
			foreach ($links as $link)
			{
				$this->pagination[$link[2]] = new CanvasPageLink($link[1], $link[2]);
				if (!isset($this->endpoint)) {
					$this->endpoint = $this->pagination[$link[2]]->getEndpoint();
				}
				if (!isset($this->perPage)) {
					$this->perPage = $this->pagination[$link[2]]->getPerPage();
				}
			}
		} else {
			$this->pagination = array(); // might only be one page of results
		}
		
		/* locate ourselves */
		$this->page = $this->getCurrentPageNumber();
		$this->key = $this->pageNumberToKey($this->page);

		/* parse the JSON response string */
		$key = $this->key;
		foreach (json_decode($jsonResponse, true) as $item) {
			$this->data[$key++] = new CanvasObject($item, $this->api);
		}
	}
	
	private function pageNumberToKey($pageNumber) {
		return ($pageNumber - 1) * $this->perPage;
	}
	
	private function keyToPageNumber($key) {
		return ((int) ($key / $this->perPage)) + 1;
	}
	
	private function getCurrentPageNumber() {
		if (array_key_exists(CanvasPageLink::NEXT, $this->pagination)) {
			return $this->pagination[CanvasPageLink::NEXT]->getPageNumber() - 1;
		} elseif (array_key_exists(CanvasPageLink::PREV, $this->pagination)) {
			return $this->pagination[CanvasPageLink::PREV]->getPageNumber() + 1;
		} else {
			return 1; // the cheese stands alone
		}
	}

	public function requestPageNumber($pageNumber, $forceRefresh = false) {
		if (!array_key_exists($this->keyToPageNumber($pageNumber), $this->data) || $forceRefresh) {
			$page = $this->api->get(
				$this->endpoint,
				array(
					CanvasPageLink::PARAM_PAGE_NUMBER => $pageNumber,
					CanvasPageLink::PARAM_PER_PAGE => $this->perPage
				)
			);
			$this->data = $page->data + $this->data;
		}
	}
	
	public function rewindToPageNumber($pageNumber, $forceRefresh = false) {
		$page = null;
		$key = $this->pageNumberToKey($pageNumber);
		if ($forceRefresh || !array_key_exists($key, $this->data)) {
			$page = $this->requestPageNumber($pageNumber, $forceRefresh);
		}
		
		$this->key = $key;
		$this->page = $pageNumber;
		$this->pagination[CanvasPageLink::PREV] = new CanvasPageLink(
			$pageNumber,
			$this->pagination[CanvasPageLink::FIRST],
			CanvasPageLink::PREV
		);
		$this->pagination[CanvasPageLink::NEXT] = new CanvasPageLink(
			$pageNumber,
			$this->pagination[CanvasPageLink::FIRST],
			CanvasPageLink::NEXT
		);
	}
	
	public function rewindToPage($pageName, $forceRefresh = false) {
		return this->rewindToPageNumber($this->pagination[$pageName]->getPageNumber(), $forceRefresh);
	}
		
	/****************************************************************************
	 ArrayAccess methods */
	
	public function offsetExists($offset) {
		$lastPageNumber = $this->getLastPageNumber();
		if ($this->keyToPageNumber($offset) == $lastPageNumber && !array_key_exists($this->pageNumberToKey($lastPageNumber), $this->data)) {
			$this->requestPageNumber($lastPageNumber);
		}
		return array_key_exists($offset, $this->data) || ($offset >= 0 && $offset < $this->pageNumberToKey($this->getLastPageNumber()))
	}
	
	public function offsetGet($offset) {
		if (offsetExists($offset) && !array_key_exists($offset, $this->data)) {
			$this->requestPageNumber($this->keyToPageNumber($offset));
		}
		return $this->data[$offset];
	}
	
	public function offsetSet($offset, $value) {
		throw new CanvasArray_Exception(
			'Canvas responses are immutable',
			CanvasArray_Exception::IMMUTABLE
		);
	}
	
	public function offsetUnset($offset) {
		throw new CanvasArray_Exception(
			'Canvas responses are immutable',
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
	// index starts at 200;
}
	
?>