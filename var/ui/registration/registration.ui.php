<?php
/**
*	UI registration form 
*
* @author	elgarat,9* august 2010	
* @access	public
* @package	SBIN Diesel 	
*/
class ui_registration extends user_interface
{
	public $title = 'Форма регистрации';
	public $req_fields = array('name'=>'Имя','email'=>'e-mail','passwd'=>'пароль','passwd2'=>'подтверждение пароля');
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
	public $mode = 'extended1';
	/*
	 currently 2 modes available 'default'(only sys user registration), 
	 'extended1'(also creates market client account)
	*/

	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

        public function pub_registration_form()
        {
		$data = array();
		if(authenticate::is_logged())
		{
			return $this->parse_tmpl('logged.html',$data);
		}
		switch($this->mode)
		{
			case 'extended1':
				$data_m = $this->prepare_ext1_data();
				$data = array_merge($data,$data_m);
				return $this->parse_tmpl('extended1.html',$data);
			break;
		}
		return $this->parse_tmpl('default.html',$data);
	}


	public function pub_start_reg()
	{
		$data = array();
		if(authenticate::is_logged())
		{
			return  '';
		}
		return  $this->parse_tmpl('button.html',$data);
	}

	public function pub_register()
        {
		try
		{
			$this->check_input();
			$data = $this->prepare_sys_input();
			$sys_user_data = $this->create_account($data);
			if($this->mode == 'extended1')
			{
				$this->create_market_client($sys_user_data);
			}
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

	public function create_account($data)
	{
		$us = data_interface::get_instance('user');
		$us->_flush();
		$us ->set_args(array('_slogin'=>$data['login']));
		$rec = $us->_get();
		if(!empty($rec))
		{
			throw new Exception('Данный логин уже имеется в системе. Попробуйте иной.');
		}
		$us->_flush();
		$us ->set_args($data,false);
		$data2 = $us ->extjs_set_json(false);
		return $data2;

	}

	public function create_market_client($sys_data)
	{
		$data = array();
		$data = $this->args;
		if(!$sys_data['data']['id'])
		{
			throw new Exception('Ошибка в создании системной записи покупателя');
		}
		$data['clnt_sys_uid'] = $sys_data['data']['id'];
		$data['clnt_name'] = $this->args['name'];
		$data['clnt_lname'] = $this->args['lname'];
		$data['clnt_mname'] = $this->args['mname'];
		$data['clnt_email'] = $this->args['email'];
		$data['clnt_created_datetime'] = date('Y-m-d H:i:S');
		$data['clnt_creator_uid']='0';
		$us = data_interface::get_instance('market_clients');
		$us->_flush();
		$us ->set_args($data,false);
		$data2 = $us ->extjs_set_json(false);
		if(!$data2['data']['id'])
		{
			throw new Exception('Ошибка в создании профиля покупателя');
		}
		return $data2;
	}


	public function prepare_sys_input()
	{
		$data['login'] = $this->args['email'];
		$data['name'] = $this->args['name'];
		$data['email'] = $this->args['email'];
		$data['passw'] = $this->args['passwd'];
		$data['lang'] = 'ru_RU';
		$data['remote_addr'] = $_SERVER['REMOTE_ADDR'];
		$data['created_datetime'] =  date('Y-m-d H:i:S'); 
		return $data;
	}

	public function check_input()
	{
		$flds = array();
		$flds = $this->req_fields;
		if($this->mode == 'extended1')
		{
			$flds = array_merge((array)$flds,(array)$this->market_req_fields);
		}
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
		if($this->args['passwd'] != $this->args['passwd2'])
		{
			$errors .= 'Набранные пароли не идентичны<br>';
			$error = true;
		}
		if($error == true)
		{
			throw new Exception("$errors");
		}

	}

	public function prepare_ext1_data()
	{
		$data = array();
		$country_di = data_interface::get_instance('guide_country');
		$country = $country_di->extjs_grid_json(array('id','title','title_eng'),false);
		$currency_di = data_interface::get_instance('guide_currency');
		$currency = $currency_di->extjs_grid_json(array('id','title'),false);

		$pay_var_di = data_interface::get_instance('guide_pay_type');
		$pay_var = $pay_var_di->extjs_grid_json(array('id','title'),false);
		
		$data['cntrys'] = $country['records'];	
		$data['currencys'] = $currency['records'];	
		$data['payvar'] = $pay_var['records'];	
		return $data;
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

}
?>
