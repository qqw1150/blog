<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/28
 * Time: 14:56
 */

namespace app\services;

use app\exceptions\NotLoginException;
use app\libraries\CryptUtil;
use app\libraries\Page;
use app\models\Article;
use app\models\domains\WriteArticleForm;
use Phalcon\Db\Adapter\Pdo\Mysql;

class ArticleService extends BaseService
{
    /**
     * 保存文章（创建或修改）
     * @param WriteArticleForm $articleForm
     * @return bool
     * @throws NotLoginException
     * @throws \Phalcon\Crypt\Mismatch
     */
    public function save($articleForm)
    {
        /**
         * @var Mysql $db
         */
        $this->db->begin();

        /**
         * @var UserService $userService
         */
        $userService = $this->di->get('userService');
        $user = $userService->getLoginedUser();
        if ($user === false) {
            throw new NotLoginException();
        }


        if (!empty($articleForm->getId())) {
            $b = $this->update($articleForm->getId(), [
                'title' => $articleForm->getTitle(),
                'content' => $articleForm->getContent(),
                'status' => $articleForm->getDrag(),
                'user_id' => $user['id'],
            ]);
            $isNew = false;
            $articleId = $articleForm->getId();
        } else {
            $data = $articleForm->toArray();
            $data['user_id'] = $user['id'];
            $b = $this->insert([
                'user_id' => $user['id'],
                'title' => $articleForm->getTitle(),
                'content' => $articleForm->getContent(),
                'status' => $articleForm->getDrag(),
            ]);
            $isNew = true;
            $articleId = $b ? $b : 0;
        }

        if ($b === false) {
            $this->db->rollback();
            return false;
        }

        /**
         * 添加标签
         * @var TagService $tagService
         */
        $tagService = $this->di->get('tagService');
        $tagNames = $articleForm->getTags();
        if ($isNew && count($tagNames) > 0) {
            $b = $tagService->addArticleTags($tagNames, $articleId);
        } else {
            $b = $tagService->updateArticleTags($tagNames, $articleId);
        }

        if ($b === false) {
            $this->db->rollback();
            return false;
        }

        $this->db->commit();

        return true;
    }

    /**
     * 获取用户文章
     * @param Page $page
     * @param int $userId
     * @return Page
     */
    public function listOfUser($page, $userId, $status = 2)
    {
        $index = $page->getIndex();
        $rows = $page->getPageSize();
        $sql = "select id,title,content,user_id,ctime,status from article where user_id=? and del=0 and status={$status} order by id desc limit {$index},{$rows}";
        $rs = $this->db->query($sql, [$userId]);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $articles = $rs->fetchAll();
        foreach ($articles as &$article) {
            $article['encrypt_id'] = CryptUtil::num_encrypt($article['id']);
        }

        $sql = "select count(id) from blog.article where user_id=? and del=0 and status={$status}";
        $rs = $this->db->query($sql, [$userId]);
        $rs->setFetchMode(\PDO::FETCH_NUM);
        $totalRes = $rs->fetchArray();
        $page->setTotalItems($totalRes[0]);
        $page->setItems($articles);
        return $page;
    }

    /**
     * 返回一篇文章
     * @param int $aritcleId
     * @param bool $lazy
     * @return array
     */
    public function getOne($aritcleId, $lazy = false)
    {
        $sql = "select a.id,a.title,a.content,a.user_id,a.ctime,a.status from article a left join user u on u.id=a.user_id where a.id=? and a.del=0";
        $rs = $this->db->query($sql, [$aritcleId]);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $article = $rs->fetchArray();

        if ($lazy === false && !empty($article)) {
            $sql = "select t.name from article_tag art left join tag t on art.tag_id=t.id where art.article_id=? and art.del=0";
            $rs = $this->db->query($sql, [$article['id']]);
            $rs->setFetchMode(\PDO::FETCH_ASSOC);
            $tags = $rs->fetchAll();
            $tagNames = [];
            foreach ($tags as $tag) {
                $tagNames[] = $tag['name'];
            }
            $article['tags'] = $tagNames;
        }

        return $article;
    }

