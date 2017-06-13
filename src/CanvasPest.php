<?php

/** smtech\CanvasPest\CanvasPest */

namespace smtech\CanvasPest;

/**
 * An object to handle interactions with the Canvas API.
 *
 * For more information on the Canvas API refer to the offical
 * {@link https://canvas.instructure.com/doc/api/ Canvas API documentation} or
 * to the (slightly more up-to-date and pleasingly interactive)
 * {@link https://canvas.instructure.com/doc/api/live live documentation}.
 *
 * You can access the live documentation for your own Canvas instance and make
 * actual API calls to it at `https://<path-to-your-instance>/doc/api/live`
 *
 * @author Seth Battis <SethBattis@stmarksschool.org>
 **/
class CanvasPest extends \Battis\Educoder\Pest
{

    /** Name of the parameter controlling the number of responses per page */
    const PARAM_PER_PAGE = 'per_page';

    /** @var string[] $headers Additional headers to be passed to the API with each call */
    protected $headers;

    /**
     * Construct a new CanvasPest
     *
     * @api
     *
     * @param string $apiInstanceUrl URL of the API instance (e.g.
     *        `'https://canvas.instructure.com/api/v1'`)
     * @param string $apiAuthorizationToken (Optional) API access token for the
     *        API instance (if not provided now, it will need to be provided later)
     *
     * @see CanvasPest::setupToken() To configure the API access token later
     **/
    public function __construct($apiInstanceUrl, $apiAuthorizationToken = null)
    {
        parent::__construct($apiInstanceUrl);
        if (!empty($apiAuthorizationToken)) {
            $this->setupToken($apiAuthorizationToken);
        }
    }

    /**
     * Set up a new API access token to access this instance
     *
     * @param string $token API access token
     *
     * @throws CanvasPest_Exception INVALID_TOKEN on an empty or non-string token value
     **/
    public function setupToken($token)
    {
        if (!empty($token)) {
            $this->headers['Authorization'] = "Bearer $token";
        } elseif ($this->throw_exceptions) {
            throw new CanvasPest_Exception(
                'API authorization token must be a non-zero-length string',
                CanvasPest_Exception::INVALID_TOKEN
            );
        }
    }

    /**
     * Preprocess API call parameters before use
     *
     * Force maximum response page size, if not already defined.
     *
     * @param string[] $data Array of parameters for the next API call
     *
     * @return string[] Updated array of parameters
     *
     * @see CanvasArray::MAXIMUM_PER_PAGE Maximum number of responses per page
     * @see CanvasPest::PARAM_PER_PAGE Page size parameter
     **/
    private function preprocessData($data)
    {
        if (is_array($data) && !array_key_exists(self::PARAM_PER_PAGE, $data)) {
            $data[self::PARAM_PER_PAGE] = CanvasArray::MAXIMUM_PER_PAGE;
        }
        return $data;
    }

    /**
     * Prepare API request headers
     *
     * Flatten headers from an associative array to a numerically indexed array
     * of `"Name: Value"` style entries like `CURLOPT_HTTPHEADER` expects.
     * Numerically indexed arrays are not modified.
     *
     * Extended by CanvasPest to include the API access token in the
     * `Authorization` header.
     *
     * @param string[] $headers
     * @return string[]
     **/
    protected function prepHeaders($headers)
    {
        return parent::prepHeaders(array_merge($this->headers, $headers));
    }

    /**
     * Parse the API response into an object (or collection of objects).
     *
     * For queries to individually identified endpoints (e.g.
     * `accounts/1/users/123`), return a CanvasObject representing the API response
     * describing _that_ individually identified object affected by the query.
     *
     * For queries to generic endpoints (e.g. `accounts/1/users`), return a
     * traversable CanvasArray (of CanvasObjects) representing the API response
     * describing the list of objects affected by the query.
     *
     * @param string $response JSON-encoded response from the API
     *
     * @return CanvasObject|CanvasArray
     **/
    protected function postprocessResponse($response)
    {
        if (substr($response, 0, 1) == '{') {
            return new CanvasObject($response);
        } elseif (substr($response, 0, 1) == '[') {
            return new CanvasArray($response, $this);
        } elseif ($this->throw_exceptions) {
            throw new CanvasPest_Exception(
                $response,
                CanvasPest_Exception::INVALID_JSON_RESPONSE
            );
        }
    }

