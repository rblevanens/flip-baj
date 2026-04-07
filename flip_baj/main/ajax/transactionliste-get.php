<?php
namespace flip_baj\main\ajax;


use function flip_baj\main\AfficherTrans;

include ('../utils.php');

if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die();
}
$type=$_POST["type"];
$ret = AfficherTrans($type);
// error_log("ret:".$ret,0);
echo json_encode(array(
    "message1" => $ret,
    "message2" => '1'
));

?>