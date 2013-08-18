<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 23072013	
* @package	SBIN Diesel
*/
class di_www_article_in_category extends data_interface
{
	public $title = 'www: Статьи - статьи в категориях';

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
	protected $name = 'www_article_in_category';

	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'category_id' => array('type' => 'integer'),
		'item_id' => array('type' => 'integer')
	);
	
	public function __construct () {
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}

	/**
	*	Получить размеры указанного изображения
	*/
	protected function sys_get_size()
	{
		$this->_flush();
		$this->_get();
		response::send(array(
			'success' => true,
			'data' => $this->get_results(0)
		), 'json');
	}
	
	protected function sys_list()
	{
		$this->_flush();
		$this->set_order('id', 'ASC');
		$ct = $this->join_with_di('www_article_type', array('category_id' => 'id'), array('title' => 'category_title'));
		$this->extjs_grid_json(array('id', 
					'category_id', 
					'item_id',
					array('di' => $ct, 'name' => 'title'),
					));
	}
	
	protected function sys_get()
	{
		$this->_flush(true);
		$ct = $this->join_with_di('www_article_type', array('category_id' => 'id'), array('title' => 'category_title'));
		$ctt = $ct->get_alias();
		$this->extjs_form_json(array(
			'*',
			"`{$ctt}`.`title`" => 'category_title',
		));
	}

	/**
	*	Добавить \ Сохранить 
	*/
	protected function sys_set()
	{
		$args =  $this->get_args();
		$silent = $this->get_args('silent',false);
		$this->set_args($args);
		$this->_flush();
		$this->insert_on_empty = true;
		$result = $this->extjs_set_json(false);
		if($silent == true)
		{
			$this->args['result'] = $result;
			return $result;
		}
		response::send($result, 'json');
	}
	

	/**
	*	Удалить 
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
	public function unset_for_article($eObj, $ids, $args)
	{
		$this->push_args(array());
		if (!is_array($ids) && $ids > 0)
		{
			$this->set_args(array(
				'_sitem_id' => $ids,
			));
			$this->_flush();
			$this->_unset();
		}
		else if (is_array($ids))
		{
			foreach ($ids as $id)
			{
				$this->set_args(array(
					'_sitem_id' => $id,
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
	public function unset_for_category($eObj, $ids, $args)
	{
		$this->push_args(array());
		if (!is_array($ids) && $ids > 0)
		{
			$this->set_args(array(
				'_scategory_id' => $ids,
			));
			$this->_flush();
			$this->_unset();
		}
		else if (is_array($ids))
		{
			foreach ($ids as $id)
			{
				$this->set_args(array(
					'_scategory_id' => $id,
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
			array('di' => 'www_article_type', 'event' => 'onUnset', 'handler' => 'unset_for_category'),
		);
	}

}
?>
