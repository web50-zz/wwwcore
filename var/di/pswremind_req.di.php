<?php
/**
*	Data Interface "Password reminder request log"
*
* @author 9*	
* @package	SBIN Diesel
*/
class di_pswremind_req extends data_interface
{
	public $title = 'The lost password recovery requests storage';

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
	protected $name = 'pswremind_req';
	
	/**
	* @var	array	$fields	Tables configuration
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'hash' => array('type' => 'string'),
		'done' => array('type' => 'integer'),
		'done_datetime' => array('type' => 'datetime'),
		'done_ip' => array('type' => 'string'),
		'req' => array('type' => 'string'),
		'req_datetime' => array('type' => 'datetime'),
		'req_ip' => array('type' => 'string'),
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
		$this->prepare_extras();
		$this->extjs_set_json();
	}
	public function prepare_extras()
	{
	
		if (!$this->args['_sid'])
		{
			$this->set_args(array('req_datetime' => date('Y-m-d H:i:S')), true);
			$this->set_args(array('req_ip' => $_SERVER['REMOTE_ADDR']), true);
			$this->set_args(array('hash' => md5($this->args['req'].date('Y-m-d H:i:S'))), true);
		}
		else
		{
			if($this->args['done'] == '1')
			{
				$this->set_args(array('done_datetime' => date('Y-m-d H:i:S')), true);
				$this->set_args(array('done_ip' => $_SERVER['REMOTE_ADDR']), true);
			}
		}
	}
	/**
	*	Unset data to storage and return results in JSON
	* @access protected
	*/
	protected function sys_unset()
	{
		$this->_flush();
		$data = $this->extjs_unset_json(false);
		response::send($data, 'json');
	}
}
?>
