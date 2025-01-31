<?php

// $useRedis = getenv('REDIS_ENABLED') ?? false;
$useRedis = false;
$redisHost = (!empty(getenv("REDIS_HOST")) ? getenv("REDIS_HOST") : "127.0.0.1");
$redisPort = (!empty(getenv("REDIS_PORT")) ? getenv("REDIS_PORT") : "6379");

if ($useRedis) {
  $redis = new Redis();
  $redis->connect($redisHost, $redisPort);
}

function WriteCache($name, $data)
{
  global $useRedis, $redis;
  //DeleteCache($name);
  if($name == 0) return;
  $serData = serialize($data);

  if($useRedis)
  {
    $redis->set($name, $serData);
  }
  else {
    $id = shmop_open($name, "c", 0644, 128);
    if($id == false) {
      exit;
     } else {
        $rv = shmop_write($id, $serData, 0);
    }
  }
}

function ReadCache($name)
{
  global $useRedis, $redis;
  if($name == 0) return "";

  $data = "";
  if($useRedis)
  {
    $data = RedisReadCache($name);
    if($data == "" && is_numeric($name))
    {
      $data = ShmopReadCache($name);
    }
  }
  else {
    $data = ShmopReadCache($name);
    if($data == "")
    {
      $data = RedisReadCache($name);
    }
  }

  return unserialize($data);
}

function ShmopReadCache($name)
{
  @$id = shmop_open($name, "a", 0, 0);
  if(empty($id) || $id == false)
  {
    return "";
  }
  $data = shmop_read($id, 0, shmop_size($id));
  $data = preg_replace_callback( '!s:(\d+):"(.*?)";!', function($match) {
    return ($match[1] == strlen($match[2])) ? $match[0] : 's:' . strlen($match[2]) . ':"' . $match[2] . '";';
    }, $data);
  return $data;
}

function RedisReadCache($name)
{
  global $redis;
  if(!isset($redis)) $redis = RedisConnect();
  return $data = $redis->get($name);
}

function RedisConnect()
{
  global $redis, $redisHost, $redisPort;
  $redis = new Redis();
  $redis->connect($redisHost, $redisPort);
  return $redis;
}

function DeleteCache($name)
{
  global $useRedis, $redis;
  if($useRedis)
  {
    $redis->del($name);
    $redis->del($name . "GS");
  }
  //Always try to delete shmop
  $id=shmop_open($name, "w", 0644, 128);
  if($id)
  {
    shmop_delete($id);
    shmop_close($id); //shmop_close is deprecated
  }
}

function SHMOPDelimiter()
{
  return "!";
}

function SetCachePiece($name, $piece, $value)
{
  $piece -= 1;
  $cacheVal = ReadCache($name);
  if($cacheVal == "") return;
  $cacheArray = explode("!", $cacheVal);
  $cacheArray[$piece] = $value;
  WriteCache($name, implode("!", $cacheArray));
}

function GetCachePiece($name, $piece)
{
  $piece -= 1;
  $cacheVal = ReadCache($name);
  $cacheArray = explode("!", $cacheVal);
  if($piece >= count($cacheArray)) return "";
  return $cacheArray[$piece];
}

function GamestateUpdated($gameName)
{
  global $currentPlayer;
  $cache = ReadCache($gameName);
  $cacheArr = explode(SHMOPDelimiter(), $cache);
  $cacheArr[0]++;
  $currentTime = round(microtime(true) * 1000);
  $cacheArr[5] = $currentTime;
  $cacheArr[8] = $currentPlayer;
  WriteCache($gameName, implode(SHMOPDelimiter(), $cacheArr));
}

?>
