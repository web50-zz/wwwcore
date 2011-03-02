<?php
/**
*	Интерфейс данных "Точки вывода UI"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_ui_view_point extends data_interface
{
	public $title = 'Точки вывода UI';

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
	protected $name = 'ui_view_point';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'page_id' => array('type' => 'integer', 'alias' => 'pid'),
		'order' => array('type' => 'integer'),		// Порядок отображения ViewPoint`а
		'deep_hide' => array('type' => 'integer'),	// Скрывать ViewPoint на подстраницах
		'has_structure' => array('type' => 'integer'),	// Имеет собственную структуру
		'view_point' => array('type' => 'integer'),
		'title' => array('type' => 'string'),
		'ui_name' => array('type' => 'string'),
		'ui_call' => array('type' => 'string'),
		'ui_configure' => array('type' => 'string'),
		'cache_enabled' => array('type' => 'integer'),
		'cache_timeout' => array('type' => 'integer'),
	);
	
	public function __construct () {
	    // Call Base Constructor
	    parent::__construct(__CLASS__);
	}

	protected function sys_apply()
	{
		$this->_flush();
		$recs = $this->_get();
		foreach ($recs as $rec)
		{
			$ui_configure= array_merge((array)json_decode($rec->ui_configure, true), (array)json_decode($this->get_args('ui_configure'), true));
			$this->push_args(array(
				'_sid' => $rec->id,
				'ui_configure' => json_encode($ui_configure)
			));
			$this->_set();
			$this->pop_args();
		}
		response::send(array('success' => true), 'json');
	}
	
	/**
	*	Список записей
	*/
	protected function sys_list()
	{
		$this->_flush(true);
		$in = $this->join_with_di('interface', array('ui_name' => 'name'), array('type' => 'type'));
		$this->set_order('view_point');
		$this->set_order('order');
		$this->set_order('human_name', 'ASC', $in);
		$this->set_args(array('_stype' => 'ui'), true);
		$this->extjs_grid_json(array('id', 'view_point', 'title', 'ui_name', 'ui_call', 'ui_configure', 'order', 'has_structure', 'deep_hide', 'cache_enabled', 'cache_timeout',
			array('di' => $in, 'name' => 'human_name')
		));
	}

	/**
	*	Получить конфигурацию страницы в виде JSON
	* @access protected
	*/
	protected function sys_page_configuration()
	{
		$this->_flush();
		$this->set_order('view_point', 'ASC');
		response::send(array(
			'success' => true,
			'data' => $this->_get()
		), 'json');
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
		$this->extjs_set_json();
	}

	/**
	*	Сохранить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	protected function sys_mset()
	{
		$records = (array)json_decode(stripslashes($this->get_args('records')), true);

		foreach ($records as $record)
		{
			$record['_sid'] = $record['id'];
			unset($record['id']);
			$this->_flush();
			$this->push_args($record);
			$this->insert_on_empty = false;
			$data = $this->extjs_set_json(false);
			if ($data['success'] == false) response::send($data, 'json');
			$this->pop_args();
		}

		response::send(array('success' => true), 'json');
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
