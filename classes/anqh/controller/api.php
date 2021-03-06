<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh API controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_API extends Controller {

	const FORMAT_JSON = 'json';
	const FORMAT_XML  = 'xml';

	/**
	 * @var  string  Output format
	 */
	protected $format;

	/**
	 * @var  array  Available output formats
	 */
	public static $_formats = array(self::FORMAT_JSON, self::FORMAT_XML);

	/**
	 * @var  array  Data to be returned
	 */
	protected $data = array();

	/**
	 * @var  string  API version
	 */
	protected $version;

	/**
	 * @var  array  Available versions
	 */
	public static $_versions = array('v1');


	/**
	 * Construct controller
	 */
	public function before() {

		// Log request
		Jelly::factory('api_request')->set(array(
			'ip'      => Request::$client_ip,
			'request' => $this->request->uri . (empty($_GET) ? '' : '?' . http_build_query($_GET)),
		))->save();

		// Rate limit
		$rate_span = Kohana::config('api.rate_span');
		$rate_limit = Kohana::config('api.rate_limit');
		$requests = Model_API_Request::request_count(time() - $rate_span, Request::$client_ip);
		$requests_left = $rate_limit - $requests;
		if ($requests_left < 0) {
			throw new Controller_API_Exception('Request limit reached');
		}

		// Check version
		$this->version = $this->request->param('version');
		if (!in_array($this->version, self::$_versions)) {
			throw new Controller_API_Exception('Invalid version');
		}

		// Check format
		$this->format = $this->request->param('format');
		!$this->format and $this->format = self::FORMAT_JSON;
		if (!in_array($this->format, self::$_formats)) {
			throw new Controller_API_Exception('Invalid format');
		}

		// Set result defaults
		$this->data = array(
			'version' => $this->version,
			'requests' => $requests,
			'requests_left' => $requests_left,
			'request_window' => $rate_span,
		);

		return parent::before();
	}


	public function after() {
		switch ($this->format) {

			case self::FORMAT_JSON:
		    $this->request->headers['Content-Type'] = 'application/json';
		    $this->request->response = json_encode($this->data);
		    break;

			case self::FORMAT_XML:
		    $this->request->headers['Content-Type'] = 'application/xml';
		    $this->request->response = Arr::xml($this->data);
		    break;

		}
	}


	/**
	 * Prepare order parameters
	 *
	 * @param   string  $order
	 * @param   array   $orderable  Orderable fields
	 * @return  array
	 */
	protected function _prepare_order($order, array $orderable = null) {
		$orders = array();

		// Build order array, field:order => field => order
		foreach (explode(':', $order) as $_order) {
			$_order = explode('.', $_order);
			if (empty($orderable) || in_array($_order[0], $orderable)) {
				$orders[$_order[0]] = isset($_order[1]) && ($_order[1] == 'asc' || $_order[1] == 'desc') ? $_order[1] : 'asc';
			}
		}

		return $orders;
	}

}
