<?php defined('SYSPATH') or die('No direct script access.');
/**
 * User model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_User extends Jelly_Model {

	/**
	 * Access to leave comments
	 */
	const ACCESS_COMMENT = 'comment';

	/**
	 * Access to edit user
	 */
	const ACCESS_EDIT = 'edit';

	/**
	 * Access to view profile
	 */
	const ACCESS_VIEW = 'view';

	/**
	 * @var  Cache  cache instance
	 */
	protected static $_cache;

	/**
	 * @var  array  Static cache of Model_Users loaded
	 */
	protected static $_users = array();

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$visitor = Visitor::instance();

		$meta
			->fields(array(
				'id' => new Field_Primary,
				'username' => new Field_String(array(
					'unique' => true,
					'rules'  => array(
						'not_empty'  => array(true),
						'min_length' => array(max((int)$visitor->get_config('username.length_min'), 1)),
						'max_length' => array(min((int)$visitor->get_config('username.length_max'), 30)),
						'regex'      => array('/^[' . $visitor->get_config('username.chars') . ']+/ui'),
					)
				)),
				'username_clean' => new Field_String(array(
					'unique' => true,
					'rules' => array(
						'not_empty' => array(true),
					)
				)),
				'password' => new Field_Password(array(
					'hash_with' => array($visitor, 'hash_password'),
					'rules' => array(
						'not_empty'  => array(true),
						'min_length' => array(5),
					)
				)),
				'password_confirm' => new Field_Password(array(
					'in_db' => false,
					'callbacks' => array(
						'matches' => array('Anqh_Model_User', '_check_password_matches')
					),
					'rules' => array(
						'not_empty'  => array(true),
						'min_length' => array(max((int)$visitor->get_config('password.length_min'), 1)),
					)
				)),
				'email' => new Field_Email(array(
					'unique' => true,
				)),

				'name' => new Field_String(array(
					'rules' => array(
						'min_length' => array(1),
						'max_length' => array(50),
					)
				)),
				'address_street' => new Field_String(array(
					'rules' => array(
						'max_length' => array(50),
					)
				)),
				'address_zip' => new Field_String(array(
					'rules' => array(
						'min_length' => array(4),
						'max_length' => array(5),
						'digit'      => array()
					)
				)),
				'address_city' => new Field_String(array(
					'rules' => array(
						'max_length' => array(50)
					)
				)),
				'city' => new Field_BelongsTo,
				'dob'  => new Field_String,
				'gender' => new Field_Enum(array(
					'choices' => array('m', 'f')
				)),

				//'image' => new Field_File()          => array('upload::valid', 'upload::type[gif,jpg,png]', 'upload::size[400K]'),

				'images' => new Field_ManyToMany,
				'friends' => new Field_HasMany,

				'last_login' => new Field_Timestamp,

				'tokens' => new Field_HasMany(array(
					'foreign' => 'user_token'
				)),
				'roles' => new Field_ManyToMany,

			));
	}

	/*
	// ORM
	protected $has_many         = array('events', 'friends', 'tokens', 'user_comments');
	protected $has_many_through = array('events' => 'favorites');
	//protected $has_one          = array('city', 'default_image' => 'image');
	protected $belongs_to       = array('default_image' => 'image');
	protected $reload_on_wakeup = false;

	// Cached data
	protected $data_friends;
	*/

	/**
	 * Magic setter
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 */
	public function __set($key, $value)	{
		switch ($key) {

			// Date of birth
			case 'dob':
				$value = date::format(date::DATE_SQL, $value);
				break;

			// Always lowercase e-mail
			case 'email':
				$value = utf8::strtolower($value);
				break;

		}

		parent::__set($key, $value);
	}


	/**
	 * Validate callback wrapper for checking password match
	 *
	 * @param  Validate  $array
	 * @param  string    $field
	 */
	public static function _check_password_matches(Validate $array, $field) {
		if ($array['password'] !== $array[$field]) {
			$array->error($field, 'matches', array('param1' => 'password'));
		}
	}

	/***** AUTH *****/

	/**
	 * Validates an array for a matching password and password_confirm field.
	 *
	 * @param  array    values to check
	 * @param  string   save the user if
	 * @return boolean
	 */
	public function change_password(array &$array, $save = false) {

		if ($status = $this->validate($array, false, array(), array(), array('rules' => 'password'))) {
			// Change the password
			$this->password = $array['password'];

			if ($save !== false && $status = $this->save()) {
				if (is_string($save)) {
					// Redirect to the success page
					url::redirect($save);
				}
			}
		}

		return $status;
	}


	/**
	 * Validates login information from an array, and optionally redirects
	 * after a successful login.
	 *
	 * @param  array    values to check
	 * @param  string   URI or URL to redirect to
	 * @return boolean
	 */
	public function login(array &$array, $redirect = false) {

		// Login starts out invalid
		$status = false;

		// Log login attempt
		$login = new Login_Model();
		$login->password = !empty($array['password']);
		$login->username = $array['username'];

		if ($this->validate($array, false, array(), array(), array('rules' => 'login'))) {

			// Attempt to load the user
			$this->find_user($array['username']);
			if ($this->loaded()) {
				$login->uid = $this->id;
				$login->username = $this->username;

				if (Visitor::instance()->login($this, $array['password'])) 	{
					$login->success = 1;

					// Redirect after a successful login
					if (is_string($redirect))	{
						$login->save();
						url::redirect($redirect);
					}

					// Login is successful
					$status = true;

				} else {
					$array->add_error('username', 'invalid');
				}
			}
		}

		$login->save();
		return $status;
	}

	/***** /AUTH *****/


	/***** COMMENTS *****/

	/**
	 * Call after adding/deleting comment
	 */
	public function clear_comment_cache() {
		for ($page = 1; $page <= User_Comment_Model::$cache_max_pages; $page++) {
			$this->cache->delete($this->cache->key('comments', $this->id, $page));
		}
	}


	/**
	 * Get user's total comment count
	 *
	 * @return  int
	 */
	public function get_comment_count() {
		return (int)ORM::factory('user_comment')->where('user_id', '=', $this->id)->count_all();
	}


	/**
	 * Get user's comments
	 *
	 * @param  int    $page_num
	 * @param  int    $page_size
	 * @param  mixed  $user  Viewer
	 */
	public function find_comments($page_num, $page_size = 25, $user = null) {
		$user = self::find_user($user);

		// Try to fetch from cache first
		/*
		$cache_key = $this->cache->key('comments', $this->id, $page_num);
		if ($page_num <= User_Comment_Model::$cache_max_pages) {
			$comments = $this->cache->get($cache_key);
		}

		// Did we find any comments?
		if (!empty($comments)) {

			// Found from cache
			$comments = unserialize($comments);

		} else {
		*/

			// Not found from cache, load from DB
			$page_offset = ($page_num - 1) * $page_size;
			if ($user && $user->id == $this->id) {

				// All comments, my profile
				$comments = $this->user_comments->find_all($page_size, $page_offset);

			} else if ($user) {

				// Public and my comments
				$comments = $this->user_comments->and_open()->where('private', '=', 0)->or_where('author_id', '=', $user->id)->close()->find_all($page_size, $page_offset);

			} else {

				// Only public comments
				$comments = $this->user_comments->where('private', '=', 0)->find_all($page_size, $page_offset);

			}

			/*
			// cache only 3 first pages
			if ($page_num <= User_Comment_Model::$cache_max_pages) {
				$this->cache->set($cache_key, serialize($comments->as_array()), null, User_Comment_Model::$cache_max_age);
			}
		}
			*/

		return $comments;
	}

	/***** /COMMENTS *****/


	/***** EXTERNAL ACCOUNTS *****/

	/**
	 * Get 3rd party account by external id
	 *
	 * @param   string  $id
	 * @return  User_External_Model
	 */
	public function find_external_by_id($id) {
		return ORM::factory('user_external')->where(array('user_id' => $this->id, 'id' => $id))->find();
	}


	/**
	 * Get 3rd party account by external provider
	 *
	 * @param   string  $provider
	 * @return  User_External_Model
	 */
	public function find_external_by_provider($provider) {
		return ORM::factory('user_external')->where(array('user_id' => $this->id, 'provider' => $provider))->find();
	}


	/**
	 * Load one user by 3rd party account id
	 *
	 * @param   string  $id
	 * @param   string  $provider
	 * @return  User_Model
	 */
	public static function find_user_by_external($id, $provider) {
		$external_user = ORM::factory('user_external')->where(array('id' => $id, 'provider' => $provider))->find();

		return ($external_user->loaded()) ? $external_user->user : new User_Model();
	}


	/**
	 * Connect 3rd party account
	 *
	 * @param  string  $id
	 * @param  string  $provider
	 */
	public function map_external($id, $provider) {

		// Are we already connected?
		$external_user = $this->find_external_by_id($id);

		if ($this->loaded() && !$external_user->loaded()) {
			$external = new User_External_Model();
			$external->user_id = $this->id;
			$external->id = $id;
			$external->provider = $provider;
			$external->stamp = time();

			return $external->save();
		}

		return false;
	}

	/***** /EXTERNAL ACCOUNTS *****/


	/***** FRIENDS *****/

	/**
	 * Create friendship
	 *
	 * @param  User_Model  $friend
	 */
	public function add_friend(User_Model $friend) {

		// don't add duplicate friends or oneself
		if ($this->loaded() && $this->id != $friend->id && !$this->is_friend($friend)) {
			$friendship = new Friend_Model();
			$friendship->user_id = $this->id;
			$friendship->friend_id = $friend->id;
			$friendship->save();
			return true;
		}

		return false;
	}


	/**
	 * Delete friendship
	 *
	 * @param  User_Model  $friend
	 */
	public function delete_friend(User_Model $friend) {
		return $this->loaded()
			&& $this->is_friend($friend)
			&& (bool)count(db::build()
				->delete('friends')
				->where('user_id', '=', $this->id)
				->where('friend_id', '=', $friend->id)
				->execute());
	}


	/**
	 * Get user's friends
	 *
	 * @param   integer  $page_num
	 * @param   integer  $page_size
	 * @return  ORM_Iterator
	 */
	public function find_friends($page_num = 1, $page_size = 25) {
		$page_offset = ($page_num - 1) * $page_size;
		$friends = ORM::factory('friend')->where('user_id', '=', $this->id)->find_all($page_size, $page_offset);

		return $friends;
	}


	/**
	 * Get user's total friend count
	 *
	 * @return  int
	 */
	public function get_friend_count() {
		return (int)ORM::factory('friend')->where('user_id', '=', $this->id)->count_all();
	}


	/**
	 * Check for friendship
	 *
	 * @param  mixed  $friend  id, User_Model
	 */
	public function is_friend($friend) {
		if (empty($friend)) {
			return false;
		}

		// Load friends
		if (!is_array($this->data_friends)) {
			$friends = array();
			if ($this->loaded()) {
				$users = db::build()->select('friend_id')->from('friends')->where('user_id', '=', $this->id)->execute()->as_array();
				foreach ($users as $user) {
					$friends[(int)$user['friend_id']] = (int)$user['friend_id'];
				}
			}
			$this->data_friends = $friends;
		}

		if ($friend instanceof User_Model) {
			$friend = $friend->id;
		}

		return isset($this->data_friends[(int)$friend]);
	}

	/***** /FRIENDS *****/


	/**
	 * Load one user.
	 *
	 * @param   mixed  $user  user_id, username, email, User_Model or false for current session
	 * @return  Model_User
	 */
	public static function find_user($id = false) {
		static $session = false;

		$user = null;
		$cache = false;

		// Try user models first (User_Model, session)
		if ($id instanceof Model_User) {

			// Model_User
			$user = $id;

		} else if ($id === false) {

			// Current session, fetch only once
			if ($session === false) {
				$session = Visitor::instance()->get_user();
			}
			$user = $session;

		}

		// Then try others (user_id, email, username_clean)
		if (!$user && $id !== true && !empty($id)) {
			if (is_numeric($id) || empty($id)) {
				$id = (int)$id;
			} else if (is_array($id)) {
				$id = (int)$id['id'];
			} else if (Validate::email($id)) {
				$id = mb_strtolower($id);
			} else {
				$id = Text::clean($id);
			}
			if (isset(self::$_users[$id])) {

				// Found from static cache
				return self::$_users[$id];

			} else if ($user = Cache::instance()->get('user_' . $id)) {

				// Found from cache
				$user = unserialize($user);

			} else {

				// Not found from caches, try db
				if (is_int($id)) {
					$user = Jelly::select('user', $id);
				} else {
					$user = Jelly::select('user')->where(Validate::email($id) ? 'email' : 'username_clean', '=', $id)->limit(1)->execute();
				}
				$cache = true;

			}
		}

		// If user found, add to cache(s)
		if ($user && $user->loaded()) {
			self::$_users[$user->id] = self::$_users[Text::clean($user->username)] = self::$_users[mb_strtolower($user->email)] = $user;
			if ($cache) {
				Cache::instance()->set('user_' . $user->id, serialize($user), 3600);
			}
		}

		return $user;
	}


	/**
	 * Check if user has access to the user
	 *
	 * @param  string          $type  'read', 'write' etc
	 * @param  int|User_Model  $user  current user on false
	 */
	public function has_access($type, $user = false) {
		static $cache = array();

		$user = ORM::factory('user')->find_user($user);
		$cache_id = sprintf('%d_%s_%d', $this->id, $type, $user ? $user->id : 0);

		if (!isset($cache[$cache_id])) {
			$access = false;
			switch ($type) {

				// Access to comment
				case self::ACCESS_COMMENT:
					$access = ($user !== null);
					break;

				// Access to edit profile
				case self::ACCESS_EDIT:
					$access = ($user && $user->id == $this->id);
					break;

				// Access to view profile
				case self::ACCESS_VIEW:
					// TODO: ignore
					$access = true;
					break;

			}
			$cache[$cache_id] = $access;
		}

		return $cache[$cache_id];
	}


	/**
	 * Does the user have any of these roles
	 *
	 * @param  array|string  $roles
	 */
	public function has_role($roles) {
		foreach ($this->roles as $role) {
			if (is_array($roles) && in_array($role->name, $roles)
				|| is_numeric($roles) && $role->id == $roles
				|| is_string($roles) && $role->name == $roles) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Allows a model to be loaded by username or email address.
	 *
	 * @param   mixed  $id  id, username, email
	 * @return  string
	 */
	public function unique_key($id)	{
		if (!empty($id) && is_string($id) && ! ctype_digit($id)) {
			return valid::email($id) ? 'email' : 'username';
		}

		return parent::unique_key($id);
	}


}