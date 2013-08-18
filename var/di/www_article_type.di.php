<?php
/**
*	Интерфейс данных "www_article type"
*
* @author	9* Fedot B Pozdnyakov 9@u9.ru 22072012
* @package	SBIN Diesel
*/
class di_www_article_type extends data_interface
{
	public $title = 'Статьи: типы статей.';
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
	protected $name = 'www_article_type';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
			'id' => array('type' => 'integer', 'serial' => 1, 'readonly' => 1),
			'hidden' => array('type' => 'boolean'),
			'title' => array('type' => 'string'),
			'name' => array('type' => 'string'),
			'uri' => array('type' => 'string'),
			'left' => array('type' => 'integer', 'protected' => 1),
			'right' => array('type' => 'integer', 'protected' => 1),
			'level' => array('type' => 'integer', 'readonly' => 1),
			'published' => array('type' => 'integer'),
			'type' => array('type' => 'integer'),
			'link_id' => array('type' => 'integer'),
		);


	public function __construct () {
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}
	
	public function get_by_uri($uri)
	{
		$this->connector->fetchMethod = PDO::FETCH_ASSOC;
		$sql = "SELECT * FROM `{$this->name}` WHERE `uri` = '/{$uri}'ORDER BY `left` DESC LIMIT 1";
		$this->_get($sql);
		$result = $this->get_results();
		if (empty($result))
		{
			return array();
		}
		return $result[0];
	}

	public function get_main_menu($parent = '1',$level_down = 1)
	{
		if(!$parent)
		{
			$parent = '1';
		}
		$this->where = '`sp1`.`hidden` = 0';
		$ns = new nested_sets($this);
		$branch = $ns->get_childs($parent, $level_down);
		$top_parent = $ns->get_parent(PAGE_ID, 2);//9* вот то что ниже надо что бы если мы рисуем топ менюдля  страниц нижних уровней индицировать в топ уровне выбранную ветку
		foreach($branch as $key=>$value)
		{
			if($value['id'] == $top_parent['id'])
			{
				$branch[$key]['top_parent'] = 'yes';
			}
		}
		return $branch;
	}
	
	public function get_sub_menu($page = 0)
	{
		if($page == 0)
		{
			$page = PAGE_ID;
		}
		$this->where = '`sp1`.`hidden` = 0';
		$ns = new nested_sets($this);
//		$data['root'] = $ns->get_parent($page, 2); //9* pathc 15082011 problems with submenu
		$data['page'] = $ns->get_node($page);
		if (empty($data['root'])) $data['root'] = $data['page'];
		$data['childs'] = $ns->get_childs($data['root']['id'], NULL);
		$data['page_id'] = PAGE_ID;
		return $data;;
	}
	
	public function get_trunc_menu()
	{
		$ns = new nested_sets($this);
		return $ns->get_parents(PAGE_ID, true);
	}
	
	/**
	*	Добавить узел
	* @access protected
	*/
	protected function sys_set()
	{
		$silent = $this->get_args('silent',false);
		try{
			$this->check_input();
			$this->check_uniq_uri($this->args['name']);
			$di = data_interface::get_instance('www_article');
			$di->check_uniq_uri($this->args['name']);
			if ($this->args['_sid'] > 0)
			{
				$uri = $this->get_args('uri');
				$this->calc_uri();
				$this->_flush();
				$this->insert_on_empty = false;
				$data = $this->extjs_set_json(false);
				$data['data']['uri'] = $this->get_args('uri');
				if ($data['data']['uri'] != $uri)
				{
					$this->recalc_uri($this->args['_sid']);
				}
			}
			else if($this->args['pid'] > 0)
			{
				$ns = new nested_sets($this);
				unset($this->args['_sid']); // Иначе будет пытаться обновить нулевую ноду
				if ($ns->add_node($this->args['pid']))
				{
					$this->args['_sid'] = $this->get_lastChangedId(0);
					$this->calc_uri();
					
					$this->_flush();
					$this->insert_on_empty = false;
					$data = $this->extjs_set_json(false);
					$data['data']['uri'] = $this->args['uri'];
				}
				else
				{
					throw new Exception('Проблемы с сохранением данных');
				}
			}
			if($silent == true)
			{
				$this->args['result'] = $data;
				return;
			}
			response::send($data, 'json');
		}
		catch(Exception $e)
		{
			$data = array(
				'success' => false,
				'errors' => $e->getMessage() 
				);
		}
		if($silent == true)
		{
			return $data;
		}
		response::send($data, 'json');
	}

	public function node_set()
	{
		if ($this->args['_sid'] > 0)
		{
			$uri = $this->get_args('uri');
			$this->calc_uri();
			
			$this->_flush();
			$this->insert_on_empty = false;
			$data = $this->extjs_set_json(false);
			$data['data']['uri'] = $this->get_args('uri');
			
			if ($data['data']['uri'] != $uri)
				$this->recalc_uri($this->args['_sid']);
		}
		else if($this->args['pid'] > 0)
		{
			$ns = new nested_sets($this);
			unset($this->args['_sid']); // Иначе будет пытаться обновить нулевую ноду
			
			if ($ns->add_node($this->args['pid']))
			{
				$this->args['_sid'] = $this->get_lastChangedId(0);
				$this->calc_uri();
				
				$this->_flush();
				$this->insert_on_empty = false;
				$data = $this->extjs_set_json(false);
				$data['data']['uri'] = $this->args['uri'];
			}
			else
			{
				$data = array(
					'success' => false,
					'errors' =>  $e->getMessage()
					);
			}
		}
		else
		{
			$data = array(
				'success' => false,
				'errors' =>  'Отсутсвуют указатели на родителя или на запись.'
				);
		}
		return $data;
	}

	/**
	*	Переместить узел
	* @access protected
	*/
	protected function sys_move()
	{
		$id = intval($this->get_args('_sid'));
		$pid = intval($this->get_args('pid'));
		$ind = intval($this->get_args('ind'));

		if ($id > 0)
		{
			$ns = new nested_sets($this);

			if ($ns->move_node($id, $pid, $ind))
			{
				$this->push_args($ns->get_node($id));		// Запоминаем параметры ноды
				$this->set_args(array('_sid' => $id), true);	// Задаём поисковый ключ
				$this->calc_uri();				// Расчитываем URI

				$this->_flush();				// Сбрасываем DI
				$this->insert_on_empty = false;			// Запрещаем записывать новые записи
				$data = $this->extjs_set_json(false);		// Сохраняем новый URI

				$this->pop_args();				// Возвращаем исходные параметры
				$this->recalc_uri();				// Расчитываем URI всех потомков
			}
		}
		else
		{
			$data = array(
				'success' => true
				);
		}
		response::send($data, 'json');
	}

	public function node_move()
	{
		$id = intval($this->get_args('_sid'));
		$pid = intval($this->get_args('pid'));
		$ind = intval($this->get_args('ind'));

		if ($id > 0)
		{
			$ns = new nested_sets($this);

			if ($ns->move_node($id, $pid, $ind))
			{
				$this->push_args($ns->get_node($id));		// Запоминаем параметры ноды
				$this->set_args(array('_sid' => $id), true);	// Задаём поисковый ключ
				$this->calc_uri();				// Расчитываем URI

				$this->_flush();				// Сбрасываем DI
				$this->insert_on_empty = false;			// Запрещаем записывать новые записи
				$data = $this->extjs_set_json(false);		// Сохраняем новый URI

				$this->pop_args();				// Возвращаем исходные параметры
				$this->recalc_uri();				// Расчитываем URI всех потомков
			}
		}
		else
		{
			$data = array(
				'success' => true
				);
		}
		return $data;
	}

	/**
	*	Расчитать URI страницы
	*/
	private function calc_uri()
	{
		$ns = new nested_sets($this);
		
		if ($this->args['_sid'] > 0)
			$parents = $ns->get_parents($this->args['_sid']);
		else
			return FALSE;
		
		$uri = '/';
		foreach ($parents as $parent)
			if ($parent['id'] > 1)
				$uri.= (($parent['name']) ? $parent['name'] : 'p' . $parent['id']) . '/';
		$uri.= (($this->args['name']) ? $this->args['name'] : 'p' . $this->args['_sid']) . '/';
		
		$this->set_args(array('uri' => $uri), true);
		return TRUE;
	}
	
	/**
	*	Пересчитать URI всех потомков
	*/
	private function recalc_uri()
	{
		$ns = new nested_sets($this);
		
		if ($this->args['_sid'] > 0)
			$childs = $ns->get_childs($this->args['_sid'],10000);//10000 чтобы всех потомков до дна
		else
			return FALSE;
		
		$this->insert_on_empty = false;
		
		foreach ($childs AS $child)
		{
			$this->set_args(array(
				'_sid' => $child['id'],
				'name' => $child['name']));
			
			if ($this->calc_uri() !== FALSE)
				$this->extjs_set_json(false);
		}
		
		return TRUE;
	}
	
	/**
	*	Удалить узел
	* @access protected
	*/
	protected function sys_unset()
	{
		$id = intval($this->args['_sid']);
		$this->unset_recursively($id);

		$ns = new nested_sets($this);
		if ($id > 0 && $ns->delete_node($id))
		{

			$this->fire_event('onUnset', array($id, $this->get_args()));
			$data = array('success' => true);
		}
		else
		{
			$data = array('success' => false);
		}
		response::send($data, 'json');
	}

	public function node_unset()
	{
		$id = intval($this->args['id']);
		$this->unset_recursively($id);

		$ns = new nested_sets($this);
		if ($id > 0 && $ns->delete_node($id))
		{
			$this->fire_event('onUnset', array($id, $this->get_args()));
			$data = array('success' => true);
		}
		else
		{
			$data = array('success' => false);
		}
		return $data;
	}

	/**
	*	Удалить рекурсивно все связи
	* @access protected
	*/
	protected function unset_recursively($id)
	{
		$ns = new nested_sets($this);

		$childs = $ns->get_childs($id,10000);
		$ids = array();
		foreach ($childs as $child)
		{
			$ids[] = $child['id'];
		}
		$this->fire_event('onUnset', array($ids, $this->get_args()));
	}
	
	/**
	*	Получить XML-пакет данных для ExtJS-формы
	* @access protected
	*/
	protected function sys_get()
	{
		//$this->extjs_form_xml();
		$this->extjs_form_json();
	}
	
	/**
	*	Получить XML-пакет данных для ExtJS-формы
	* @access protected
	*/
	protected function sys_page()
	{
		$this->extjs_form_json();
	}
	
	/**
	*	Получить JSON-пакет данных для ExtJS-дерева
	* @access protected
	*/
	protected function sys_slice()
	{
                $pid = intval($this->args['node']);
		$table =  $this->name;
                $fields = array(
				'id',
				'title'=>'text',
				'uri',
				'type',
				'link_id', 
				"IF (`{$table}`.`type` = 2, 'link', '')" => 'iconCls',
				'concat("id: ",`'.$this->name.'`.`id`,"<br>URI: ",`'.$this->name.'`.`uri`)'=>'qtip');
		$this->mode = 'NESTED_SETS_SLICE';
                if ($pid > 0)
                {
                        $this->set_args(array('_sid' => $pid));
                        $this->extjs_slice_json($fields, 1);
                }
                else
                {
                        $this->set_args(array('_slevel' => 1));
                        $this->extjs_slice_json($fields);
                }
	}

	public function publish($input)
	{
		$sql = 'update '.$this->name." set published ='". $input['p']."' where id=".$input['job_id'];
		$this->connector->query($sql);
	}

	// 9* 23072012 проверяем наличие  узла с именем в аргументах на уникальность
	public function check_uniq_uri($uri)
	{
		if($this->args['_sid']>0)
		{
			$where = " AND  id != {$this->args['_sid']} ";
		}
		$sql = "SELECT count(*) AS cnt FROM {$this->name} WHERE name = '{$uri}' $where";
		$this->_get($sql);
		$res = $this->get_results();
		if($res[0]->cnt>0)
		{
			throw new Exception('Используйте другой тэг для узла. Текущий не уникален.');
		}
	}

	public function move_spec()
	{
		$this->_flush();
		$this->args['ind'] = $this->args['i'];
		$this->args['_sid'] = $this->args['w'];
		$this->args['pid']= $this->args['p'];
		$resp = $this->node_move();
		return $resp;
	}
	public function sys_type_list()
	{
		$this->_flush();
		$this->set_order('left','ASC');
		$this->extjs_grid_json(array('id','title'));
	}
	public function search_by_uri($uri = '')
	{
		if($uri == '')
		{
			return false;
		}
		$this->_flush();
		$this->set_args(array('_suri'=>$uri));
		$fld = array(
			'id',
			'uri'
		);
		$res = $this->extjs_grid_json($fld,false);
		if($res['total'] == 1)
		{
			return $res['records'][0]['id'];
		}
		return false;
	}

	public function check_input()
	{
		$args = $this->args;
		if(!preg_match('/^[a-zA-Z1-90\-_]+$/',$args['name']))
		{
			throw new Exception('Имя может содержать только латинские буквы, цифры  и символы _ -. Пробелы не допустимы');
		}
	}


	/**
	*	Залинковать узел
	*/
	protected function sys_link()
	{
		list($pid, $lid) = array_map('intval', $this->get_args(array('pid', 'lid'), NULL, TRUE));
		if ($pid > 0 && $lid > 0)
		{
			// Получаем инфолрмацию о линкуемой ноде
			$node = $this->_flush()
				->push_args(array('_sid' => $lid))
				->_get()
				->pop_args()
				->get_results(0);

			$ns = new nested_sets($this);
			if ($ns->add_node($pid))
			{
				$this->_flush()
				->push_args(array(
					'_sid' => $this->get_lastChangedId(0),	// ID свежесозданого узла
					'title' => $node->title,		// Title узла источника
					'type' => 2,				// Тип текущего узла (2 - ссылка)
					'link_id' => $node->id,			// ID узла источника
				))->insert_on_empty = false;
				$result = $this->extjs_set_json(false);
				$this->pop_args();
			}
			else
			{
				$result = array(
					'success' => false,
					'errors' =>  $e->getMessage()
				);
			}
		}
		else
		{
			dbg::write("Не указан один из параметров (pid: {$pid}, lid: {$lid})", LOG_PATH . 'di_errors.log');
			$result = array(
				'success' => false,
				'errors' =>  'Неверный набор параметров',
			);
		}
		
		response::send($result, 'json');
	}
	public function get_all()
	{
		$parent = 1;
		$this->_flush();
		if($this->args['parent']>0){
			$parent = $this->args['parent'];
		}
		$this->set_args(array(
				'sort'=>'left',
				'dir'=>'ASC',
				));
		$d2 = $this->join_with_di('m2_category',array('link_id'=>'id'),array('uri'=>'l_uri','name'=>'l_name','title'=>'l_title'));
		$flds = array(
			'id',
			'title',
			'name',
			'left',
			'right',
			'level',
			'uri',
			'link_id',
			'type',
			array('di'=>$d2,'name'=>'uri'),
			array('di'=>$d2,'name'=>'name'),
			array('di'=>$d2,'name'=>'title'),
		);
		$this->connector->debug = true;
		$this->data =  $this->extjs_grid_json($flds,false);
		$this->get_childs(0);
		$this->correct_links();
		$this->get_childs(0);
		if($parent != 1){
			$this->search_parent($this->data['records'],$parent);
			return $this->result;
		}
		return $this->data['records'][0];
	}

	public function get_childs($index)
	{
		$this->cnt++;
		$this->data['records'][$index]['childs']= array();
		foreach($this->data['records'] as $key=>$value){
			if($value['level'] == $this->data['records'][$index]['level']+1)
			{
				if($value['left']>$this->data['records'][$index]['left'] && $value['right']<$this->data['records'][$index]['right'])
				{
						$this->data['records'][$key]['parent'] = $this->data['records'][$index]['id'];
						$this->get_childs($key);
						array_push($this->data['records'][$index]['childs'],$this->data['records'][$key]);
				}
			}
		}
	}

	public function search_parent($array_in,$parent)
	{
		foreach($array_in as $key=>$value)
		{
			if($value['id'] == $parent)
			{
				$this->result = $value;
				return;
			}
			else{
				$this->search_parent($value['childs'],$parent);
			}
		}
	}
	public function correct_links()
	{
		foreach($this->data['records'] as $key=>$value){
			if($value['type'] == 2){
				foreach($this->data['records'] as $key2=>$value2)
				{
					if($value2['id'] == $value['link_id'])
					{
						$this->data['records'][$key]['l_parent'] = $value2['parent'];
					}
				}
			}
		}
	}

	public function get_level_down($node)
	{
		$data = array();
		$ns = new nested_sets($this);
		$data['childs'] = $ns->get_childs($node, NULL);
		return $data;;
	}
	public function get_all_descendants_ids($id)
	{
		$ns = new nested_sets($this);
		$childs = $ns->get_childs($id,10000);
		$ids = array();
		foreach ($childs as $child)
		{
			$ids[] = $child['id'];
		}
		return $ids;
	}

	public function get_simple_list()
	{
		$this->_flush();
		$this->set_order('left','ASC');
		$this->where = "{$this->name}.published = 1 and {$this->name}.id != 1"; 
		return $this->extjs_grid_json(false,false);
	}

	public function get_counted_list()
	{
		$this->_flush();
		$di = $this->join_with_di('www_article_in_category',array('id'=>'category_id'),array('item_id'=>'item_id'));
		$this->where = "{$this->name}.published = 1 and {$this->name}.id != 1"; 
		$this->set_group('category_id',$di);
		$this->set_order('count(*)','ASC');
		$what = array(
				'*',
				'count(*)'=>'cnt',
				);
		return $this->extjs_grid_json($what,false);
	}

}
?>
