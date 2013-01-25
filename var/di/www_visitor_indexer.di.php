<?php
/**
*	Интерфейс данных "Индексатор поселещний страниц"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_www_visitor_indexer extends data_interface
{
	public $title = 'Индексатор поселещний страниц';

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
	protected $name = 'www_visitor_indexer';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'visiting_datetime' => array('type' => 'datetime'),	// Дата визита
		'main_uri' =>  array('type' => 'string'),		// URI страницы
		'page_uri' => array('type' => 'string'),		// URI страницы
		'srch_uri' => array('type' => 'string'),		// URI страницы
		'referer' => array('type' => 'string'),			// Страница с которой пришли
		'user_agent' => array('type' => 'string'),		// Информация о User агенте
		'session_id' => array('type' => 'string'),		// ID сессии
		'visitor_ip' => array('type' => 'string'),		// IP посетителя
		'content_di' => array('type' => 'string'),		// Имя DI контента
		'content_id' => array('type' => 'integer'),		// ID контента
	);
	
	public function __construct ()
	{
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}

	public function register_visitor()
	{
		$main_uri = (empty($_SERVER['REDIRECT_URL'])) ? '/' : $_SERVER['REDIRECT_URL'];

		// Определяем уникальность посетителя
		$this->_flush();
		$this->connector->exec(
			"SELECT COUNT(*) AS `count` FROM `{$this->name}` WHERE `main_uri` = :main_uri AND `visitor_ip` = :visitor_ip AND `visiting_datetime` BETWEEN TIMESTAMP(CURDATE()) AND TIMESTAMP(CURDATE(), '23:59:59')",
			array('main_uri' => $main_uri, 'visitor_ip' => $_SERVER['REMOTE_ADDR']), true);
		$isNew = !((int)$this->get_results(0, 'count') > 0);

		$data = array(
			'visiting_datetime' => date('Y-m-d H:i:s'),
			'main_uri' => $main_uri,
			'page_uri' => PAGE_URI,
			'srch_uri' => SRCH_URI,
			'referer' => getenv('HTTP_REFERER'),
			'user_agent' => $_SERVER['HTTP_USER_AGENT'],
			'session_id' => session_id(),
			'visitor_ip' => $_SERVER['REMOTE_ADDR'],
		);
		$this->_flush();
		$this->push_args($data);
		$this->_set();
		$this->pop_args();

		$this->fire_event('visitor', array($this->get_lastChangedId(0), $data, $isNew));
	}

	public function count_for_uri_by_ip($main_uri)
	{
	}

	public function count_for_uri_by_session($main_uri)
	{
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
}
?>
