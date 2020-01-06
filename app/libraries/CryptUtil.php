<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/12/19
 * Time: 11:48
 */

namespace app\libraries;


class CryptUtil
{
    public static function num_encrypt($n)
    {
        $s = base_convert($n * 8, 10, 36); //将10进制转为36进制
        return base64_encode($s);
    }

    public static function num_decrypt($n)
    {
        $s = base_convert(base64_decode($n), 36, 10) / 8; //将36进制转为10进制
        return $s;
    }

    /**
     * @param \Redis $redis
     * @param string $prefix
     * @param string $ip
     * @param int $tlimit
     * @param int $climit
     * @return bool
     */
    public static function checkIpRep($redis, $prefix, $ip, $tlimit, $climit)
    {
        $keyTime = $prefix . '_' . $ip . '_time';
        $keyCount = $prefix . '_' . $ip . '_count';
        $count = intval($redis->get($keyCount));
        $time = intval($redis->get($keyTime));
        $now = time();
        if ($now - $time >= $tlimit) {
            $redis->set($keyTime, $now);
            $redis->set($keyCount, 1);
            return true;
        } else {
            if ($count >= $climit) {
                return false;
            } else {
                $redis->incr($keyCount);
                return true;
            }
        }
    }

    /**
     * @return string
     */
    public static function getIp()
    {
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $res = preg_match('/[\d\.]{7,15}/', $ip, $matches) ? $matches [0] : '';
        return $res;
    }

}