<?php
 $tt = new TokyoTyrantTable("127.0.0.1",$argv[1]);
 $query = $tt->getQuery();
 $ret = $query->search();
 foreach($ret as $key => $value )
 {
   print_r("[".$key."] ");
/*   foreach($value as $k => $v )
   {
     print_r(" ".$v);
   }
*/
   print_r("\n");
 }
?>
