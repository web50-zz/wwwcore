<?php
/**
*	Интерфейс данных "Статьи: Комментарии пользователей"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_www_article_comment extends data_interface
{
	public $title = 'Статьи: Комментарии пользователей';

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
	protected $name = 'www_article_comment';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'created_datetime' => array('type' => 'datetime'),
		'published' => array('type' => 'integer'),
		'article_id' => array('type' => 'integer'),
		'name' => array('type' => 'string'),
		'comment' => array('type' => 'string'),
	);
	
	public function __construct ()
	{
	    // Call Base Constructor
	    parent::__construct(__CLASS__);
	}

	public function get_article_comments($article_id, $limit = 20, $page = 0)
	{
		$this->_flush();
		$this->push_args(array(
			'_sarticle_id' => $article_id,
			'sort' => 'created_datetime',
			'dir' => 'DESC',
			'start' => ($page - 1) * $limit,
			'limit' => $limit
		));
		$results = $this->extjs_grid_json(false, false);
		$this->pop_args();
		return $results;
	}
	
	/**
	*	Список записей
	*/
	protected function sys_list()
	{
		$this->_flush(true);
		$art = $this->join_with_di('www_article', array('article_id' => 'id'), array('title' => 'title'));
		if (!empty($this->args['query']) && !empty($this->args['field']))
		{
			$this->args["_s{$this->args['field']}"] = "%{$this->args['query']}%";
		}

		$where = array();
		// Фильтр по Дате комментария
		if (($df = $this->get_args('created_from', '')) != '' && ($dt = $this->get_args('created_to', '')) != '')
			$where[] = "`$table`.`created_datetime` BETWEEN STR_TO_DATE('{$df} 00:00:00', '%Y-%m-%d %H:%i:%s')
			  AND STR_TO_DATE('{$dt} 23:59:59', '%Y-%m-%d %H:%i:%s')";
		else if (($df = $this->get_args('created_from', '')) != '')
			$where[] = "`$table`.`created_datetime` >= STR_TO_DATE('{$df} 00:00:00', '%Y-%m-%d %H:%i:%s')";
		else if (($dt = $this->get_args('created_to', '')) != '')
			$where[] = "`$table`.`created_datetime` <= STR_TO_DATE('{$dt} 23:59:59', '%Y-%m-%d %H:%i:%s')";

		if (!empty($where))
			$this->where = join(' AND ', $where);

		$this->extjs_grid_json(array(
			'id',
			'created_datetime',
			'published',
			'article_id',
			array('di' => $art, 'name' => 'title'),
			'name'
		));
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
		$records = (array)json_decode($this->get_args('records'), true);

		foreach ($records as $record)
		{
			$record['_sid'] = $record['id'];
			unset($record['id']);
			$this->_flush();
			$this->push_args($record);
			$this->insert_on_empty = true;
			$data = $this->extjs_set_json(false);
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

	public function unset_for_article($eObj, $ids, $args)
	{
		$this->push_args(array());
		if (!is_array($ids) && $ids > 0)
		{
			$this->set_args(array(
				'_sarticle_id' => $ids,
			));
			$this->_flush();
			$this->_unset();
		}
		else if (is_array($ids))
		{
			foreach ($ids as $id)
			{
				$this->set_args(array(
					'_sarticle_id' => $id,
				));
				$this->_flush();
				$this->insert_on_empty = true;
				$this->_unset();
			}
		}
		else
		{
			// Some error, because unknown project ID
		}
		$this->pop_args();
	}


	public function _listeners()
	{
		return array(
			array('di' => 'www_article', 'event' => 'onUnset', 'handler' => 'unset_for_article'),
		);
	}

}
?>
