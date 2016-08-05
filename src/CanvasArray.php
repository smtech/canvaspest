<?php
	
/** smtech\CanvasPest\CanvasArray */

namespace smtech\CanvasPest;

/**
 * An object to represent a list of Canvas Objects returned as a response from
 * the Canvas API.
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class CanvasArray implements \Iterator, \ArrayAccess, \Serializable {
	
	/** The maximum supported number of responses per page */
	const MAXIMUM_PER_PAGE = 100;
	
	
	/** @var CanvasPest $api Canvas API (for paging through the array) */
	protected $api;
	
	/**
	 * @var string $endpoint API endpoint whose response is represented by this
	 * object
	 **/
	private $endpoint = null;
	
	/**
	 * @var CanvasPageLink[] $pagination The canonical (first, last, next, prev, current)
	 * pages relative to the current page of responses
	 **/
	private $pagination = array();
	
	/** @var CanvasObject[] $data Backing store */
	private $data = array();
	
	/** @var int $page Page number corresponding to current $key */
	private $page = null;
		
	/** @var int $key Current key-value of iterator */
	private $key = null;
	
	/**
	 * Construct a CanvasArray
	 *
	 * @param string $jsonResponse A JSON-encoded response array from the Canvas API
	 * @param CanvasPest $canvasPest An API object for making pagination calls
	 **/
	public function __construct($jsonResponse, $canvasPest) {
		$this->api = $canvasPest;

		/* parse Canvas page links */
		if (preg_match_all('%<([^>]*)>\s*;\s*rel="([^"]+)"%', $this->api->lastHeader('link'), $links, PREG_SET_ORDER)) {
			foreach ($links as $link)
			{
				$this->pagination[$link[2]] = new CanvasPageLink($link[1], $link[2]);
			}
		} else {
			$this->pagination = array(); // might only be one page of results
		}
		
		/* locate ourselves */
		if (isset($this->pagination[CanvasPageLink::CURRENT])) {
			$this->page = $this->pagination[CanvasPageLink::CURRENT]->getPageNumber();
		} else {
			$this->page = 1; // assume only one page (since no pagination)
		}
		$this->key = $this->pageNumberToKey($this->page);

		/* parse the JSON response string */
		$key = $this->key;
		foreach (json_decode($jsonResponse, true) as $item) {
			$this->data[$key++] = new CanvasObject($item, $this->api);
		}
	}
	/**
	 * Convert a page number to an array key
	 *
	 * @param int $pageNumber 1-indexed page number
	 *
	 * @return int
	 *
	 * @throws CanvasArray_Exception INVALID_PAGE_NUMBER If $pageNumber < 1
	 **/
	private function pageNumberToKey($pageNumber) {
		if ($pageNumber < 1) {
			throw new CanvasArray_Exception(
				"{$pageNumber} is not a valid page number",
				CanvasArray_Exception::INVALID_PAGE_NUMBER
			);
		}
		if (isset($this->pagination[CanvasPageLink::CURRENT])) {
			return ($pageNumber - 1) * $this->pagination[CanvasPageLink::CURRENT]->getPerPage();
		} else {
			return 0; // assume only one page (since no pagination);
		}
	}
	
	/**
	 * Convert an array key to a page number
	 *
	 * @param int $key Non-negative array key
	 *
	 * @return int
	 *
	 * @throws CanvasArray_Exception INVALID_ARRAY_KEY If $key < 0
	 **/
	private function keyToPageNumber($key) {
		if ($key < 0) {
			throw new CanvasArray_Exception(
				"$key is not a valid array key",
				CanvasArray_Exception::INVALID_ARRAY_KEY
			);
		}
		
		if (isset($this->pagination[CanvasPageLink::CURRENT])) {
			return ((int) ($key / $this->pagination[CanvasPageLink::CURRENT]->getPerPage())) + 1;
		} else {
			return 1; // assume single page if no pagination
		}
	}
	
	/**
	 * Request a page of responses from the API
	 *
	 * A page of responses will be requested if it appears that that page has not
	 * yet been loaded (tested by checking if the initial element of the page has
	 * been initialized in the $data array).
	 *
	 * @param int $pageNumber Page number to request
	 * @param bool $forceRefresh (Optional) Force a refresh of backing data, even
	 *		if cached (defaults to `FALSE`)
	 *
	 * @return bool `TRUE` if the page is requested, `FALSE` if it is already cached
	 *		(and therefore not requested)
	 **/
	private function requestPageNumber($pageNumber, $forceRefresh = false) {
		if (!isset($this->data[$this->pageNumberToKey($pageNumber)]) || ($forceRefresh && isset($this->api))) {
			// assume one page if no pagination (and already loaded)
			if (isset($this->pagination[CanvasPageLink::CURRENT])) {
				$params = $this->pagination[CanvasPageLink::CURRENT]->getParams();
				$params[CanvasPageLink::PARAM_PAGE_NUMBER] = $pageNumber;
				$page = $this->api->get($this->pagination[CanvasPageLink::CURRENT]->getEndpoint(), $params);
				$this->data = array_replace($this->data, $page->data);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Request all pages from API
	 *
	 * This stores the entire API response locally, in preparation for, most
	 * likely, serializing this object.
	 *
	 * @param bool $forceRefresh (Optional) Force a refresh of backing data, even
	 *		if cached (defaults to `FALSE`)
	 *
	 * @return void
	 */
	private function requestAllPages($forceRefresh = false) {
		$_page = $this->page;
		$_key = $this->key;
		
		if (isset($this->pagination[CanvasPageLink::LAST])) {
			for ($page = 1; $page <= $this->pagination[CanvasPageLink::LAST]->getPageNumber(); $page++) {
				$this->requestPageNumber($page, $forceRefresh);
			}
		}
		
		$this->page = $_page;
		$this->key = $_key;
	}
	
	/**
	 * Rewind the iterator to a specific page of data
	 *
	 * If the page of data is already cached, it will not (by default) be reloaded
	 * from the API, although this can be overridden with the $forceRefresh
	 * parameter.
	 *
	 * @param int $pageNumber Page number to rewind to
	 * @param bool $forceRefresh (Optional) Defaults to `FALSE`
	 *
	 * @return void
	 **/
	private function rewindToPageNumber($pageNumber, $forceRefresh = false) {
		$page = null;
		$key = $this->pageNumberToKey($pageNumber);
		if ($forceRefresh || !isset($this->data[$key])) {
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
	
	/****************************************************************************
	 ArrayObject methods */
	
	/**
	 * Get the number of CanvasObjects in the Canvas response
	 *
	 * @return int
	 *
	 * @see http://php.net/manual/en/arrayobject.count.php ArrayObject::count
	 **/
	public function count() {
		if (isset($this->pagination[CanvasPageLink::LAST])) {
			$this->requestPageNumber($this->pagination[CanvasPageLink::LAST]->getPageNumber());
			if (!end($this->data)) {
				return 0;
			}
			return key($this->data) + 1;
		} else {
			return count($this->data);
		}
	}
	
	/**
	 * Creates a copy of the CanvasArray
	 *
	 * @return CanvasObject[]
	 *
	 * @see http://php.net/manual/en/arrayobject.getarraycopy.php ArrayObject::getArrayCopy
	 **/
	public function getArrayCopy() {
		$_key = $this->key;
		$this->rewindToPageNumber(1);
		while (isset($this->pagination[CanvasPageLink::NEXT])) {
			$this->rewindToPageNumber($this->pagination[CanvasPageLink::NEXT]);
		}
		$this->key = $_key;
		return $this->data;
	}
	
	/****************************************************************************
	 ArrayAccess methods */
	
	/**
	 * Whether an offset exists
	 *
	 * @param int|string $offset
	 *
	 * @return bool
	 *
	 * @see http://php.net/manual/en/arrayaccess.offsetexists.php ArrayAccess::offsetExists
	 **/
	public function offsetExists($offset) {
		if (isset($this->pagination[CanvasPageLink::LAST])) {
			$lastPageNumber = $this->pagination[CanvasPageLink::LAST]->getPageNumber();
			if ($this->keyToPageNumber($offset) == $lastPageNumber && !isset($this->data[$this->pageNumberToKey($lastPageNumber)])) {
				$this->requestPageNumber($lastPageNumber);
			}
			return isset($this->data[$offset]) || ($offset >= 0 && $offset < $this->pageNumberToKey($lastPageNumber));
		} else {
			return isset($this->data[$offset]);
		}
	}
	
	/**
	 * Offset to retrieve
	 *
	 * @param int|string $offset
	 *
	 * @return CanvasObject|null
	 *
	 * @see http://php.net/manual/en/arrayaccess.offsetexists.php ArrayAccess::offsetGet
	 **/
	public function offsetGet($offset) {
		if ($this->offsetExists($offset) && !isset($this->data[$offset])) {
			$this->requestPageNumber($this->keyToPageNumber($offset));
		}
		return $this->data[$offset];
	}
	
	/**
	 * Assign a value to the specified offset
	 *
	 * @deprecated Canvas responses are immutable
	 *
	 * @param int|string $offset
	 * @param CanvasObject $value
	 *
	 * @return void
	 *
	 * @throws CanvasArray_Exception IMMUTABLE All calls to this method will cause an exception
	 *
	 * @see http://php.net/manual/en/arrayaccess.offsetset.php ArrayAccess::offsetSet
	 **/
	public function offsetSet($offset, $value) {
		throw new CanvasArray_Exception(
			'Canvas responses are immutable',
			CanvasArray_Exception::IMMUTABLE
		);
	}
	
	/**
	 * Unset an offset
	 *
	 * @deprecated Canvas responses are immutable
	 *
	 * @param int|string $offset
	 *
	 * @return void
	 *
	 * @throws CanvasArray_Exception IMMUTABLE All calls to this method will cause an exception
	 *
	 * @see http://php.net/manual/en/arrayaccess.offsetunset.php ArrayAccess::offsetUnset
	 **/
	public function offsetUnset($offset) {
		throw new CanvasArray_Exception(
			'Canvas responses are immutable',
			CanvasArray_Exception::IMMUTABLE
		);
	}
	
	/****************************************************************************/
	
	/****************************************************************************
	 Iterator methods */
	
	/**
	 * Return the current element
	 *
	 * @return CanvasObject
	 *
	 * @see http://php.net/manual/en/iterator.current.php Iterator::current
	 **/
	public function current() {
		if (!isset($this->data[$this->key])) {
			$this->requestPageNumber($this->keyToPageNumber($this->key));
		}
		return $this->data[$this->key];
	}
	
	/**
	 * Return the key of the current element
	 *
	 * @return int
	 *
	 * @see http://php.net/manual/en/iterator.key.php Iterator::key
	 **/
	public function key() {
		return $this->key;
	}
	
	/**
	 * Move forward to next element
	 *
	 * @return void
	 *
	 * @see http://php.net/manual/en/iterator.next.php Iterator::next
	 **/
	public function next() {
		$this->key++;
	}
	
	/**
	 * Rewind the iterator to the first element
	 *
	 * @return void
	 * 
	 * @see http://php.net/manual/en/iterator.rewind.php Iterator::rewind
	 **/
	public function rewind() {
		$this->key = 0;
	}
	
	/**
	 * Checks if current position is valid
	 *
	 * @return bool
	 *
	 * @see http://php.net/manual/en/iterator.valid.php Iterator::valid
	 **/
	public function valid() {
		return ($this->offsetExists($this->key));
	}
	
	/****************************************************************************/

	/****************************************************************************
	 Serializable methods */
	
	/**
	 * String representation of CanvasArray
	 *
	 * @return string
	 *
	 * @see http://php.net/manual/en/serializable.serialize.php Serializable::serialize()
	 **/
	public function serialize() {
		$this->requestAllPages();
		return serialize(
			array(
				'page' => $this->page,
				'key' => $this->key,
				'data' => $this->data
			)
		);
	}
	
	/**
	 * Construct a CanvasArray from its string representation
	 *
	 * The data in the unserialized CanvasArray is static and cannot be refreshed,
	 * as the CanvasPest API connection is _not_ serialized to preserve the
	 * security of API access tokens.
	 *
	 * @param string $data
	 *
	 * @return string
	 * 
	 * @see http://php.net/manual/en/serializable.unserialize.php Serializable::unserialize()
	 **/
	public function unserialize($data) {
		$_data = unserialize($data);
		$this->page = $_data['page'];
		$this->key = $_data['key'];
		$this->data = $_data['data'];
		$this->api = null;
		$this->endpoint = null;
		$this->pagination = array();
	}

	/****************************************************************************/
}