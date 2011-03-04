<?php
$tt=new TokyoTyrantTable('localhost',$argv[1]);
$tt->vanish();

$tt->setIndex("column1",TokyoTyrant::RDBIT_LEXICAL);

$tt->setIndex("column2",TokyoTyrant::RDBIT_LEXICAL);
$tt->setIndex("column3",TokyoTyrant::RDBIT_LEXICAL);
$tt->setIndex("column4",TokyoTyrant::RDBIT_LEXICAL);
$tt->setIndex("column5",TokyoTyrant::RDBIT_LEXICAL);

for($t=0;$t<10;$t++){
  echo "-----------------------\n";
  echo $t*100000;
  echo "\n-----------------------\n";
  $oldtime=microtime(true);
  for ($i = 0 ; $i < 100000 ; $i++) {
    $tt->put("test/data/0$t/$i",array("column1"=>"value1$t$i","column2"=>"中文Value2$t$i","column3"=>"value3$t$i","column4"=>"value4$t$i","column5"=>"value5$t$i"));
  }
  $nowtime=microtime(true);
  echo $nowtime-$oldtime;
  echo "\n";

  for ($i = 0 ; $i < 10000 ; $i++) {
    $qry=$tt->getQuery();
    $qry->addCond("column2",TokyoTyrant::RDBQC_STREQ,"中文Value2$t$i");
    $resault=$qry->search();
  }
  $nowtime2=microtime(true);
  echo $nowtime2-$nowtime;
  echo "\n";

}

?>
