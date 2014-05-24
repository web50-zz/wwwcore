#!/usr/bin/env php5
<?php
/*
* Importer market  to market2 UI i25042014
* in default mode by default
* to  use make $EMULATE= false;
*/

//$EMULATE = true;
$source_wp_path = '/server/web/documents/avik.u9.ru/images/data/clients/ru';
$STOP_URI_INTERPRETER = true;

include('../../../base.php');
define('DI_CALL_PREFIX', ADM_PREFIX);
echo("starts.....\r\n");
$posts = get_items();
foreach($posts as $key=>$value)
{
	$j++;
	echo("\r\n iteration: $j\r\n");
	echo("id: {$value->id} {$value->name} \r\n");
	$post_args = array(
		'client_name'=>$value->name,
		'description'=>$value->description.$value->items.$value->items_description,
		'silent'=>true,
	);
	$logo_path = $source_wp_path.'/'.$value->id.'/'.$value->logo;
	if(file_exists($logo_path) && $value->logo != '')
	{
		$post_args['source']= $logo_path;
	}
	dbg::show($post_args);
	if(!($EMULATE == true))
	{
		$di = data_interface::get_instance('www_client');
		$di->set_args($post_args);
		$res = $di->sys_set(true);
		$new_post_id = $res['data']['id'];
	}
	else{
		$new_post_id = $j;
	}
	echo("Post imported new ID: {$new_post_id}\r\n");
	// images
	$images = get_images($value->id);
//	dbg::show($images);
	foreach($images as $key1=>$val2)
	{
		$thumb = $value;
		$source_absolute = $source_wp_path.'/'.$value->id.'/'.$val2->filename;
		if(file_exists($source_absolute))
		{
			$args = array(
				'item_id'=>$new_post_id,
				'file_type'=>3,
				'title'=>'Картинка',
				'comment'=>'Импорт из вордпресса',
				'source'=>$source_absolute,
				'silent'=>'true',
			);
			echo("Loading thumb\r\n");
			if(!($EMULATE == true))
			{
				$di = data_interface::get_instance('m2_item_files');
				$di->set_args($args);
//				$res = $di->sys_set(true);
			}
		}
	}
}

echo("done\r\n");




function get_items()
{
	$sql = 'SELECT i.id,name,name_header,description,items_description,logo,date_presented,items
		        FROM clients i;
		';
	$old = data_interface::set_query($sql,
		array(
			'id'=>array('type'=>'integer'),
			'name'=>array('type'=>'string'),
			'name_header'=>array('type'=>'string'),
			'description'=>array('type'=>'string'),
			'items_description'=>array('type'=>'string'),
			'logo'=>array('type'=>'string'),
			'items'=>array('type'=>'string'),
			'date_presented'=>array('type'=>'date'),
		),
		'old',
		'db1'
	);
	$results = $old->_get($sql)->get_results();
	return $results;
}

function get_images($id,$exist = 0)
{
	$sql = "SELECT filename 
		        FROM photogallery where section_id  ={$id} and section = 'clients'
		";
	$old = data_interface::set_query($sql,
		array(
			'filename'=>array('type'=>'string'),
		),
		'old',
		'db1'
	);
	$results = $old->_get($sql)->get_results();
	return $results;
}




	function rus2translit($string)
	{
		$converter = array(
			'а' => 'a',   'б' => 'b',   'в' => 'v',
			'г' => 'g',   'д' => 'd',   'е' => 'e',
			'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
			'и' => 'i',   'й' => 'y',   'к' => 'k',
			'л' => 'l',   'м' => 'm',   'н' => 'n',
			'о' => 'o',   'п' => 'p',   'р' => 'r',
			'с' => 's',   'т' => 't',   'у' => 'u',
			'ф' => 'f',   'х' => 'h',   'ц' => 'c',
			'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
			'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
			'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
			'А' => 'A',   'Б' => 'B',   'В' => 'V',
			'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
			'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
			'И' => 'I',   'Й' => 'Y',   'К' => 'K',
			'Л' => 'L',   'М' => 'M',   'Н' => 'N',
			'О' => 'O',   'П' => 'P',   'Р' => 'R',
			'С' => 'S',   'Т' => 'T',   'У' => 'U',
			'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
			'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
			'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
			'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
		);

		return strtr($string, $converter);
	}
	 function str2url($str)
	{
		// переводим в транслит
		$str = rus2translit($str);

		// в нижний регистр
		$str = strtolower($str);

		// заменям все пробелы на "_", а ненужное удаляем
		$str = preg_replace(array('/\s+/', '/[^-a-z0-9_]+/u'), array('-', ''), $str);

		// удаляем начальные и конечные '-'
		$str = trim($str, "-");

		return $str;
	}

?>
