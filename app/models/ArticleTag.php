<?php

namespace app\models;

use app\validations\validators\PhoneValidator;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email as EmailValidator;

class ArticleTag extends \Phalcon\Mvc\Model
{

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $article_id;

    /**
     * @var integer
     */
    protected $tag_id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getArticleId()
    {
        return $this->article_id;
    }

    /**
     * @param int $article_id
     */
    public function setArticleId($article_id)
    {
        $this->article_id = $article_id;
    }

    /**
     * @return int
     */
    public function getTagId()
    {
        return $this->tag_id;
    }

    /**
     * @param int $tag_id
     */
    public function setTagId($tag_id)
    {
        $this->tag_id = $tag_id;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("blog");
        $this->setSource("article_tag");
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'article_tag';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return User[]|User|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return User|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}
