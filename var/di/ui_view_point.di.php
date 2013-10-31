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
		$data = $this->get_list_data();
		response::send($data, 'json');
	}


	public function get_list_data()
	{
		$this->_flush(true);
		$in = $this->join_with_di('interface', array('ui_name' => 'name'), array('type' => 'type'));
		$this->set_order('view_point');
		$this->set_order('order');
		$this->set_order('human_name', 'ASC', $in);
		$this->set_args(array('_stype' => 'ui'), true);
		return $this->extjs_grid_json(array('id', 
							'view_point', 
							'title', 
							'ui_name',
							'page_id', 
							'ui_call', 
							'ui_configure', 
							'order', 
							'has_structure', 
							'deep_hide', 
							'cache_enabled', 
							'cache_timeout',
							array('di' => $in, 'name' => 'human_name')
							),
						false);
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
	*	Получить новый order для vp на указанной странице
	* @param	integer	$page_id	ID страницы
	* @param	integer	$vp_id		ID view point
	* @return	integer			Новыя порядковый номер
	*/
	public function get_new_order($page_id, $vp_id)
	{
		$this->_flush();
		$this->_get("SELECT MAX(`order`) + 1 AS `order` FROM `{$this->name}` WHERE `page_id` = " . intval($page_id) . " AND `view_point` = " . intval($vp_id));
		return $this->get_results(0, 'order');
	}

	/**
	*	Перестроить order в указанном view point
	* @param	integer	$page_id	ID страницы
	* @param	integer	$vp_id		ID view point
	*/
	public function reorder($page_id, $vp_id)
	{
		// Получаем все записи в указанном view point
		$recs = $this->_flush()
			->push_args(array('_spage_id' => $page_id, '_sview_point' => $vp_id))
			->set_what(array('id'))
			->_get()
			->pop_args()
			->get_results(false, 'id');

		// перебираем их и записываем в order № п\п
		foreach ($recs as $order => $id)
		{
			$this->_flush()
				->push_args(array('_sid' => $id, 'order' => $order))
				->_set()
				->pop_args();
		}
	}

	/**
	*	Перестроить order в указанном view point
	* @param	integer	$page_id	ID страницы
	* @param	integer	$vp_id		ID view point
	* @param	integer	$source_id	ID контента
	* @param	string	$dir		Направление (up или down)
	* @return	boolean			TRUE, если перемещение свершилось, иначе FALSE
	*/
	public function move($page_id, $vp_id, $source_id, $dir)
	{
		$dir = strtolower($dir);
		// Определяем текущий order
		$opos = (int)$this->_flush()
			->push_args(array('_sid' => $source_id))
			->set_what(array('order'))
			->_get()
			->pop_args()
			->get_results(0, 'order');
		
		if ($dir == 'up' && $opos > 0)
		{
			// Определяем order предыдущего элемента
			$npos = $this->_flush()
				->push_args(array('_spage_id' => $page_id, '_sview_point' => $vp_id, '_lorder' => $opos))
				->set_what(array('order'))
				->set_order('order', 'DESC')
				->set_limit(0, 1)
				->_get()
				->pop_args()
				->get_results(0, 'order');
		}
		else if ($dir == 'down')
		{
			// Определить order следующего элемента
			$npos = $this->_flush()
				->push_args(array('_spage_id' => $page_id, '_sview_point' => $vp_id, '_morder' => $opos))
				->set_what(array('order'))
				->set_order('order', 'ASC')
				->set_limit(0, 1)
				->_get()
				->pop_args()
				->get_results(0, 'order');
		}
		else
		{
			return false;
		}

		if ($npos === false || $opos === false)
			return false;

		$values = array(
			'opos' => $opos,
			'npos' => $npos,
			'id' => $source_id,
			'pid' => $page_id,
			'vid' => $vp_id,
		);

		if ($opos < $npos)
			$query = "UPDATE `{$this->name}` SET `order` = IF(`id` = :id, :npos, `order` - 1) WHERE `order` >= :opos AND `order` <= :npos AND `page_id` = :pid AND `view_point` = :vid";
		else
			$query = "UPDATE `{$this->name}` SET `order` = IF(`id` = :id, :npos, `order` + 1) WHERE `order` >= :npos AND `order` <= :opos AND `page_id` = :pid AND `view_point` = :vid";

		$this->_flush();
		$this->connector->exec($query, $values);

		return true;
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

	public function drop_by_page_id($page_id = 0)
	{
		if($page_id>0)
		{
			$sql = 'DELETE FROM '.$this->name." WHERE page_id = $page_id";
			$this->connector->query($sql);
		}
	}

	/* 
		9* хэндлер на удаление  ноды или ветки  в структуре. Будем удалять все VP, которые на  указанные ноды повешены.
	*/
	public function remove_for_pages($obj, $ids =  array(),$args = array())
	{
		if(count($ids)>0)
		{
			$ids_list = implode(',',$ids);
			$sql =  "DELETE FROM {$this->name} WHERE page_id in ($ids_list)";
			$this->connector->query($sql);
		}
	}

	public function _listeners()
	{
		return array(
			array('di' => 'structure', 'event' => 'onUnsetRecursively', 'handler' => 'remove_for_pages'),
		);
	}

}
?>
