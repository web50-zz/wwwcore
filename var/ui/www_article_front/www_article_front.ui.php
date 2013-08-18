<?php
/**
*
* @author   9* <9@u9.ru>  refactored at  26072013
* @package	SBIN Diesel
*/
class ui_www_article_front extends user_interface
{
	public $title = 'www: Публикации - front';

	protected $deps = array(
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/';
		$this->unique_marker = 's';
		$this->default_root = 'articles';
	}
 	/**
	*	Интерпретатор входящих параметров
	*/
	public function pub_content()
	{
		if(SRCH_URI == '')
		{
			return $this->get_post_list();
		}
		if(preg_match('/tag\//',SRCH_URI))
		{
			return $this->list_by_tag();
		}
		$di = data_interface::get_instance('www_article_url_indexer');
		$res = $di->search_by_uri('/'.SRCH_URI);
		if($res['item_id']>0)
		{
			return $this->get_item($res['item_id']);
		}
		if($res['item_id']==0 && $res['category_id'] >0)
		{
			$this->detected_category = $res['category_id'];
			return $this->get_post_list($res['category_id']);
		}
		return 'Not found';
	}

	//9*  вывод списка постов  по входному   тэгу
	public function list_by_tag()
	{
		$parts = explode('/',SRCH_URI);
		$tag = $parts[1];
		$page = request::get('page', 1);
		$limit = $this->get_args('limit',5);
		$di =  data_interface::get_instance('www_article_indexer');
		$this->args['srch'] = array(
			'tag'=>$tag,
			'sort'=>'release_date',
			'dir'=>'DESC',
			'start' => ($page - 1) * $limit,
			'post_type'=>1,
			'limit'=>$limit,
		);
		$this->prepare_search();
		$data = $di->get_list_by_srch($this->args['srch']);
		$pager = user_interface::get_instance('pager');
		$st=user_interface::get_instance('structure');
		$st->collect_resources($pager,'pager');
		$data['pager'] =$pager->get_pager(array('page' => $page, 'total' => $data['total'], 'limit' => $limit, 'prefix' => $_SERVER['QUERY_STRING']));
		return $this->parse_tmpl('list.html',$data);
	}

	//9* вывод  списка постов с типом поста  - Пост 
	public function get_post_list($id = '')
	{
		$di =  data_interface::get_instance('www_article_indexer');
		$limit = $this->get_args('limit',5);
		$page = request::get('page', 1);
		$this->args['srch'] = array(
			'category_id'=>$id,
			'sort'=>'release_date',
			'dir'=>'DESC',
			'start' => ($page - 1) * $limit,
			'post_type'=>1,
			'limit'=>$limit,
		);
		$this->prepare_search();
		$data = $di->get_list_by_srch($this->args['srch']);
		$pager = user_interface::get_instance('pager');
		$st=user_interface::get_instance('structure');
		$st->collect_resources($pager,'pager');
		$data['pager'] =$pager->get_pager(array('page' => $page, 'total' => $data['total'], 'limit' => $limit, 'prefix' => $_SERVER['QUERY_STRING']));
		return $this->parse_tmpl('list.html',$data);

	}
	//9*  метод для вывода на морду  списка постов  по задаваемымы  в вьюпоинте  параметрам вплоть до шаблона вывода
	public function pub_get_list_parametric()
	{
		$di =  data_interface::get_instance('www_article_indexer');
		$limit = $this->get_args('limit',5);
		$post_type = $this->get_args('post_type',1);
		$template = $this->get_args('template','list.html');
		$page = request::get('page', 1);
		$category_id = $this->get_args('category_id','');
		$enable_pager = $this->get_args('pager',false);
		$this->args['srch'] = array(
			'sort'=>'release_date',
			'dir'=>'DESC',
			'start' => ($page - 1) * $limit,
			'post_type'=>$post_type,
			'limit'=>$limit,
		);
		if($category_id>0)
		{
			$this->args['srch']['category_id'] = $category_id;
		}
		$this->prepare_search();
		$data = $di->get_list_by_srch($this->args['srch']);
		if($enable_pager == true)
		{
			$pager = user_interface::get_instance('pager');
			$st=user_interface::get_instance('structure');
			$st->collect_resources($pager,'pager');
			$data['pager'] =$pager->get_pager(array('page' => $page, 'total' => $data['total'], 'limit' => $limit, 'prefix' => $_SERVER['QUERY_STRING']));
		}
		return $this->parse_tmpl($template,$data);
	}

	public function get_item($id)
	{
		$di =  data_interface::get_instance('www_article_indexer');
		$data = $di->get_record($id);
		return $this->parse_tmpl('item.html',$data);
	}

	// 9* 30072013 список категорий  в шаблон для разных нужд навигации например
	public function pub_categories()
	{
		$di =  data_interface::get_instance('www_article_type');
		$data = $di->get_counted_list();//9* если что, есть проще метод без подсчета вхождений - $di->get_simple_list()
		$data['current_category'] = $this->detected_category; //9* это работает только, если на странице ранее уже вызывался метод pub_content где детект происходит
		return $this->parse_tmpl('categories.html',$data);
	}

	// 9*  30072013 список тагов
	public function pub_tags()
	{
		$di =  data_interface::get_instance('www_article_tags');
		$data = $di->get_assigned_list();
		return $this->parse_tmpl('tags.html',$data);
	}

	/* 9*  небольшой хук для интерпретации входных параметров и добавлению их в массив параметров для дальнейшей передачи в  DI
	*  исполльзуется  в  get_post_list  list_by_tag
	*/
	public function prepare_search()
	{
		$string = request::get('s','');
		if($string != '')
		{
			$this->args['srch']['s'] = $string;
		}
	}
		
}
?>
