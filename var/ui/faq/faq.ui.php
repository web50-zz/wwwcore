<?php
/**
*	UI FAQ 
*
* @author       9*	
* @access	public
* @package	SBIN Diesel 	
*/
// see also faq.di.php 
class ui_faq extends user_interface
{
	public $title = 'FAQ';

	public $req_fields = array('faq_question_author_name'=>'Имя','faq_question_author_email'=>'e-mail','faq_question'=>'Вопрос');
	protected $deps = array(
		'main' => array(
			'faq.faq_form',
			'faq.parts_form',
			'faq.list',
			'faq.parts_list'
		),
	);

	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

        public function pub_content()
        {
		if (preg_match('/parts\/(\d+)/', SRCH_URI, $matches))
		{
			return $this->get_part(array('id'=>$matches[1],'mode'=>'item'));
		}
		else
		{
			return $this->default_front(array('mode'=>'item','id'=>'0'));
		}
	}


	
	public function default_front($input)
	{
		$data = array();
		$fp = data_interface::get_instance('faq_parts');
		$data = $fp->extjs_grid_json(false,false);
		return $this->parse_tmpl('default.html',$data);
	}

	public function get_part($input)
	{
		$fp = data_interface::get_instance('faq_parts');
		$fp->set_args(array('_sid'=>$input['id']));
		$data1 = $fp->extjs_form_json(false,false);
		$fq = data_interface::get_instance('faq');
		$fq->set_args(array(
				'sort'=>'id',
				'dir'=>'DESC',
				'_sfaq_part_id'=>$input['id'],
				));
		$data = $fq->extjs_grid_json(false,false);
		$data['part_title'] = $data1['data']['faqp_title'];
		$data['faq_part_id'] = $data1['data']['id'];
		return $this->parse_tmpl('part.html',$data);
	}

	public function pub_getfrm()
	{
		$data['faq_part_id'] = $this->args['cid'];
		$resp['code'] = '200';
		$resp['form'] = $this->parse_tmpl('faq_question_form.html',$data);
		response::send($resp,'json');
	}

	public function pub_save_question()
	{	try
		{
			$this->check_input();
			$di = data_interface::get_instance('faq');
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
		$tmpl = new tmpl($this->pwd() . 'faq.js');
		response::send($tmpl->parse($this), 'js');
	}

	/**
	*       Форма редактирования вопроса faq 
	*/
	public function sys_faq_form()
	{
		$tmpl = new tmpl($this->pwd() . 'faq_form.js');
		response::send($tmpl->parse($this), 'js');
	}

	/**
	*      Грид списка фопросов faq 
	*/
	public function sys_list()
	{
		$tmpl = new tmpl($this->pwd() . 'faq.list.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*  Грид разделов faq       
	*/
	public function sys_parts_list()
	{
		$tmpl = new tmpl($this->pwd() . 'faq.parts_list.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*  Грид разделов faq       
	*/
	public function sys_parts_form()
	{
		$tmpl = new tmpl($this->pwd() . 'faq.parts_form.js');
		response::send($tmpl->parse($this), 'js');
	}

}
?>
