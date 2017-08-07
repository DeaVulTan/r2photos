<?php
header('Content-Type: text/xml');

class DataBaseMysql {
   var $dbId;
   function DataBaseMysql($host, $user, $password, $database) { if (!$this->dbId = @mysql_connect($host, $user, $password)) die("<b>MySQL</b>: Unable to connect to database"); if (!mysql_select_db($database)) die("<b>MySQL</b>: Unable to select database <b>".$database."</b>"); }
   function Query($sqlString){ if (!$resourseId =@mysql_query($sqlString, $this->dbId)) die("<b>MySQL</b>: Unable to execute<br /><b>SQL</b>: ".$sqlString."<br /><b>Error (".mysql_errno().")</b>: ".@mysql_error()); return $resourseId; }
   function SelectValue($sqlString) { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); $row =& mysql_fetch_row($resourseId); @mysql_free_result($resourseId); return $row[0]; }
   function &SelectRow($sqlString) { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); $row =& mysql_fetch_assoc($resourseId); @mysql_free_result($resourseId); return $row; }
   function &SelectSet($sqlString, $idTable = '') { $resourseId = DataBaseMysql::Query($sqlString); $row = array(); while ($rowOne =& mysql_fetch_assoc($resourseId)) { if ($idTable) $row[$rowOne[$idTable]] = $rowOne; else $row[] = $rowOne; } @mysql_free_result($resourseId); return $row; }
   function SelectLastInsertId() { return @mysql_insert_id($this->dbId); }
   function SelectAffectedRows() { return @mysql_affected_rows($this->dbId); }
   function Destroy() { if (!@mysql_close($this->dbId)) die("Cann't disconnect from database"); }
}

//config
$path = dirname(__FILE__).'/';

include($path.'config.php');
$site = ucfirst(strtolower($config['projectName']));

preg_match('/^([^:]*)?:([^@]*)?@([^\/]*)?\/(.+)$/', $config['database'], $arDatabase);
$db = new DataBaseMysql($arDatabase[3], $arDatabase[1], $arDatabase[2], $arDatabase[4]);

//Необходимо заполнить массив позиций
$items = array();

$yml = new yml();
$yml->set_shop($site, $site, 'http://www.'.strtolower($site).'.ru/');
$yml->add_currency('RUR', 1);

if (sizeOf($items) > 0) {
   //Функция для добавлений разделов (описание см. ниже)
   //$yml->add_category();
   
   //Функция для добавления позиций (описание см. ниже)
   //$yml->add_offer();
}

$xml = $yml->get_xml();
echo $xml;
$db->Destroy();
unset($db);

####################################################
# class for generation YML (yandex market language)
####################################################

class yml
{
	var $from_charset = 'windows-1251';
	var $shop = array('name'=>'', 'company'=>'', 'url'=>'');
	var $currencies = array();
	var $categories = array();
	var $offers = array();

	# конструктор
	function yml($from_charset = 'windows-1251')
	{
		$this->from_charset = trim(strtolower($from_charset));
	}

	# преобразование массива в тег
	function convert_array_to_tag($arr)
	{
		$s = '';
		foreach($arr as $tag=>$val)
		{
			$s .= '<'.$tag.'>'.$val.'</'.$tag.'>';
		}
		$s .= "\r\n";
		return $s;
	}

	# преобразование массива в атрибуты
	function convert_array_to_attr($arr, $tagname, $tagvalue = '')
	{
		$s = '<'.$tagname.' ';
		foreach($arr as $attrname=>$attrval)
		{
			$s .= $attrname . '="'.$attrval.'" ';
		}
		$s .= ($tagvalue!='') ? '>'.$tagvalue.'</'.$tagname.'>' : '/>';
		$s .= "\r\n";
		return $s;
	}

	# подготовка текстового поля в соответствии с требованиями Яндекса
	function prepare_field($s)
	{
		$from = array('"', '&', '>', '<', '\'');
		$to = array('&quot;', '&amp;', '&gt;', '&lt;', '&apos;');
		$s = str_replace($from, $to, $s);
		if ($this->from_charset!='windows-1251') $s = iconv($this->from_charset, 'windows-1251//IGNORE//TRANSLIT', $s);
		$s = preg_replace('#[\x00-\x08\x0B-\x0C\x0E-\x1F]+#is', ' ', $s);
		return trim($s);
	}

	# указать данные магазина
	# @name - название интернет-магазина
	# @company - официальное название компании
	# @url - адрес сайта
	function set_shop($name, $company, $url)
	{
		$this->shop['name'] = $this->prepare_field($name);
		$this->shop['name'] = substr($this->shop['name'], 0, 20);
		$this->shop['company'] = $this->prepare_field($company);
		$this->shop['url'] = $this->prepare_field($url);
	}

