<?php
/**
*	Data Interface "Subscribe Users link to groups"
*
* @author	9*
* @package	SBIN Diesel
*/
class di_subscribe_user extends data_interface
{
	public $title = 'Link between subscribe users and subscribe groups';

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
	protected $name = 'subscribe_user';
	
	/**
	* @var	array	$fields	Tables configuration
	*/
	public $fields = array(
		'group_id' => array('type' => 'integer', 'alias' => 'gid'),
		'user_id' => array('type' => 'integer', 'alias' => 'uid'),
	);
	
	public function __construct ()
	{
	    // Call Base Constructor
	    parent::__construct(__CLASS__);
	}

	/**
	*	Remove all users from group
	* @param	integer	$gid	The group`s ID
	*/
	public function remove_users_from_group($gid)
	{
		$this->_flush();
		$this->set_args(array('_sgid' => $gid));
		$this->_unset();
	}

	/**
	*	Remove user from all groups
	* @param	integer	$uid	The user`s ID
	*/
	public function remove_user_from_groups($uid)
	{
		$this->_flush();
		$this->set_args(array('_suid' => $uid));
		$this->_unset();
	}

	/**
	*	Add user to group
	* @access protected
	*/
	protected function sys_add_users_to_group()
	{
		$success = $this->_add_users_to_group();
		response::send(array('success' => $success), 'json');
	}
	public function _add_users_to_group()
	{
		$success = true;
		$gid = $this->get_args('gid');
		$uids = split(',', $this->get_args('uids'));
		if (!empty($uids))
		{
			foreach ($uids as $uid)
			{
				$this->_flush();
				$this->insert_on_empty = true;
				$this->set_args(array(
					'uid' => $uid,
					'gid' => $gid
				));
				$this->_set();
			}
		}
		else
		{
			$success = false;
		}
		return $success;
	}
	/**
	*	Remove users from group
	* @access protected
	*/
	protected function sys_remove_users_from_group()
	{

		$success = $this->_remove_users_from_group();
		response::send(array('success' => $success), 'json');
	}

	public function _remove_users_from_group()
	{
		$success = true;
		$gid = $this->get_args('gid');
		$uids = split(',', $this->get_args('uids'));
		if (!empty($uids))
		{
			foreach ($uids as $uid)
			{
				$this->_flush();
				$this->set_args(array(
					'_suid' => $uid,
					'_sgid' => $gid
				));
				$this->_unset();
			}
		}
		else
		{
			$success = false;
		}

		return $success;
	}
}
?>
