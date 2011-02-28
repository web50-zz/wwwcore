<?php
/**
*	UI profile 
*
* @author       9*	
* @access	public
* @package	SBIN Diesel 	
*/
class ui_profile extends user_interface
{
	public $title = 'Профиль';

	public $req_fields_passw = array('oldp'=>'Старый пароль','newp'=>'Новый пароль','newp2'=>'Подтверждение нового пароля');
	public $req_p_fields = array('name'=>'Имя','email'=>'e-mail');
	public $market_req_fields = array(
					'lname'=>'Фамилия',
					'clnt_country'=>'Страна',	
					'clnt_region'=>'Регион',
					'clnt_address'=>'Адрес',
					'clnt_nas_punkt'=>'Город/Населенный пункт',
					'clnt_phone'=>'Телефон',
					'clnt_payment_pref'=>'Предпочтительеный способ оплаты',
					'clnt_payment_curr'=>'Валюта',
					);

	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

        public function pub_content()
        {
		$data = array();
		if (!authenticate::is_logged())
		{
			response::redirect('/');
		}
		$this->_check_auth();
		$m_client = $this->get_client_info();
		if(!$m_client)
		{
			//Берем системные данные
			$data['body'] = 'only sys';
		}
		else
		{
			$data['market_client'] = 1;
			$data['body'] = $this->parse_tmpl('market_client_personal_ro.html',$m_client);
		}
		 return $this->parse_tmpl('default.html',$data);
	}

	public function get_client_info()
	{
		$di = data_interface::get_instance('market_clients');
		$data = $di->get_data(UID);
		return $data;
	}

	public function pub_client_info_part()
	{
		$this->_check_auth();
		$d = $this->get_client_info();
		if($d)
		{
			$resp['payload'] = $this->parse_tmpl('market_client_personal_ro.html',$d);
			$resp['code'] = '200';
		}
		else
		{
			$resp['code'] = '400';
			$resp['report'] = 'Не является клиентом магазина';

		}
		response::send($resp,'json');
	}
	
	public function pub_client_orders_part()
	{
		$this->_check_auth();
		$d = $this->get_client_info();
		if($d)
		{
			$di2 = data_interface::get_instance('order');
			$di2->set_args(array(
					'_screator_uid'=>UID,
					));
			
			$e = $di2->get_list_data();
			$resp['payload'] = $this->parse_tmpl('market_client_orders_ro.html',$e);
			$resp['code'] = '200';
		}
		else
		{
			$resp['code'] = '400';
			$resp['report'] = 'Не является клиентом магазина';

		}
		response::send($resp,'json');
	}


	public function pub_get_pform()
	{
		$this->_check_auth();
			
		$d = $this->get_client_info();
		if($d)
		{
			$cl = data_interface::get_instance('market_clients');
			$cl ->set_args(array(
					'_suid'=>UID,	
					));
			$dt = $cl->get_client_data_extended();
			$resp['form'] = $this->parse_tmpl('pform.html',$dt['data']);
			$resp['code'] = '200';
		}
		else
		{
			$resp['code'] = '400';
			$resp['report'] = 'Не является клиентом магазина';

		}
		response::send($resp,'json');	
	}

