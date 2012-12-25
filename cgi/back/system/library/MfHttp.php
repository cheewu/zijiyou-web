<?php


class MfHttp 
{
  public static function redirect($dest_url, $http_code = 301)
  {
    $http_code = intval($http_code);
    if (!in_array($http_code, array(301, 302))) {
      trigger_error("Redirect code:$http_code error");
    }
    header("Location: $dest_url", null, $http_code); exit;
  }  
}