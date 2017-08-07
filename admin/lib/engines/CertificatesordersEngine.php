<?php
class CertificatesordersEngine extends AdminEngine {
   function CertificatesordersEngine($name, $parms) { $this->AdminEngine($name, $parms); }

   function GetCountItems($parms = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Certificatesorders::GetCountRows($parms);
   }

   function &GetItems($parms = array(), $limit = array(), $order = array()) {
      $parms['lang'] = Site::GetSession($this->name."-lang");
      $parms['like'] = Site::GetSession($this->name."-like");
      return Certificatesorders::GetRows($parms, $limit, $order);
   }

   function selfRun() {
      if ($this->parms['action'] == 'filter') {
         Site::SetSession($this->name.'-like', strip_tags($_POST['like']));
         if (defined('LANG')) Site::SetSession($this->name.'-lang', $_POST['lang']);
         return Site::CreateUrl($this->name.'-list');
      }
      return;
   }

   function getPictureStr($name, $item) {
      $file = $item[ $name ];
      echo ($file && file_exists(Site::GetParms('absolutePath').$file) ? '<img style="max-width: 400px;" src="../'.$file.'" />' : 'Картинки нет.');
   }

   function getCertificateStr($name, $item) {
      $certificate = ( $item[ $name ] ? Certificates::GetRow( array( 'id' => $item[ $name ] ) ) : array() );
      $certificateType = ( $item['certificate_type_id'] ? Certificatestypes::GetRow( array( 'id' => $item['certificate_type_id'] ) ) : array() );
      echo $certificate['name_full'].'<br />';
      if( $certificateType['id'] ) {
          echo 'Тип: '.( $certificateType['name'] ).'<br />';
      }
   }

   function getLocationStr($name, $item) {
      $location = ( $item[ $name ] ? Locations::GetRow( array( 'id' => $item[ $name ] ) ) : array() );
      echo $location['name'];
      if( $location['price'] > 0 ) {
          echo '<br />+'.number_format( $location['price'], 0 ).'&nbsp;руб.';
      }
   }

   function getDataStr($name, $item) {
      echo date("d.m.Y", $item[$name]);
   }

   function doBeforeRun() {
      if (IS_ORDERS !== true) return '404.htm';
      if (!Site::GetSession($this->name."-sort")) {
         Site::SetSession($this->name."-sort", 'idate');
         Site::SetSession($this->name."-order", 'desc');
      }
   }
}
?>