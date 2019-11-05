<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/11/4
 * Time: 13:45
 */

namespace app\services;


use app\models\Tag;
use app\models\User;
use app\models\UserTag;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Logger\Adapter\File;

class TagService extends BaseService
{
    public function addUserTags($tagNames,$userId){
        foreach ($tagNames as $tagName){
            if($this->exist($tagName===false)){
                $tag=new Tag();
                $tag->setName($tagName);
                $tag->setIcon(mt_rand(0,5));
                if($tag->save()===false){
                    $this->recordErr($tag);
                    return false;
                }

                $userTag=new UserTag();
                $userTag->setUserId($userId);
                $userTag->setTagId($tag->getId());
            }
        }

        return true;
    }

    public function exist($tagName){
        $count=Tag::count(['name=?0','bind'=>[$tagName]]);
        if($count>0){
            return true;
        }else{
            return false;
        }
    }
}