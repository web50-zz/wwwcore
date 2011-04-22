<?php
/**
*	Интерфейс данных "Новости"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_news extends data_interface
{
	public $title = 'Лента новостей';
	
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
	protected $name = 'news';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => 1),
		'category' => array('type' => 'integer'),
		'release_date' => array('type' => 'date'),
		'title' => array('type' => 'string'),
		'source' => array('type' => 'string'),
		'author' => array('type' => 'string'),
		'content' => array('type' => 'text')
	);
	
        public function __construct () {
            // Call Base Constructor
            parent::__construct(__CLASS__);
        }
	
	/**
	*	Получить JSON-пакет данных для ExtJS-грида
	* @access protected
	*/
	protected function sys_list()
	{
		//$this->_flush(true);
		//$sc = $this->join_with_di('structure_content', array('id' => 'cid'), array('pid' => 'pid'));
		$this->_flush();
		if (!empty($this->args['query']) && !empty($this->args['field']))
		{
			$this->args["_s{$this->args['field']}"] = "%{$this->args['query']}%";
		}
		$this->extjs_grid_json(array('id', 'release_date', 'title', 'author', 'source'));
	}
	
	/**
	*	Получить данные для ExtJS-формы
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
		$data = $this->extjs_set_json(false);
		if ($this->args['_sid'] == 0)
		{
			$sc = data_interface::get_instance('structure_content');
			$sc->save_link($this->args['pid'], $data['data']['id'], $this->name);
		}
		response::send($data, 'json');
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
		$data = $this->extjs_unset_json(false);
		$ids = $this->get_lastChangedId();
		
		if (($ids > 0 || count($ids) > 0) && $this->args['_spid'] > 0)
		{
			$sc = data_interface::get_instance('structure_content');
			$sc->remove_link($this->args['_spid'], $ids, $this->name);
		}

		response::send($data, 'json');
	}
}
?>
