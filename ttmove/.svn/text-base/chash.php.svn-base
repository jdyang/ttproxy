<?php
require_once("util.php");
class ConsistHash
{
  public $servername;
  public $circle;
  public $replicas;
    
  function __construct()
  {
    $this->replicas = 10;
  }
  
  function __destruct()
  {
    
  }
  function sortcircle()
  {
    $tmparray;
    while( count($this->circle) >=1 )
    {
      $tmpvalue = 0;
      foreach($this->circle as $k=>$v) 
      {
        if( 0 == $tmpvalue )
        {
          $tmpvalue=$k;
        }
        else
        {
          if( $tmpvalue > $k )
          {
            $tmpvalue = $k; 
          }  
        }
      }
      $tmparray[$tmpvalue] = $this->circle[$tmpvalue];
      $tmp = array();
      foreach($this->circle as $k=>$v)
      {
        if( $k != $tmpvalue )
        {
          $tmp[$k]=$v;
        }
      }
      $this->circle = $tmp;
      unset($tmp);
    }
    $this->circle=$tmparray;
  }
  function add_node($name)
  {
    if( count($this->servername) >= 1 )
    {
    foreach($this->servername as $k=>$v )
    {
      if( $name == $v )
      {
        echo $name." has exist.";
      }
    }
    } 
    for( $i=0; $i<$this->replicas; $i++)
    {
      $sn = $name."-".$i;
      $digest = md5bin($sn);
      $point = shl32(ord(substr($digest,3,1)),24) | shl32(ord(substr($digest,2,1)),16) | shl32(ord(substr($digest,2,1)),16) | shl32(ord(substr($digest,1,1)),8) | ord(substr($digest,0,1)) ;
      $point=sprintf('%u',$point);
      $this->circle[$point] = $name;
//      echo $point."\n"; 
    }
    if( count($this->servername) >= 1 )
    {
      array_push($this->servername,$name);
      $this->sortcircle();
    }
    else
    {
      $this->servername = array($name);
      $this->sortcircle();
    }
  }
  function del_node($name)
  {
  
  }
  function get_node($key)
  {
    $digest = md5bin($key);
      $point = shl32(ord(substr($digest,3,1)),24) | shl32(ord(substr($digest,2,1)),16) | shl32(ord(substr($digest,2,1)),16) | shl32(ord(substr($digest,1,1)),8) | ord(substr($digest,0,1)) ;
      $point=sprintf('%u',$point);
    if( count($this->circle) >=1 )
    {
      foreach( $this->circle as $k=>$v )
      {
        if( $point <= $k ) 
        {
          return $v;
        }
      }
      foreach( $this->circle as $k=>$v )
      {
        return $v;
      }
    }
  }
  function print_node()
  {
    foreach($this->circle as $k=>$v)
    {
      echo $k."  ".$v."\n";
    }
  }
}
?>
