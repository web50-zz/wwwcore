<?php
/**
*	UI Public auth 
*
* @author       9*	
* @access	public
* @package	SBIN Diesel 	
*/
class ui_pub_auth extends user_interface
{
	public $title = 'Публичная авторизация';

	public $req_fields = array('login'=>'Логин');
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

        public function pub_content()
        {
		$data = array();

		if (authenticate::is_logged())
		{
			$su = data_interface::get_instance(AUTH_DI);
			$data ['user'] = $su->get_user();
			return $this->parse_tmpl('logged.html',$data);
		}
		else
		{
			return $this->parse_tmpl('login.html',$data);
		}
	}

	public function pub_getfrm()
	{
		$data = array();
		$data['login'] = $this->args['email'];
		$resp['code'] = '200';
		$resp['form'] = $this->parse_tmpl('default_form.html',$data);
		response::send($resp,'json');
	}

	public function pub_confirm()
	{
		try
		{
			$di = data_interface::get_instance('pswremind_req');
			$di->set_args(array('_shash'=>$this->args['d'],
						'_sdone'=>0
					));
			$data = $di->extjs_grid_json(false,false);
			if($data['total'] != 1)
			{
				//'Запрошенная операция уже завершена, либо отсутствует';
				throw new Exception("Спасибо, запрос принят");
			}
			$req = unserialize($data['records'][0]['req']);
			$usr = data_interface::get_instance('user');
			$usr->set_args(array(
					'_slogin'=>$req['login'],
					));
			$u_data  = $usr->extjs_grid_json(false,false);
			if($u_data['total'] !=1)
			{
				throw new Exception("Спасибо, запрос принят");
			}
			$out = user_interface::get_instance('action_page');
					$new_passwd = '654321';
			$nargs = array(
					'_sid'=>$u_data['records'][0]['id'],
					'passw'=>$new_passwd,
			);
			$usr->push_args($nargs);
			$datac = $usr->_set_passwd();
			$usr->pop_args();
			$datam = array();
			$datam['recipients'] = array(array('email'=>$req['login']));
			$datam['title'] = 'Восстановление пароля';
			$datam['passwd'] = $new_passwd; 
			$datam['body'] = $this->parse_tmpl('new_pswd_mail.html',$datam);	
			$mail = data_interface::get_instance('subscribe_messages');
			$mail->_send_message_now('',$datam);
		}
		catch(Exception $e)
		{
			$to_say['msg'] = $e->getMessage();
			return $this->_do_out($to_say);
		}
		// ставим флаг что запрос отработан
		$di->set_args(array(
				'_sid'=>$data['records'][0]['id'],
				'done'=>'1',
				));
		$di->prepare_extras();
		$di->_set();
		
		$to_say['msg'] = 'Операция прошла успешно. Дальнейшие инструкции отправлены на ваш e-mail.';
		return $this->_do_out($to_say);
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
			$req['login'] = trim($this->args['login']);
			$usr = data_interface::get_instance('user');
			$usr->set_args(array(
					'_slogin'=>$req['login'],
					));
			$u_data  = $usr->extjs_grid_json(false,false);
			if($u_data['total'] !=1)
			{
				throw new Exception("Спасибо, запрос принят");
			}
			$di = data_interface::get_instance('pswremind_req');
			$data['req'] = serialize($req);
			$di->set_args($data);
			$di->prepare_extras();
			$di->_set();
			$data = array();
			$data['recipients'] = array(array('email'=>$req['login']));
			$data['title'] = 'Восстановление пароля';
			$data['link'] = 'http://'.$_SERVER['SERVER_NAME'].'/ui/pub_auth/confirm.do?d='.$di->args['hash']; 
			$data['body'] = $this->parse_tmpl('cnfrm_this_mail.html',$data);	
			$di1 = data_interface::get_instance('subscribe_messages');
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
		if($error == true)
		{
			throw new Exception("$errors");
		}
	}
}
?>
