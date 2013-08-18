<?php
/**
*	ПИ "Статьи: Рэйтинг"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_www_article_rating extends user_interface
{
	public $title = 'www: Публикации - Рэйтинг';

	protected $deps = array(
	);
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	protected function pub_rating_bar()
	{
		$data = $this->get_args();
		$template = $this->get_args('tmpl', 'rating_bar.html');
                return $this->parse_tmpl($template, $data);
	}

	protected function reload_bar($id)
	{
		$di = data_interface::get_instance('article');
		$di->what = array('id', 'like', 'dislike');
		$di->set_args(array('_sid' => $id));
		$di->_get();
		$data = $di->get_results(0);
		$template = $this->get_args('tmpl', 'reload_bar.html');
                return $this->parse_tmpl($template, $data);
	}

	/**
	*	Статья нравится
	*/
	protected function pub_like()
	{
		$id = request::get('x');
		if (data_interface::get_instance('www_article_rating')->register_action($id, 1))
		{
			response::send(array(
				'success' => true,
				'bar' => $this->reload_bar($id),
			), 'json');
		}
		else
		{
			response::send(array(
				'success' => true,
			), 'json');
		}
	}

	/**
	*	Статья не нравится
	*/
	protected function pub_dislike()
	{
		$id = request::get('x');
		if (data_interface::get_instance('www_article_rating')->register_action($id, 0))
		{
			response::send(array(
				'success' => true,
				'bar' => $this->reload_bar($id),
			), 'json');
		}
		else
		{
			response::send(array(
				'success' => true,
			), 'json');
		}
	}
	
	/**
	*       Page configure form
	*/
	protected function sys_configure_form()
	{
		$tmpl = new tmpl($this->pwd() . 'configure_form.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
