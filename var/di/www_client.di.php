<?php
/**
*	Интерфейс данных "WWW: Клиенты"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_www_client extends data_interface
{
	public $title = 'WWW: Клиенты';

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
	protected $name = 'www_client';

	/**
	* @var	string	$path_to_storage	Путь к хранилищу файлов каталога
	*/
	public $path_to_storage = 'filestorage/www_client/';

	/**
	* @var	array	$_preview	Массив с описанием параметров preview-файлов по умолчанию, можно переопределить ключём реестра "www_client"
	*/
	protected $_preview = array(
		'thumb' => array(
			'width' => 332,
			'height' => 294,
			'adaptive' => true,	//Адаптивное масштабирование true - Да, false - Нет
			'bckgr' => null,	//Указывается цвет подложки (white, black, red и т.п.), если null, то подложка не используется
		)
	);
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'order' => array('type' => 'integer'),		// Порядок отображение
		'client_name' => array('type' => 'string'),	// Наименование клиента
		'real_name' => array('type' => 'string'),	// Файл с логотипом
		'description' => array('type' => 'text'),	// Описание
		'link' => array('type' => 'string'),		// Ссылка
		'photoalbum_id' => array('type' => 'integer'),		// Фотоальбом
	);
	
	public function __construct ()
	{
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}

	/**
	*	Применить параметры для превью
	* @param	string	$reg_key	Имя ключа в реестре с параметрами preview
	*/
	protected function prepare_preview_params($reg_key = 'www_client')
	{
		if ($reg_key && ($new_params = registry::get($reg_key)) != '')
		{
			// {"thumb": {"width": 50, "height": 50, "adaptive": true, "bckgr": null}}
			$this->_preview = $new_params;
		}
	}

	/**
	*	Получить путь к хранилищу файлов на файловой системе
	*/
	public function get_path_to_storage()
	{
		return BASE_PATH . $this->path_to_storage;
	}
	
	protected function sys_list()
	{
		$this->_flush();
		$this->set_order('order', 'ASC');
		$this->extjs_grid_json(array(
			'id',
			'order',
			"CONCAT('/', '{$this->path_to_storage}', 'thumb-', `real_name`)" => 'preview',
			'client_name',
			'link',
		));
	}

	protected function sys_combo_list()
	{
		$this->_flush();
		$this->set_order('order', 'ASC');
		$data = $this->extjs_grid_json(array(
			'id',
			'order',
			"CONCAT('/', '{$this->path_to_storage}', 'thumb-', `real_name`)" => 'preview',
			'client_name',
			'link',
		),false);
		array_unshift($data['records'],array('id'=>'0','client_name'=>'Не выбран'));
		response::send($data,'json');
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
		$values = $this->get_args(array('opos', 'npos', 'id'));

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
	public function sys_set()
	{
		$fid = $this->get_args('_sid');
		$silent = $this->get_args('silent',false);
		$from_source = $this->get_args('source',false);
		if ($fid > 0)
		{
			$this->_flush();
			$this->_get();
			$file = $this->get_results(0);
			$old_file_name = $file->real_name;
		}

		$dir = $this->get_path_to_storage();
		// Create share folder if not exists
		if (!file_exists($dir)) mkdir($dir, 0775, true);

		if(!empty($old_file_name))
		{
			if($from_source != false)
			{
				$file = file_system::copy_file($from_source,$this->get_path_to_storage(), $old_file_name);
			}
			else
			{
				$file = file_system::replace_file('file', $old_file_name, $this->get_path_to_storage());
			}
		}else{
			if($from_source != false)
			{
				$file = file_system::copy_file($from_source, $this->get_path_to_storage());
			}
			else
			{
				$file = file_system::upload_file('file', $this->get_path_to_storage());
			}
		};


//		$file = (!empty($old_file_name)) ? file_system::replace_file('file', $old_file_name, $dir) : file_system::upload_file('file', $dir);
		
		if ($file !== false)
		{
			$this->prepare_preview_params();
			if (!($fid > 0))
			{
				$file['order'] = $this->get_new_order();
			}

			if ($file['real_name'])
			{
				// Удаляем старые превьюшки
				foreach ($this->_preview as $pref => $params)
				{
					file_system::remove_file("{$pref}-{$old_file_name}", $dir);
				}
			}

			$this->set_args($file, true);
			$this->_flush();
			$this->insert_on_empty = true;
			$result = $this->extjs_set_json(false);

			if ($file['real_name'])
			{
				//list($file['width'], $file['height']) = getimagesize($dir . $file['real_name']);
				//$file['real_name'] = $this->create_preview_9($file);
				foreach ($this->_preview as $pref => $params)
				{
					$params = $params;
					$this->create_preview($file, "{$pref}-", $params['width'], $params['height'], $params['adaptive'], $params['bckgr']);
				}
			}
		}
		else
		{
			$result = array('success' => false);
		}
		if($silent == true)
		{
			return $result;
		}
		response::send(response::to_json($result), 'html');
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
		$dir = $this->get_path_to_storage();
		$files = $this->_get()->get_results();
		$this->_flush();
		$data = $this->extjs_unset_json(false);
		
		if (!empty($files))
		{
			$this->prepare_preview_params();
			foreach ($files as $file)
			{
				file_system::remove_file($file->real_name, $dir);
				foreach ($this->_preview as $pref => $params)
				{
					file_system::remove_file("{$pref}-{$file->real_name}", $dir);
				}
			}
		}

		response::send($data, 'json');
	}
	
	/**
	*	Создать preview-изображение
	*
	* @access	private
	* @param	array	$file	Массив с данными по текущему файлу
	* @param	string	$pref	Префикс превьюшки, по умолчанию 'thumb-'
	* @param	integer	$width	Ширина превьюшки, по умолчанию 300px
	* @param	integer	$height	Высота превьюшки, по умолчанию 300px
	* @param	boolean	$adaptive	Адаптивное масштабирование true - Да, false - Нет
	* @param	string	$bckgr	Указывается цвет подложки (white, black, red и т.п.), если null, то подложка не используется
	*/
	private function create_preview($file, $pref = "thumb-", $width = 300, $height = 300, $adaptive = true, $bckgr = null)
	{
		require_once INSTANCES_PATH .'wwwcore/lib/thumb/ThumbLib.inc.php';
		$file_name = $this->get_path_to_storage() . $file['real_name'];
		$thumb_name = $this->get_path_to_storage() . $pref . $file['real_name'];
		$thumb = PhpThumbFactory::create($file_name);
		// Если указан адаптивное изменение размеров, то применяем adaptiveResize()
		if ($adaptive)
			$thumb->adaptiveResize($width, $height);
		else
			$thumb->resize($width, $height);
		// Сохраняем превью в указанный файл
		$thumb->save($thumb_name);

		// Если указана полдложка, то накладываем её
		if ($bckgr !== null)
		{
			$crImg = new Imagick($thumb_name);
			$bgImg = new Imagick();
			$bgImg->newImage($width, $height, new ImagickPixel('white'));
			$bgImg->setImageFormat($crImg->getImageFormat());
			$bgImg->setImageColorspace($crImg->getImageColorspace());
			$bgImg->compositeImage($crImg, $crImg->getImageCompose(), (int)($width - $crImg->getImageWidth()) / 2, (int)($height - $crImg->getImageHeight()) / 2);
			$bgImg->writeImage($thumb_name);
		}
	}

	//9* apply mask using GD
	public function mask_it($file, $new_p)
	{
		$filen = $this->get_path_to_storage() . 'thumb-'.$new_p;
		$mask = INSTANCES_PATH . '/res/mask175.gif';
		$image = imagecreatefrompng($filen);
		$mask = imagecreatefromgif($mask);
		$dest = imagecreatetruecolor(imagesx($image), imagesy($image));
		$obj = $this->imagemask($dest, $image, $mask);
		imagepng($obj, $filen, 9);
		imagedestroy($obj);
	}

	public function imagemask($dest,$image,$mask)
	{
		$width =  imagesx($image);
		$height = imagesy($image);
		$tc = imagecolorallocate($dest, 0, 0, 0);
		imagecolortransparent($dest, $tc);
		for ($i = 0; $i < $width; $i++)
		{
			for ($j = 0; $j < $height; $j++)
			{
				$c = imagecolorat($image, $i, $j);
				$color = imagecolorsforindex($image, $c);
				$c = imagecolorat($mask, $i, $j);
				$mcolor = imagecolorsforindex($mask, $c);

				if (!($mcolor['red']==255 && $mcolor['green']==255 && $mcolor['blue']==255))
				{
					$c = imagecolorallocate($dest, $color['red'], $color['green'], $color['blue']);
					imagesetpixel($dest, $i, $j, $c);
				} 
			}
		}
		return $dest;
	}
}
?>
