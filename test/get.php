<?php
$tt = new TokyoTyrantTable("127.0.0.1",$argv[1]);
$v = $tt->get($argv[2]);
print_r($v);

?>
