<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 23072013	
* @package	SBIN Diesel
*/
class di_www_article_file_types extends data_interface
{
	public $title = 'www: Статьи - Файлы типы';

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
	protected $name = 'www_article_file_types';

	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'order' => array('type' => 'integer'),
		'not_available' => array('type' => 'integer'),
		'title' => array('type' => 'string'),
		'prefix' => array('type' => 'string'),
		'width' => array('type' => 'string'),
		'height' => array('type' => 'string'),
		'is_image' => array('type' => 'integer'),
	);
	
	public function __construct () {
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}
	protected function sys_type_list()
	{
		$this->_flush();
		$this->set_order('order', 'ASC');
		$this->set_args(array('_snot_available'=>'0'));
		$this->extjs_grid_json(array('id', 'order', 'title'));
	}

	protected function sys_list()
	{
		$this->_flush();
		$this->set_order('order', 'ASC');
		$this->extjs_grid_json(array('id', 'order', 'title'));
	}
	
	protected function sys_get()
	{
		$this->_flush();
		$this->extjs_form_json();
	}

	/**
	*	Реорганизация порядка вывода
	*/
	protected function sys_reorder()
	{
		list($npos, $opos) = array_values($this->get_args(array('npos', 'opos')));
		$values = $this->get_args(array('opos', 'npos', 'id', 'pid'));

		if ($opos < $npos)
			$query = "UPDATE `{$this->name}` SET `order` = IF(`id` = :id, :npos, `order` - 1) WHERE `order` >= :opos AND `order` <= :npos";
		else
			$query = "UPDATE `{$this->name}` SET `order` = IF(`id` = :id, :npos, `order` + 1) WHERE `order` >= :npos AND `order` <= :opos";

		$this->_flush();
		$this->connector->exec($query, $values);
		response::send(array('success' => true), 'json');
	}
	
	/**
	*	Добавить \ Сохранить файл
	*/
	protected function sys_set()
	{
		$fid = $this->get_args('_sid');

		if ($fid > 0)
		{
			$this->_flush();
			$this->_get();
			$file = $this->get_results(0);
		}
		$file = array();
		$args =  $this->get_args();
		if (!($fid > 0))
		{
			$args['order'] = $this->get_new_order();
		}
		$this->set_args($args);
		$this->_flush();
		$this->insert_on_empty = true;
		$result = $this->extjs_set_json(false);
		response::send($result, 'json');
	}
	

	/**
	*
	*/
	private function get_new_order()
	{
		$this->_flush();
		$this->_get("SELECT MAX(`order`) + 1 AS `order` FROM `{$this->name}`");
		return $this->get_results(0, 'order');
	}
	
	/**
	*	Удалить файл[ы]
	* @access protected
	*/
	protected function sys_unset()
	{
		if ($this->args['records'] && !$this->args['_sid'])
		{
			$this->args['_sid'] = request::json2int($this->args['records']);
		}
		$this->_flush();
		$data = $this->extjs_unset_json(false);
		response::send($data, 'json');
	}
// 9* return object
	public function get_type_data($type)
	{
		$this->_flush();
		$this->set_args(array('_sid'=>$type));
		$this->_get();
		$data = $this->get_results();
		if(count($data) == 1)
		{
			return $data[0];
		}
		return false;

	}
}
?>
