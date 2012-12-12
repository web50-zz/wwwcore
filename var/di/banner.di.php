<?php
/**
*	Интерфейс данных "Баннеры"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_banner extends data_interface
{
	public $title = 'Баннеры';

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
	protected $name = 'banner';

	/**
	* @var	string	$path_to_storage	Путь к хранилищу файлов каталога
	*/
	public $path_to_storage = 'filestorage/bnr/';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'banner_group_id' => array('type' => 'integer', 'alias' => 'pid'),
		'type' => array('type' => 'integer'),
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
		$bg = $this->join_with_di('banner_group', array('banner_group_id' => 'id'));
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
			unset($file['type']);// Ибо type - это тип баннера (1 - изображение, 2 - flash)
			$this->set_args($file, true);
			$this->_flush();
			$this->insert_on_empty = true;
			$result = $this->extjs_set_json(false);
		}
		else
		{
			$result = array('success' => false);
		}
		
		response::send(response::to_json($result), 'html');
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
		$this->_flush();
		$records = (array)json_decode($this->get_args('records'), true);
		if(count($records)>0)
		{
			$rr =  implode(',',$records);
			$this->push_args(array('_sid' => $rr));
			$files = $this->_get();
			$this->pop_args();
		}
		$this->_flush();
		$data = $this->extjs_unset_json(false);
		
		if (!empty($files))
		{
			foreach ($files as $file)
			{
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
