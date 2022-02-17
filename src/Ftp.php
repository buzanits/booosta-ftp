<?php
namespace booosta\ftp;

use \booosta\Framework as b;
b::init_module('ftp');

class FTP extends \booosta\base\Module
{ 
  use moduletrait_ftp;

  protected $host, $port;
  protected $username, $password;
  protected $conn;
  protected $passivemode = true;
  protected $implicit_tls = false;
  protected $error;

  public function __construct($host, $username, $password, $options = [])
  {
    $this->host = $host;
    $this->username = $username;
    $this->password = $password;
    $this->port = is_numeric($options['port']) ? $options['port'] : 21;

    if($options['implicit_tls']):
      $this->implicit_tls = true;
      $this->port = is_numeric($options['port']) ? $options['port'] : 990;

      $this->conn = curl_init();
      curl_setopt($this->conn, CURLOPT_USERPWD, "$username:$password");
      curl_setopt($this->conn, CURLOPT_SSL_VERIFYPEER, FALSE);
      curl_setopt($this->conn, CURLOPT_SSL_VERIFYHOST, FALSE);
      curl_setopt($this->conn, CURLOPT_FTP_SSL, CURLFTPSSL_TRY);
      curl_setopt($this->conn, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_TLS);
      curl_setopt($this->conn, CURLOPT_FTP_SSL, CURLFTPSSL_ALL);
      curl_setopt($this->conn, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_DEFAULT);
    elseif($options['plaintext']):
      $this->conn = ftp_connect($host, $this->port);

      if($this->conn === false):
        $this->error = "Cannot connect to $host:$this->port";
      else:
        $login_result = ftp_login($this->conn, $username, $password);
        if($login_result == false) $this->error = "Cannot login to $host:$port as $username";;
      endif;
    else:
      $this->conn = ftp_ssl_connect($host, $this->port);

      if($this->conn === false):
        $this->error = "Cannot connect to $host:$this->port";
      else:
        $login_result = ftp_login($this->conn, $username, $password);
        if($login_result == false) $this->error = "Cannot SSL login to $host:$port as $username";;
      endif;
    endif;
  }

  public function __destruct()
  {
    if($this->implicit_tls) curl_close($this->conn);
    else ftp_close($this->conn);
  }


  public function set_passivemode($flag) { $this->passivemode = $flag; }
  public function error() { return $this->error; }
  public function get_error() { return $this->error; }

  public function download($remote_name, $local_name = null)
  {
    if($this->error) return "ERROR: $this->error";

    if($local_name == null) $local_name = basename($remote_name);
    if($this->passivemode) ftp_pasv($this->conn, true);

    return ftp_get($this->conn, $local_name, $remote_name, FTP_BINARY);
  }

  public function upload($filename, $remote_name = null)
  {
    if($this->error) return "ERROR: $this->error";

    if($remote_name == null) $remote_name = basename($filename);
    if($this->passivemode) ftp_pasv($this->conn, true);

    if($this->implicit_tls):
      $fp = fopen($filename, 'r');
      if($fp === false):
        $this->error = "Could not open $filename";
        return false;
      endif;

      curl_setopt($this->conn, CURLOPT_URL, "ftps://$this->host/$remote_name");
      curl_setopt($this->conn, CURLOPT_PORT, $this->port);
      curl_setopt($this->conn, CURLOPT_UPLOAD, 1);
      curl_setopt($this->conn, CURLOPT_INFILE, $fp);

      curl_exec($this->conn);
      $error_no = curl_errno($this->conn);
      $this->error = curl_error($this->conn);
      #\booosta\debug("error_no: $error_no");

      fclose($fp);
      return $error_no === 0;
    else:
      return ftp_put($this->conn, $remote_name, $filename, FTP_BINARY);
    endif;
  }

  public function get_timestamp($filename)
  {
    return ftp_mdtm($this->conn, $filename);
  }
}
