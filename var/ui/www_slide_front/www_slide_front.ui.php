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
		$group =  $this->get_args('group',0);
		if($group  == 0)
		{
			return false;
		}
		$group_di  = data_interface::get_instance('www_slide_group');
		$group_di->_flush();
		$group_di->set_args(array("_sid"=>$group));
		$group_di->_get();
		$group_info = $group_di->get_results(0);
		if(!($group_info->id >0))
		{
			return false;
		}
		$slide_di =  data_interface::get_instance('www_slide');
		
		$slide_di->_flush();
		$slide_di->set_args(array("_sslide_froup_id"=>$group));
		$slide_di->_get();
		$slide_frames = $slide_di->get_results();
		$group_info->slides = $slide_frames;
		return $this->parse_tmpl('default.html',$group_info);
	}
		
}
?>
