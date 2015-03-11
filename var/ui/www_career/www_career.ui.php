<?php
/**
*
* @author   9* 9@u9.ru  
* cloned  news ui 02052013
* SBIN Diesel
*/
class ui_www_career extends user_interface
{
	public $title = 'www: карьера вакансии';

	protected $deps = array(
		'main' => array(
			'www_career.item_form',
		)
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}
 	/**
	*	Вывод списка 
	*/
	public function pub_list()
	{
		$data = array();
		$di = data_interface::get_instance('www_career');
		$di->_flush();
		$data = $di->extjs_grid_json(false,false);
		$data['path'] = $di->get_url();
		return $this->parse_tmpl('list.html',$data);
	}
	
	public function pub_vcard(){
		$parts = explode("/",SRCH_URI);
		$di= data_interface::get_instance('www_career');
		$id = $di->search_by_uri($parts[0]);
		$di->set_args(array('_sid'=>$id));
		$di->_flush();
		$data = $di->extjs_form_json(false,false);
		$data['data']['lang'] = $this->args['lang'];
		$res = $this->parse_tmpl('vcard.html',$data['data']);
		if(!$this->args['lang'])
		{
			$out = iconv('utf8','cp1251',$res);
			$res =  $out;
		}
		header('Content-Type: text/x-vcard');  
		header('Content-Disposition: inline; filename= "vcard.vcf"');  
		header('Content-Length: '.strlen($res));  
		echo($res);
		die();
	}

	public function pub_resume_form()
	{
		$data = array();
		$tmpl = $this->get_args('template','resume_form.html');
		return $this->parse_tmpl($tmpl,$data);
	}

	public function pub_send_note()
	{
		try
		{
			$args = request::get();
			if($args['description'] == '')
			{
				throw new Exception('Заполните поле "Сообщение"');
			}
			if(array_key_exists('email',$args) && $args['email'] == '')
			{
				throw new Exception('Заполните поле "E-mail"');
			}
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
			$this->send_email($args);
			$msg = 'Спасибо, резюме отправлено';
			$resp = array('success'=>true,'message'=>$msg);
			response::send(response::to_json($resp), 'html');
		}
		catch (Exception $e)
		{
			$resp = array('success'=>false,'message'=>$e->getMessage());
			response::send($resp,'json');
		}
	}

	private function send_email($acc)
	{
		$rcpt = registry::get('RESUME_FORM_EMAIL');
		$mail_data = $acc;
		$id = registry::get('RESUME_EMAIL_TEMPLATE_TEXT');
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
			$title ='Получено резюме с формы обратной связи';
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

	/**
	*       Управляющий JS админки
	*/
	public function sys_main()
	{
		$tmpl = new tmpl($this->pwd() . 'main.js');
		response::send($tmpl->parse($this), 'js');
	}
	
	/**
	*       ExtJS - Форма редактирования
	*/
	public function sys_item_form()
	{
		$tmpl = new tmpl($this->pwd() . 'item_form.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
