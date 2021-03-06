<?php
/**
*	UI Contacts 
*
* @author       9*	
* @access	public
* @package	SBIN Diesel 	
* @deps jQuery  jqueryPnotify jQuery UI
*/
// see also contacts.di.php 

class ui_contacts extends user_interface
{
	public $title = 'Контактная форма';

	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

        public function pub_content()
        {
		if($this->args['acceptor_only'] == true)
		{
			$this->send_note();
		}
		if(SRCH_URI == 'save/'){
			$this->send_note();
		}
		$data = array();
		return $this->parse_tmpl('default.html',$data);
	}

	private function send_note()
	{
		$headers = getallheaders();
		if($headers['X-Requested-With'] != 'XMLHttpRequest')
		{
			$st = user_interface::get_instance('structure');
			$st->do_404();
			return false;
		}

		try
		{
			$args = request::get();
			if($args['message'] == '')
			{
				throw new Exception('Заполните поле "Сообщение"');
			}
			if(array_key_exists('email',$args) && $args['email'] == '')
			{
				throw new Exception('Заполните поле "E-mail"');
			}
			if(array_key_exists('subject',$args) && $args['subject'] == '')
			{
	//9* 22072013 needed only episodically			throw new Exception('Заполните поле "Тема"');
			}

			if(array_key_exists('email',$args) && $args['email'] != '')
			{
				if(!filter_var($args['email'], FILTER_VALIDATE_EMAIL))
				{
					throw new Exception('поле "E-mail" заполнено некорректно');
				}
				$dns = '';

				// ==== Getting DNS part of the mail ==== //
				$pieces = explode('@', $args['email']);
				if(isset($pieces[1]))
				{
					$dns = $pieces[1];
				}

				// ==== Checking if the checkdnsrr exists ==== //
				if(function_exists('checkdnsrr') && checkdnsrr($dns) === false)
				{
					throw new Exception('поле "E-mail" заполнено некорректно');
				}
				// ==== Checking if the gethostbyname exists ==== //
				else if(function_exists('gethostbyname') && gethostbyname($dns) === $dns)
				{
					throw new Exception('поле "E-mail" заполнено некорректно');
				}
			}
			$this->send_email($args);
			$msg = 'Спасибо, сообщение отправлено';

			if($this->args['response'] == 'plaintext')
			{
				response::send($msg,'text');
			}
			$resp = array('success'=>true,'message'=>$msg);
			response::send($resp,'json');
		}
		catch (Exception $e)
		{
			if($this->args['response'] == 'plaintext')
			{
				response::send($e->getMessage(),'text');;
			}

			$resp = array('success'=>false,'message'=>$e->getMessage());
			response::send($resp,'json');
		}
	}

	private function send_email($acc)
	{
		$rcpt = registry::get('CONTACT_FORM_EMAIL');
		$mail_data = $acc;
		$id = registry::get('EMAIL_TEMPLATE_TEXT');
		if($id >0)
		{
			$di =  data_interface::get_instance('text');
			$di->_flush();
			$di->set_args(array('_sid'=>$id));
			$res = $di->extjs_form_json(false,false);
			if($res['data']['content'] != '')
			{
				$tmpl =  new tmpl($res['data']['content'],'text');
				$body =  $tmpl->parse($mail_data);
				$title = $res['data']['title'];
			}
		}
		else
		{
			$body = $this->parse_tmpl('message.html',$mail_data);
			$title ='Получено сообщение с формы обратной связи';
		}
		$core_domain = $_SERVER['HTTP_HOST'];
		if(!$core_domain)
		{
			$core_domain = 'localhost';
		}
		require_once LIB_PATH.'Swift/swift_required.php';
		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);
		$message = Swift_Message::newInstance($title)
			->setFrom(array('no-reply@'.$core_domain => 'no-reply'))
			->setTo($rcpt)
			->setBody($body);
		$message->setContentType("text/html");	
		$numSent = $mailer->batchSend($message);

	}
}
?>
