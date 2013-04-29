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
		if(SRCH_URI == 'save/'){
			$this->send_note();
		}
		$data = array();
		return $this->parse_tmpl('default.html',$data);
	}

	private function send_note()
	{
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
				throw new Exception('Заполните поле "Тема"');
			}

			$this->send_email($args);
			$msg = 'Спасибо, сообщение отправлено';
			$resp = array('success'=>true,'message'=>$msg);
			response::send($resp,'json');
		}
		catch (Exception $e)
		{
			$resp = array('success'=>false,'message'=>$e->getMessage());
			response::send($resp,'json');
		}
	}

	private function send_email($acc)
	{
		$rcpt = registry::get('CONTACT_FORM_EMAIL');
		$mail_data['subject'] = $acc['subject'];
		$mail_data['name'] = $acc['name'];
		$mail_data['email'] = $acc['email'];
		$mail_data['message'] = $acc['message'];
		$id = registry::get('EMAIL_TEMPLATE_TEXT');
		if($id >0)
		{
			$di =  data_interface::get_instance('text');
			$di->_flush();
			$di->set_args(array('_sid'=>$id));
			$res = $di->extjs_form_json(false,false);
			if($res['data']['text'] != '')
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
		$core_domain = 'localhost';
		require_once LIB_PATH.'Swift/swift_required.php';
		$transport = Swift_SendmailTransport::newInstance();
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
