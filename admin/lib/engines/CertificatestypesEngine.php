<?php
class CertificatestypesEngine extends AdminEngine {
   function CertificatestypesEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['certificate_id'] = Site::GetSession($this->name."-certificate_id");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Certificatestypes::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['certificate_id'] = Site::GetSession($this->name."-certificate_id");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Certificatestypes::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-certificate_id', ($_POST['fromFF'] ? $_POST['certificate_id'] : $this->values[0]));
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         return Site::CreateUrl($this->name.'-list');
      }
   }

   function getPictureStr($name, $item) {
      $file = $item['picture'];
      echo ($file && file_exists(Site::GetParms('absolutePath').$file) ? '<img src="../'.$file.'" style="max-width: 400px;" />' : 'Картинки нет.');
   }

   function doBeforeRun() {
      if (IS_CATALOG !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'ord');
         Site::SetSession($this->name."-order", 'asc');
      }
   }

   function topNav() {
      $certificateId = ( int ) Site::GetSession( $this->name.'-certificate_id' );
      $certificate = ( $certificateId ? Certificates::GetRow( array( 'id' => $certificateId ) ) : array() );
      return '<a href="certificates-list.htm" title="Сертификаты">Сертификаты</a> &gt; '.( $certificate['id'] ? $certificate['name'].': ' : '' ).'Типы';
   }
}
?>