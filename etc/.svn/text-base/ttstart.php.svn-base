<?php
for($i=0;$i<48;$i++)
{
  echo "./ttserver -host 127.0.0.1 -port ".(1900+$i)." -thnum 8 -dmn -pid /opt/ttserver/ttserver".$i.".pid -log /opt/ttserver/ttserver".$i.".log -le -ulog /opt/ttserver/ -ulim 128m /opt/ttserver/db".$i.".tct\n";
}
?>