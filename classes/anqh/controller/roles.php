<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Roles controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Roles extends Controller_Template {

	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		if (!Visitor::instance()->logged_in('admin')) {
			throw new Permission_Exception(new Model_Role);
		}

	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$this->page_title = __('Roles');
		$this->page_actions[] = array('link' => Route::get('role')->uri(), 'text' => __('New role'), 'class' => 'role-add');

		Widget::add('main', View_Module::Factory('roles/roles', array('roles' => Jelly::select('role')->execute())));
	}


	/**
	 * Action: delete
	 */
	public function action_delete($role_id) {
		$this->history = false;

		$role_id = (int)$this->request->param('id');
		$role = Jelly::select('role', $role_id);
		if (!$role->loaded()) {
			throw new Model_Exception($role, $role_id);
		}
		Permission::required($role, Model_Role::PERMISSION_DELETE, self::$user);

		$role->delete();

		Request::back(Route::get('roles')->uri());
	}


	/**
	 * Action: edit
	 */
	public function action_edit() {
		$this->history = false;

		// Load role
		$role_id = (int)$this->request->param('id', 0);
		if ($role_id) {
			$role = Jelly::select('role', $role_id);
			if (!$role->loaded()) {
				throw new Model_Exception($role, $role_id);
			}
			Permission::required($role, Model_Role::PERMISSION_UPDATE, self::$user);
		} else {
			$role = Jelly::factory('role');
			Permission::required($role, Model_Role::PERMISSION_CREATE, self::$user);
		}

		// Handle post
		$errors = array();
		if ($_POST) {
			$role->set($_POST);
			try {
				$role->save();
				$this->request->redirect(Route::get('roles')->uri());
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}

		// Set title
		$this->page_title = __('Role') . ($role->name ? ': ' . HTML::chars($role->name) : '');

		// Set actions
		if ($role->loaded() && Permission::has($role, Model_Role::PERMISSION_DELETE, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($role, 'delete', false), 'text' => __('Delete role'), 'class' => 'role-delete');
		}

		// Build form
		$form = array(
			'values' => $role,
			'errors' => $errors,
			'cancel' => Request::back(Route::get('roles')->uri(), true),
			'groups' => array(
				array(
					'fields' => array(
						'name'        => array(),
						'description' => array(),
					)
				)
			)
		);
		//Widget::add('main', View_Module::factory('roles/edit', array('role' => $role, 'errors' => $errors)));
		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}

}
