<?php
/**
*
* @author	Fedot B Pozdnyakov <9@u9.ru>
* @package	SBIN Diesel
*/
class di_www_faq extends data_interface
{
	public $title = 'WWW: FAQ';
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
	protected $name = 'www_faq';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
			'id' => array('type' => 'integer', 'serial' => 1, 'readonly' => 1),
			'created_datetime' => array('type' => 'datetime'),
			'name' => array('type' => 'string'),
			'email' => array('type' => 'string'),
			'comment' => array('type' => 'text'),
			'left' => array('type' => 'integer', 'protected' => 1),
			'right' => array('type' => 'integer', 'protected' => 1),
			'level' => array('type' => 'integer', 'readonly' => 1)
		);
	
	public function __construct () {
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}

	/**
	*	Получить XML-пакет данных для ExtJS-формы
	* @access protected
	*/
	protected function sys_get()
	{
		$this->_flush();
		$this->extjs_form_json();
	}
	
	/**
	*	Обвязка для внутреннего вызова sys_set, с возвратом результата	
	*/
	public function _inner_set($args)
	{
		$this->push_args($args);
		$result = $this->sys_set(true);
		$this->pop_args();
		return $result;
	}
	
	/**
	*	Добавить альбом
	* @access protected
	*/
	protected function sys_set($inner = false)
	{
		$id = $this->get_args('_sid');

		if ($this->args['_sid'] > 0)
		{
			$this->_flush();
			$this->insert_on_empty = false;
			$result = $this->extjs_set_json(false);
			$result['data'] = $this->_flush()
				->set_what(array(
					'id',
					'name',
					'email',
					"DATE_FORMAT(`created_datetime`, '%H:%i, %d %b %Y')" => 'created_datetime',
				))
				->_get()->get_results(0);
		}
		else if($this->args['pid'] > 0)
		{
			$this->set_args(array('created_datetime' => date('Y-m-d H:i:s')), true);

			$ns = new nested_sets($this);
			unset($this->args['_sid']); // Иначе будет пытаться обновить нулевую ноду
			
			if ($ns->add_node($this->args['pid']))
			{
				$result = array(
					'success' => true,
					'data' => array(
						'id' => $this->get_lastChangedId(0),
						'name' => $this->get_args('name'),
						'email' => $this->get_args('email'),
						'created_datetime' => date('H:i, d M Y', strtotime($this->get_args('created_datetime'))),
					)
				);
			}
			else
			{
				$result = array(
					'success' => false,
					'errors' =>  $e->getMessage()
				);
			}
		}
		
		if ($inner == true)
		{
			return $result;
		}
		else
		{
			response::send($result, 'json');
		}
	}
	
	/**
	*	Переместить узел
	* @access protected
	*/
	protected function sys_move()
	{
		list($id, $pid, $ind) = array_map('intval', $this->get_args(array('_sid', 'pid', 'ind'), NULL, TRUE));

		if ($id > 0)
		{
			$ns = new nested_sets($this);

			if ($ns->move_node($id, $pid, $ind))
			{
				$data = array(
					'success' => true
				);
			}
			else
			{
				$data = array(
					'success' => false,
					'error' => 'Не удалось переместить папку'
				);
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
	*	Получить JSON-пакет данных для ExtJS-дерева
	* @access protected
	*/
	protected function sys_slice()
	{
		$tbl = $this->get_name();
                $pid = intval($this->args['node']);
                //$fields = array('id', "CONCAT(`{$tbl}`.`name`, ' &lt;', `{$tbl}`.`email`, '&gt; [', DATE_FORMAT(`{$tbl}`.`created_datetime`, '%H:%i, %d %b %Y'), ']')" => 'text');
                $fields = array('id', "name" => 'text');
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

	/**
	*	Внутренний вызов функции sys_unset с возвращением результатов
	* @param	array	$args	Входящие аргументы
	* @return	array	Результат выполнения sys_unset
	*/
	public function _inner_unset($args)
	{
		$this->push_args($args);
		$results = $this->sys_unset(true);
		$this->pop_args();
		return $results;
	}
	
	/**
	*	Удалить узел и его потомков
	* @param	boolean	$inner	TRUE - вернуть результат, FALSE - вернуть ответ браузеру
	* @access protected
	*/
	protected function sys_unset($inner = false)
	{
		$id = intval($this->get_args('_sid'));
		$cids = (array)$this->get_removable_ids($id);
		$this->fire_event('onBeforeUnset', array($cids, $this->get_args()));

		$ns = new nested_sets($this);

		if ($id > 0 && $ns->delete_node($id))
		{
			$result = array('success' => true);
			$this->fire_event('onUnset', array($cids, $this->get_args()));
		}
		else
		{
			$result = array('success' => false);
		}

		if ($inner === true)
		{
			return $result;
		}
		else
		{
			response::send($result, 'json');
		}
	}

	/**
	*	Сформировать полный список ID удаляемых узлов
	* @access protected
	*/
	protected function get_removable_ids($id)
	{
		$ids = array($id);

		$ns = new nested_sets($this);
		$childs = $ns->get_childs($id, false);

		foreach ($childs as $child)
		{
			$ids[] = $child['id'];
		}

		return $ids;
	}
}
?>
