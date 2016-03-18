<?php

/** smtech\CanvasPest\CanvasPestImmutable */

namespace smtech\CanvasPest;

/**
 * Treat the API as read-only.
 *
 * Without excessive editorializing, the permissions structure in Canvas bites.
 * For example, one can't create a user who has read-only access to the
 * complete API -- if a user has complete access to the API, they have
 * _complete_ access to the API, including the ability to alter and delete
 * information. This object provides a comparative level of safety, enforcing
 * a restriction on GET-only API calls.
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class CanvasPestImmutable extends CanvasPest {
	/**
	 * {@inheritDoc}
	 *
	 * @deprecated CanvasPestImmutable only supports GET calls to the API
	 *
	 * @return void
	 *
	 * @throws CanvasPestImmutable_Exception IMMUTABLE All calls to this method will cause an exception
	 **/
	public function put() {
		throw new CanvasPestImmutable_Exception(
			'Only GET calls to the API are allowed from CanvasPestImmutable.',
			CanvasPestImmutable_Exception::IMMUTABLE
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated CanvasPestImmutable only supports GET calls to the API
	 *
	 * @return void
	 *
	 * @throws CanvasPestImmutable_Exception IMMUTABLE All calls to this method will cause an exception
	 **/
	public function post() {
		throw new CanvasPestImmutable_Exception(
			'Only GET calls to the API are allowed from CanvasPestImmutable.',
			CanvasPestImmutable_Exception::IMMUTABLE
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @deprecated CanvasPestImmutable only supports GET calls to the API
	 *
	 * @return void
	 *
	 * @throws CanvasPestImmutable_Exception IMMUTABLE All calls to this method will cause an exception
	 **/
	public function delete() {
		throw new CanvasPestImmutable_Exception(
			'Only GET calls to the API are allowed from CanvasPestImmutable.',
			CanvasPestImmutable_Exception::IMMUTABLE
		);
	}
}