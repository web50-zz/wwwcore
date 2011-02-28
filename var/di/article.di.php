<?php
/**
*	Интерфейс данных "Статьи"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_article extends data_interface
{
	public $title = 'Статьи';

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
	protected $name = 'article';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
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
	*	Получить данные элемента в виде JSON
	* @access protected
	*/
	public function get()
	{
		$this->_flush(true);
		$this->connector->fetchMethod = PDO::FETCH_ASSOC;
		$sc = $this->join_with_di('structure_content', array('id' => 'cid'), array('pid' => 'pid'));
		return $this->_get();
	}
	
	/**
	*	Список записей
	*/
	protected function sys_list()
	{
		$this->_flush();
		$this->extjs_grid_json();
	}
	
	/**
	*	Получить данные элемента в виде JSON
	* @access protected
	*/
	protected function sys_get()
	{
		$this->_flush(true);
		$sc = $this->join_with_di('structure_content', array('id' => 'cid'), array('pid' => 'pid'));
		$this->extjs_form_json(array(
			'id', 'title', 'content'
		));
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
		$data = $this->extjs_set_json(false);
		if ($this->args['_sid'] == 0)
		{
			$sc = data_interface::get_instance('structure_content');
			$sc->save_link($this->args['pid'], $data['data']['id'], $this->name);
		}
		response::send($data, 'json');
	}
	
	/**
	*	Удалить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	protected function sys_unset()
	{
		$this->_flush();
		$sc = data_interface::get_instance('structure_content');
		$sc->remove_by_page($this->args['_sid']);
		$this->extjs_unset_json();
	}
}
?>
