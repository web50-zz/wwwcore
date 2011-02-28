<?php
/**
*	Интерфейс данных "Guestbook"
*
* @author       9*	
* @package	SBIN Diesel
*/
class di_guestbook extends data_interface
{
	public $title = 'Гостевая';

	/**
	* @var	string	$cfg	Имя конфигурации БД
	*/
	protected $cfg = 'localhost';
	
	/**
	* @var	string	$db	Имя БД
	*/
	protected $db = 'db1';
	
	/**
	* @var	string	$name	Имя таблицы
	*/
	protected $name = 'guestbook';

	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'gb_created_datetime' => array('type' => 'string'),
		'gb_changed_datetime' => array('type' => 'string'),
		'gb_deleted_datetime' => array('type' => 'string'),
		'gb_creator_uid' => array('type' => 'string'),
		'gb_changer_uid' => array('type' => 'string'),
		'gb_deleter_uid' => array('type' => 'string'),
		'gb_author_email' => array('type' => 'string'),
		'gb_author_name' => array('type' => 'string'),
		'gb_author_location' => array('type' => 'string'),
		'gb_record' => array('type' => 'text'),
		'gb_answer' => array('type' => 'text')
	);

	
	public function __construct ()
	{
	    // Call Base Constructor
	    parent::__construct(__CLASS__);
	}
	
	/**
	*	Получить данные элемента в виде JSON
	* @access protected
	*/
	public function get()
	{
		$this->_flush();
		$this->connector->fetchMethod = PDO::FETCH_ASSOC;
		return $this->_get();
	}

	/**
	*	Список записей
	*/
	protected function sys_list()
	{
		$this->_flush();
		$this->set_args(array(
					'sort'=>'id',
					'dir'=>'DESC',
				)
			);
		$this->extjs_grid_json(array('id','gb_creator_uid','gb_changer_uid','gb_deleter_uid','gb_created_datetime','gb_author_name','gb_author_location','gb_author_email','gb_record','gb_answer'));
	}
	
	/**
	*	Получить данные элемента в виде JSON
	* @access protected
	*/
	protected function sys_get()
	{
		$this->_flush();
		$this->extjs_form_json();
	}
	
	/**
	*	Получить данные элемента в виде JSON
	* @access protected
	*/
	protected function sys_item()
	{
		$this->_flush();
		$this->extjs_form_json();
	}
	
	/**
	*	Сохранить данные и вернуть JSON-пакет для ExtJS
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
	
		if ($this->args['_sid']>0)
		{
			$this->set_args(array('gb_changed_datetime' => date('Y-m-d H:i:S')), true);
			$this->set_args(array('gb_changer_uid' => UID), true);
		}
		else
		{
			$this->set_args(array('gb_created_datetime' => date('Y-m-d H:i:S')), true);
			$this->set_args(array('gb_changed_datetime' => date('Y-m-d H:i:S')), true);
			$this->set_args(array('gb_changer_uid' => UID), true);
			$this->set_args(array('gb_creator_uid' => UID), true);
		}
	}

	/**
	*	Удалить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	protected function sys_unset()
	{
		$this->_flush();
		$this->extjs_unset_json();
	}
}
?>
