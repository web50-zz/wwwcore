<?php
/**
*  DI FAQ Разделы
*
* @author       9*	
* @package	SBIN Diesel
*/
class di_faq_parts extends data_interface
{
	public $title = 'FAQ parts';

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
	protected $name = 'faq_parts';

	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'faqp_title' => array('type' => 'string'),
		'faqp_created_datetime'=>array('type'=>'string'),
		'faqp_changed_datetime'=>array('type'=>'string'),
		'faqp_deleted_datetime'=>array('type'=>'string'),
		'faqp_deleter_uid'=>array('type'=>'integer'),
		'faqp_creator_uid'=>array('type'=>'integer'),
		'faqp_changer_uid'=>array('type'=>'integer'),
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
		$this->extjs_grid_json(array('id','faqp_title'));
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
		if ($this->get_args('_sid')>0)
		{
			$this->set_args(array('faqp_changed_datetime' => date('Y-m-d H:i:S')), true);
			$this->set_args(array('faqp_changer_uid' => UID), true);
		}
		else
		{
			$this->set_args(array('faqp_created_datetime' => date('Y-m-d H:i:S')), true);
			$this->set_args(array('faqp_changed_datetime' => date('Y-m-d H:i:S')), true);
			$this->set_args(array('faqp_changer_uid' => UID), true);
			$this->set_args(array('faqp_creator_uid' => UID), true);
		}

		$this->extjs_set_json();
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
