<?php

class Action {
   static function Input($name, $value = '', $extra = '', $type = 'text') { ?><input id="field_<?php echo preg_replace('/[\[\]]/', '_', $name); ?>" name="<?php echo $name; ?>" type="<?php echo ($type ? $type : 'text'); ?>" value="<?php echo htmlspecialchars($value); ?>"<?php echo $extra; ?> /><?php }
   static function TextArea($name, $value = '', $extra = '') { ?><textarea id="field_<?php echo preg_replace('/[\[\]]/', '_', $name); ?>" name="<?php echo $name; ?>"<?php echo $extra; ?>><?php echo htmlspecialchars($value); ?></textarea><?php }
   static function CheckBox($name, $value = 0, $extra = '') { ?><input type="checkbox" id="field_<?php echo preg_replace('/[\[\]]/', '_', $name); ?>" name="<?php echo $name; ?>"<?php echo ($value ? ' checked="checked"' : '').$extra; ?> value="1" /><?php }
   static function Radio($name, $value, $idx, &$items, $extra = '', $parms = array()) { if ($parms['annotated'] == true) { $keys = array_keys($items); ?><input type="radio" id="field_<?php echo preg_replace('/[\[\]]/', '_', $name); ?>" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($keys[$idx]); ?>"<?php echo ($keys[$idx] == $value ? ' checked="checked" ' : '').$extra; ?> />&nbsp;<?php echo $items[$keys[$idx]]; ?><?php } else { ?>   <input type="radio" id="field_<?php echo preg_replace('/[\[\]]/', '_', $name); ?>" name="<?php echo $name; ?>" value="<?php echo htmlspecialchars($items[$idx]); ?>"<?php echo ($items[$idx] == $value ? ' checked="checked" ' : '').$extra; ?> /><?php } }
   static function Select($name, $value, &$items, $extra = '', $parms = array()) { ?><select id="field_<?php echo preg_replace('/[\[\]]/', '_', $name); ?>" name="<?php echo $name; ?>"<?php echo $extra; ?>><?php foreach ($items as $n => $v) { ?><option value="<?php echo htmlspecialchars($n); ?>"<?php echo ($n == $value ? ' selected="selected"' : ''); ?>><?php echo ($parms['field'] ? $v[$parms['field']] : $v); ?></option><?php } ?></select><?php }
   static function Calendar($name, $value = '', $extra = '') { ?><input type="text" id="field_<?php echo preg_replace('/[\[\]]/', '_', $name); ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>"<?php echo $extra; ?> /><?php JS::begin(); ?>$(function() { $("#field_<?php echo preg_replace('/[\[\]]/', '_', $name); ?>").datepicker({ <?php if ($value) { ?>defaultDate: '<?php echo $value; ?>', <?php } ?>showOn: "button", buttonImage: "<?php echo Site::GetParms('locationPath'); ?>image/calendar/calendar.gif", buttonImageOnly: true, showWeek: true, firstDay: 1 }); });<?php JS::end(); }
   static function FCKeditor($name, $value = '', $parms = array(), $extra = '') {
      include_once(Site::GetParms('absolutePath').'admin/lib/common/ckeditor/ckeditor.php');
      $CKEditor = new CKEditor();
      $CKEditor->returnOutput = true;
      $CKEditor->basePath = Site::GetParms('locationPath').'admin/lib/common/ckeditor/';
      $config['skin'] = 'office2003';
      $config['height'] = ($parms['Height'] ? $parms['Height'] : '500');
      $kcfinder = Site::GetParms('locationPath').'admin/lib/common/kcfinder/';
      $config['filebrowserBrowseUrl'] = $kcfinder.'browse.php?type=file';
      $config['filebrowserImageBrowseUrl'] = $kcfinder.'browse.php?type=image';
      $config['filebrowserFlashBrowseUrl'] = $kcfinder.'browse.php?type=flash';
      $config['filebrowserUploadUrl'] = $kcfinder.'upload.php?type=file';
      $config['filebrowserImageUploadUrl'] = $kcfinder.'upload.php?type=image';
      $config['filebrowserFlashUploadUrl'] = $kcfinder.'upload.php?type=flash';
      print '<div class="js-ckeditor">'.$CKEditor->editor($name, $value, $config).'</div>';
   }
   static function Upload($name, $value = '', $extra = '', $parms = array(), $extra2 = '') { if ($parms['text']) { ?><input type="text" value="<?php echo htmlspecialchars($value); ?>" id="field_<?php echo preg_replace('/[\[\]]/', '_', $name); ?>_text" name="<?php echo $name; ?>_text"<?php echo $extra; ?> />&nbsp;<?php } ?><input type="file" id="field_<?php echo preg_replace('/[\[\]]/', '_', $name); ?>_file" name="<?php echo $name; ?>_file"<?php echo ($parms['text'] ? ($extra2 ? $extra2 : '') : ($extra ? $extra : '')); ?> /><?php }
   static function Capcha($name = 'code', $extra = '') { ?><p class="absM"><img src="pcode.php<?php echo '?'.md5( serialize( microtime() ) ); ?>" width="165" height="60" alt="Введите код" />&nbsp;<span><input autocomplete="off" type="text" id="field_<?php echo ($name ? preg_replace('/[\[\]]/', '_', $name) : 'code'); ?>" name="<?php echo ($name ? $name : 'code'); ?>" value=""<?php echo $extra; ?> /></span></p><?php }
   static function Button($name, $value = 'OK', $type = 'submit', $extra = '') { ?><input id="field_<?php echo preg_replace('/[\[\]]/', '_', $name); ?>" name="<?php echo $name; ?>" type="<?php echo $type; ?>" value="<?php echo htmlspecialchars($value); ?>"<?php echo $extra; ?> /><?php }
   static function Datetimepicker($name, $value = '', $extra = '') { ?><input type="text" id="field_<?php echo preg_replace('/[\[\]]/', '_', $name); ?>" name="<?php echo $name; ?>" value="<?php echo $value; ?>"<?php echo $extra; ?> /><script type="text/javascript"> $(function() { $("#field_<?php echo preg_replace('/[\[\]]/', '_', $name); ?>").datetimepicker();});</script><?php }
}