<?php

function md5bin( $target ) 
{
    $md5 = md5( $target );
    $ret = '';

    for ( $i = 0; $i < 32; $i += 2 ) {
        $ret .= chr( hexdec( $md5{ $i + 1 } ) + hexdec( $md5{ $i } ) * 16 );
    }

    return $ret;
}

function shl32 ($x, $bits)
{    
    if($bits <= 0)
    {          
      return $x;      
    }      
    if($bits >= 32)
    {          
      return 0;      
    }      
    $bin = decbin($x);      
    $l = strlen($bin);      
    if($l > 32)
    {          
      $bin = substr($bin, $l - 32, 32);      
    }
    elseif($l < 32)
    {          
      $bin = str_pad($bin, 32, '0', STR_PAD_LEFT);      
    }      
    return bindec(str_pad(substr($bin, $bits), 32, '0', STR_PAD_RIGHT));  
}

function readcfg($file)
{
        $xmlDoc = new DOMDocument();
        $xmlDoc->load($file);
        $x = $xmlDoc->documentElement;
        $cfg = array();
        $table = "";
        $name = array();
        foreach($x->childNodes AS $item )
        {
           if( "ttc" == $item->nodeName )
           {
                $name = array();
                foreach($item->childNodes AS $y)
                {
                  if( "table" == $y->nodeName ) $table = $y->nodeValue;
                  if( "tt" == $y->nodeName )
                  {
                     foreach( $y->childNodes AS $z ) 
                     {
                        if("name" == $z->nodeName)
                        {
                           array_push($name,$z->nodeValue);
                           print "[NAME]".$z->nodeValue."\n";
                        }
                     }
               	  }
	 
                }
                $cfg[$table] = $name;
                $table = "";
           }
        }
        return $cfg;
}
?>
