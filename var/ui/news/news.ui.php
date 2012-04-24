<?php
/**
*	ПИ "Новости"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_news extends user_interface
{
	public $title = 'Новости';

	protected $deps = array(
		'main' => array(
			'news.item_form',
		)
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}
 	/**
	*	Вывод списка новостей
	*/
	public function pub_content()
	{
		$di = data_interface::get_instance('news');
		$id = request::get('id', false);
		if ($id)
		{
			$di->set_args(array('_sid' => $id));
			$di->_flush();
			$di->_get();
			return $this->parse_tmpl('page.html', $di->get_results(0));
		}
		else
		{
			$limit = $this->get_args('limit', 10);
			$page = request::get('page', 1);
			$di->set_args($this->args);
			$di->set_args(array(
				'sort' => 'release_date',
				'dir' => 'DESC',
				'start' => ($page - 1) * $limit,
				'limit' => $limit,
			));

			//9* если задана категория в конфиге
			if (($category = $this->get_args('category', 0)) > 0)
				$di->set_args(array('_scategory' => $category), true);

			$data = $di->extjs_grid_json(array(
				'id',
				'release_date',
				"CONCAT('/{$di->path_to_storage}', `image`)" => 'image',
				'title',
				'author',
				'source',
				'content'
			), false);
			// Создаём аннотации для новостей
			foreach ($data['records'] as $n => $record)
				$data['records'][$n]['_annotation'] = $this->substr_by_words($record['content']);

			$pager = user_interface::get_instance('pager');
			$data['page'] = $page;
			$data['limit'] = $limit;
			$data['pager'] = $pager->get_pager(array('page' => $page, 'total' => $data['total'], 'limit' => $limit, 'prefix' => $_SERVER['QUERY_STRING']));
			return $this->parse_tmpl('default.html', $data);
		}
	}

	/**
	*	Обрезание строк по словам, с избеганием проблемы substr и UTF-8
	*/
	public function substr_by_words($content, $length = 50, $s = ' ')
	{
		$content = strip_tags($content);
		$words = split($s, $content);
		if (count($words) > $length)
			$content = join(stripslashes($s), array_slice($words, 0, $length));
		return $content;
	}
       
	/**
        *       Отрисовка контента для внешней части  имеет парамтер limit определяющий лимит записей для вывода
        */
        public function pub_top()
        {
		$limit = $this->get_args('limit', 10);
		$page = request::get('page', 1);
                $di = data_interface::get_instance('news');
		$di->set_args($this->args);
			$di->set_args(array(
				'sort' => 'release_date',
				'dir' => 'DESC',
				'start' => ($page - 1) * $limit,
				'limit' => $limit,
			));
			$category = $this->get_args('category',0);
		if($category>0)//9* если задана категория в конфиге
		{
			$di->set_args(array('_scategory'=>$category),true);
		}
		$data = $di->extjs_grid_json(false,false);
		// Создаём аннотации для новостей
		foreach ($data['records'] as $n => $record)
			$data['records'][$n]['_annotation'] = $this->substr_by_words($record['content'], 15);
		$pager = user_interface::get_instance('pager');
		$data['page'] = $page;
		$data['limit'] = $limit;
		$data['pager'] = $pager->get_pager(array('page' => $page, 'total' => $data['total'], 'limit' => $limit, 'prefix' => $_SERVER['QUERY_STRING']));
		return $this->parse_tmpl('top.html',$data);
        }
	
	/**
	*       Управляющий JS админки
	*/
	public function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'news.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_item_form()
	{
		$tmpl = new tmpl($this->pwd() . 'item_form.js');
		response::send($tmpl->parse($this), 'js');
	}

	public function pub_double()
        {
		return 'guest book here';
	}


}
?>
