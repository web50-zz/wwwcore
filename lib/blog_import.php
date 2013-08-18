#!/usr/bin/env php5
<?php
/*
* Importer blogs from Wordpress to www_article UI 01082013
* in default mode by default
* to  use make $EMULATE= false;
*/

$EMULATE = true;
$source_wp_path = '/server/web/documents/mfgo.u9.ru/';


include('base.php');
define('DI_CALL_PREFIX', ADM_PREFIX);
echo("starts.....\r\n");
$posts = get_posts();
$rels = get_relations();
$tags = get_tags();
$cats = get_categories();
set_cats($cats,$EMULATE);
set_tags($tags,$EMULATE);
foreach($posts as $key=>$value)
{
	$j++;
	echo("\r\n iteration: $j\r\n");
	echo("id: {$value->post_id} {$value->ptitle}\r\n");
	$pcontent = str_replace('http://mfgo.u9.ru/wp-content/','/filestorage/',$value->pcontent);
	$post_args = array(
		'release_date'=>$value->pdate,
		'title'=>$value->ptitle,
		'content'=>$pcontent,
		'uri'=>$value->post_name,
		'published'=>1,
		'silent'=>'true',
	);
	if(!($EMULATE == true))
	{
		$di = data_interface::get_instance('www_article');
		$di->call('set',$post_args);
		$res = $di->get_args('result');
		$new_post_id = $res['data']['id'];
	}
	else{
		$new_post_id = $j;
	}
	echo("Post imported new ID: {$new_post_id}\r\n");
	$thumb = $value->thumb;
	$parts = explode('/',$thumb);
	array_shift($parts);
	array_shift($parts);
	array_shift($parts);
	$relative = implode('/',$parts);
	$source_absolute = $source_wp_path.$relative;
	if(file_exists($source_absolute))
	{
		$file_args = array(
			'item_id'=>$new_post_id,
			'file_type'=>4,
			'title'=>'Томб основной',
			'comment'=>'Импорт из вордпресса',
			'source'=>$source_absolute,
			'silent'=>'true',
		);
		echo("Updating thumb\r\n");
		if(!($EMULATE == true))
		{
			$di = data_interface::get_instance('www_article_files');
			$di->call('set',$file_args);
			$res = $di->get_args('result');
		}
	}
	foreach($rels as $key1=>$value1)
	{
		if($value1->object_id == $value->post_id)
		{
			foreach($cats as $key2=>$value2)
			{
				if($value1->term_taxonomy_id == $value2->term_taxonomy_id)
				{
					echo("Match of taxonomy_id {$value1->term_taxonomy_id} to term_id:{$value2->term_id}\r\n");
					echo("Set category id:{$value2->category_id} name:{$value2->name} for $new_post_id\r\n");
					$cat_args = array(
						'item_id'=>$new_post_id,
						'category_id'=>$value2->category_id,
						'silent'=>'true',
					);
					if(!($EMULATE == true))
					{
						$di = data_interface::get_instance('www_article_in_category');
						$di->call('set',$cat_args);
						$res = $di->get_args('result');
					}
				}
			}
			foreach($tags as $key3=>$value3)
			{
				if($value1->term_taxonomy_id == $value3->term_taxonomy_id)
				{
					echo("Match of taxonomy_id {$value1->term_taxonomy_id} to term id:{$value3->term_id}\r\n");
					echo("Set tag id:{$value3->category_id} {$value3->name} for $new_post_id\r\n");
					$tag_args = array(
						'article_id'=>$new_post_id,
						'epids'=>$value3->category_id,
						'silent'=>'true',
					);
					if(!($EMULATE == true))
					{
						$di = data_interface::get_instance('www_article_tags');
						$di->call('add_tags',$tag_args);
						$res = $di->get_args('result');
					}
				}
			}
		}
	}
}

