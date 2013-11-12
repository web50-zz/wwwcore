<?php
/**
*
* @author	9@u9.ru 09112013	
* @package	SBIN Diesel
*/
class di_img_processor_a extends data_interface
{
	public $title = 'IMG_PROCESSOR_PROTOYPE';
	/**
	* @var	integer	$preview_width	Макс. ширина картинки
	*/
	public $preview_width = 136;

	/**
	* @var	integer	$preview_height	Макс. высота картинки
	*/
	public $preview_height = 136;
	/* config example
	public $settings = array(
		'source_di'=>'photoalbum',  //Ди откуда берем  имя файла
		'source_field'=>'preview', // поле в котором хранится  это имя
		'resize'=> true, // ресайзить ли  изображение
		'prefix_result'=>'masked-', // префикс перед названием  файла после всех операций
		'mask'=>array(
			'mask_file' => "img/mask_gallery.png", //  имя файла с маской  в папке текущей темы
		),
		'overlay'=>array(
			'overlay_file' => "img/gallery_tomb_overlay.png", //  имя файла  оверлея в папке текущей темы
			'x_shift'=>15,  // сдвиг оригинала под  оверлеем по горизонтали
			'y_shift'=>1,  // сдвиг по вертикали
		)
	);
	*/

	
	public function __construct () {
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}

	
	private function create_overlay($file_in,$overlay)
	{
		$base = new Imagick();
		$base->readImage($file_in);
		 
		$o = new Imagick();
		$o->readImage($overlay);
		$o->compositeImage($base, imagick::COMPOSITE_OVERLAY, $this->settings['overlay']['x_shift'], $this->settings['overlay']['y_shift']);
		$o->writeImage($this->file_out);
	}
	/**
	*	Создание уменьшенного изображения с наложением маски при помощи ImageMagick
	* @param	string	$file	Имя файла-изображения, для операции
	* @return	string	Новое имя файла
	*/
	private function create_mask($file_in,$mask_file)
	{
		if (empty($file_in)) return false;

		$dt = pathinfo($file_in);
		$base = new Imagick($this->file_out);
		$mask = new Imagick($mask_file);
		// IMPORTANT! Must dectivate the opacity channel
		// See: http://www.php.net/manual/en/function.imagick-setimagematte.php
		$base->setImageMatte(false); 
		$base->compositeImage($mask, Imagick::COMPOSITE_COPYOPACITY, 0, 0);
		$base->writeImage($this->file_out);
		return "{$this->settings['prefix_result']}$this->filename";
	}

	public function process_image($eObj, $ids, $args)
	{
		$data = $this->get_data($ids);
		try{
			$data = $this->prepare_data($data);
		}catch(Exception $e){
			return;
		}
		$file_name = $data[$this->settings['source_field']];
		if($file_name == '')
		{
			return  ;
		}
		$file_as_is = $this->path.'/'.$file_name;
		$dt = pathinfo($file_as_is);

		$filename = "{$dt['filename']}.png";
		$this->filename = $filename;
		$file_out = "{$dt['dirname']}/{$this->settings['prefix_result']}$filename";
		$this->file_out = $file_out;

		if($this->settings['resize'] == true)
		{
			require_once INSTANCES_PATH .'wwwcore/lib/thumb/ThumbLib.inc.php';
			$thumb = PhpThumbFactory::create($file_as_is);
			$thumb->adaptiveResize($this->preview_width, $this->preview_height)->save($file_out);
			$file_as_is = $file_out;
		}

		if(array_key_exists('mask',$this->settings))
		{
			$mask = CURRENT_THEME_PATH.$this->settings['mask']['mask_file'];
		
			if(is_readable($file_as_is))
			{
				$result = $this->create_mask($file_as_is,$mask);
				$file_as_is = $this->path.'/'.$result;
			}
		}
		if(array_key_exists('overlay',$this->settings))
		{
			$overlay = CURRENT_THEME_PATH.$this->settings['overlay']['overlay_file'];;
			if(is_readable($overlay))
			{
				$this->create_overlay($file_as_is,$overlay);
			}
		}
	}


	public function prepare_remove_image($eObj,$ids)
	{
		$data = $this->get_data($ids);
		$file_name = $data[$this->settings['source_field']];
		if($file_name == '')
		{
			return;
		}
		$file_as_is = $this->path.'/'.$file_name;
		$dt = pathinfo($file_as_is);
		$filename = "{$dt['filename']}.png";
		$this->file_to_remove = "{$dt['dirname']}/{$this->settings['prefix_result']}$filename";
	}

	public function remove_image($eObj, $ids, $args)
	{
		if(is_readable($this->file_to_remove))
		{
			unlink($this->file_to_remove);
		}
	}


	public function get_data($ids)
	{
		$di = data_interface::get_instance($this->settings['source_di']);
		$di->_flush(true);
		$di->push_args(array('_sid' => $ids));
		$di->_get();
		$data = (array)$di->get_results(0);
		$di->pop_args();
		$this->path  = $di->get_path_to_storage();
		return $data;
	}
//	Для  операций  в  потомках класса
	public function prepare_data($data)
	{
		return $data;
	}
	public function _listeners()
	{
		if(!($this->settings['source_di'] != ''))
		{
			return  array();
		}

		return array(
			array('di' => $this->settings['source_di'], 'event' => 'onSet', 'handler' => 'process_image'),
			array('di' => $this->settings['source_di'], 'event' => 'onUnset', 'handler' => 'remove_image'),
			array('di' => $this->settings['source_di'], 'event' => 'onBeforeUnset', 'handler' => 'prepare_remove_image'),
		);
	}


}
?>
