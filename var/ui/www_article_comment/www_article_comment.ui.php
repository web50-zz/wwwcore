<?php
/**
*	ПИ "Статьи: Комментарии пользователей"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_www_article_comment extends user_interface
{
	public $title = 'www: Публикации - Комментарии пользователей';

	protected $deps = array(
		'main' => array(
			'www_article_comment.main_grid',
			'www_article_comment.main_filter',
		),
		'main_grid' => array(
			'www_article_comment.grid',
		),
	);
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	/**
	*	Список комментариев
	*/
	protected function pub_comments_list()
	{
		$uiArticle = user_interface::get_instance('article');

		// Определяем ID статьи
		if (preg_match("/{$uiArticle->unique_marker}\/([a-zA-Z1-90\-_\/]+)\/([a-zA-Z1-90\-_]+)\.html$/", SRCH_URI, $matches))
		{
			if (($aid = $uiArticle->search_item_id($matches)) > 0)
			{
				$pager = user_interface::get_instance('pager');
				$page = request::get('page');
				$limit = $this->get_args('limit', 10);
				$st=user_interface::get_instance('structure');
				$st->collect_resources($pager,'pager');

				$template = $this->get_args('tmpl', 'comments_list.html');
				$data = data_interface::get_instance('www_article_comment')->get_article_comments($aid, $limit, $page);
				$data = array_merge($data, array(
					'page' => $page,
					'limit' => $limit,
					'pager' => $pager->get_pager(array('page' => $page, 'total' => $comments['total'], 'limit' => $limit, 'prefix' => $_SERVER['QUERY_STRING'])),
				));
				return $this->parse_tmpl($template, $data);
			}
		}

		return '';
	}

	/**
	*	Пользовательская форма для ввода комментария
	*/
	protected function pub_user_form()
	{
		$uiArticle = user_interface::get_instance('article');

		if (preg_match("/{$uiArticle->unique_marker}\/([a-zA-Z1-90\-_\/]+)\/([a-zA-Z1-90\-_]+)\.html$/", SRCH_URI, $matches))
		{
			if (($id = $uiArticle->search_item_id($matches)) > 0)
			{
				$template = $this->get_args('tmpl', 'user_form.html');
				$data = array(
					'article_id' => $id,
				);
				return $this->parse_tmpl($template, $data);
			}
		}

		return '';
	}

	/**
	*	Метод для добавления пользовательского комментария
	*/
	protected function pub_add_comment()
	{
		require_once INSTANCES_PATH .'wwwcore/lib/dapphp-securimage-3.2RC2/securimage.php';
		$captcha = new Securimage();
		if ($captcha->check(request::get('user_code')) != false)
		{
			// Определяем ID статьи
			if (($aid = request::get('x')) > 0)
			{
				$di = data_interface::get_instance('www_article_comment');
				$di->_flush();
				$di->push_args(array(
					'created_datetime' => date('Y-m-d H:i:s'),	// Дата создания комментария
					'published' => 1,					// Признак того, что комментарий сразу публикуется
					'article_id' => $aid,				// ID статьи, к которой прикрепляется комментарий
					'name' => request::get('commentor_name'),	// Имя пользователя
					'comment' => htmlspecialchars(request::get('user_comment')),	// Комментарий
				));
				$di->_set();
				$di->pop_args();
				$response = array('success' => true);
			}
			else
			{
				$response = array('success' => false);
			}
		}
		else
		{
				$response = array('success' => false, 'message' => 'Неверно указан код');
		}

		response::send($response, 'json');
	}
	
	/**
	*       Page configure form
	*/
	protected function sys_configure_form()
	{
		$tmpl = new tmpl($this->pwd() . 'configure_form.js');
		response::send($tmpl->parse($this), 'js');
	}

	protected function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'main.js');
		response::send($tmpl->parse($this), 'js');
	}

	protected function sys_main_grid()
	{
		$tmpl = new tmpl($this->pwd() . 'main_grid.js');
		response::send($tmpl->parse($this), 'js');
	}

	protected function sys_main_filter()
	{
		$tmpl = new tmpl($this->pwd() . 'main_filter.js');
		response::send($tmpl->parse($this), 'js');
	}

	protected function sys_grid()
	{
		$tmpl = new tmpl($this->pwd() . 'grid.js');
		response::send($tmpl->parse($this), 'js');
	}

	protected function sys_item_form()
	{
		$tmpl = new tmpl($this->pwd() . 'item_form.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
