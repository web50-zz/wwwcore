<?php
/**
*	UI Guestbook
*
* @author       9*	
* @access	public
* @package	SBIN Diesel 	
*/
// see also guestbook.di.php 
class ui_guestbook extends user_interface
{
	public $title = 'Гостевая';

	public $req_fields = array('gb_author_name'=>'Имя','gb_author_email'=>'e-mail','gb_record'=>'Сообщение','gb_author_location'=>'Местоположение');

	protected $deps = array(
		'main' => array(
			'guestbook.guestbook_form'
		),
	);

	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

        public function pub_content()
        {
		return $this->parse_tmpl('default.html',$data);
	}
        
        public function pub_items_content()
        {
		$limit = 10;
		$page = request::get('page', 1);
       
		$di = data_interface::get_instance('guestbook');
		$di->set_args($this->args);
		$di->set_args(array(
				'sort' => 'id',
				'dir' => 'DESC',
				'start' => ($page - 1) * $limit,
				'limit' => $limit,
				));
		$data = $di->extjs_grid_json(false,false);
		$pager = user_interface::get_instance('pager');
		$data['page'] = $page;
		$data['limit'] = $limit;
		$data['pager'] = $pager->get_pager(array('page' => $page, 'total' => $data['total'], 'limit' => $limit, 'prefix' => $_SERVER['QUERY_STRING']));
		return $this->parse_tmpl('default_items.html',$data);
	}


	public function pub_comment_form()
        {
                $di = data_interface::get_instance('guestbook');
		return $this->parse_tmpl('default_form.html',$data);
	}

	public function pub_save_form()
	{
		try
		{
			$this->check_input();
			$di = data_interface::get_instance('guestbook');
			$di->set_args($this->args);
			$di->prepare_extras();
			$di->_set();
		}
		catch(Exception $e)
		{
			$resp['code'] = '400';	
			$resp['error'] = $e->getMessage();
		}

		if($resp['code'] != '400')
		{
			$resp['code'] = '200';
			$resp['report']  = 'success';
		}
		response::send($resp,'json');
	}

	public function pub_getfrm()
        {
	
		$data = array();
		$resp['code'] = '200';
		$resp['form'] = $this->parse_tmpl('default_form.html',$data);
		response::send($resp,'json');
	}

	public function check_input()
	{
		$flds = array();
		$flds = $this->req_fields;
		foreach($flds as $key=>$value)
		{
			if(!$this->args[$key])
			{
				$errors.= "Незаполнено обязательное поле \"$value\" <br>";
				$error = true;
			}
		}
		if($error == true)
		{
			throw new Exception("$errors");
		}
	}

	public function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'guestbook.js');
		response::send($tmpl->parse($this), 'js');
	}

	/**
	*       Форма редактирования 
	*/
	public function sys_guestbook_form()
	{
		$tmpl = new tmpl($this->pwd() . 'guestbook_form.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
