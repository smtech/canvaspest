<?php
	
/**
 * An object to represent pagination information
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class CanvasPageLink {
	
	const FIRST = 'first';
	const LAST = 'last';
	const NEXT = 'next';
	const PREV = 'prev';
	
	const PARAM_PAGE_NUMBER = 'page';
	const PARAM_PER_PAGE = 'per_page';
	
	private $name;
	private $endpoint;
	private $params;
	
	public function __construct() {
		switch (func_num_args()) {
			case 2: {
				$pageUrl = func_get_arg(0);
				$this->name = func_get_arg(1);
				if (is_string($pageUrl) && !empty($pageUrl) && is_string($this->name) && !empty($this->name)) {
					$this->endpoint = preg_replace('%.*/api/v1(/.*)$%', '$1', parse_url($pageUrl, PHP_URL_PATH);
					$this->params = parse_url($pageUrl, PHP_URL_QUERY);
				} else {
					throw new CanvasPageLink_Exception(
						'Expected two non-empty strings for page URL and name',
						CanvasPageLink_Exception::INVALID_CONSTRUCTOR
					);
				}
				break;
			}
			case 3: {
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
	
	public function getName() {
		return $this->name;
	}

	public function getEndpoint() {
		return $this->endpoint;
	}
	
	public function getParams() {
		return $this->params;
	}
	
	public function getPageNumber() {
		return $this->params[self::PARAM_PAGE_NUMBER];
	}
	
	public function getPerPage() {
		return $this->params[self::PARAM_PER_PAGE];
	}
}

/**
 * All exceptions thrown by CanvasPageLink objects
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class CanvasPageLink_Exception extends CanvasArray_Exception {
	const INVALID_CONSTRUCTOR = 301;
}

?>