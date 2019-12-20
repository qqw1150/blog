<?php

namespace app\models;

class Article extends Base
{

    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var string
     */
    protected $title;

    /**
     *
     * @var string
     */
    protected $content;

    /**
     *
     * @var string
     */
    protected $ctime;

    /**
     *
     * @var string
     */
    protected $uptime;

    /**
     *
     * @var integer
     */
    protected $user_id;

    /**
     * @return int
     */
    public function getId()
    {
        return intval($this->id);
    }

    /**
     * @param int $id
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
     * @return string
     */
    public function getCtime()
    {
        return $this->ctime;
    }

    /**
     * @param string $ctime
     */
    public function setCtime($ctime)
    {
        $this->ctime = $ctime;
    }

    /**
     * @return string
     */
    public function getUptime()
    {
        return $this->uptime;
    }

    /**
     * @param string $uptime
     */
    public function setUptime($uptime)
    {
        $this->uptime = $uptime;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }


    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->setSchema("blog");
        $this->setSource("article");
        $this->hasMany('id', 'app\models\Comment', 'article_id', ['alias' => 'Comment']);
        $this->belongsTo('user_id', 'app\models\User', 'id', ['alias' => 'User']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'article';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Article[]|Article|\Phalcon\Mvc\Model\ResultSetInterface
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Article|\Phalcon\Mvc\Model\ResultInterface
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
