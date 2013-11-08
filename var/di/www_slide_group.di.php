<?php
/**
*	Интерфейс данных "WWW: Слайды"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_www_slide_group extends data_interface
{
	public $title = 'WWW: Слайды';

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
	protected $name = 'www_slide_group';

	/**
	* @var	string	$path_to_storage	Путь к хранилищу файлов каталога
	*/
	public $path_to_storage = 'filestorage/';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'title' => array('type' => 'string'),
		'comment' => array('type' => 'text'),
		'width' => array('type' => 'integer'),
		'height' => array('type' => 'integer'),
		'left' => array('type' => 'integer', 'protected' => 1),
		'right' => array('type' => 'integer', 'protected' => 1),
		'level' => array('type' => 'integer', 'protected' => 1)
	);
	
	public function __construct () {
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}

	/**
	*	Получить путь к хранилищу файлов на файловой системе
	*/
	public function get_path_to_storage()
	{
		return BASE_PATH . $this->path_to_storage;
	}

	/**
	*	Список доступных слайдеров
	*/
	protected function sys_available()
	{
		$this->_flush();
		$this->connector->exec('SELECT `bg`.`id`, CONCAT(`bg`.`title`, " [", `bg`.`width`, "x", `bg`.`height`, "]") AS `title`, COUNT(*) AS `count`
			FROM `www_slide` AS `b` LEFT JOIN `www_slide_group` AS `bg` ON `b`.`slide_group_id` = `bg`.`id`
			GROUP BY `bg`.`id` HAVING `count` > 0', array(), true);
		$data = $this->get_results();
		return response::send($data, 'json');
	}
	
	/**
	*	Добавить альбом
	* @access protected
	*/
	protected function sys_set()
	{
		if ($this->args['_sid'] > 0)
		{
			$this->_flush();
			$this->insert_on_empty = false;
			$result = $this->extjs_set_json(false);
		}
		else if($this->args['pid'] > 0)
		{
			$ns = new nested_sets($this);
			unset($this->args['_sid']); // Иначе будет пытаться обновить нулевую ноду
			
			if ($ns->add_node($this->args['pid']))
			{
				$this->args['_sid'] = $this->get_lastChangedId(0);
				$result = array(
					'success' => true,
					'data' => array(
						'id' =>  $this->get_lastChangedId(0)
					));
			}
			else
			{
				$result = array(
					'success' => false,
					'errors' =>  $e->getMessage()
					);
			}
		}
		
		response::send($result, 'json');
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
	*	Получить XML-пакет данных для ExtJS-формы
	* @access protected
	*/
	protected function sys_get()
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
                $fields = array('id', 'title' => 'text');
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
	*	Удалить узел
	* @access protected
	*/
	protected function sys_unset()
	{
		$this->_flush();
		$id = intval($this->args['_sid']);
		$cids = (array)$this->unset_recursively($id);

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

		response::send($result, 'json');
	}

	/**
	*	Получаем ID удаляемого узла и его потомков
	* @access protected
	* @param	integer	$id	ID удаляемого узла
	* @return	array		Массиво ID удаляемых узлов
	*/
	protected function unset_recursively($id)
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

	/**
	*	Удалить рекурсивно все связи
	* @access protected
	protected function unset_recursively($id)
	{
		$ns = new nested_sets($this);
		$childs = $ns->get_childs($id);

		$ids = array($id);
		foreach ($childs as $child) $ids[] = $child['id'];

		$sc = data_interface::get_instance('www_slide');
		$sc->remove_files($ids);
	}
	*/
}
?>