    /**
     * 真实删除文章
     * @param int $articleId
     * @return bool
     */
    public function delete($articleId)
    {
        $this->db->begin();

        $sql = "delete from article_tag where article_id=?";
        $b = $this->db->execute($sql, [$articleId]);
        if ($b === false) {
            $this->db->rollback();
            return false;
        }

        $sql = "delete from article where id=?";
        $b = $this->db->execute($sql, [$articleId]);
        if ($b === false) {
            $this->db->rollback();
            return false;
        }

        $this->db->commit();

        return true;

    }

    /**
     * 软删除用户文章
     * @param $articleId
     * @return bool
     */
    public function delteSoft($articleId)
    {
        $this->db->begin();

        $sql = "update article_tag set del=1 where article_id=?";
        $b = $this->db->execute($sql, [$articleId]);
        if ($b === false) {
            $this->db->rollback();
            return false;
        }

        $sql = "update article set del=1 where id=?";
        $b = $this->db->execute($sql, [$articleId]);
        if ($b === false) {
            $this->db->rollback();
            return false;
        }

        $this->db->commit();

        return true;
    }

    /**
     * 发布文章
     * @param int $articleId
     * @return bool
     */
    public function pub($articleId)
    {
        $sql = "update article set status=2,uptime=? where id=?";
        $b = $this->db->execute($sql, [date('Y-m-d H:i:s'), $articleId]);
        return $b === false ? false : true;
    }

    /**
     * 文章列表
     * @param Page $page
     * @return Page
     */
    public function list(Page $page)
    {
        $index = $page->getIndex();
        $rows = $page->getPageSize();
        $sql = "select id,title,content,ctime,user_id,status from article where status=2 and del=0 order by id desc limit {$index},{$rows}";
        $rs = $this->db->query($sql);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $articles = $rs->fetchAll();
        foreach ($articles as &$article) {
            $article['encrypt_id'] = CryptUtil::num_encrypt($article['id']);
        }

        $page->setItems($articles);

        $sql = "select count(id) as total from article where status=2 and del=0";
        $rs = $this->db->query($sql);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $totalRes = $rs->fetchArray();
        $page->setTotalItems($totalRes['total']);
        return $page;
    }

    /**
     * 插入文章
     * @param array $data
     * @return bool
     */
    public function insert(array $data)
    {
        if (count($data) == 0) {
            return false;
        }
        $data['ctime'] = date('Y-m-d H:i:s');

        $fields = array_keys($data);
        $values = array_values($data);
        $sql = "insert into article(" . implode(',', $fields) . ") values(" . implode(",", array_fill(0, count($values), '?')) . ")";
        $b = $this->db->execute($sql, $values);

        if ($b === false) {
            return false;
        }

        return $this->db->lastInsertId();
    }


    /**
     * @param int $id
     * @param array $set
     * @return bool
     */
    public function update(int $id, array $set)
    {
        if (count($set) === 0 || $id === 0) {
            return false;
        }

        $sql = "update article set title=?,content=?,status=?,user_id=?,uptime=? where id=?";
        $b = $this->db->execute($sql, [
            $set['title'],
            $set['content'],
            $set['status'],
            $set['user_id'],
            date('Y-m-d H:i:s'),
            $id
        ]);

        return $b === false ? false : true;

    }

    /**
     * 格式化处理文章类容
     * @param string $content
     * @return string
     */
    public function formatContent($content){
            echo $content;

//exit;
//
//        if(preg_match_all("@<code[\s\S\t]*?(.*?)>(.*?)</code>@i",$content,$m)){
//            echo "<pre>";
//            print_r($m);exit;
//            $mContent=$m[1];
//            $c='<code><ol><li>';
//            for ($i=0;$i<mb_strlen($mContent);$i++){
//                if($mContent[$i]=="\n"){
//                    $c.="</li><li>";
//                }
//                $c.=$mContent[$i];
//            }
//            $c.='</ol></code>';
//
//            $s=stripos($content,'<pre>')+5;
//            $e=stripos($content,'</pre>');
//            $preContent=substr($content,0,$s);
//            $lastContent=substr($content,$e);
//            $content=$preContent.$c.$lastContent;
//
//        }
        return $content;
    }

}