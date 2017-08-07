<?php
class Ftp {
   private $settings = array();
   private $connection = false;

   public function __construct($settings = array()) {
      $this->settings = $settings;
   }

   public function connect() {
      $this->connection = false;
      try {
         if ($this->connection = ftp_connect($this->settings['host'])) {
            if ($login_result = ftp_login($this->connection, $this->settings['user'], $this->settings['pass'])) {
               if ($this->connection && $login_result) {
                  return true;
               }
               else throw new Exception('FTP cоединение не установлено');
            }
            else throw new Exception('Неверный логин или пароль');
         }
         else throw new Exception('Невозможно соединиться с "'.$this->settings['host'].'"');
      }
      catch (Exception $exception) {
         echo $exception->getMessage();
      }
      return false;
   }

   public function disconnect() {
      if (ftp_close($this->connection)) {
         $this->connection = false;
         return true;
      }
      return false;
   }

   public function nlist($path) {
      return ftp_nlist($this->connection, $path);
      /*if (false !== ($files = ftp_nlist($this->connection, $path))) return $files;
      throw new Exception('Не удалось получить список файлов в удаленном каталоге "'.$path.'"');
      return false;*/
   }

   public function rawlist($path) {
      return ftp_rawlist($this->connection, $path);
   }

   public function ftp_rawlist($path) {
      return ftp_rawlist($this->connection, $path);
   }

   public function ftp_size($file) {
      return ftp_size($this->connection, $file);
   }

   public function chdir($path) {
      if (ftp_chdir($this->connection, $path)) return true;
      return false;
   }

   public function delete($file) {
      if (ftp_delete($this->connection, $file)) return true;
      throw new Exception('Не удалось удалить файл "'.$file.'"');
      return false;
   }

   public function put($dst = '', $src = '') {
      if (ftp_put($this->connection, $dst, $src, FTP_BINARY)) return true;
      throw new Exception('Не удалось отправить файл "'.$src.'"');
      return false;
   }

   public function get($dst = '', $src = '') {
      if (ftp_get($this->connection, $dst, $src, FTP_BINARY)) return true;
      throw new Exception('Не удалось получить файл "'.$src.'"');
      return false;
   }

   public function fget($dstHandle = '', $src = '') {
      if (ftp_fget($this->connection, $dstHandle, $src, FTP_BINARY)) return true;
      throw new Exception('Не удалось получить файл "'.$src.'"');
      return false;
   }
}
?>