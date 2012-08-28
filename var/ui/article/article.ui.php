<?php
/**
*	ПИ "Новости"
*
* @author   9* 9@u9.ru  cloned  news ui 06072012
* @package	SBIN Diesel
*/
class ui_article extends user_interface
{
	public $title = 'Статьи';

	protected $deps = array(
		'main' => array(
			'article.item_form',
			'article_type.main',
		)
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/';
		$this->unique_marker = 's';
		$this->default_root = 'articles';
	}
 	/**
	*	Вывод списка новостей
	*/
	public function pub_content()
	{
		if(SRCH_URI == '')
		{
			return $this->get_list();
		}
		if(preg_match('/'.$this->unique_marker.'\/$/',SRCH_URI))
		{
			return $this->get_list();
		}
	
		if(preg_match('/'.$this->unique_marker.'\/([a-zA-Z1-90\-_\/]+)\/$/',SRCH_URI,$matches))
		{
			return $this->get_list($matches[1]);
		}
		if(preg_match('/'.$this->unique_marker.'\/([a-zA-Z1-90\-_\/]+)\/([a-zA-Z1-90\-_]+)\.html$/',SRCH_URI,$matches))
		{
			$di = data_interface::get_instance('article');
			$id = $this->search_item_id($matches);
			if ($id>0)
			{
				$di->set_args(array('_sid' => $id));
				$di->_flush();
				$data = $di->extjs_form_json(false,false);
				$out = $data['data'];
				$out['types_list'] = $this->types_list();
				return $this->parse_tmpl('page.html', $out);
			}
			response::redirect('/articles/');
		}
	}

	public function get_list($category_uri = '')
	{
			$category = $this->search_category_id($category_uri);
			if($category_uri != '' && !($category >0))
			{
				response::redirect('/articles/');
			}
			$di = data_interface::get_instance('article');
			$di->_flush();
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
			if ($category > 0)
			{
				$di->set_args(array('_scategory' => $category), true);
			}
			$di2 = $di->join_with_di('article_type',array('category'=>'id'),array('uri'=>'cat_uri'));
			$data = $di->extjs_grid_json(array(
				'id',
				'release_date',
				"CONCAT('/{$di->path_to_storage}', `image`)" => 'image',
				'title',
				'author',
				'source',
				'uri',
				array('di'=>$di2,'name'=>'uri'),
				'content'
			), false);
			// Создаём аннотации для новостей
			foreach ($data['records'] as $n => $record)
			{
				$data['records'][$n]['_annotation'] = $this->substr_by_words($record['content']);
			}
			$pager = user_interface::get_instance('pager');
			$data['unique'] = $this->unique_marker;
			$data['root'] = $this->default_root;
			$data['page'] = $page;
			$data['limit'] = $limit;
			$data['pager'] = $pager->get_pager(array('page' => $page, 'total' => $data['total'], 'limit' => $limit, 'prefix' => $_SERVER['QUERY_STRING']));
			$data['types_list'] = $this->types_list($category);
			return $this->parse_tmpl('default.html', $data);

	}

	public function search_category_id($category_uri)
	{
			$di =  data_interface::get_instance('article_type');
			return $di->search_by_uri('/'.$category_uri.'/');
	}
	public function search_item_id($matches)
	{
			$category_uri =  $matches[1];
			$item_uri =  $matches[2];
			$di =  data_interface::get_instance('article');
			return $di->search_by_uri($item_uri,$category_uri);
	}
	// 9* 22072012 список типов в шаблон для разных нужд навигации например

	public function pub_types_list()
	{
		return $this->types_list();
	}

	public function types_list($current = '')
	{
		$di = data_interface::get_instance('article_type');
		$di->_flush();
		$di->set_args(array());
		$di->set_order('left','ASC');
		$data = $di->extjs_grid_json(array('id','title','uri','name'),false);
		$data['current'] = $current;
		return $this->parse_tmpl('types_list.html',$data);
	}

	/**
	*	Обрезание строк по словам, с избеганием проблемы substr и UTF-8
	*/
	public function substr_by_words($content, $length = 50, $s = ' ')
	{
		$content = strip_tags($content);
		$words = explode($s, $content);
		if (count($words) > $length)
			$content = join(stripslashes($s), array_slice($words, 0, $length));
		return $content;
	}
       
	/**
        *       Отрисовка контента для внешней части  имеет парамтер limit определяющий лимит записей для вывода
        */
        public function pub_top()
        {
			$di = data_interface::get_instance('article');
			$di->_flush();
			$limit = $this->get_args('limit', 10);
			$page = request::get('page', 1);
			$di->set_args($this->args);
			$di->set_args(array(
				'sort' => 'release_date',
				'dir' => 'DESC',
				'start' => ($page - 1) * $limit,
				'limit' => $limit,
			));

			$di2 = $di->join_with_di('article_type',array('category'=>'id'),array('uri'=>'cat_uri'));
			$data = $di->extjs_grid_json(array(
				'id',
				'release_date',
				"CONCAT('/{$di->path_to_storage}', `image`)" => 'image',
				'title',
				'author',
				'source',
				'uri',
				array('di'=>$di2,'name'=>'uri'),
				'content'
			), false);
			// Создаём аннотации для новостей
			foreach ($data['records'] as $n => $record)
			{
				$data['records'][$n]['_annotation'] = $this->substr_by_words($record['content'],'15');
			}
			$pager = user_interface::get_instance('pager');
			$data['unique'] = $this->unique_marker;
			$data['root'] = $this->default_root;
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
		$tmpl = new tmpl($this->pwd() . 'article.js');
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
}
?>
