<?php
class Cache
{
	/**
	 * redis
	 *
	 * @var Redis 
	 */
	private $redis;

	/**
	 * wait next get
	 * milliseconds
	 * 
	 * @var integer
	 */
	private $rate = 200;

	/**
	 * max loops
	 *
	 * @var integer
	 */
	private $maxLoops = 5;

	public function __construct($redis)
	{
		$this->redis = $redis;
	}

	public function setRate(int $rate)
	{
		$this->rate = $rate;
	}

	/**
	 * 第一版,初步实现
	 * 通过闭包的方式实现
	 *
	 * @param string $key
	 * @param Closure $fn
	 * @return mixed
	 */
	public function getCache($key, Closure $fn)
	{
		for ($i = 0; $i < $this->maxLoops; $i ++) {
			// get redis
			$value = $this->redis->get($key);
			if (!empty($value)) {
				return $value;
			}
			// global lock
			$lock = new Lock();
			if (!$lock->lock()) {
				// wait next get
				usleep($this->rate * 1000);
				continue;
			}
			$value = $fn();	
			// 写入Redis
			$this->redis->set($key, $value);
			// global unlock	
			$lock->unlock();
	
			return $value;
	
		}

		return 0;
	}
}

// DB Model
class User
{
	public function getName()
	{
		return 'jerry';
	}

	public function getUserById($userId)
	{
		$key = 'cache:user_id' . $userId;
		$redis = new Redis();
		$redis->connect('192.168.4.61', 6379);
		$cache = new Cache($redis);
		$cache->setRate(300);
		return $cache->getCache($key, function () use ($userId) {
			// DB
			return (new User())->getName();	
		});
	}
}

// test
$user = new User();
echo $user->getUserById(1);





