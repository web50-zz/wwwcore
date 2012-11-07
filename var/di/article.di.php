<?php
/**
*	Интерфейс данных "Новости"
*
* @author       9*  9@u9.ru cloned news.di.php
* @package	SBIN Diesel
* 9* if in registry exists key - 'article_thumb_size' for example  90x20,  then this size will be applied on thumb over current article defaults 
*/
class di_article extends data_interface
{
	public $title = 'Лента новостей';
	
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
	* @var	string	$path_to_storage	Путь к хранилищу файлов каталога
	*/
	public $path_to_storage = 'filestorage/article/';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => 1),
		'category' => array('type' => 'integer'),
		'image' => array('type' => 'string'),
		'release_date' => array('type' => 'date'),
		'title' => array('type' => 'string'),
		'source' => array('type' => 'string'),
		'author' => array('type' => 'string'),
		'uri' => array('type' => 'string'),
		'content' => array('type' => 'text')
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
	*	Получить JSON-пакет данных для ExtJS-грида
	* @access protected
	*/
	protected function sys_list()
	{
		//$this->_flush(true);
		//$sc = $this->join_with_di('structure_content', array('id' => 'cid'), array('pid' => 'pid'));
		$this->_flush();
		if (!empty($this->args['query']) && !empty($this->args['field']))
		{
			$this->args["_s{$this->args['field']}"] = "%{$this->args['query']}%";
		}
		$this->extjs_grid_json(array(
			'id',
			'release_date',
			"CONCAT('/{$this->path_to_storage}', `image`)" => 'image',
			'title',
			'author',
			'source'
		));
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

	/**
	*	Сохранить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	protected function sys_set()
	{
		$id = $this->get_args('_sid');
		try{
			$this->check_input();
			$this->check_uniq_uri();
			if ($id > 0)
			{
				$this->_flush();
				$this->_get();
				$image = $this->get_results(0);
				$old_image_name = $image->image;
			}
			if(!is_dir($this->get_path_to_storage()))
			{
				mkdir($this->get_path_to_storage());
			}
			$image = (!empty($old_image_name)) ? file_system::replace_file('file', $old_image_name, $this->get_path_to_storage()) : file_system::upload_file('file', $this->get_path_to_storage());
			if (!empty($image['real_name'])) $this->set_args(array('image' => $image['real_name']), true);
			$this->resize_original($image);
			
			$this->_flush();
			$this->insert_on_empty = true;
			$data = $this->extjs_set_json(false);
			response::send(response::to_json($data), 'html');
		}
		catch(Exception $e)
		{
			$data['success'] = false;
			$data['errors'] =  $e->getMessage();
			response::send(response::to_json($data),'html');
		}
	}

	/**
	*	Уменьшить изображение
	*/
	private function resize_original($file)
	{
		$width = registry::get('article_preview_width');
		$height = registry::get('article_preview_height');
		if(!($width>0)){
			$width = 99;
		}
		if(!($height)>0){
			$height = 75;
		}

		if (!empty($file) && $file['real_name'])
		{			
			require_once INSTANCES_PATH .'wwwcore/lib/thumb/ThumbLib.inc.php';
			// regular image
			$thumb = PhpThumbFactory::create($this->get_path_to_storage() . $file['real_name']);
			$res =  registry::get('article_thumb_size');
			if($res != '')
			{
				$size = explode('x',$res);
				if($size[0]>0 && $size[1]>0)
				{
					$width = $size[0];
					$height = $size[1];
				}
			}
			$thumb->adaptiveResize($width, $height);
			$thumb->save($this->get_path_to_storage() . $file['real_name']);
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
	*	Удалить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	protected function sys_unset()
	{
		$this->_flush();
		$this->_get();
		$images = $this->get_results();

		$this->_flush();
		$data = $this->extjs_unset_json(false);
		$ids = $this->get_lastChangedId();
		
		if (($ids > 0 || count($ids) > 0) && $this->args['_spid'] > 0)
		{
			$sc = data_interface::get_instance('structure_content');
			$sc->remove_link($this->args['_spid'], $ids, $this->name);
		}
		
		if (!empty($images))
		{
			foreach ($images as $image)
			{
				file_system::remove_file($image->image, $this->get_path_to_storage());
			}
		}

		response::send($data, 'json');
	}

	public function search_by_uri($uri = '',$cat_uri = '')
	{
		if($uri == '')
		{
			return false;
		}
		$this->_flush();
		$this->set_args(array('_suri'=>$uri));
		$di2 = $this->join_with_di('article_type',array('category'=>'id'),array('uri'=>'cat_uri'));
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
	
	// 9* 23072012 проверяем наличие  узла с именем в аргументах на уникальность
	public function check_uniq_uri()
	{
		if($this->args['_sid']>0)
		{
			$where = " AND  id != {$this->args['_sid']} ";
		}
		$sql = "SELECT count(*) AS cnt FROM {$this->name} WHERE uri = '{$this->args['uri']}' $where";
		$this->_get($sql);
		$res =  $this->get_results();
		if($res[0]->cnt>0)
		{
			throw new Exception('Используйте другой URI для узла. Текущий не уникален.');
		}
	}
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
