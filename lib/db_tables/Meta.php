<?php
class Meta {
  function __construct() { }
  function Structure($init = false) {
    if ($init) {
      $form = array(
        'priority' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Приоритет'))),
        'url_regexp' => array('default' => '', 'form' => array('Input' => array('name' => 'Шаблон URL', 'style' => 'style="width: 100%;"'))),
        'title' => array('default' => '', 'form' => array('TextArea' => array('name' => 'TITLE', 'style' => 'style="width: 100%;" rows="5" cols="100"'))),
        'keywords' => array('default' => '', 'form' => array('TextArea' => array('name' => 'KEYWORDS', 'style' => 'style="width: 100%;" rows="5" cols="100"'))),
        'description' => array('default' => '', 'form' => array('TextArea' => array('name' => 'DESCRIPTION', 'style' => 'style="width: 100%;" rows="5" cols="100"'))),
      );
      $columns = array(
        'priority' => array('name' => 'Приоритет', 'autoUpdate' => true, 'sort' => true),
        'url_regexp' => array('name' => 'Шаблон&nbsp;URL', 'style' => 'align="left"', 'sort' => true),
        'title' => array('name' => 'TITLE', 'style' => 'width="100%" align="left"', 'sort' => true),
      );
      if (defined('LANG')) {
        $form = array_merge(array('lang' => array('default' => 'en', 'form' => array('Select' => array('items' => Site::GetLangs(), 'name' => 'Язык')))), $form);
        $columns = array_merge(array('lang' => array('name' => 'Язык')), $columns);
      }
    }
    return array(
      'form' => ($form ? $form : false),
      'columns' => ($columns ? $columns : false),
      'table'  => 'metainfo',
      'tableName'  => 'Метаинформация'
    );
  }
  function Get($id = '', $init = false) { return new Table(self::Structure($init), $id); }
  function Update($rows) { $t = new Table(self::Structure()); return $t->UpdateRows($rows); }
  function Save($rows) { $t = new Table(self::Structure()); return $t->SaveRows($rows); }
  function Delete($rows) { $t = new Table(self::Structure()); return $t->DeleteRows($rows); }

  static function whereString($parms) {
    $where = '';
    if (isset($parms['id']) && $parms['id'] > 0) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
    if (isset($parms['lang']) && $parms['lang']) $where .= ($where ? " AND " : " WHERE ")."lang='".mysql_real_escape_string($parms['lang'])."'";
    if (isset($parms['regexp'])) $where .= ($where ? " AND " : " WHERE ")."'".mysql_real_escape_string($parms['regexp'])."' REGEXP url_regexp";
    if (isset($parms['like']) && $parms['like']) $where .= ($where ? " AND " : " WHERE ")."CONCAT(url_regexp, ' ', title, ' ', description, ' ', keywords) LIKE '%".mysql_real_escape_string($parms['like'])."%'";
    return $where;
  }

