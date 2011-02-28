<?php
/**
*	UI action pdage 
*
* @author       9*	
* @access	public
* @package	SBIN Diesel 	
*/
class ui_action_page extends user_interface
{
	public $title = 'Action page';

	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

        public function render()
        {
		$data = array();
		$data = $this->args;
		$vps = array(
		array(
			'ui_name'=>'structure',
		));
		
		foreach ($vps as $vp)
		{
				$ui = user_interface::get_instance($vp['ui_name']);
				// 9*  css output
				if(!$css_resource[$vp->ui_name])
				{
					if($path = $ui->get_resource_path($vp['ui_name'].'.css'))
					{
						$data['css_resources'][] = $path;
					}
					$css_resource[$vp->ui_name] = true;
				}
				//9* js output
				if(!$js_resource[$vp->ui_name])
				{
					if($path = $ui->get_resource_path($vp['ui_name'].'.res.js'))
					{
						$data['js_resources'][] = $path;
					}
					$js_resource[$vp->ui_name] = true;
				}

		}
		if($path = $this->get_resource_path($this->interfaceName.'.css'))
		{
			$data['css_resources'][] = $path;
		}
        		if($path = $this->get_resource_path($this->interfaceName.'.res.js'))
		{
			$data['js_resources'][] = $path;
		}
		response::send($this->parse_tmpl('default.html',$data),'html');
	}
}
?>
