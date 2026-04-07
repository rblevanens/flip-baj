<?php
namespace flip_baj\main\ajax;


use function flip_baj\main\AfficherRemb;

include ('../utils.php');

if (isset($_POST["id"])) {
    if (! isset($_SERVER['HTTP_X_REQUESTED_WITH']) and strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        die();
    }
    $id = $_POST["id"];
    $ret = AfficherRemb($id);
    // error_log("ret:".$ret,0);
    echo json_encode(array(
        "message1" => $ret,
        "message2" => '1'
    ));
}

?>