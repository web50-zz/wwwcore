<?php
/**
*	ПИ "WWW: Отзывы front"
*
* @author	Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_www_response_front extends user_interface
{
	public $title = 'WWW: Отзывы front';
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	/**
	*	Вывод контента
	*/
	protected function pub_content()
	{
		$template = $this->get_args('tmpl', 'content.html');

		$data = array(
			'records' => data_interface::get_instance('www_response')
					->_flush()
					->push_args(array())
					->set_order('created_datetime', 'DESC')
					->_get()
					->pop_args()
					->get_results()
		);

		$data = array_merge($data, (array)$this->args);

                return $this->parse_tmpl($template, $data);
	}

	/**
	*	Вывод формы
	*/
	protected function pub_form()
	{
		$template = $this->get_args('tmpl', 'form.html');

		$data = request::get();

                return $this->parse_tmpl($template, $data);
	}

	/**
	*	Сабмит данныз формы
	*/
	protected function pub_submit()
	{
		$result = array();
		$error = false;
		$input = request::get(array('pid', 'name', 'email', 'comment', 'user_code'), array(1, false, false, false, false));

		if (!($input['pid'] > 0))
		{
			$input['pid'] = 1;
		}

		if (empty($input['name']))
		{
			$error = true;
			$result['errors'][] = 'Необходимо указать имя';
			$result['fields'][] = 'name';
		}
	
		// Regexp для проверки e-mail
		$regex = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
		if (empty($input['email']) || !preg_match($regex, $input['email']))
		{
			$error = true;
			$result['errors'][] = 'Необходимо правильно указать e-mail';
			$result['fields'][] = 'email';
		}

		if (empty($input['comment']))
		{
			$error = true;
			$result['errors'][] = 'Необходимо заполнить комментарий';
			$result['fields'][] = 'comment';
		}

		require_once INSTANCES_PATH .'wwwcore/lib/dapphp-securimage-3.2RC2/securimage.php';
		$captcha = new Securimage();

		if (!$captcha->check($input['user_code']))
		{
			$error = true;
			$result['errors'][] = 'Необходимо указать правильный код';
			$result['fields'][] = 'user_code';
		}

		try
		{
			if (!$error)
			{
				// Контрольная проверка методом ФБ
				$ctrl = strlen($input['user_code']) + mb_strlen($input['name'], ENCODING) + mb_strlen($input['email'], ENCODING) + mb_strlen($input['comment'], ENCODING);

				// Если контрольная сумма не совпадает, то делаем вид, что всё Ок
				if ($ctrl != (int)request::get('climb'))
				{
					$result = array('success' => true);
				}
				else
				{
					$result = data_interface::get_instance('www_response')
						->_inner_set($input);
				}
			}
			else
			{
				$result['success'] = false;
			}
		}
		catch(Exception $e)
		{
			dbg::write("Site user submit ERROR:\nREQUEST_URI: {$_SERVER['REQUEST_URI']}\n" . $e->getMessage() . "\n" . $e->getTraceAsString(), LOG_PATH . 'di_errors.log');
		}

		if (empty($result))
		{
			$result = array(
				'success' => false,
			);
		}

		response::send($result, 'json');
	}
}
?>
