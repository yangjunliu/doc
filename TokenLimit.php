<?php

class TokenLimit
{
   private $script = '
-- 生成token的速率 
local rate = tonumber(ARGV[1])
-- 令牌桶最大容量
local capacity = tonumber(ARGV[2])
-- 当前时间
local now = tonumber(ARGV[3])
-- 获取token的数量
local requested = tonumber(ARGV[4])
-- 填满整个桶需要多久时间
local fill_time = capacity / rate
-- 时间取整
local ttl = math.floor(fill_time*2)

-- 获取目前桶中剩余令牌的数量
-- 如果第一次进入，则设置桶内令牌的数量为最大值
local last_tokens = tonumber(redis.call("get", KEYS[1]))
if last_tokens == nil then
    last_tokens = capacity
end

-- 上次更新 桶的时间
local last_refreshed = tonumber(redis.call("get", KEYS[2]))
if last_refreshed == nil then
    last_refreshed = 0
end

-- 上次从桶中获取令牌的时间距离现在的时间 
local delta = math.max(0, now - last_refreshed)
-- 上次从桶中获取令牌的时间距离现在的时间内总共生成了令牌的数量
-- 如果超过了最大数量则丢弃多余的令牌
local filled_tokens = math.min(capacity, last_tokens + (rate * delta))
-- 本次请求令牌数量是否足够
local allowed = filled_tokens >= requested
-- 令牌桶剩余数量
local new_tokens = filled_tokens
if allowed then
    new_tokens = filled_tokens - requested
end

-- 更新桶中剩余令牌的数量
redis.call("setex", KEYS[1], ttl, new_tokens)
-- 更新获取令牌的时间
redis.call("setex", KEYS[2], ttl, now)
return allowed
';

    /**
     * @var string $key
     */
    private $key;

    /**
     * @var string $timestampKey
     */
    private $timestampKey;

    /**
     * @var int $rate
     */
    private $rate;

    /**
     * @var int $capacity
     */
    private $capacity;

    /**
     * @var Redis $redis
     */
    private $redis;

    /**
     * @var string $tokenFormat
     */
    private $tokenFormat = '%s.token';

    /**
     * @var string $timestampFormat
     */
    private $timestampFormat = '%s.ts';

    /**
     * TokenLimit constructor.
     * @param string $key
     * @param int $rate
     * @param int $burst
     * @param Redis $redis
     */
    public function __construct(string $key, int $rate, int $burst, Redis $redis)
    {
        $this->key = sprintf($this->tokenFormat, $key);
        $this->timestampKey = sprintf($this->timestampFormat, $key);
        $this->rate = $rate;
        $this->capacity = $burst;
        $this->redis = $redis;
    }

    /**
     * build argv
     * @param int $now_time
     * @param int $n
     * @return array
     */
    private function buildArgv(int $now_time, int $n): array
    {
        return array($this->key, $this->timestampKey, $this->rate, $this->capacity, $now_time, $n);
    }

    /**
     * execute script
     * @param int $now_time
     * @param int $n
     * @return bool
     */
    private function reserve(int $now_time, int $n): bool
    {
        return $this->redis->eval($this->script, $this->buildArgv($now_time, $n), 2); 
    }

    /**
     * reserve multi token
     * @param int $n
     * @return bool
     */
    public function allowN(int $n): bool
    {
        $now_time = time();
        return $this->reserve($now_time, $n);
    }

    /**
     * reserve one token
     * @return bool
     */
    public function allow(): bool
    {
        return $this->allowN(1);
    }
}

$redis = new Redis();
$redis->connect("192.168.4.61", 6379);

$tokenKey = 'token:test';
$tokenLimit = new TokenLimit($tokenKey, 1, 10, $redis);
$allowed = $tokenLimit->allow();
$allowed = $tokenLimit->allow();
$allowed = $tokenLimit->allow();
$allowed = $tokenLimit->allow();
$allowed = $tokenLimit->allow();
$allowed = $tokenLimit->allow();
var_dump($allowed);




