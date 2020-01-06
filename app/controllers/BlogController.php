<?php
/**
 * Created by PhpStorm.
 * User: 14521
 * Date: 2019/12/30
 * Time: 14:59
 */

namespace app\controllers;


use app\libraries\CryptUtil;
use app\libraries\Page;
use app\models\domains\CommentForm;
use Phalcon\Crypt\Mismatch;

class BlogController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();

        $tags = $this->tagService->listAll();
        $this->view->setVar('tags', $tags);
    }

    public function indexAction()
    {
        //加载静态资源
        $this->assets->addCss('assets/css/index.css' . $this->staticDebug());
        $this->assets->addJs('assets/js/index.js' . $this->staticDebug());

        $p = $this->dispatcher->getParam('p', 'int!', 1);
        $page = new Page($p);
        $page = $this->articleService->list($page);
        $this->view->setVar('page', $page);

        $tags = $this->tagService->listAll();
        $this->view->setVar('tags', $tags);
    }

    public function starAction()
    {
        if ($this->request->isGet()) {
            $articleId = CryptUtil::num_decrypt($this->request->get('articleId', 'trim', ''));
            $userId = $this->request->get('userId', 'int!', 0);
            if ($articleId !== 0 && $userId !== 0) {
                try {
                    $b = $this->articleService->star($userId, $articleId);
                    if ($b !== false) {
                        return json_encode(array('error' => 0, 'msg' => 'SUCCESS'));
                    } else {
                        return json_encode(array('error' => 1, 'msg' => '已点过赞'));
                    }
                } catch (\Exception $e) {
                }

            }
        }

        return json_encode(array('error' => 1, 'msg' => '点赞失败')); 
    }

    /**
     * @throws Mismatch
     */
    public function showArticleAction()
    {
        $this->assets->addCss('assets/css/index.css' . $this->staticDebug());
        $this->assets->addCss('assets/base/css/railscasts.min.css' . $this->staticDebug());
        $this->assets->addJs('assets/js/index.js' . $this->staticDebug());
        $this->assets->addJs('assets/plugs/ckeditor/plugins/codesnippet/lib/highlight/highlight.pack.js' . $this->staticDebug());

        $articleId = CryptUtil::num_decrypt($this->dispatcher->getParam('articleId', 'string', ''));

        if ($articleId !== "") {
            $this->articleService->recordLookNum($articleId);
            $article = $this->articleService->getOne($articleId);
            $this->view->setVar('article', $article);

            $user = $this->userService->getLoginedUser();
            $this->view->setVar('user', $user);

            $page = new Page();
            $page->setPageSize(10);
            $page = $this->commentService->listOfArticle($page, $articleId);
            $this->view->setVar('page', $page);
        } else {
            $this->flashSession->error("文章不存在");
            $this->goBack();
        }
    }

    public function listByTagAction()
    {
        //加载静态资源
        $this->assets->addCss('assets/css/index.css' . $this->staticDebug());
        $this->assets->addJs('assets/js/index.js' . $this->staticDebug());

        $p = $this->dispatcher->getParam('p', 'int!', 1);
        $tagId = $this->dispatcher->getParam('tagId', 'int!', 0);
        if ($tagId !== 0) {
            $page = new Page($p);
            $page = $articles = $this->articleService->listByTag($tagId, $page);
            $this->view->setVar('page', $page);

            $tags = $this->tagService->listAll();
            $this->view->setVar('tags', $tags);

            $curTag = $tags[0];
            foreach ($tags as $tag) {
                if (intval($tag['id']) === $tagId) {
                    $curTag = $tag;
                    break;
                }
            }
            $this->view->setVar('curTag', $curTag);
        } else {
            $this->response->redirect('/');
        }
    }

    /**
     * @param CommentForm $commentForm
     */
    public function writeCommentAction(CommentForm $commentForm)
    {
        if ($this->request->isPost()) {
            if ($this->security->checkToken()) {
                $data = [];
                $data['user_id'] = $commentForm->getUserId();
                $data['content'] = $commentForm->getContent();
                $data['article_id'] = $commentForm->getArticleId();
                $comment = $this->commentService->add($data);
                $comment['content'] = preg_replace("/@(.*?):/iu", "<span class='ato'>@$1:</span>", $comment['content']);
                if ($comment !== false) {
                    return json_encode(['error' => 0, 'msg' => 'success', 'data' => [
                        'token' => [
                            'key' => $this->security->getTokenKey(),
                            'value' => $this->security->getToken()
                        ],
                        'comment' => $comment
                    ]]);
                } else {
                    return json_encode(['error' => 1, 'msg' => '添加失败']);
                }
            } else {
                return json_encode(['error' => 1, 'msg' => '非法提交']);
            }
        } else {
            return json_encode(['error' => 1, 'msg' => '非法提交']);
        }
    }

    public function getCommentsArticleAction()
    {
        $articleId = CryptUtil::num_decrypt($this->request->get('articleId', 'trim', ''));
        $p = $this->request->get('p', 'int!', 1);

        if ($articleId !== "") {
            $page = new Page($p);
            $page->setPageSize(10);
            $page = $this->commentService->listOfArticle($page, $articleId);
            return json_encode(['error' => 0, 'msg' => 'SUCCESS', 'data' => ['page' => $page->toArray()]]);
        } else {
            return json_encode(['error' => 1, 'msg' => '评论不存在']);
        }
    }

}