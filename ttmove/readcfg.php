<?php

function readcfg($file)
{
        $xmlDoc = new DOMDocument();
        $xmlDoc->load($file);
        $x = $xmlDoc->documentElement;
        $cfg = array();
        $table = array();
        $name = array();
        $tablename = "";
        $hashname = "";
        foreach($x->childNodes AS $item )
        {
           if( "ttc" == $item->nodeName )
           {
                foreach($item->childNodes AS $y)
                {
                  if( "table" == $y->nodeName ) $tablename = $y->nodeValue;
                  if( "tt" == $y->nodeName )
                  {
                     foreach( $y->childNodes AS $z ) 
                     {
                        if("ip" == $z->nodeName) $name["ip"] = $z->nodeValue;
                        else if("port" == $z->nodeName) $name["port"] = $z->nodeValue;
                        else if("name" == $z->nodeName) $hashname = $z->nodeValue;
                     }
                     $table[$hashname] = $name;
                     unset($name);
                     $hashname = "";
		  						}
                }
                $cfg[$tablename] = $table;
                unset($table);
                $tablename = "";
           }
        }
        return $cfg;
}
$cfg = readcfg($argv[1]);
foreach($cfg as $key => $value )
{
	 print "[--]".$key."\n";
	 foreach( $value as $k => $v )
	 {
	 	  print " (--) ".$k."\n";
	 	  foreach( $v as $kk => $vv )
	 	  {
	 	  	  print "{--}".$kk." ==== ".$vv."\n";
	 	  }
	 }
}
?>
