<?php
/**
*	Data interface "subscribe messages"
*
* @author 9*	
* @package	SBIN Diesel
*/
class di_subscribe_messages extends data_interface
{
	public $title = 'Subscribe messages';

	/**
	* @var	string	$cfg	DB configuration`s name
	*/
	protected $cfg = 'localhost';
	
	/**
	* @var	string	$db	The name of data base
	*/
	protected $db = 'db1';
	
	/**
	* @var	string	$name	The name of table
	*/
	protected $name = 'subscribe_messages';

	/**
	* @var	array	$user	Current logged in user`s data
	*/
	protected $user = array();
	
	/**
	* @var	array	$fields	The fields configuration
	*/
	public $fields = array(
			'id' => array('type' => 'integer', 'serial' => TRUE, 'protected' => FALSE),
			'subscr_created_datetime' => array('type' => 'string'),
			'subscr_changed_datetime' => array('type' => 'string'),
			'subscr_deleted_datetime' => array('type' => 'string'),
			'subscr_creator_uid' => array('type' => 'string'),
			'subscr_changer_uid' => array('type' => 'string'),
			'subscr_deleter_uid' => array('type' => 'string'),
			'subscr_title' => array('type' => 'string'),
			'subscr_message_body' => array('type' => 'string'),
			'subscr_id' => array('type' => 'string'),
			'subscr_sheduled_to_send' => array('type' => 'integer'),
			'subscr_sended_flag' => array('type' => 'integer'),
			'subscr_sended_datetime' => array('type' => 'string'),
		);
	
	public function __construct () {
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}

	/**
	*	Get records
	* @access protected
	*/
	protected function sys_list()
	{
		$this->extjs_grid_json(array('id', 'subscr_created_datetime', 'subscr_title','subscr_id'));
	}
	
	/**
	*	Get record
	* @access protected
	*/
	protected function sys_get()
	{
		$this->_flush();
		$this->extjs_form_json(array('id',
						'subscr_created_datetime',
						'subscr_changed_datetime',
						'subscr_deleted_datetime',
						'subscr_creator_uid',
						'subscr_changer_uid',
						'subscr_deleter_uid',
						'subscr_title',
						'subscr_message_body',
						'subscr_id',
						'subscr_sheduled_to_send',
						'subscr_sended_flag',
						'subscr_sended_datetime'
						)
					);
	}
	
	/**
	*	Save record
	* @access protected
	*/
	protected function sys_set()
	{
		$this->_flush();
		$this->insert_on_empty = true;
		if ($this->get_args('_sid')>0)
		{
			$this->set_args(array('subscr_changed_datetime' => date('Y-m-d H:i:S')), true);
			$this->set_args(array('subscr_changer_uid' => UID), true);
		}
		else
		{
			$this->set_args(array('subscr_created_datetime' => date('Y-m-d H:i:S')), true);
			$this->set_args(array('subscr_changed_datetime' => date('Y-m-d H:i:S')), true);
			$this->set_args(array('subscr_changer_uid' => UID), true);
			$this->set_args(array('subscr_creator_uid' => UID), true);
		}
		if($this->get_args('subscr_sheduled_to_send') == 1)
		{
			$data_b = $this->extjs_form_json(array('subscr_sheduled_to_send','id'),false);
			if($data_b['data']['subscr_sheduled_to_send'] == '0')
			{
				$send_after_saving = true;
			}
		}
		$data = $this->extjs_set_json(false);
		if($send_after_saving == true)
		{
			$this->_send_message_now($this->get_args('_sid'));
		}
		response::send($data, 'json');
	}
	
	
	/**
	*	Delete user
	* @access protected
	*/
	protected function sys_unset()
	{
		$this->_flush();
		$data = $this->extjs_unset_json(false);
		response::send($data, 'json');
	}

	public function _send_message_now($id,$extras = array())
	{
		if($id>0)
		{
			$data = $this->_get('SELECT * FROM '.$this->name." WHERE id = $id");
			$sl = data_interface::get_instance('subscribe_accounts');
			$slist = $sl->_users_in_group($data[0]['subscr_id']);

		}
		else
		{
			$data[0] = array();
			$data[0]['subscr_message_body'] = $extras['body'];
			$data[0]['subscr_title'] = $extras['title'];
			$slist = $extras['recipients'];
		}
		foreach($slist as $key=>$value)
		{
			$headers = "From:<some@mail.ru>\r\nContent-Type: text/html;charset=\"windows-1251\"\r\nContent-Transfer-Encoding: 8bit"; 
			mail($value['email'],$data[0]['subscr_title'],$data[0]['subscr_message_body'],$headers);
		}
	}
}
?>
