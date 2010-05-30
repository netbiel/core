<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Simple Permission interface
 *
 * @interface
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
interface Anqh_Interface_Permission {

	/**
	 * Permission to create new object
	 */
	const PERMISSION_CREATE = 'create';

	/**
	 * Permission to delete the object
	 */
	const PERMISSION_DELETE = 'delete';

	/**
	 * Permission to read from object
	 */
	const PERMISSION_READ = 'read';

	/**
	 * Permission to update object
	 */
	const PERMISSION_UPDATE = 'update';


	/**
	 * Get object id
	 *
	 * @abstract
	 * @return  integer|string
	 */
	public function get_permission_id();


	/**
	 * Check permission
	 *
	 * @abstract
	 * @param   string      $permission
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function has_permission($permission, $user);

}