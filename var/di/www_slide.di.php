<?php
/**
*	Интерфейс данных "WWW: Группы слайдов"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_www_slide extends data_interface
{
	public $title = 'WWW: Группы слайдов';

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
	protected $name = 'www_slide';

	/**
	* @var	string	$path_to_storage	Путь к хранилищу файлов каталога
	*/
	public $path_to_storage = 'filestorage/www_slide/';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'slide_group_id' => array('type' => 'integer', 'alias' => 'pid'),
		'order' => array('type' => 'integer'),
		'type' => array('type' => 'integer'),		// Тип контента (1 - изображение, 2 - Flash, 3 - Видео, 4 - Текст)
		'real_name' => array('type' => 'string'),
		'link' => array('type' => 'string'),
		'target' => array('type' => 'string'),
		'title' => array('type' => 'string'),
		'comment' => array('type' => 'text'),
	);
	
	public function __construct ()
	{
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
	*	Получить данные элемента в виде JSON
	* @access protected
	*/
	public function get($pid)
	{
		$this->_flush(true);
		$this->push_args(array('_spid' => $pid));
		$this->connector->fetchMethod = PDO::FETCH_ASSOC;
		$bg = $this->join_with_di('www_slide_group', array('slide_group_id' => 'id'));
		$this->what = array(
			'*',
			"'/{$this->path_to_storage}'" => 'path',
			array('name' => 'width', 'di' => $bg),
			array('name' => 'height', 'di' => $bg)
		);
		$this->set_order('RAND()');
		//$this->set_order('title');
		$this->set_limit(1);
		//$this->connector->debug = true;
		$this->_get();
		$this->pop_args();
		return $this->get_results(0);
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
	*
	*/
	private function get_new_order($pid)
	{
		$this->_flush();
		$this->_get("SELECT MAX(`order`) + 1 AS `order` FROM `{$this->name}` WHERE `slide_group_id` = {$pid}");
		return $this->get_results(0, 'order');
	}
	
	/**
	*	Пересоздать индекс сортировки в группе (например при удалении элемента из группы)
	* @param	integer	$pid	ID группы
	*/
	protected function reorder_in_group($pid)
	{
		$this->_flush()
			->push_args(array('_spid' => $pid))
			->set_order('order', 'ASC')
			->set_what(array('id'))
			->_get()
			->pop_args();
		$ids = $this->get_results(false, 'id');
		$n = 0;
		foreach ($ids as $id)
		{
			$this->_flush(1)
				->push_args(array('_sid' => $id, 'order' => ++$n))
				->_set()
				->pop_args();
		}
		return $this;
	}

	/**
	*	Реорганизация порядка вывода
	*/
	protected function sys_reorder()
	{
		list($npos, $opos) = array_values($this->get_args(array('npos', 'opos')));
		$values = $this->get_args(array('opos', 'npos', 'id', 'pid'));

		if ($opos < $npos)
			$query = "UPDATE `{$this->name}` SET `order` = IF(`id` = :id, :npos, `order` - 1) WHERE `order` >= :opos AND `order` <= :npos AND `slide_group_id` = :pid";
		else
			$query = "UPDATE `{$this->name}` SET `order` = IF(`id` = :id, :npos, `order` + 1) WHERE `order` >= :npos AND `order` <= :opos AND `slide_group_id` = :pid";

		$this->_flush();
		$this->connector->exec($query, $values);
		response::send(array('success' => true), 'json');
	}
	
	/**
	*	Сохранить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	protected function sys_set()
	{
		$fid = $this->get_args('_sid');

		if ($fid > 0)
		{
			$this->_flush();
			$this->_get();
			$file = $this->get_results(0);
			$old_file_name = $file->real_name;
		}
		if(!is_dir($this->get_path_to_storage()))
		{
			mkdir($this->get_path_to_storage());
		}

		$file = (!empty($old_file_name)) ? file_system::replace_file('file', $old_file_name, $this->get_path_to_storage()) : file_system::upload_file('file', $this->get_path_to_storage());
		
		if ($file !== false)
		{
			unset($file['type']);// Ибо type - это тип слайда
			if (!($fid > 0))
			{
				$file['order'] = $this->get_new_order((int)$this->get_args('slide_group_id'));
			}
			$this->set_args($file, true);
			$this->_flush();
			$this->insert_on_empty = true;
			$result = $this->extjs_set_json(false);
		}
		else
		{
			$result = array('success' => false);
		}
		
		dbg::write($result);
		response::send(response::to_json($result), 'html');
	}

	protected function sys_simple_set()
	{
		if (!($this->get_args('_sid') > 0))
		{
			$this->set_args(array('order' => $this->get_new_order((int)$this->get_args('slide_group_id'))), true);
		}
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
	*	Удалить файл[ы]
	* @access protected
	*/
	protected function sys_unset()
	{
		if ($this->args['records'] && !$this->args['_sid'])
			$this->set_args(array('_sid' => request::json2int($this->args['records'])), true);

		$this->_flush()->set_order('order', 'ASC');
		$files = $this->_get()->get_results();
		$pid = $this->get_results(0, 'www_slide_group');

		$this->_flush();
		$data = $this->extjs_unset_json(false);
		$this->reorder_in_group($pid);
		
		if (!empty($files))
		{
			$n = 0;
			foreach ($files as $file)
			{
				// Удаляем файл
				file_system::remove_file($file->real_name, $this->get_path_to_storage());
			}
		}

		response::send($data, 'json');
	}

	/**
	*	Remove all files
	* @access public
	* @param	array|integer	$ids	The ID for `catalog_item_id` field
	*/
	public function remove_files($ids)
	{
		$this->_flush();
		$this->set_args(array('_spid' => $ids));
		$files = $this->_get();

		$this->_flush();
		$this->set_args(array('_spid' => $ids));
		$this->extjs_unset_json(false);
		
		if (!empty($files))
		{
			foreach ($files as $file)
			{
				file_system::remove_file($file->real_name, $this->get_path_to_storage());
			}
		}
	}
}
?>
