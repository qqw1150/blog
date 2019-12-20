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
    public static function num_encrypt($n){
        $s = base_convert($n*8, 10, 36); //将10进制转为36进制
        return base64_encode($s);
    }

    public static function num_decrypt($n){
        $s = base_convert(base64_decode($n),36,10)/8; //将36进制转为10进制
        return $s;
    }

}