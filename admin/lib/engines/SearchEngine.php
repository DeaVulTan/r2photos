<?php
class SearchEngine {
   var $name; var $parms;
   function SearchEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }

   function run() {
      if (IS_OFFICE !== true) return '404.htm';
      return $this->viewForm();
   }

   function viewForm() {
      $result = '';
      if (trim(strip_tags($_POST['searchadmin']))) {
         $search = addslashes(stripslashes(trim(strip_tags($_POST['searchadmin']))));
         preg_match('/((.*)?admin\/)(.*)?/', $_SERVER['REQUEST_URI'], $m);
         include($_SERVER['DOCUMENT_ROOT'].$m[2].'config.php');
         preg_match('/^([^:]*)?:([^@]*)?@([^\/]*)?\/(.+)$/', $config['database'], $arDatabase);
         $ind = 0;
         $db = Site::GetDB();
         $tables = $tablesTmp = $fieldsTmp = array();
         $equal = array('config' => 'configs', 'banner_places' => 'bannersplaces', 'metainfo' => 'meta');
         $tablesTmp = $db->SelectSet("SHOW tables FROM ".$arDatabase[4]);
         $noT = array('admins', 'banner_stat', 'sitepages', 'subscribers_bans', 'subscribers_cont', 'statistic_inputs', 'orders_items');
         if (sizeOf($tablesTmp) > 0) foreach ($tablesTmp as $k => $v) {
            $tName = trim(reset($v));
            if (!in_array($tName, $noT)) {
               $tables[$ind]['name'] = $tName;
               $ind ++;
            }
         }
         unset($tablesTmp);
         if (sizeOf($tables) > 0) foreach ($tables as $k => $v) {
            $fieldsTmp = $db->SelectSet("SHOW fields FROM ".$v['name']);
            if (sizeOf($fieldsTmp) > 0) foreach ($fieldsTmp as $k1 => $v1) if (preg_match('/^(varchar|text)/', $v1['Type'])) $tables[$k]['fields'][] = $v1['Field'];
           if (!sizeOf($tables[$k]['fields'])) unset($tables[$k]);
         }
         unset($fieldsTmp);
         if (sizeOf($tables) > 0) foreach ($tables as $k => $v) {
            $tables[$k]['items'] = $db->SelectSet("SELECT * FROM ".$v['name']." WHERE ".(sizeOf($v['fields']) > 1 ? "CONCAT(`".implode("`, ' ', `", $v['fields'])."`)" : "`".reset($v['fields'])."`")." LIKE '%".$search."%'");
            if (!sizeOf($tables[$k]['items'])) unset($tables[$k]);
         }
         $ind = 0;
         if (sizeOf($tables) > 0) foreach ($tables as $k => $v) {
            if (array_key_exists($v['name'], $equal)) $link = $equal[$v['name']];
            else if (preg_match('/^forum_/', $v['name'])) $link = preg_replace('/^forum_/', '', $v['name']);
            else $link = preg_replace('/_/', '', $v['name']);
            foreach ($v['items'] as $k1 => $v1) {
               $text = '';
               foreach ($v['fields'] as $k2 => $v2) $text .= (mb_strlen($text, 'utf-8') ? ' ' : '').strip_tags($v1[$v2]);
               $text = preg_replace('/&nbsp;/', ' ', $text);
               $text = preg_replace('/\s+/', ' ', trim($text));
               if (mb_strlen($text, 'utf-8') < 1) $text = 'Смотреть';
               $pos = mb_strpos(mb_strtolower($text, 'utf-8'), mb_strtolower(strip_tags($_POST['searchadmin']), 'utf-8'), 0, 'utf-8');
               if ($pos) $start = (($pos - 125) > 0 ? ($pos - 125) : 0);
               $text = (($pos - 125) > 0 ? '...' : '').mb_substr($text, $start, 255, 'utf-8').(mb_strlen($text, 'utf-8') > 0 ? '...' : '');
               $text = preg_replace('/'.strip_tags($_POST['searchadmin']).'/i', '<span style="background-color: #B5D7DC">'.strip_tags($_POST['searchadmin']).'</span>', $text);
               $result .= '<div>'.(++$ind).'. <a href="'.$link.'-edit'.($link == 'subscribersmain' ? 'form' : '').'_'.$v1['id'].'.htm" target="_blank">'.$text.'</a></div>';
            }
         }
      }
      include(Site::GetTemplate($this->name, 'form'));
      return true;
   }
}
?>
