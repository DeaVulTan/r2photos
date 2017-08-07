<html>
<style>
 TABLE { font-size: 11px; font-family: Tahoma; }
 INPUT { border: 1px solid #404040; background-color: #FFFFFF; font-size: 11px; font-family: Tahoma; }
 TEXTAREA { border: 1px solid #000000; font-family: "Courier New"; font-size: 12px; }
</style>
<script language="javascript">
var is_ie = (/msie/i.test(navigator.userAgent) && !/opera/i.test(navigator.userAgent));
function GID(id) { return (is_ie) ? document.all[id] : document.getElementById(id); }
</script>
<?php
/* rules.xml
<rule>
 <regexp>^k3script\.htm$</regexp>
 <engine1>Script</engine1>
</rule>
*/
if(get_magic_quotes_gpc())
  foreach($_POST as $name => $val)
    $_POST[$name] = stripslashes($val);
class ScriptEngine {
   var $name; var $parms;
   function ScriptEngine($name, $parms) { $this->name = $name; $this->parms = $parms; }
   
   function run() {
    $_ScriptEngineVersion = '1.04';
    ?><div align='right' style='color: #FF0000; font-weight: bold; font-size: 8px; font-family: Tahoma;'>v<?php echo $_ScriptEngineVersion; ?></div>
    <form method='post' action='script-q.htm' id="formScript" name="formScript">
    <table width='100%'><tr><td>
    <fieldset><legend>[ Выполнить MySQL-скрипт ]</legend>
    <textarea style='width: 100%; background-color: #EFEFFF;' rows='10' name='scriptmysql'><?php echo $_POST['scriptmysql']; ?></textarea>
    </fieldset>
    <br />
    <fieldset><legend>[ Выполнить PHP-скрипт ]</legend>
    <textarea style='width: 100%; background-color: #EFFFEF;' rows='10' name='scriptphp'><?php echo $_POST['scriptphp']; ?></textarea>
    </fieldset>
    <br />
    <fieldset><legend>[ SSH ]</legend>
    <textarea style='width: 100%; background-color: #FFEFEF;' rows='10' name='scriptssh'><?php echo $_POST['scriptssh']; ?></textarea>
    </fieldset>
    <br /><input type='submit' value='Выполнить' /> <input type='reset' value='Очистить' onClick='this.scriptmysql="";this.scriptphp="";' /><br /></td></tr></table>
    <?php
    return $this->doScript();
   }
   
   function doScript()
   {
    $db = Site::GetDB();
    if(strlen($_POST['scriptmysql']))
    {
     //explode multi-script to more scripts
     $scripts = array();
     $par = array('skobka' => 0, 'kav1' => 0, 'kav2' => 0, 'kav3' => 0);
     $kav = array("'" => "'", '"' => '"', '`' => '`', ')' => '(');
     $kav2 = array("'", "'", '`', '(');
     $q = 0;
     $cmd = $_POST['scriptmysql'].';';
     $start = 0;
     while($q < strlen($cmd))
     {
      if(in_array($cmd[$q], $kav2))
      {
        $zakr = '';
        foreach($kav as $to => $from)
          if($from == $cmd[$q] && ($q > 0 && $cmd[$q - 1] != '\\'))
          {
            $zakr = $to;
            break;
          }
        //skip all data between [q; zakr]
        $q++;
        while($q < strlen($cmd) && $cmd[$q] != $zakr)
        {
          if($cmd[$q] == '\\')
            $q++; //skip next symbol after \\
          $q++;
        }
      }
      else
        if($cmd[$q] == ';')
        {
          $tmp = trim(substr($cmd, $start, $q - $start));
          if(strlen($tmp))
            $scripts[] = $tmp;
          $start = $q + 1;
        }
      $q++;
     }//while q

     foreach($scripts as $cmd)
     {
       echo "<fieldset><legend>[ Run MySQL-script: <b>{$cmd}</b> ]</legend><br />";
       $res = $db->SelectSet($cmd);
       $affectedRows = $db->SelectAffectedRows();
       echo "&raquo; Result: sizeof[".sizeof($res)."]".($res == false && $affectedRows < 0 ? ', false[<span style="color: red;">Error: '.mysql_error().'</span>]' : ($res == true ? ', true' : '')).", affected rows[".$affectedRows."]".":<pre style='background-color: #E0E0F0;'>";
       print_r($res);
       echo '</pre>&raquo; Done.</fieldset>';
     }
    }
    if(strlen($_POST['scriptphp']))
    {
     echo "<fieldset><legend>[ Run PHP-script ]</legend><pre style='background-color: #E0F0E0;'>";
     $res = eval($_POST['scriptphp'].';');
     echo "</pre>&raquo; Done.</fieldset><br />";
     print_r($res);
    }
    //if(strlen($_POST['scriptssh']))
    {
     //print_r($_POST);
     echo '<div style="background-color: #EEEEEE; font-family: Courier new; font-size: 12px;"><input type="hidden" id="ssh_path" name="ssh_path" value="'.$_POST['ssh_path'].'" /><input type="hidden" id="ssh_file" name="ssh_file" value="" /><input type="hidden" id="ssh_file_asis" name="ssh_file_asis" value="0" />';
     $path = (isset($_POST['ssh_path']) ? $_POST['ssh_path'] : '');
     $path = trim(`cd {$path} && pwd`);
     if(strlen($path))
      $path .= '/';
     else
      $path = '.';
     echo 'Path: '.$path.'<br />';
     $dir = `cd {$path} && ls -l`;
     $lines = explode("\xA", $dir);
     foreach($lines as $id => $item)
     {
      if(preg_match('/^([dl\-][r\-][w\-][x\-][r\-][w\-][x\-][r\-][w\-][xt\-])( .* )(.*?)$/i', $item, $tmp))
      {
        if($tmp[1][0] == '-') //file
          echo $tmp[1].$tmp[2].'<a href="#" onClick="GID(\'ssh_file\').value=\''.$tmp[3].'\'; GID(\'formScript\').submit(); return false;">'.$tmp[3].'</a>&nbsp;&nbsp;'.
          /*'<a href="#" onClick="GID(\'ssh_file\').value=\''.$tmp[3].'\'; GID(\'ssh_file_asis\').value=1; GID(\'formScript\').submit(); return false;">[View As Is]</a>'*/''.'<br />';
        else  //directory
          echo $tmp[1].$tmp[2].'<a href="#" onClick="GID(\'ssh_path\').value+=\''.$tmp[3].'/\'; GID(\'formScript\').submit(); return false;">'.$tmp[3].'</a>'.'<br />';
      }
      else
      {
        if(preg_match('/^.* ([0-9]+)\s*$/i', $item, $tmp))
          echo 'Items: '.$tmp[1].'<br /><br /><a href="#" onClick="GID(\'ssh_path\').value+=\'./\'; GID(\'formScript\').submit(); return false;">[Current directory]</a><br /><a href="#" onClick="GID(\'ssh_path\').value+=\'../\'; GID(\'formScript\').submit(); return false;">[Parent directory]</a><br />';
      }
     }
     echo '</div>';
     $res = (strlen($_POST['scriptssh']) ? `cd {$path} && {$_POST['scriptssh']}` : '');
     if(isset($_POST['ssh_file']) && strlen(trim($_POST['ssh_file'])))
      echo '<pre><b>[File "'.$_POST['ssh_file'].'"]</b><br />

'.
        ($_POST['ssh_file_asis'] ? `cd {$path} && cat {$_POST['ssh_file']}` : htmlspecialchars(`cd {$path} && cat {$_POST['ssh_file']}`)).
        '

<br /><b>[/FILE]</b></pre>';
     echo "&raquo; Done.</fieldset><pre style='background-color: #EEEEEE;'>";
     print_r($res);
     echo '</pre>';
    }//ssh
    echo '</form>';
    return true;
   }
}
?>
</html>