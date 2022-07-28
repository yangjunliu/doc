<?php
Class Bloom
{
	/* $var int $maps */
	private $maps = 14;	

	/* $var int $bits */
	
	private $bits = 20000;

	/* $var string $setScript */
	private $setScript = '
for _, offset in ipairs(ARGV) do
	redis.call("setbit", KEYS[1], offset, 1)
end
return true
';
	
	/* $var string $testScript */
	private $testScript = '
for _, offset in ipairs(ARGV) do
	if tonumber(redis.call("getbit", KEYS[1], offset)) == 0 then
		return false
	end
end
return true
';

	/* $var string $key*/
	private $key;	
	
	/* $var mixed $redis*/
	private $redis;

	/**
	 * construct
	 * 
	 * @param mixed $redis
	 * @param string $key
	 * @return void
	 */
	public function __construct($redis, $key)
	{
		$this->redis = $redis;	
		$this->key = $key;
	}
	
	/**
	 * 格式化参数
	 *
	 * @param array $offsets
	 * @return array
	 */
	private function buildOffsetArgs(array $offsets): array
	{
		array_unshift($offsets, $this->key);
		return $offsets;
	}

	/**
	 * 设置过滤器的值
	 *
	 * @param array $offsets
	 * @return boolean
	 */
	private function set(array $offsets): bool
	{
		return $this->redis->eval($this->setScript, $this->buildOffsetArgs($offsets), 1);
	}

	/**
	 * 检查值是否存在
	 *
	 * @param array $offsets
	 * @return boolean
	 */
	private function check(array $offsets): bool
	{
		return $this->redis->eval($this->testScript, $this->buildOffsetArgs($offsets), 1);
	}

	/**
	 * 计算bloom过滤器对应的bit位
	 * 暂时使用crc32 获取摘要
	 *
	 * @param string $data
	 * @return array
	 */
	private function getLocations(string $data): array
	{
		$locations = [];
		for ($i = 0; $i < $this->maps; $i ++) {
			$data .= (string) $i;
			$hashValue = crc32($data);
			$locations[$i] = $hashValue % $this->bits;
		}

		return $locations;
	}

	/**
	 * 增加过滤器数据
	 *
	 * @param string $data
	 * @return void
	 */
	public function add(string $data)
	{
		$locations = $this->getLocations($data);
		
		return $this->set($locations);
	}

	/**
	 * 判断值是否存在
	 *
	 * @param string $data
	 * @return boolean
	 */
	public function exists(string $data): bool
	{
		$locations = $this->getLocations($data);
		
		return $this->check($locations);
	}
	
	/**
	 * 设置时间 
	 *
	 * @param int $second
	 * @return boolean
	 */

	public function expire(int $second): bool
	{
		return $this->redis->expire($this->key, $second);
	}
}

// 测试
$redis = new Redis();
$redis->connect('192.168.4.61', 6379);

$bloomKey = 'bloom:test';
$data = 'helloworld2';
$bloom = new Bloom($redis, $bloomKey);
// var_dump($bloom->add($data));
// var_dump($bloom->expire(600));
var_dump($bloom->exists($data));