	# добавить валюту магазина
	# @id - код валюты (RUR, USD, EUR...)
	# @rate - CBRF или свой курс
	# @plus учитывается только в случае rate = CBRF и означает насколько увеличить курс в процентах от ЦБ РФ
	function add_currency($id, $rate = 'CBRF', $plus = 0)
	{
		$rate = strtoupper($rate);
		$plus = str_replace(',', '.', $plus);
		if ($rate=='CBRF' && $plus>0)
			$this->currencies[] = array('id'=>$this->prepare_field(strtoupper($id)), 'rate'=>'CBRF', 'plus'=>(float)$plus);
		else
		{
			$rate = str_replace(',', '.', $rate);
			$this->currencies[] = array('id'=>$this->prepare_field(strtoupper($id)), 'rate'=>(float)$rate);
		}
		return true;
	}

	# добавление категории товаров
	# @id - id рубрики
	# @parent_id - id родительской рубрики, если нет, то -1
	# @name - название рубрики
	function add_category($name, $id, $parent_id = -1)
	{
		if ((int)$id<1||trim($name)=='') return false;
		if ((int)$parent_id>0)
			$this->categories[] = array('id'=>(int)$id, 'parentId'=>(int)$parent_id, 'name'=>$this->prepare_field($name));
		else
			$this->categories[] = array('id'=>(int)$id, 'name'=>$this->prepare_field($name));
		return true;
	}

	# добавление позиции
	# @id - id товара
	# @available - товар доступен сейчас (true) или на заказ (false)
	# @data - массив остальных параметров (звездочкой помечены обязательные)
	#	*url - URL-адрес страницы товара
	#	*price - цена товара
	#	*currencyId - идентификатор валюты товара (RUR, USD, UAH...)значением цены.
	#	*categoryId - идентификатор категории товара (целое число не более 18 знаков). Товарное предложение может принадлежать только одной категории
	#	picture - Ссылка на картинку соответствующего товарного предложения. Недопустимо давать ссылку на "заглушку", т.е. на картинку где написано "картинка отсутствует" или на логотип магазина
	#	*delivery - элемент, обозначающий возможность доставить соответствующий товар. "false" данный товар не может быть доставлен("самовывоз"). "true" товар доставляется на условиях, которые указываются в партнерском интерфейсе http://partner.market.yandex.ru на странице "редактирование".
	#	*name - наименование товарного предложения
	#	vendor - производитель
	#	vendorCode - Код товара (указывается код производителя)
	#	*description - Описание товарного предложения
	#	country_of_origin - Элемент предназначен для указания страны производства товара.
	#	downloadable - Элемент предназначен обозначения товара, который можно скачать.
	function add_offer($id, $data, $available = true)
	{
		$allowed = array('url', 'price', 'currencyId', 'categoryId', 'picture', 'delivery', 'name', 'vendor', 'vendorCode', 'description', 'sales_notes', 'country_of_origin', 'downloadable');

		foreach($data as $k=>$v)
		{
			if (!in_array($k, $allowed)) unset($data[$k]);
			$data[$k] = strip_tags($this->prepare_field($v));
		}
		$tmp = $data;
		$data = array();
		foreach($allowed as $key)
		{
			if (isset($tmp[$key])) $data[$key] = $tmp[$key]; # Порядок важен для Я.Маркета!!!
		}
		$this->offers[] = array('id'=>(int)$id, 'data'=>$data, 'available'=>($available)?'true':'false');
	}

	# шапка документа
	function get_xml_header()
	{
		return '<?xml version="1.0" encoding="windows-1251"?><!DOCTYPE yml_catalog SYSTEM "shops.dtd"><yml_catalog date="'.date('Y-m-d H:i').'">';
	}

	# тело документа
	function get_xml_shop()
	{
		$s = '<shop>' . "\r\n";

			# shop info
			$s .= $this->convert_array_to_tag($this->shop);

			# currencies
			$s .= '<currencies>' . "\r\n";
			foreach($this->currencies as $currency)
			{
				$s .= $this->convert_array_to_attr($currency, 'currency');
			}
			$s .= '</currencies>' . "\r\n";

			# categories
			$s .= '<categories>' . "\r\n";
			foreach($this->categories as $category)
			{
				$category_name = $category['name'];
				unset($category['name']);
				$s .= $this->convert_array_to_attr($category, 'category', $category_name);
			}
			$s .= '</categories>' . "\r\n";

			# offers
			$s .= '<offers>' . "\r\n";
			foreach($this->offers as $offer)
			{
				$data = $offer['data'];
				unset($offer['data']);
				$s .= $this->convert_array_to_attr($offer, 'offer', $this->convert_array_to_tag($data));
			}
			$s .= '</offers>' . "\r\n";

		$s .= '</shop>';
		return $s;
	}

	# футер документа
	function get_xml_footer()
	{
		return '</yml_catalog>';
	}

	# получить весь XML код
	function get_xml()
	{
		$xml = $this->get_xml_header();
		$xml .= $this->get_xml_shop();
		$xml .= $this->get_xml_footer();
		return $xml;
	}
}
?>