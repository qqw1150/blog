<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/12/23
 * Time: 16:56
 */

namespace app\models\domains;


class CommentForm extends Base
{
    private $id = 0;
    private $content = '';
    private $userId = 0;
    private $articleId = 0;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = intval($id);
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent(string $content): void
    {
        $this->content = trim($content);
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId(int $userId): void
    {
        $this->userId = intval($userId);
    }

    /**
     * @return int
     */
    public function getArticleId(): int
    {
        return $this->articleId;
    }

    /**
     * @param int $articleId
     */
    public function setArticleId(int $articleId): void
    {
        $this->articleId = intval($articleId);
    }

    public function toArray()
    {
        return parent::_toArray(self::class);
    }

    public function fillData($data)
    {
        if (isset($data['id'])) {
            $this->setId($data['id']);
        }
        $this->setContent($data['content']);
        $this->setArticleId($data['articleId']);
        $this->setUserId($data['userId']);
    }
}