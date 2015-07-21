<?php
	
/**
 * An object to represent pagination information
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class Pagination {
	
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
				$this->endpoint = preg_replace('%.*/api/v1(/.*)$%', '$1', parse_url($pageUrl, PHP_URL_PATH);
				$this->params = parse_url($pageUrl, PHP_URL_QUERY);
				break;
			}
			case 3: {
				$pageNumber = func_get_arg(0);
				$model = func_get_arg(1);
				$this->name = func_get_arg(2);
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
					case self::FIRST:
					case self::LAST:
					default: {
						throw new Pagination_Exception();
					}
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
 * All exceptions thrown by Pagination objects
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class Pagination_Exception extends CanvasPest_ResponseList_Exception {}

?>