	public function pub_save_pform()
	{
		$this->_check_auth();
		try
		{
			$t = array_merge($this->req_p_fields,$this->market_req_fields);
			$this->check_input($t);
			$di = data_interface::get_instance('user');
			$di->_flush();
			$di->insert_on_empty = true;
			$di->set_args(array(
					'_sid' => UID,
					'name' => $this->args['lname'].' '.$this->args['name'].' '.$this->args['mname'],
		//			'email'=> $this->args['email'],
					));
			$data = $di->extjs_set_json(false);
			if($data['success'] != true)
			{
				throw new Exception("Ошибка обновления учетных данных");
			}
			
			$d = $this->get_client_info();
			if($d)
			{
				$di2 = data_interface::get_instance('market_clients');
				$di2->connector->debug = true;
				$di2->set_args(array(
						'clnt_address'=>$this->args['clnt_address'],	
						'clnt_country'=>$this->args['clnt_country'],	
						'clnt_nas_punkt'=>$this->args['clnt_nas_punkt'],	
						'clnt_payment_curr'=>$this->args['clnt_payment_curr'],	
						'clnt_payment_pref'=>$this->args['clnt_payment_pref'],	
						'clnt_phone'=>$this->args['clnt_phone'],	
						'clnt_region'=>$this->args['clnt_region'],	
						'clnt_region_custom'=>$this->args['clnt_region_custom'],	
						'clnt_email'=>$this->args['clnt_email'],	
						'clnt_lname'=>$this->args['lname'],	
						'clnt_mname'=>$this->args['mname'],	
						'clnt_name'=>$this->args['name'],	
						'_sid' => $d->id,
						));
				$data2 = $di2->extjs_set_json(false);
				$di2->connector->debug = false;
				if($data2['success'] != true)
				{
					throw new Exception("Ошибка обновления учетных данных клиента");
				}
			}
		}
		catch(Exception $e)
		{
			$resp['code'] = '400';	
			$resp['error'] = $e->getMessage();
		}
		if($resp['code'] != 400)
		{
			$resp['code'] = '200';
			$resp['report']  = 'success';
		}
		response::send($resp,'json');	
	}

	public function pub_get_order()
	{
		$this->_check_auth();
		$resp['code'] = '200';
		$data =  array();
		$di = data_interface::get_instance('order_item');
		$di->set_args(array(
				'_sorder_id'=>$this->args['_sid'],
				));
		$data = $di->get_list_data();
		$data['id'] = $this->args['_sid'];
		$resp['form'] = $this->parse_tmpl('order.html',$data);
		response::send($resp,'json');	
	}

	public function pub_get_passform()
	{
		$this->_check_auth();
		$resp['code'] = '200';
		$data =  array();
		$data['id'] = $this->args['_sid'];
		$resp['form'] = $this->parse_tmpl('passform.html',$data);
		response::send($resp,'json');	
	}

	public function pub_save_passform()
	{
		$this->_check_auth();
		try
		{
			$this->check_input($this->req_fields_passw,'1');
			$di = data_interface::get_instance('user');
			$di ->set_args(array(
					'_sid'=>UID,
					'passw'=>$this->args['newp2'],
					));
			$data = $di->_set_passwd();
			if($data['success'] != true)
			{
				throw new Exception("Новый пароль не был сохранен");
			}
		}
		catch(Exception $e)
		{
			$resp['code'] = '400';	
			$resp['error'] = $e->getMessage();
		}
		if($resp['code'] != 400)
		{
			$resp['code'] = '200';
			$resp['report']  = 'success';
		}
		response::send($resp,'json');	
	}

	public function pub_get_regs()
	{
		$reg = (int)$this->get_args('clnt_country');
		$reg_di = data_interface::get_instance('guide_region');
		$reg_di->_flush();
		$reg_di->what = array('id', 'title');
		$reg_di->set_order('title');
		$reg_di->set_args(array('_scid' => $reg));
		$reg_di->_get();
		$data = array('records' => $reg_di->get_results());
		response::send($this->parse_tmpl('reg_selector_json.html', $data), 'text');
	}

	public function check_input($flds = array(),$type = 0)
	{
		foreach($flds as $key=>$value)
		{
			if($key == 'clnt_region') /* 9* хак регионы обязательны только для россии */
			{
				if($this->args['clnt_country']  != 1)
				{
					continue;
				}
			}
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

		if($type == 1)
		{
			if($this->args['newp'] != $this->args['newp2'])
			{
				throw new Exception("Пароль и подтверждение не идентичны");
			}
			$usr = data_interface::get_instance('user');
			$usr->set_args(array(
					'_sid'=>UID,
					));
			$data = $usr->extjs_grid_json(false,false);
			if($data['total'] != 1)
			{
				throw new Exception("Операция не может быть выполнена");
			}
			$usr->_flush();
			if(!$usr->get_by_password($data['records'][0]['login'],$this->args['oldp']))
			{
				throw new Exception("Старый пароль несоответствует реальному");
			}
		}
	}


	private function _check_auth()
	{
		if (!authenticate::is_logged())
		{
			response::redirect('/');
		}
	}
}
?>
