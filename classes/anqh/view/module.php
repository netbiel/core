<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * ViewMod
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_Module extends View {

	protected $_viewfile;

	/**
	 * Creates a new View Mod using the given parameters.
	 *
	 * @param   string  $name  view file name
	 * @param   array   $data  pre-load data
	 * @return  ViewMod
	 */
	public static function factory($name = null, array $data = null) {
		return new self($name, $data);
	}


	/**
	 * Wrap requested view inside module and render
	 *
	 * @return  string
	 */
	public function render($file = null) {
		return (string)View::factory('generic/mod', array(
			'class'      => 'mod ' . Arr::get_once($this->_data, 'mod_class', strtr(basename($this->_file, '.php'), '_', '-')),
			'id'         => Arr::get_once($this->_data, 'mod_id'),
			'actions'    => isset($this->_data['mod_actions'])  ? (string)View::factory('generic/actions', array('actions' => Arr::get_once($this->_data, 'mod_actions'))) : null,
			'actions2'   => isset($this->_data['mod_actions2']) ? (string)View::factory('generic/actions', array('actions' => Arr::get_once($this->_data, 'mod_actions2'))) : null,
			'title'      => Arr::get_once($this->_data, 'mod_title'),
			'subtitle'   => Arr::get_once($this->_data, 'mod_subtitle'),
			'pagination' => Arr::get_once($this->_data, 'pagination'),
			'content'    => parent::render($file),
		));
	}

}
