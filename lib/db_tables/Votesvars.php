<?php
include_once(Site::GetParms('tablesPath')."Votes.php");
class Votesvars {
   function Votesvars() { }
   function &Structure($init = false) {
      if ($init) {
         $form = array(
            'is_active' => array('default' => 0, 'form' => array('CheckBox' => array('name' => 'Активность'))),
            'ord' => array('default' => 0, 'form' => array('Input' => array('name' => 'Сортировка', 'style' => 'size="4"'))),
            'poll_id' => array('default' => (int)Site::GetSession("votesvars-poll_id"), 'form' => array('Select' => array('items' => Votes::GetRows(), 'field' => 'question', 'name' => 'Вопрос'))),
            'variant' => array('default' => '', 'form' => array('Input' => array('name' => 'Вариант ответа', 'style' => 'style="width: 100%;"'))),
         );
         $columns = array(
            'is_active' => array('name' => 'Активность', 'autoUpdate' => true),
            'ord' => array('name' => 'Сортировка', 'autoUpdate' => true, 'sort' => true, 'order' => true),
            'variant' => array('name' => 'Вариант ответа', 'style' => 'width="100%" align="left"', 'sort' => true),
            'q' => array('name' => 'Кол-во ответов', 'sort' => true),
         );
      }
      return array(
         'form' => ($form ? $form : false),
         'columns' => ($columns ? $columns : false),
         'table'  => 'poll_variants',
         'tableName'  => 'Варианты ответов'
      );
   }

   function &Get($id = '', $init = false) { return new Table(Votesvars::Structure($init), $id); }
   function Update(&$rows) { $t = new Table(Votesvars::Structure()); return $t->UpdateRows($rows, Votesvars::Structure()); }
   function Save(&$rows) { $t = new Table(Votesvars::Structure()); return $t->SaveRows($rows, Votesvars::Structure()); }
   function Delete(&$rows) { $t = new Table(Votesvars::Structure()); return $t->DeleteRows($rows, Votesvars::Structure()); }

   function whereString($parms) {
      $where = '';
      if (isset($parms['active'])) $where .= ($where ? " AND " : " WHERE ")."is_active='".(int)$parms['active']."'";
      if ($parms['like']) $where .= ($where ? " AND " : " WHERE ")."question LIKE '%".mysql_real_escape_string($parms['like'])."%'";
      if ($parms['ip']) $where .= ($where ? " AND " : " WHERE ")."ip='".mysql_real_escape_string($parms['ip'])."'";
      if ($parms['id']) $where .= ($where ? " AND " : " WHERE ")."id='".(int)$parms['id']."'";
      if ($parms['poll_id']) $where .= ($where ? " AND " : " WHERE ")."poll_id='".(int)$parms['poll_id']."'";
      return $where;
   }

   function limitString($parms) { $limit = ''; if ($parms['limit'] > 0) { if ($parms['offset'] > 0) $limit = " LIMIT ".(int)$parms['offset'].", ".(int)$parms['limit']; else $limit = " LIMIT ".(int)$parms['limit']; } return $limit; }

   function orderString($parms = array()) {
      if (!sizeOf($parms)) return ' ORDER BY ord';
      else foreach ($parms as $k => $v) return ' ORDER BY '.$k.' '.strtoupper($v);
   }

   function &GetCountRows($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(*) FROM poll_variants".Votesvars::whereString($parms));
   }

   function &GetRows($parms = array(), $limit = array(), $order = array()) {
      $db =& Site::GetDB();
      return $db->SelectSet("SELECT * FROM poll_variants".
                             Votesvars::whereString($parms).Votesvars::orderString($order).
                             Votesvars::limitString($limit), 'id');
   }

   function isThisIPVotes($parms) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT id FROM poll_ip ".Votesvars::whereString($parms));
   }

   function IncrementQ($parms) {
      $db =& Site::GetDB();
      $db->Query("UPDATE poll_variants SET q=1+q".Votesvars::whereString($parms));
   }

   function setThisIPVotes($parms) {
      $db =& Site::GetDB();
      $db->Query("INSERT INTO poll_ip (poll_id, ip) VALUES ('".(int)$parms['poll_id']."', '".addslashes(trim(Votesvars::get_ip()))."')");
   }

   function SumQ($parms) {
      $db =& Site::GetDB();
      return $db->SelectValue("SELECT COUNT(id) FROM poll_ip".Votesvars::whereString($parms));
   }

   function &GetRow($parms = array()) {
      $db =& Site::GetDB();
      return $db->SelectRow("SELECT * FROM poll_variants".Votesvars::whereString($parms));
   }
   
    function get_ip()
    {
        $ip = false;
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipa[] = trim(strtok($_SERVER['HTTP_X_FORWARDED_FOR'], ','));
        
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipa[] = $_SERVER['HTTP_CLIENT_IP'];       
        
        if (isset($_SERVER['REMOTE_ADDR']))
            $ipa[] = $_SERVER['REMOTE_ADDR'];
        
        if (isset($_SERVER['HTTP_X_REAL_IP']))
            $ipa[] = $_SERVER['HTTP_X_REAL_IP'];
        
        // проверяем ip-адреса на валидность начиная с приоритетного.
        foreach($ipa as $ips)
        {
            //  если ip валидный обрываем цикл, назначаем ip адрес и возвращаем его
            if(Votesvars::is_valid_ip($ips))
            {                    
                $ip = $ips;
                break;
            }
        }
        return $ip;
        
    }
    
    function is_valid_ip($ip=null)
    {
        if(preg_match("#^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$#", $ip))
            return true;
        
        return false;
    }
}
?>