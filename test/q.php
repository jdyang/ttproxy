<?php
/* Connect to a table database */
$tt = new TokyoTyrantTable("localhost",$argv[1]);
print_r($tt->get("huzhan1"));

?>
