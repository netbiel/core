<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Permission Exception
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Permission_Exception extends Kohana_Exception {

	const ERROR_LEVEL = 'WARNING';


	/**
	 * Permission denied
	 *
	 * @param  Jelly_Model $model
	 * @param  integer     $id
	 * @param  string      $permission
	 */
	public function __construct(Jelly_Model $model, $id = 0, $permission = null) {
		parent::__construct("Permission ':permission' denied: :model #:id", array(
			':id'         => $id,
			':model'      => Jelly::model_name($model),
			':permission' => $permission,
		));
	}

}