  static function limitString($parms) { if (!isset($parms['limit'])) $parms['limit'] = 0; if (!isset($parms['offset'])) $parms['offset'] = 0; $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

  static function orderString($parms = array()) {
    if (!count($parms)) return ' ORDER BY priority DESC, url_regexp, id';
    else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
  }

  static function GetCountRows($parms = array()) {
    $db = Site::GetDB();
    return $db->SelectValue("SELECT COUNT(*) FROM metainfo".self::whereString($parms));
  }

  static function GetRows($parms = array(), $limit = array(), $order = array()) {
    $db = Site::GetDB();
    return $db->SelectSet("SELECT * FROM metainfo".
                    self::whereString($parms).self::orderString($order).
                    self::limitString($limit), 'id');
  }

  static function GetRow($parms = array()) {
    $db = Site::GetDB();
    return $db->SelectRow("SELECT * FROM metainfo".self::whereString($parms)." ORDER BY priority DESC, url_regexp, id LIMIT 1");
  }

  static function GetDefault() {
    $db = Site::GetDB();
    return $db->SelectRow("SELECT title, description, keywords FROM metainfo WHERE url_regexp='^$'".(defined("LANG") ? " AND lang='".mysql_real_escape_string(LANG)."'" : ''));
  }

  static function Init() {
    $db = Site::GetDB();
    $metaArr = array();
    $requestUri = preg_replace(array('{^'.Site::GetParms('locationPath').Site::GetParms('offsetPath').'}', '{\?.*$}'), array('', ''), Site::GetParms('requestUri'));
    if (defined("LANG")) $requestUri = preg_replace('/'.LANG.'\//', '', $requestUri);
    if (!Site::GetParms('titleSite')) {
      $parms = array('regexp' => $requestUri);
      if (defined("LANG")) $parms['lang'] = LANG;
      $metaList = self::GetRow($parms);
      if ($metaList['id'] > 0) {
        preg_match('/'.$metaList['url_regexp'].'/', $requestUri, $m);
        $metaArr['title'] = self::Transform($metaList['title'], $m);
        $metaArr['description'] = self::Transform($metaList['description'], $m);
        $metaArr['keywords'] = self::Transform($metaList['keywords'], $m);
      }
      else $metaArr = self::GetDefault();
      Site::SetParms('titleSite', trim($metaArr['title']));
      Site::SetParms('descriptionSite', trim($metaArr['description']));
      Site::SetParms('keywordsSite', trim($metaArr['keywords']));
    }
    Site::SetParms('tempMetaObj', '');
    return true;
  }

  static function Transform($tag, $urlMatches) {
    //Заменить ${same} на Site::GetParms('metaVars-same');
    $tag = preg_replace('/\$\{([A-Za-z_]+)\}/e', '(Site::GetParms("metaVars-\\1") ? Site::GetParms("metaVars-\\1") : "${\\1}")', $tag);
    //Заменить %{Func($1...)} на self::Func(номер параметра в rules.xml, то есть 1-й);
    if (preg_match("/^\%\{([A-Za-z_]+)\((.*)\)\}$/i", $tag)) {
      $tag = preg_replace('/\$([0-9]+)/e', '$urlMatches[\\1]', $tag);
      preg_match('/\%\{([A-Za-z_]+)\((.*)\)\}/e', $tag, $temp);
      $temp1 = explode(",", $temp[2]);
      foreach ($temp1 as $k => $v) $temp2[] = (preg_match("/\'[^\']+\'/i", $v) ? trim($v) : "'".trim($v)."'");
      $temp[2] = implode(", ", $temp2);
      $arrMet = get_class_methods(__CLASS__);
      foreach ($arrMet as $k => $v) $arrMet[$k] = mb_strtolower($v, 'utf-8');
      if (in_array(mb_strtolower($temp[1], 'utf-8'), $arrMet)) {
        if ($temp[1] == 'GetPages') $ret = self::GetPages($urlMatches[0]);
        else eval("\$ret = self::$temp[1]($temp[2]);");
        return $ret;
      }
      else return $temp[2];
    }
    return $tag;
  }

  static function GetNews($id, $flag) {
    if (!Site::GetParms('tempMetaObj')) {
      include_once(Site::GetParms('tablesPath')."News.php");
      if (!$item = News::GetRow(array('id' => $id))) return false;
      Site::SetParms('tempMetaObj', $item);
    }
    $item = Site::GetParms('tempMetaObj');
    if ($flag == 'title') return $item['name'];
    else if ($flag == 'keywords') return $item['name'];
    else if ($flag == 'description') return $item['name'];
    return '';
  }

  static function GetArticles($id, $flag) {
    if (!Site::GetParms('tempMetaObj')) {
      include_once(Site::GetParms('tablesPath')."Articles.php");
      if (!$item = Articles::GetRow(array('id' => $id))) return false;
      Site::SetParms('tempMetaObj', $item);
    }
    $item = Site::GetParms('tempMetaObj');
    if ($flag == 'title') return $item['name'];
    else if ($flag == 'keywords') return $item['name'];
    else if ($flag == 'description') return $item['name'];
    return '';
  }

  function GetFaqs($id) {
    if (!Site::GetParms('tempMetaObj')) {
      include_once(Site::GetParms('tablesPath')."Faq.php");
      if (!$faq = Faq::GetRow(array('id' => $id))) return false;
      $name = 'question';
      if ($faq['id']) {
      $pos_beg = strpos($faq[$name], '.');
      if (!($pos_beg > 0)) $pos_beg = 0;
      $meta = substr($faq[$name], $pos_beg+1);
      $next = strpos($meta, ' ');
      $meta = substr($meta, $next);
      if (mb_substr($meta, 0, 95, 'utf-8') == 0) {
        $pos_beg = strpos($faq[$name], '?');
        if (!($pos_beg > 0)) $pos_beg = 0;
        $meta = substr($faq[$name], $pos_beg+1);
        $next = strpos($meta, ' ');
        $meta = substr($meta, $next);
      }
      if ($meta == 0) {
        $pos_beg = strpos($faq[$name], '\W');
        if (!($pos_beg > 0)) $pos_beg = 0;
        $meta = substr($faq[$name], $pos_beg+1);
        $next = strpos($meta, ' ');
        $meta = substr($meta, $next);
      }
      $meta = mb_substr($meta, 0, 95, 'utf-8').'...';
      }
      Site::SetParms('tempMetaObj', $meta);
    }
    $item = Site::GetParms('tempMetaObj');
    return $item;
  }

  static function GetCatalog($id, $flag) {
    if (!Site::GetParms('tempMetaObj')) {
      include_once(Site::GetParms('tablesPath')."Catalog.php");
      if (!$item = Catalog::GetRow(array('id' => $id))) return false;
      Site::SetParms('tempMetaObj', $item);
    }
    $item = Site::GetParms('tempMetaObj');
    if ($flag == 'title') return $item['name'];
    else if ($flag == 'keywords') return $item['name'];
    else if ($flag == 'description') return $item['name'];
    return '';
  }

  static function GetFabrics($id, $flag) {
    if (!Site::GetParms('tempMetaObj')) {
      include_once(Site::GetParms('tablesPath')."Fabrics.php");
      if (!$item = Fabrics::GetRow(array('id' => $id))) return false;
      Site::SetParms('tempMetaObj', $item);
    }
    $item = Site::GetParms('tempMetaObj');
    if ($flag == 'title') return $item['name'];
    else if ($flag == 'keywords') return $item['name'];
    else if ($flag == 'description') return $item['name'];
    return '';
  }

  static function GetItems($id, $flag) {
    if (!Site::GetParms('tempMetaObj')) {
      include_once(Site::GetParms('tablesPath')."Items.php");
      if (!$item = reset(Items::GetRows(array('id' => $id)))) return false;
      Site::SetParms('tempMetaObj', $item);
    }
    $item = Site::GetParms('tempMetaObj');
    if ($flag == 'title') return $item['art'].' | '.$item['name'].' | '.$item['nameC'].' | '.$item['nameF'];
    else if ($flag == 'keywords') return $item['art'].', '.$item['nameC'].', '.$item['nameF'];
    else if ($flag == 'description') return $item['nameC'].' '.$item['nameF'].'. '.$item['art'].'.';
    return '';
  }

  static function GetPages($id) {
    if (!Site::GetParms('tempMetaObj')) {
      include_once(Site::GetParms('tablesPath')."Mainmenu.php");
      if (!$item = Mainmenu::GetRow(array('href' => $id))) {
        $def = self::GetDefault();
        $item = $def['title'];
      }
      else {
      $item = $item['name'];
      }
      Site::SetParms('tempMetaObj', $item);
    }
    $item = Site::GetParms('tempMetaObj');
    return $item;
  }
}
?>
