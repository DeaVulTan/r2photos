<?php
include_once(Site::GetParms('libPath').'Actions.php');
class Form {
   var $parms = array('method' => 'post', 'name' => 'sameForm', 'encoding' => '' ); var $fields = array(); var $isValid = true;
   function Form($fields, $parms = array()) { for ($i = 0; $i < sizeOf($fields); $i++) { $name = $fields[$i]->name; $this->fields[$name] =& $fields[$i]; } $this->parms = array_merge($this->parms, $parms); $this->isValid = true; }
   function header($extra = '') { if (isset($this->parms['function']) && $this->parms['function']) { echo "\n<script language=\"JavaScript\" type=\"text/javascript\"><!--\n  function ".$this->parms['function']."(form_name) {\n"; foreach ($this->fields as $id => $row) { if ($row->parms['regexp']) { echo "    if (!form_name.$id.value.match(".$row->parms['regexp'].")) {\n"; if (isset($row->parms['alert'])) echo "      alert(\"".addslashes($row->parms['alert'])."\");\n"; echo "      form_name.$id.focus();\n      return false;\n    }\n"; } } echo "   return true;\n  }\n//-->\n</script>\n"; } $html = ''; foreach ($this->parms as $n => $v) if ($v && $v !== true && $n <> 'function') $html .= " ".$n."='".$v."'"; echo "<form".$html.(trim($extra) ? " ".trim($extra) : '').(isset($this->parms['function']) && $this->parms['function'] ? ' onsubmit="return '.$this->parms['function'].'(this);"' : '').'>'; }
   function footer() { echo '</form>'; }
   function field($name, $extra = '', $extra2 = 0) { $this->fields[$name]->write($extra, $extra2); }
   function processIfSubmitted() { return ($this->isSubmitted() ? $this->process() : false); }
   function isSubmitted() { $data =& $this->_httpData(); return sizeOf($data) > 0; }
   function process() { $data =& $this->_httpData(); $this->isValid = true; foreach (array_keys($this->fields) as $n) { $this->fields[$n]->makeValue($data); $this->fields[$n]->check(); $this->isValid = $this->isValid && $this->fields[$n]->isValid; } return $this->isValid; }
   function &_httpData() { if ($this->parms['method'] == 'get') return $_GET; else return $_POST; }
   function setParm($name, $value) { $this->parms[$name] = $value; }
   function isValid($name = false) { return $name ? $this->fields[$name]->isValid : $this->isValid; }
   function makeValid($name) { $this->isValid = true; $this->fields[$name]->isValid = true; foreach (array_keys($this->fields) as $n) $this->isValid = $this->fields[$n]->isValid && $this->isValid; return $this->isValid; }
   function &getFieldObject($name) { return $this->fields[$name]; }
   function set($fields, $value = false, $check = true) { if (!is_array($fields)) $fields = array($fields => $value); foreach ($fields as $n => $v) { if (!isset($this->fields[$n])) continue; $this->fields[$n]->set($v); if ($check) { $this->fields[$n]->check(); $this->isValid = $this->isValid && $this->fields[$n]->isValid; } } }
   function assign($fields, $value = false) { $this->set($fields, $value, false); }
   function get($name = false) { if ($name) { return $this->fields[$name]->value(); } else { $res = array(); foreach (array_keys($this->fields) as $n) if (get_class($this->fields[$n]) <> 'Capcha') $res[$n] = $this->fields[$n]->value(); return $res; } }
}

