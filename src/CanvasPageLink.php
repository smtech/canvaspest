<?php

/** smtech\CanvasPest\CanvasPageLink */

namespace smtech\CanvasPest;

/**
 * An object to represent Canvas API pagination information.
 *
 * As the whole point of CanvasPest is to abstract the actual API response out
 * of the way, concealing the mundane details of the response pagination, this
 * particular object is used only internally to create object-oriented access
 * to the API response's `link` header.
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class CanvasPageLink {
	
	/** Name of the current page link */
	const CURRENT = 'current';
	
	/** Name of the first page link */
	const FIRST = 'first';
	
	/** Name of the last page link */
	const LAST = 'last';
	
	/** Name of the next page link */
	const NEXT = 'next';
	
	/** Name of the previous page link */
	const PREV = 'prev';
	
	
	/** @var string $name Name of the page link */
	private $name;
	
	/** @var string $endpoint Path of the API endpoint being paginated */
	private $endpoint;
	
	/** @var array $params Query parameters for the page link API call */
	private $params;
	
	/** Name of the page number parameter in the page link */
	const PARAM_PAGE_NUMBER = 'page';
	
	/** Name of the parameter describing the number of responses per page in the page link */
	const PARAM_PER_PAGE = 'per_page';
	
	/**
	 * Construct a new Canvas page link object.
	 *
	 * CanvasPageLinks can be constructed with two possible parameter lists:
	 *
	 * 1. `__construct(string $pageUrl, string $pageName)` which expects a
	 * non-empty string representing the URL of the API endpoint to retrieve the
	 * page and a non-empty string representing the canonical name of the page
	 * relative to the current page.
	 * 2. `__construct(int $pageNumber, CanvasPageLink $modelCanvasPageLink, string $pageName)`
	 * which expects a page number greater than zero, any CanvasPageLink object
	 * relative to the current page (to be used as a model) and a non-empty string
	 * representing the canonical name of the page relative to the current page.
	 *
	 * @throws CanvasPageLink_Exception INVALID_CONSTRUCTOR If $pageUrl or $pageName is empty or a non-string
	 * @throws CanvasPageLink_Exception INVALID_CONSTRUCTOR If $pageNumber is not a number greater than zero, $modelCanvasPageLink is not an instance of CanvasPageLink or $pageName is empty or a non-string
	 */
	public function __construct() {
		switch (func_num_args()) {
			case 2: { /* __construct($pageUrl, $pageName) */
				$pageUrl = func_get_arg(0);
				$this->name = func_get_arg(1);
				if (is_string($pageUrl) && !empty($pageUrl) && is_string($this->name) && !empty($this->name)) {
					$this->endpoint = preg_replace('%.*/api/v1(/.*)$%', '$1', parse_url($pageUrl, PHP_URL_PATH));
					parse_str(parse_url($pageUrl, PHP_URL_QUERY), $this->params);
				} else {
					throw new CanvasPageLink_Exception(
						'Expected two non-empty strings for page URL and name',
						CanvasPageLink_Exception::INVALID_CONSTRUCTOR
					);
				}
				break;
			}
			case 3: { /* __construct($pageNumber, $modelCanvasPageLink, $pageName) */
				$pageNumber = func_get_arg(0);
				$model = func_get_arg(1);
				$this->name = func_get_arg(2);
				if (is_int($pageNumber) && $pageNumber > 0 && $model instanceof CanvasPageLink && is_string($this->name) && !empty($this->name)) {
					$this->endpoint = $model->endpoint;
					$this->params = $model->params;
					switch($this->name) {
						case self::PREV: {
							$this->params[self::PARAM_PAGE_NUMBER] = $pageNumber - 1;
							break;
						}
						case self::NEXT: {
							$this->params[self::PARAM_PAGE_NUMBER] = $pageNumber + 1;
							break;
						}
						case self::FIRST: {
							$this->params[self::PARAM_PAGE_NUMBER] = 1;
							break;
						}
						case self::LAST:
						default: {
							throw new CanvasPageLink_Exception(
								"'{$this->name}' cannot be converted to a page number",
								CanvasPageLink_Exception::INVALID_CONSTRUCTOR
							);
						}
					}
				} else {
					throw new CanvasPageLink_Exception(
						'Expected a page number, a model CanvasPageLink object and a non-empty string page name',
						CanvasPageLink_Exception::INVALID_CONSTRUCTOR
					);
				}
				break;
			}
		}
	}
	
	/**
	 * Canonical name of this page link
	 *
	 * @return string
	 **/	
	public function getName() {
		return $this->name;
	}

	/**
	 * API endpoint being paginated
	 *
	 * @return string
	 **/
	public function getEndpoint() {
		return $this->endpoint;
	}
	
	/**
	 * Query parameters to retrieve the linked page
	 *
	 * @return array 
	 **/
	public function getParams() {
		return $this->params;
	}
	
	/**
	 * The (1-indexed) page number of this page
	 *
	 * @return int
	 **/
	public function getPageNumber() {
		if (is_numeric($this->params[self::PARAM_PAGE_NUMBER])) {
			return $this->params[self::PARAM_PAGE_NUMBER];
		} elseif ($this->params[self::PARAM_PAGE_NUMBER] == 'first') {
			return 1; /* weirdly, api/v1/users/<user_id>/logins returns first instead of page numbers */
		} else {
			return 1; // FIXME probably not good to assume that this is safe
		}
	}
	
	/**
	 * The number of responses per page generating this pagination
	 *
	 * @return int
	 **/
	public function getPerPage() {
		return $this->params[self::PARAM_PER_PAGE];
	}
	
	/**
	 * An array representation of the CanvasArray
	 *
	 * @return array
	 **/
	public function getArrayCopy() {
		$arr = array();
		$_key = $this->key;
		$_page = $this->page;
		foreach($this as $obj) {
			$arr[] = $obj->getArrayCopy();
		}
		$this->page = $_page;
		$this->key = $_key;
		return $arr;
	}
}