echo("done\r\n");
function set_cats($cats,$EMULATE = false)
{
	$i = 1;
	foreach($cats as $key=>$value)
	{
		$cat_args = array(
			'_sid'=>'',
			'name'=>str2url($value->name),
			'title'=>$value->name,
			'published'=>1,
			'pid'=>1,
			'silent'=>'true',
		);
		if(!($EMULATE == true))
		{
			$di = data_interface::get_instance('www_article_type');
			$di->call('set',$cat_args);
			$res = $di->get_args('result');
			$value->category_id = $res['data']['id'];
		}
		else{
			$value->category_id = $i;
		}
		$i++;
	}
}

function set_tags($tags,$EMULATE = false)
{
	$i = 1;
	foreach($tags as $key=>$value)
	{
		$cat_args = array(
			'_sid'=>'',	
			'not_available'=>'0',
			'title'=>$value->name,
			'uri'=>str2url($value->name),
			'silent'=>'true',
		);
		if(!($EMULATE == true))
		{
			$di = data_interface::get_instance('www_article_tag_types');
			$di->call('set',$cat_args);
			$res = $di->get_args('result');
			$value->category_id = $res['data']['id'];
		}
		else{
			$value->category_id = $i;
		}
		$i++;
	}
}

function get_tags()
{
	$sql = "select a.term_id as term_id, a.name as name, b.taxonomy as taxonomy,b.term_taxonomy_id as term_taxonomy_id from wp_terms a 
		left join wp_term_taxonomy b on a.term_id = b.term_id
		where b.taxonomy = 'post_tag'
		";
	$di = data_interface::set_query($sql,
	array(
		'term_id' => array('type' => 'integer'),
		'name' => array('type' => 'string'),
		'taxonomy' => array('type' => 'string'),
		'term_taxonomy_id' => array('type' => 'string'),
	),
	'old',
	'db1'
	);
	$di->_get();
	$results = $di->get_results();
	return $results;
}
function get_categories()
{
	$sql = "select a.term_id as term_id, a.name as name, b.taxonomy as taxonomy,b.term_taxonomy_id as term_taxonomy_id from wp_terms a 
		left join wp_term_taxonomy b on a.term_id = b.term_id
		where b.taxonomy = 'category'
		";
	$di = data_interface::set_query($sql,
	array(
		'term_id' => array('type' => 'integer'),
		'name' => array('type' => 'string'),
		'taxonomy' => array('type' => 'string'),
		'term_taxonomy_id' => array('type' => 'string'),
	),
	'old',
	'db1'
	);
	$di->_get();
	$results = $di->get_results();
	return $results;
}


function get_posts()
{
	$sql = 'SELECT 
		pp.id as post_id,
		pp.post_date as pdate,
		pp.post_title as ptitle,
		pp.post_content as pcontent,
		pp.post_name as post_name,
		p.guid as thumb
	,m.meta_value,p.guid FROM wp_posts pp
	LEFT JOIN wp_postmeta m ON pp.id = m.post_id AND m.meta_key = "_thumbnail_id"
	LEFT JOIN wp_posts p ON m.meta_value = p.id
	WHERE  pp.post_status = "publish" AND pp.post_type="post" 
	ORDER BY pp.post_date DESC';
	$old = data_interface::set_query($sql,
		array(
			'post_id' => array('type' => 'integer'),
			'ptitle' => array('type' => 'string'),
			'pdate' => array('type' => 'string'),
			'pcontent' => array('type' => 'string'),
			'thumb' => array('type' => 'string'),
			'post_name' => array('type' => 'string'),
		),
		'old',
		'db1'
	);
	$old->_get();
	$results = $old->get_results();
	return $results;
}
function get_relations()
{
	$sql = "select a.object_id as object_id, a.term_taxonomy_id as term_taxonomy_id from wp_term_relationships a";
	$di = data_interface::set_query($sql,
	array(
		'object_id' => array('type' => 'integer'),
		'term_taxonomy_id' => array('type' => 'integer'),
	),
	'old',
	'db1'
	);
	$di->_get();
	$results = $di->get_results();
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