class Field { var $parms = array(); var $name; var $value; var $isValid = true; function Field($name, $parms = array()) { $this->name = $name; $this->parms =& $parms; if (isset($this->parms['default'])) $this->set($this->parms['default']); } function value() { return $this->value; } function set($value) { $this->value = $value; } function check() { return ($this->isValid = true); } function makeValue(&$data) { $this->value = isset($data[$this->name]) ? $data[$this->name] : null; } }
class Input extends Field { function Input($name, $parms = array()) { $this->Field($name, $parms); } function check() { return ($this->isValid = (isset($this->parms['regexp']) ? preg_match($this->parms['regexp'], $this->value) : true)); } function write($extra) { $type = (isset($this->parms['type']) && $this->parms['type'] ? $this->parms['type'] : ''); Action::Input($this->name, $this->value, $extra, $type); } }
class TextArea extends Field { function TextArea($name, $parms = array()) { $this->Field($name, $parms); } function check() { return ($this->isValid = (isset($this->parms['regexp']) ? preg_match($this->parms['regexp'], $this->value) : true)); } function write($extra) { Action::TextArea($this->name, $this->value, $extra); } }
class CheckBox extends Field { function CheckBox($name, $parms = array()) { $this->Field($name, $parms); } function makeValue(&$data) { $this->value = ((isset($data[$this->name]) && $data[$this->name]) ? 1 : 0); } function set($value) { $this->value = ($value ? 1 : 0); } function write($extra) { Action::CheckBox($this->name, $this->value, $extra); } }
class Select extends Field { var $items; function Select($name, $parms = array()) { $this->Field($name, $parms); $this->items =& $parms['items']; } function write($extra) { Action::Select($this->name, $this->value, $this->items, $extra, $this->parms); } }
class Calendar extends Field { function Calendar($name, $parms = array()) { $this->Field($name, $parms); } function set($value) { if (!$value) $this->value = ''; else $this->value = strftime('%d.%m.%Y', $value); } function value() { if (!preg_match('{\s*(\d\d)[.-/](\d\d)[.-/](\d\d\d\d)\s*}', $this->value, $m)) return null; return mktime(0, 0, 0, $m[2], $m[1], $m[3]); } function write($extra) { Action::Calendar($this->name, $this->value, $extra); } }
class FCKeditor extends Field { function FCKeditor($name, $parms = array()) { $this->Field($name, $parms); } function check() { return ($this->isValid = (isset($this->parms['regexp']) ? preg_match($this->parms['regexp'], $this->value) : true)); } function write($extra) { Action::FCKeditor($this->name, $this->value, $this->parms, $extra); } }
class Radio extends Field { function Radio($name, $parms = array()) { $this->Field($name, $parms); $this->items =& $parms['items']; } function write($extra, $idx) { Action::Radio($this->name, $this->value, $idx, $this->items, $extra, $this->parms); } }
class Capcha extends Field { function Capcha($name, $parms = array()) { $this->Field($name, $parms); } function check() { return ($this->isValid = (isset($this->parms['regexp']) ? preg_match($this->parms['regexp'], $this->value) : true)); } function write($extra) { Action::Capcha($this->name, $extra); } }
class Upload extends Field
{
    function Upload($name, $parms = array())
    {
        $this->Field($name, $parms);
    }
    function check()
    {
        if ($this->parms['obligatory']) return $this->isValid = ($_FILES[$this->name."_file"]['tmp_name'] != '' && (filesize($_FILES[$this->name."_file"]['tmp_name']) > 0));
        else return true;
    }
    function makeValue(&$data)
    {
        if (!$this->parms['text']) $this->value = (isset($_FILES[$this->name."_file"]) ? $_FILES[$this->name."_file"] : null);
        else
        {
            $file =& $_FILES[$this->name."_file"];
            $path = Site::GetParms('absolutePath').$this->parms['path'];
            if (trim($file['name']))
            {
                $fileNameInfo = pathinfo( $file['name'] );
                $trName = Utils::Translit( $fileNameInfo['filename'] ).'.'.strtolower( $fileNameInfo['extension'] );
                $newPath = $path.'/'.$trName;
                if (file_exists($newPath))
                {
                    $trName = $this->rename($path, $trName);
                    $newPath = $path.'/'.$trName;
                }
                if (filesize($file['tmp_name']) <= 0) trigger_error('File '.$file['name'].' not found!', ERROR);
                if (!is_uploaded_file($file['tmp_name'])) trigger_error('File '.$file['name'].' cann\'t be uploaded!', ERROR);
                move_uploaded_file($file['tmp_name'], $newPath);
                @chmod($newPath, 0755);
                $this->value = $this->parms['path'].'/'.$trName;
                if (!file_exists($newPath)) trigger_error('File '.$file['name'].' will be not uploaded!', ERROR);
            }
            else $this->value = $data[$this->name."_text"];
        }
    }
    function write($extra, $extra2)
    {
        Action::Upload($this->name, (is_array($this->value) ? '' : $this->value), $extra, $this->parms, $extra2);
    }

    function rename($path, $tmpName, $i = 0)
    {
        $name = $i.'_'.$tmpName;
        if (file_exists($path.'/'.$name))
        {
            $inc = $i + 1;
            $newName = $this->rename($path, $tmpName, $inc);
        }
        else $newName = $name;

        return $newName;
    }
}

class Datetimepicker extends Field {
  function __construct($name, $parms = array()) {
    $this->Field($name, $parms); }
  function set($value) {
    if (!$value) $this->value = ''; else $this->value = strftime('%d.%m.%Y %H:%M', $value); }
  function value() {
    return strtotime($this->value);
    }
  function write($extra) { Action::Datetimepicker($this->name, $this->value, $extra); }
}