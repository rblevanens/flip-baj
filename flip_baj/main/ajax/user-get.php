<?php
namespace flip_baj\main\ajax;


use function flip_baj\main\getUser;

include ('../utils.php');

if(isset($_POST["id"]))
{
  if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
      die();
  }
  if(isset($_POST["id"])){
    $str = preg_replace('/\x00|<[^>]*>?/', '', ($_POST["id"]));
    $id= str_replace(["'", '"'], ['&#39;', '&#34;'], $str);
    //$id = filter_var($_POST["id"], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW|FILTER_FLAG_STRIP_HIGH);
  }
  echo json_encode(getUser($id));

}


?>
