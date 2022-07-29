<?php

class Lock
{
	/**
	 * redis 
	 * 
	 * @var Redis 
	 */
	private $redis;

	/**
	 * id lenght
	 *
	 * @var integer
	 */
	private $randomLen = 16;
 
	/**
	 * expire tolarance
	 *
	 * @var integer
	 */	
	private $tolerance = 500; // milliseconds

	/**
	 * seconds to millis
	 *
	 * @var integer
	 */
	private $millisPerSeconds = 1000;

	/**
	 * set redis lua script 
	 *
	 * @var string
	 */
	private $lockCommand = 'if redis.call("GET", KEYS[1]) == ARGV[1] then
	redis.call("SET", KEYS[1], ARGV[1], "PX", ARGV[2])
	return "OK"
else
	return redis.call("SET", KEYS[1], ARGV[1], "NX", "PX", ARGV[2])
end
';

	/**
	 * get redis lua script
	 *
	 * @var string
	 */
	private $delCommand = 'if redis.call("GET", KEYS[1]) == ARGV[1] then
    return redis.call("DEL", KEYS[1])
else
    return 0
end
';

	/**
	 * lock key
	 *
	 * @var string 
	 */
	private $key;

	/**
	 * lock id
	 *
	 * @var [type]
	 */
	private $id;

	/**
	 * lock expire 
	 *
	 * @var integer
	 */
	private $seconds = 0;

	public function __construct(Redis $redis, string $key)
	{
		$this->redis = $redis;
		$this->id = $this->randomUUID();
		$this->key = $key;
	}

	/**
	 * generate uuid 
	 * provisional way
	 *
	 * @return string
	 */
	public function randomUUID(): string
	{
		return substr(uniqid('', true), 5, $this->randomLen);
	}

	/**
	 * expire
	 *
	 * @return integer
	 */
	public function getPexpire(): int
	{
		return $this->seconds * $this->millisPerSeconds + $this->tolerance;
	}

	/**
	 * acquire lock 
	 *
	 * @return boolean
	 */
	public function acquire(): bool
	{
		$resp = $this->redis->eval($this->lockCommand, [$this->key, $this->id, $this->getPexpire()], 1);
		if ($resp == 'OK') {
			return true;
		}

		return false;
	}	

	/**
	 * release lock 
	 *
	 * @return boolean
	 */
	public function release(): bool
	{
		$resp = $this->redis->eval($this->delCommand, [$this->key, $this->id], 1);
	
		return $resp == 1;
	}

	/**
	 * set expire
	 *
	 * @param integer $expire
	 * @return void
	 */
	public function setExpire(int $expire)
	{
		$this->seconds = $expire;
	}
}

// test
$redis = new Redis();
$redis->connect('192.168.4.61', 6379);
$key = 'lock:testtest';
$lock = new Lock($redis, $key);
$lock->setExpire(60);
var_dump($lock->acquire());
var_dump($lock->acquire());
// var_dump($lock->release());
$lock2 = new Lock($redis, $key);
$lock2->setExpire(30);
var_dump($lock2->acquire());

