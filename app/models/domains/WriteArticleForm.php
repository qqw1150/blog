<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/10/28
 * Time: 14:59
 */

namespace app\models\domains;

/**
 * Class ArticleForm
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property array $tags
 * @package app\models\domains
 */
class WriteArticleForm extends Base
{
    protected $id;
    protected $title = '';
    protected $content = '';
    protected $tags = '';

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string $tags
     */
    public function setTags($tags)
    {
        $this->tags = explode(',', $tags);
    }

    public function toArray()
    {
        parent::_toArray(self::class);
    }

    public function fillData($data)
    {
        if (!empty($data['id'])) {
            $this->setId($data['id']);
        }
        $this->setTitle($data['title']);
        $this->setContent($data['content']);
        $this->setTags($data['tags']);
    }
}