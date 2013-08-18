<?php
/**
*
* @author       9*  9@u9.ru 
* @package	SBIN Diesel
* 9* if in registry exists key - 'www_article_thumb_size' for example  90x20,  then this size will be applied on thumb over current www_article defaults 
*/
class di_www_article extends data_interface
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
	protected $name = 'www_article';

	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => 1),
		'order' => array('type' => 'integer'),
		'post_type' => array('type' => 'integer'),
		'release_date' => array('type' => 'date'),
		'title' => array('type' => 'string'),
		'source' => array('type' => 'string'),
		'author' => array('type' => 'string'),
		'uri' => array('type' => 'string'),
		'content' => array('type' => 'text'),
		'published' => array('type' => 'integer'),
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
		$this->_flush();
		if($this->args['by_category'] == true)
		{
			$this->sys_search_by_category();
		}
		$this->extjs_grid_json(array('id', 'order', 'uri', 'title','release_date'));
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


	protected function sys_set()
	{
		$fid = $this->get_args('_sid');
		$silent = $this->get_args('silent',false);
		try{
			$this->check_input();
			$this->check_uniq_uri($this->args['uri']);
			$di = data_interface::get_instance('www_article_type');
			$di->check_uniq_uri($this->args['uri']);
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
			if($silent == true)
			{
				$this->args['result'] = $result;
				return;
			}
			response::send($result, 'json');
		}
		catch(Exception $e)
		{
			$data['success'] = false;
			$data['errors'] =  $e->getMessage();
			if($silent == true)
			{
				$this->args['result'] = $data;
				return;
			}
			response::send($data,'json');
		}

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

	/**
	* получаем новое значение  order
	*/
	private function get_new_order()
	{
		$this->_flush();
		$this->_get("SELECT MAX(`order`) + 1 AS `order` FROM `{$this->name}`");
		return $this->get_results(0, 'order');
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

	protected function sys_search_by_category()
	{
		$di = $this->join_with_di('www_article_category',array('id'=>'item_id'),array('category_id','category_id'));
		$this->where = $di->get_alias().'.category_id = '.$this->args['category_id'];
		$this->extjs_grid_json(array('id', 'order', 'name', 'title',
			array('di'=>$di,'name'=>'category_id')
		));
	}
/*
	public function search_by_uri($uri = '',$cat_uri = '')
	{
		if($uri == '')
		{
			return false;
		}
		$this->_flush();
		$this->set_args(array('_suri'=>$uri));
		$di2 = $this->join_with_di('www_article_type',array('category'=>'id'),array('uri'=>'cat_uri'));
		if($cat_uri != '')
		{
			$this->where = $di2->get_alias().".uri = '/{$cat_uri}/'";
		}
		$fld = array(
			'id',
			'uri',
			array('di'=>$di2,'name'=>'uri')
		);
		$res = $this->extjs_grid_json($fld,false);
		if($res['total'] == 1)
		{
			return $res['records'][0]['id'];
		}
		return false;
	}
*/	
	// 9* 23072012 проверяем наличие  узла с именем в аргументах на уникальность
	public function check_uniq_uri($uri)
	{
		if($this->args['_sid']>0)
		{
			$where = " AND  id != {$this->args['_sid']} ";
		}
		$sql = "SELECT count(*) AS cnt FROM {$this->name} WHERE uri = '{$uri}' $where";
		$this->_get($sql);
		$res =  $this->get_results();
		if($res[0]->cnt>0)
		{
			throw new Exception('Используйте другой URI для узла. Текущий не уникален.');
		}
	}

	//9* 23072013  проверяем  инпут для uri
	public function check_input()
	{
		$args = $this->args;
		if(!preg_match('/^[a-zA-Z1-90\-_]+$/',$args['uri']))
		{
			throw new Exception('URI может содержать только латинские буквы, цифры  и символы _ -. Пробелы не допустимы');
		}
	}

}
?>
