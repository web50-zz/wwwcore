<?php
/**
*	Интерфейс данных "Статьи: Рэйтинг"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_www_article_rating extends data_interface
{
	public $title = 'Статьи: Рэйтинг';

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
	protected $name = 'www_article_rating';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'action_datetime' => array('type' => 'datetime'),	// Дата события
		'action' => array('type' => 'integer'),			// Событие (1 - like, 0 - dislike)
		'article_id' => array('type' => 'integer'),		// ID статьи
		'session_id' => array('type' => 'string'),		// ID сессии
		'visitor_ip' => array('type' => 'string'),		// IP посетителя
	);
	
	public function __construct ()
	{
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}

	/**
	*	Регистрация "Нравится \ Не нравится"
	* @access	public
	* @param	integer	$article_id	ID статьи
	* @param	integer	$action		Событие
	* @return	boolean			TRUE - зарегистрированно, FALSE - не зарегистрированно
	*/
	public function register_action($article_id, $action)
	{
		if ($article_id > 0)
		{
			// Определяем уникальность посетителя
			$this->_flush();
			$this->connector->exec(
				"SELECT COUNT(*) AS `count` FROM `{$this->name}` WHERE `article_id` = :article_id AND `visitor_ip` = :visitor_ip AND `action_datetime` BETWEEN TIMESTAMP(CURDATE()) AND TIMESTAMP(CURDATE(), '23:59:59')",
				array('article_id' => $article_id, 'visitor_ip' => $_SERVER['REMOTE_ADDR']), true);
			$isNew = !((int)$this->get_results(0, 'count') > 0);

			if ($isNew)
			{
				$this->_flush();
				$this->push_args(array(
					'action_datetime' => date('Y-m-d H:i:s'),
					'action' => $action,
					'article_id' => $article_id,
					'session_id' => session_id(),
					'visitor_ip' => $_SERVER['REMOTE_ADDR'],
				));
				$this->_set();
				$this->pop_args();
				if ($action == 1)
					$this->fire_event('like', array($this->get_lastChangedId(0), $article_id));
				else
					$this->fire_event('dislike', array($this->get_lastChangedId(0), $article_id));
				return true;
			}
		}
		return false;
	}

	/**
	*	Список записей
	*/
	protected function sys_list()
	{
		$this->_flush();
		if (!empty($this->args['query']) && !empty($this->args['field']))
		{
			$this->args["_s{$this->args['field']}"] = "%{$this->args['query']}%";
		}
		$this->extjs_grid_json();
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
