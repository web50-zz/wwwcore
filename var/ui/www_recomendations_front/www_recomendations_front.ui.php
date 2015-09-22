<?php
/**
*
* @author	Fedot B Pozdnyakov <9@u9.ru> 24062014
* @package	SBIN Diesel
*/
class ui_www_recomendations_front extends user_interface
{
	public $title = 'www: Рекомендательные  письма фронт';

	protected $deps = array(
	);
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}
        
        /**
        *       Отрисовка контента для внешней части
        */
        public function pub_form()
        {
		$data = array();
                return $this->parse_tmpl('form.html', $data);
        }

	public function  pub_save()
	{
		$di =  data_interface::get_instance('www_recomendations');
		$di->set_args(request::get());
		$result = $di->sys_set(true);
		$this->fire_event('onSent', array(request::get()));
		response::send(response::to_json($result), 'html');
	}
}
?>
