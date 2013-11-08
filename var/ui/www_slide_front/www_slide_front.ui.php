<?php
/**
*
* @author   9* <9@u9.ru>  05092013
* @package	SBIN Diesel
*/
class ui_www_slide_front extends user_interface
{
	public $title = 'www: Слайдер - front';

	protected $deps = array(
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}


	public function pub_content()
	{
		if (($gid = $this->get_args('group', false)) && $gid > 0)
		{
			$slider = data_interface::get_instance('www_slide_group')
				->_flush()
				->push_args(array("_sid" => $gid))
				->_get()
				->pop_args()
				->get_results(0);

			if (!empty($group_info))
			{
				$slider->slides = data_interface::get_instance('www_slide')
					->_flush()
					->push_args(array("_sslide_froup_id" => $gid))
					->_get()
					->pop_args()
					->get_results();

				return $this->parse_tmpl('default.html', $slider);
			}
		}

		return false;
	}
		
}
?>
