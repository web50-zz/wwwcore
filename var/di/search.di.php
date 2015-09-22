<?php
/**
*	Интерфейс данных "Поиск по сайту"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_search extends data_interface
{
	public $title = 'Поиск по сайту';

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
	protected $name = 'search';
	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
		'exists' => array('type' => 'integer'),			// Флаг актуализации страницы
		'created_datetime' => array('type' => 'datetime'),	// Дата создания (необходим, для обвноелния кэша)
		'uri' => array('type' => 'string'),			// URI страницы
		'title' => array('type' => 'string'),			// Наименование страницы
		'content' => array('type' => 'text'),			// Контент страницы
	);
	
	public function __construct ()
	{
	    // Call Base Constructor
	    parent::__construct(__CLASS__);
	}

	/**
	*	Поиск
	*/
	public function do_search()
	{
		$this->_collect();
		$word = request::get('search');
		$srch = "%{$word}%";
		$table = $this->get_name();
		$data = array();
		if (!empty($word))
		{
			$this->_flush();
			//$site = $this->connector->exec("SELECT *, SUBSTRING(`content`, LOCATE(:word, `content`) - 128, LOCATE(:word, `content`) + 128) AS `finded` FROM `{$table}` WHERE MATCH(`content`) AGAINST (:word)", array('word' => $word), true);
			$site = $this->connector->exec("SELECT *, SUBSTRING(`content`, LOCATE(:word, `content`) - 128, LOCATE(:word, `content`) + 128) AS `finded` FROM `{$table}` WHERE `content` LIKE :srch", array('word' => $word, 'srch' => $srch), true);

			foreach ($site as $page)
			{
				$finded = $page->finded;
				$finded = preg_replace("/({$word})/iu", '<span style="background-color: yellow">\1</span>', $finded);
				$data[] = array_merge((array)$page, array('finded' => $finded));
			}
		}
		return $data;
	}

	/**
	*	Обновить индекс поиска (внешний вызов)
	* @access	protected
	*/
	protected function sys_collect()
	{
	}

	/**
	*	Обновить индекс поиска
	*/
	public function _collect()
	{
		$strcUI = user_interface::get_instance('structure');
		$strcDI = data_interface::get_instance('structure');
		$strcDI->where = '[table]`hidden` = 0';
		$ns = new nested_sets($strcDI);
		$tree = $ns->get_childs(0, NULL);
		foreach ($tree as $page)
		{
			// Формируем страницу
			$content = $strcUI->process_page($page, false);
			// Убираем <!--(SKIP_SEARCH--> чтото <!--SKIP_SEARCH-->
			$content = preg_replace('/\<\!--\(SKIP_SEARCH--\>(.+?)\<\!--SKIP_SEARCH\)--\>(?:\n|\r|)+/ms','',$content);
			// Убираем все тэги
			$content = strip_tags($content);
			//$content = preg_replace("/<[\/]?[^>]*>/", " ", $content);
			// Убираем лишние тэги внутри строки
			$content = preg_replace("/(\s){2,}/", " ", $content);

			// Запоминаем
			$this->push_args(array(
				'_sexists' => 0,
				'_suri' => $page['uri'],
				'exists' => 1,
				'uri' => $page['uri'],
				'title' => $page['title'],
				'content' => $content,
				'created_datetime' => date("Y-m-d H:i:s"),
			));
			$this->_flush();
			$this->insert_on_empty = true;
			$this->_set();
			$this->pop_args();
		}

		// Удаляем устаревшие страницы из индекса
		$this->push_args(array(
			'_sexists' => 0,
		));
		$this->_flush();
		$this->_unset();
		$this->pop_args();

		// Сбрасываем флаг
		$this->push_args(array(
			'_sexists' => 1,
			'exists' => 0,
		));
		$this->_flush();
		$this->_set();
		$this->pop_args();
	}
}
?>
