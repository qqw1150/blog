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
use app\models\domains\ArticleForm;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Sunra\PhpSimple\HtmlDomParser;

class ArticleService extends BaseService
{
    /**
     * 保存文章（创建或修改）
     * @param ArticleForm $articleForm
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
            $article['short_content'] = mb_strlen($article['content']) > 350 ? mb_substr($article['content'], 0, 350) : $article['content'];
            $article['short_content'] = (new \Phalcon\Filter())->sanitize($article['short_content'], 'striptags');
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
    public function getOne($articleId, $lazy = false)
    {
        $sql = "select a.id,a.title,a.content,a.user_id,a.ctime,a.status from article a left join user u on u.id=a.user_id where a.id=? and a.del=0";
        $rs = $this->db->query($sql, [$articleId]);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $article = $rs->fetchArray();

        $sql="select count(id) from star where article_id=?";
        $rs=$this->db->query($sql, [$articleId]);
        $rs->setFetchMode(\PDO::FETCH_NUM);
        $star=$rs->fetchArray();
        $article['star']=$star[0];

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
            $article['short_content'] = mb_strlen($article['content']) > 350 ? mb_substr($article['content'], 0, 350) : $article['content'];
            $article['short_content'] = (new \Phalcon\Filter())->sanitize($article['short_content'], 'striptags');
            $articleIds[] = $article['id'];
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
     * 根据标签列出文章
     * @param int $tagId
     * @param Page $page
     * @return Page
     */
    public function listByTag(int $tagId, Page $page)
    {
        $index = $page->getIndex();
        $rows = $page->getPageSize();
        $sql = "select 
a.id,a.title,a.content,a.ctime,a.user_id,a.status
 from article a left join article_tag art on a.id=art.article_id
where art.tag_id=? and a.del=0 and a.status=2 order by a.id desc limit {$index},{$rows}";
        $rs = $this->db->query($sql, [$tagId]);
        $rs->setFetchMode(\PDO::FETCH_ASSOC);
        $articles = $rs->fetchAll();
        foreach ($articles as &$article) {
            $article['encrypt_id'] = CryptUtil::num_encrypt($article['id']);
            $article['short_content'] = mb_strlen($article['content']) > 350 ? mb_substr($article['content'], 0, 350) : $article['content'];
            $article['short_content'] = (new \Phalcon\Filter())->sanitize($article['short_content'], 'striptags');
        }
        $page->setItems($articles);

        $sql = "select count(a.id)
 from article a left join article_tag art on a.id=art.article_id
where art.tag_id=? and a.del=0 and a.status=2";
        $rs = $this->db->query($sql, [$tagId]);
        $rs->setFetchMode(\PDO::FETCH_NUM);
        $totalRes = $rs->fetchArray();
        $page->setTotalItems($totalRes[0]);

        return $page;
    }

    public function star($userId, $articleId)
    {
        $sql = "select id from star where user_id=? and article_id=?";
        $rs = $this->db->query($sql, [$userId, $articleId]);
        $count = $rs->numRows();
        if ($count > 0) {
            return false;
        }

        $sql = "insert into star(id,user_id,article_id) values (null,?,?)";
        $b = $this->db->execute($sql, [$userId, $articleId]);
        if($b === false){
            throw new \Exception('插入失败');
        }

        return true;
    }

}