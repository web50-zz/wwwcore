<?php
/**
*	UI Subscribe 
*
* @author       9*	
* @access	public
* @package	SBIN Diesel 	
*/
class ui_subscribe extends user_interface
{
	public $title = 'Рассылка';
	public $for_operations = array();
	
	public $req_fields = array('email'=>'e-mail');
	protected $deps = array(
		'main' => array(
			'subscribe.group',
			'subscribe.subscriber_list',
			'subscribe.editForm',
			'subscribe.accounts_list',
			'subscribe.account_form',
			'subscribe.messages_list',
			'subscribe.message_form',
		)
	);




	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

        public function pub_content()
        {
		$data = array();
		return $this->parse_tmpl('default.html',$data);
	}

	public function pub_getfrm()
	{
		$data = array();
		$di = data_interface::get_instance('subscribe');
		$data = $di->extjs_grid_json(false,false);
		$data['email'] = $this->args['email'];
		$resp['code'] = '200';
		$resp['form'] = $this->parse_tmpl('default_form.html',$data);
		response::send($resp,'json');
	}

	public function pub_confirm()
	{
		try
		{
			$di = data_interface::get_instance('subscribe_req');
			$di->set_args(array('_shash'=>$this->args['d'],
					'_sdone'=>0
					));
			$data = $di->extjs_grid_json(false,false);
			if($data['total'] != 1)
			{
				//'Запрошенная операция уже завершена, либо отсутствует';
				response::redirect('/');
			//	throw new Exception("Спасибо, запрос принят");
			}
		
			$req = unserialize($data['records'][0]['req']);
			foreach($req['operations'] as $key=>$value)
			{
				if($value['operation'] == 1)
				{
					$to_subscribe = true;
				}
				if($value['operation'] == 2)
				{
					$unsubscribe = true;
				}
			}
			$di1 =data_interface::get_instance('subscribe_accounts');
			$di1->set_args(array('_semail'=>$req['email']));
			$data1 = $di1->extjs_grid_json(false,false);
			if($data1['total'] > 1)
			{
				throw new Exception("В силу ряда причин операция не может быть проведена корректно. Обратитесь к администратору");
				// 'Больше одного e-mail обратиться к админу';
			}
			if($data1['total'] == 0 && !$to_subscribe)
			{
				// 'Не отчего отписывать';
				throw new Exception("В силу ряда причин операция не может быть проведена корректно. Обратитесь к администратору");
			}

			if($data1['total'] == 0 && $to_subscribe == true)
			{
				// 'Будем только подписывать';
				$di1->_flush();
				$di1 -> set_args(array(
						'email'=>$req['email']
						));
				$di1->_set();
				$new_id = $di1->args['id'];
				$di2 = data_interface::get_instance('subscribe_user');
				foreach($req['operations'] as $key=>$value)
				{
					if($value['operation'] == 1)
					{
						$di2->set_args(array(
								uids => $new_id,
								gid => $value['id']
								));
						$di2->_add_users_to_group();
					}
				}
			}

			if($data1['total'] == 1)
			{
			// 'Будем и то и другое если есть';
				$uid = $data1['records'][0]['id'];
				$di2 = data_interface::get_instance('subscribe_user');
				$di2->connector->debug = true;
				foreach($req['operations'] as $key=>$value)
				{
					if($value['operation'] == 1)
					{
						$di2->set_args(array(
								uids => $uid,
								gid => $value['id']
								));
						$di2->_remove_users_from_group();
						$di2->set_args(array(
								uids => $uid,
								gid => $value['id']
								));
						$di2->_add_users_to_group();
					}
					if($value['operation'] == 2)
					{
						$di2->set_args(array(
								uids => $uid,
								gid => $value['id']
								));
						$di2->_remove_users_from_group();
	
					}
				}
				$di2->connector->debug = false;
				$di3 = data_interface::get_instance('subscribe_req');
			}
		}
		catch(Exception $e)
		{
			$out['msg'] = $e->getMessage();
			return $this->_do_out($out);
		}

		// ставим флаг что запрос отработан
		$di->set_args(array(
				'_sid'=>$data['records'][0]['id'],
				'done'=>'1',
				));
		$di->prepare_extras();
		$di->_set();
		$out['msg'] = 'Операция подтверждена';
		return $this->_do_out($out);
	}

	private function _do_out($input)
	{
		$out = user_interface::get_instance('action_page');
			$msg = $input['msg'];
			$redir_url = '/';
			$out->set_args(array(
					'action_msg'=>$msg,
					'action_redirect'=>$redir_url,
					));
			return $out->render();
	}

	public function pub_save_form()
	{
		try
		{
			$this->check_input();
			$req = array();
			$req['email'] = trim($this->args['email']);
			$req['operations'] = $this->for_operations;
			$di = data_interface::get_instance('subscribe_req');
			$data['req'] = serialize($req);
			$di->set_args($data);
			$di->prepare_extras();
			$di->_set();
			$di1 = data_interface::get_instance('subscribe_messages');
			$data = array();
			$data['recipients'] = array(array('email'=>$req['email']));
			$data['title'] = 'подписка рассылка';
			$data['link'] = 'http://'.$_SERVER['SERVER_NAME'].'/ui/subscribe/confirm.do?d='.$di->args['hash']; 
			$data['body'] = $this->parse_tmpl('subscribe_mail.html',$data);	
			$di1->_send_message_now('',$data);
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
		$strict = true;
		$regex = $strict?
			'/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' : 
			'/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i'; 
		if(!preg_match($regex,trim($this->args['email'])))
		{
				$errors.= "Проверьте правильность написания e-mail <br>";
				$error = true;
		}
		foreach($this->args as $key=>$value)
		{
			if(preg_match('/subscr_(\d+)/',$key,$matches)&&$value>0)
			{
				$some_selected = true;
				$dt = array();
				$dt['id'] =$matches['1'];
				$dt['operation'] = $value;
				array_push($this->for_operations,$dt);
			}
		}
		if(!$some_selected)
		{
			$error = true;
			$errors.= 'Ни одна из операций подписаться/отписаться не была выбрана<br>';
		}
		if($error == true)
		{
			throw new Exception("$errors");
		}
	}
	/**
	*       Управляющий JS админки
	*/
	protected function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'subscribe.js');
		response::send($tmpl->parse($this), 'js');
	}

	protected function sys_group()
	{
		$tmpl = new tmpl($this->pwd() . 'subscribe.group.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	protected function sys_subscriber_list()
	{
		$tmpl = new tmpl($this->pwd() . 'subscriber_list.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	protected function sys_editForm()
	{
		$tmpl = new tmpl($this->pwd() . 'editForm.js');
		response::send($tmpl->parse($this), 'js');
	}

	protected function sys_accounts_list()
	{
		$tmpl = new tmpl($this->pwd() . 'subscribe.accounts_list.js');
		response::send($tmpl->parse($this), 'js');
	}


	protected function sys_account_form()
	{
		$tmpl = new tmpl($this->pwd() . 'subscribe.account_form.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	protected function sys_messages_list()
	{
		$tmpl = new tmpl($this->pwd() . 'subscribe.messages_list.js');
		response::send($tmpl->parse($this), 'js');
	}

	protected function sys_message_form()
	{
		$tmpl = new tmpl($this->pwd() . 'subscribe.message_form.js');
		response::send($tmpl->parse($this), 'js');
	}


}
?>
