<?php
/**
*	Интерфейс данных "Структура сайта"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_structure extends data_interface
{
	public $title = 'Структура сайта';
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
	protected $name = 'structure';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
			'id' => array('type' => 'integer', 'serial' => 1, 'readonly' => 1),
			'hidden' => array('type' => 'boolean'),
			'title' => array('type' => 'string'),
			'name' => array('type' => 'string'),
			'uri' => array('type' => 'string'),
			'redirect' => array('type' => 'string'),
			'module' => array('type' => 'string'),
			'params' => array('type' => 'string'),
			'template' => array('type' => 'string'),
			'theme_overload' => array('type' => 'string'),
			'private' => array('type' => 'boolean'),
			'auth_module' => array('type' => 'string'),
			'left' => array('type' => 'integer', 'protected' => 1),
			'right' => array('type' => 'integer', 'protected' => 1),
			'level' => array('type' => 'integer', 'readonly' => 1),
			'mtitle' => array('type' => 'string'),
			'mkeywords' => array('type' => 'string'),
			'mdescr' => array('type' => 'string')
		);
	
	public function __construct () {
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}
	
	public function get_page_by_uri($uri)
	{
		$this->connector->fetchMethod = PDO::FETCH_ASSOC;
		$x = preg_split('/\//', $uri);
		array_pop($x);
		$y = array();
		for ($i = 1; $i <= count($x); $i++)
			$y[] = '"' . join('/', array_slice($x, 0, $i)) . '/"';
		$result = $this->_get("SELECT * FROM `{$this->name}` WHERE `uri` IN (" . join(', ', $y) . ") ORDER BY `left` DESC LIMIT 1");

		if (empty($result))
		{
			$sql = 'SELECT * FROM `' . $this->name . '` WHERE `id` = :id';
			$result = $this->connector->exec($sql, array('id' => 1), true, true);
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
		
		response::send($data, 'json');
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
			$childs = $ns->get_childs($this->args['_sid']);
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
			$data = array('success' => true);
		else
			$data = array('success' => false);
		response::send($data, 'json');
	}

	/**
	*	Удалить рекурсивно все связи
	* @access protected
	*/
	protected function unset_recursively($id)
	{
		$sc = data_interface::get_instance('structure_content');
		$ns = new nested_sets($this);

		$childs = $ns->get_childs($id);
		$ids = array($id);
		foreach ($childs as $child)
			$ids[] = $child['id'];

		foreach ($ids as $pid)
			$sc->remove_by_page($pid);
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
                $fields = array('id', 'title' => 'text', 'module' => 'ui', 'params','concat("id: ",`'.$this->name.'`.`id`,"<br>URI: ",`'.$this->name.'`.`uri`)'=>'qtip');
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
}
?>
