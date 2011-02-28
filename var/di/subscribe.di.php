<?php
/**
*	Data Interface "Interfaces"
*
* @author 9*	
* @package	SBIN Diesel
*/
class di_subscribe extends data_interface
{
	public $title = 'The subscribe user`s groups';

	/**
	* @var	string	$cfg	DB configurations name
	*/
	protected $cfg = 'localhost';
	
	/**
	* @var	string	$db	DB name
	*/
	protected $db = 'db1';
	
	/**
	* @var	string	$name	Tables name
	*/
	protected $name = 'subscribe';
	
	/**
	* @var	array	$fields	Tables configuration
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'name' => array('type' => 'string'),
	);
	
	public function __construct () {
	    // Call Base Constructor
	    parent::__construct(__CLASS__);
	}
	
	/**
	*	Get records list in JSON
	* @access protected
	*/
	protected function sys_list()
	{
		$this->_flush();
		$this->extjs_grid_json();
	}
	
	/**
	*	Get record in JSON
	* @access protected
	*/
	protected function sys_get()
	{
		$this->_flush();
		$this->extjs_form_json();
	}
	
	/**
	*	Set data to storage and return results in JSON
	* @access protected
	*/
	protected function sys_set()
	{
		$this->_flush();
		$this->insert_on_empty = true;
		$this->extjs_set_json();
	}
	
	/**
	*	Unset data to storage and return results in JSON
	* @access protected
	*/
	protected function sys_unset()
	{
		$this->_flush();
		$data = $this->extjs_unset_json(false);

		// Remove all links between users and deleted groups
		$gu = data_interface::get_instance('subscribe_user');
		$ids = (array)$this->get_lastChangedId();
		foreach ($ids as $gid)
		{
			$gu->remove_users_from_group($gid);
		}

		response::send($data, 'json');
	}
}
?>