    /**
     * Reformat query parameters for Canvas
     *
     * Specifically, Canvas expects no numeric indices for base array parameters.
     *
     * @param mixed $data
     *
     * @return string
     * @codingStandardsIgnoreStart
     **/
    protected function http_build_query($data)
    {
        // @codingStandardsIgnoreEnd
        return preg_replace('/%5B\d+%5D/simU', '[]', http_build_query($data));
    }

    /**
     * Make a GET call to the API
     *
     * For queries to individually identified endpoints (e.g.
     * `accounts/1/users/123`), return a CanvasObject representing the API response
     * describing _that_ individually identified object affected by the query.
     *
     * For queries to generic endpoints (e.g. `accounts/1/users`), return a
     * traversable CanvasArray (of CanvasObjects) representing the API response
     * describing the list of objects affected by the query.
     *
     * @api
     *
     * @param string $path Path to the API endpoint of this call
     * @param string|string[] $data (Optional) Query parameters for this call
     * @param string|string[] $headers (Optional) Any additional HTTP headers for this call
     *
     * @return CanvasObject|CanvasArray
     **/
    public function get($path, $data = array(), $headers = array())
    {
        return $this->postprocessResponse(
            parent::get($path, $this->preprocessData($data), $headers)
        );
    }

    /**
     * Make a POST call to the API
     *
     * For queries to individually identified endpoints (e.g.
     * `accounts/1/users/123`), return a CanvasObject representing the API response
     * describing _that_ individually identified object affected by the query.
     *
     * @api
     *
     * @param string $path Path to the API endpoint of this call
     * @param string|string[] $data (Optional) Query parameters for this call
     * @param string|string[] $headers (Optional) Any additional HTTP headers for this call
     *
     * @return CanvasObject
     **/
    public function post($path, $data = array(), $headers = array())
    {
        return $this->postprocessResponse(
            parent::post($path, $this->preprocessData($data), $headers)
        );
    }

    /**
     * Make a PUT call to the API
     *
     * For queries to individually identified endpoints (e.g.
     * `accounts/1/users/123`), return a CanvasObject representing the API response
     * describing _that_ individually identified object affected by the query.
     *
     * @api
     *
     * @param string $path Path to the API endpoint of this call
     * @param string|string[] $data (Optional) Query parameters for this call
     * @param string|string[] $headers (Optional) Any additional HTTP headers for this call
     *
     * @return CanvasObject
     **/
    public function put($path, $data = array(), $headers = array())
    {
        return $this->postprocessResponse(
            parent::put($path, $this->preprocessData($data), $headers)
        );
    }

    /**
     * Make a DELETE call to the API
     *
     * For queries to individually identified endpoints (e.g.
     * `accounts/1/users/123`), return a CanvasObject representing the API response
     * describing _that_ individually identified object affected by the query.
     *
     * @api
     *
     * @param string $path Path to the API endpoint of this call
     * @param string|string[] $data (Optional) Query parameters for this call
     * @param string|string[] $headers (Optional) Any additional HTTP headers for this call
     *
     * @return CanvasObject
     **/
    public function delete($path, $data = array(), $headers = array())
    {
        if (!empty($data)) {
            $pathData = [];
            $pos = strpos($path, '?');
            if ($pos !== false) {
                parse_str(substr($path, $pos + 1), $pathData);
                $path = substr($path, 0, $pos);
            }
            $path .= '?' . $this->http_build_query(array_merge($pathData, $data));
        }
        return $this->postprocessResponse(
            parent::delete($path, $headers)
        );
    }

    /**
     * Make a PATCH call to the API
     *
     * @deprecated The Canvas API does not currently support PATCH calls
     *
     * @param string $path Path to the API endpoint of this call
     * @param string|string[] $data (Optional) Query parameters for this call
     * @param string|string[] $headers (Optional) Any additional HTTP headers for this call
     *
     * @return void
     *
     * @throws CanvasPest_Exception UNSUPPORTED_METHOD All calls to this method will cause an exception
     **/
    public function patch($path, $data = array(), $headers = array())
    {
        if ($this->throw_exceptions) {
            throw new CanvasPest_Exception(
                'The Canvas API does not support the PATCH method',
                CanvasPest_Exception::UNSUPPORTED_METHOD
            );
        }
    }
}
