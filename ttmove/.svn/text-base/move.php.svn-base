<?php
        if( $argc < 7 )
        {
          echo "please input ".$argv[0]." oldproxyip oldproxyport newproxyip newproxyport oldcfg newcfg \n";
          exit;
        }
	$oldip = $argv[1];
	$oldport = $argv[2];
	$newip = $argv[3];
	$newport = $argv[4];
        $oldcnf = $argv[5];
        $newcnf = $argv[6]; 
	require_once("chash.php");
	
	$cfgold = readcfg($oldcnf);
        $cn=count($cfgold);
        echo "cfgold num:".$cn."\n";
	$oldcircle;
	foreach($cfgold as $k=>$v)
	{
		$ch = new ConsistHash;
		foreach($v as $key=>$value)
		{
			$ch->add_node($value);
                        print "[add node ]".$value."\n";
		}
		$oldcircle[$k]=$ch;
	}

	$cfgnew = readcfg($newcnf);
        $cn=count($cfgnew);
        echo "cfgnew num:".$cn."\n";
	$newcircle;
	foreach($cfgnew as $k=>$v)
	{
		$ch = new ConsistHash;
		foreach($v as $key=>$value)
		{
			$ch->add_node($value);
                        print "[add node]".$value."\n";
		}
		$newcircle[$k]=$ch;
	}

 	$ttold = new TokyoTyrantTable($oldip,$oldport);
 	$ttnew = new TokyoTyrantTable($newip,$newport);
 	foreach($oldcircle as $k=>$v)
 	{
                echo "----------------------------table:".$k."\n";
 		$newch;
                $bin = false;
 		foreach($newcircle as $key=>$value)
 		{
 			if( $k == $key )
 			{
 				$newch = $value;
                                $bin=true;
 				break;
 			}
 		}
                if( false === $bin ) continue;
 		$skip=0;
 		$max=5;
                $outkey = array();
 		while(true)
 		{
 			$query = $ttold->getQuery();
 			$query->addCond("tablename",-1,$k);
			$query->setLimit($max,$skip);
 			$ret = $query->search();
 			$cnt = count($ret);
 			foreach($ret as $key=>$value)
 			{
 				$oldname = $v->get_node($key);
 				$newname = $newch->get_node($key);
 				//echo $key." [".$oldname."] [".$newname."]\n";
                                if( $oldname != $newname )
                                {
                                  $nvalue=$ttnew->get($key);
                                  if( isset($nvalue["UPDATE_TIME"]) && isset($nvalue["UPDATE_TIME"]) && $value["UPDATE_TIME"] < $nvalue["UPDATE_TIME"])
                                  {
                                  }
                                  else
                                  {
                                    $ttnew->put($key,$value);
                                  }
                                  array_push($outkey,$key);
                                }
 			}
                        echo " search num:".$cnt."\n";
                        if( $cnt < $max || 0 == $cnt )
                        {
                                break;
                        }
                        else
                        {
                                $skip = $skip + $max;
                                //echo "skip: ".$skip."\n";
                        }
 		}
                foreach($outkey as $k=>$v)
                {
                  $ttold->out($v);
                }
                echo "table:".$k." move done ! move data ".count($outkey)."\n";
 	}
	


?>
