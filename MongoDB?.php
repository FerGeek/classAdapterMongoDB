<?php
class MongoDB_{
  protected $obj, $arg = [];
  private $user, $pwd;
  public $database, $port, $svr, $uri;
  public $collection = '';
  public $objectReturn = false;
  function __construct(){
    $this -> arg = func_get_args();
    $uri = $this -> buildURI(...$this -> arg);
    $this -> obj = new MongoDB\Driver\Manager($uri); //"mongodb://localhost:27017"
    // var_dump($uri);
  }
  protected function buildURI($args){
    $uri = '';
    $arg = is_array($args) ? $args : func_get_args();//'svr','port','user','pwd','database'
    switch (count($arg)) {
      case 0: $uri = 'mongodb://localhost:27017/test';break;
      case 1:
       if(strpos($arg[0],'mongodb://') !== false){
         $uri = $arg[0];
         $tmp = preg_split('/[\:,\@,\/]/',$arg[0]);
         // print_r($tmp);
         $this -> setItems($tmp[3],$tmp[4],$tmp[5],$tmp[6],$tmp[7]);
       }
       else{
         $uri = 'mongodb://localhost:27017/test';
         $this -> setItems('','','localhost','27017','test');
       }
       break; //Complete URI
      case 2: $uri = 'mongodb://'.$arg[0].':'.$arg[1].'/admin';break;
      case 3: $uri = 'mongodb://'.$arg[1].':'.$arg[2].'@'.$arg[0].':'.$this -> port.'/admin';
       $this -> setItems($arg[1],$arg[2],$arg[0]);
       break;
      case 4: $uri = 'mongodb://'.$arg[2].':'.$arg[3].'@'.$arg[0].':'.$arg[1].'/admin';
       $this -> setItems($arg[2],$arg[3],$arg[0],$arg[1]);
       break;
      case 5: $uri = 'mongodb://'.$arg[2].':'.$arg[3].'@'.$arg[0].':'.$arg[1].'/'.$arg[4];
       $this -> setItems($arg[2],$arg[3],$arg[0],$arg[1],$arg[4]);
       break;
      case 6: $uri = 'mongodb://'.$arg[2].':'.$arg[3].'@'.$arg[0].':'.$arg[1].'/'.$arg[4].'/?'.$this -> parseOptions($arg[5]);break;
       $this -> setItems($arg[2],$arg[3],$arg[0],$arg[1],$arg[4]);
      default: $uri = '';break;
    }
      $this -> uri = $uri;
    // $this -> port = isset($arg[1]) && strpos('mongodb://',$arg[0]) !== false ? $arg[1] : '27017';
    return $uri;
  }
  private function setItems(...$arg){
    // $ord = ['user','pwd','svr','port','database'];
    $this -> user = isset($arg[0]) ? $arg[0] :  null;
    $this -> pwd = isset($arg[1]) ? $arg[1] :  null;
    $this -> svr = isset($arg[2]) ? $arg[2] :  'localhost'; //Default server
    $this -> port = isset($arg[3]) ? $arg[3] :  '27017'; // Default port
    $this -> database = isset($arg[4]) ? $arg[4] :  'test'; // Default database
  }
  private function parseOptions($array){
    $ret = is_array($array) ? '' : '&';
    if($ret === '') foreach ($array as $key => $value){$ret .=$key.'='.$value.'&';};
    return $ret == '&' ? $array : substr($ret,0,-1);
  }
  private function toArrayRec($obj){
    if(!is_string($obj)){
      $ret = [];
      foreach ($obj as $key => $value) {
        $ret[$key] = !is_string($value) ? $this -> toArrayRec($value) : $value ;
      }
      return $ret;
    }
    else{return $obj;}
  }
  public function runCommand($cmd){
    $db = $this -> obj;
    $command = new MongoDB\Driver\Command($cmd);
    $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
    try{
      // $result = $db->executeCommand($this -> database.'.'.$this -> collection, $bulk, $writeConcern);
      $result = $db->executeCommand($this -> database, $command);
      return $result -> toArray();
    }
    catch(MongoDB\Driver\Exception\Exception $i){
      echo '<strong>'.$i -> getMessage().'</strong>';
      // var_dump($result);
      return false;
    }
  }
  public function use($db,$options = []){
    $db = is_array($options) && $options != [] ? $db.'/'.$this -> parseOptions($options) : $db;
    $this -> setItems($this -> user,$this -> pwd,$this -> svr, $this -> port, $db);
    $uri = $this -> buildURI($this -> svr, $this -> port,$this -> user,$this -> pwd, $db);
    $ret = $this -> obj = new MongoDB\Driver\Manager($uri) ? true : false;
    return $ret;
  }
  public function auth($obj){
    if(isset($obj['user']) && isset($obj['pwd'])){
      $ret = false;
      $uri = $this -> buildURI($this -> svr, $this -> port,$obj['user'],$obj['pwd'], $this -> database);
      try{
        if($this -> obj = new MongoDB\Driver\Manager($uri)) $ret = true;
        $re = $db -> executeCommand($this -> database,$cmd);
      } catch(MongoDB\Driver\Exception\Exception $i){
        echo '<strong>'.$i -> getMessage().'</strong>';
      }
      return $ret;
    }
    else{return null;}
  }
  public function selectCollection($cll){
    $this -> collection = $cll;
  }
  public function createCollection($name,$options = []){
    $cmd = ['create'=>$name];
    $cmd = array_merge($cmd,$options);
    try{
      $cmd = new MongoDB\Driver\Command($cmd);
      $result = $this -> obj -> executeCommand($this -> database, $cmd);
      return true;
    } catch(MongoDB\Driver\Exception $i){
      echo '<strong>'.$i -> getMessage().'</strong>';
      return false;
    }

  }
  public function find($obj,$option = []){
    $db = $this -> obj;
    $cmd = new MongoDB\Driver\Query($obj,$option);
    $cursor = $db -> executeQuery($this-> database.'.'.$this -> collection,$cmd);
    return $this -> objectReturn ? $cursor -> toArray() : $this -> toArrayRec($cursor -> toArray());
  }
  public function insert(...$obj){
    $db = $this -> obj;
    $bulk = new MongoDB\Driver\BulkWrite();
    foreach($obj as $doc){
      $bulk -> insert($doc);
    }
    $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
    $result = $db->executeBulkWrite($this -> database.'.'.$this -> collection, $bulk, $writeConcern);
    // print_r($result);
  }
  public function count($obj){}
  public function update($filter,$update){
    $db = $this -> obj;
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk -> update($filter,$update);
    $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
    try{
      $result = $db->executeBulkWrite($this -> database.'.'.$this -> collection, $bulk, $writeConcern);
      return true;
    }
    catch(MongoDB\Driver\Exception\Exception $i){
      echo '<strong>'.$i -> getMessage().'</strong>';
      return false;
    }
  }
  public function manyUpdates(...$obj){
    list($c,$i) = 0;
    foreach ($obj as $x) {
      if(isset($x[0]) && is_array($x[0]) && isset($x[1]) && is_array($x[1])){
        $this -> update($x[0],$x[1]); $c++;
      } else{echo '<strong>Error in index '.$i.'</strong>';} $i++;
    }
    return $c === count($obj) ? true : ( $c === 0 ? false : 0) ;
  }
  public function delete($filter,$options = []){
    $db = $this -> obj;
    $bulk = new MongoDB\Driver\BulkWrite();
    $bulk -> delete($filter,$options);
    $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
    try{
      $result = $db->executeBulkWrite($this -> database.'.'.$this -> collection, $bulk, $writeConcern);
      return true;
    }
    catch(MongoDB\Driver\Exception\Exception $i){
      echo '<strong>'.$i -> getMessage().'</strong>';
      return false;
    }
  }
}
?>
