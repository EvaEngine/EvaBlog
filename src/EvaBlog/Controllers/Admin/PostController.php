<?php

namespace Eva\EvaBlog\Controllers\Admin;

use Eva\EvaBlog\Models;
use Eva\EvaBlog\Models\Post;
use Eva\EvaBlog\Forms;
use Eva\EvaEngine\Exception;

/**
 * @resourceName("Post Managment")
 * @resourceDescription("Post Managment")
 */
class PostController extends ControllerBase
{
    /**
     * @operationName("Post List")
     * @operationDescription("Post List")
     */
    public function indexAction()
    {
        $limit = $this->request->getQuery('per_page', 'int', 25);
        $limit = $limit > 100 ? 100 : $limit;
        $limit = $limit < 10 ? 10 : $limit;
        $order = $this->request->getQuery('order', 'string', '-created_at');
        $query = array(
            'q' => $this->request->getQuery('q', 'string'),
            'status' => $this->request->getQuery('status', 'string'),
            'type' => $this->request->getQuery('type', 'string'),
            'uid' => $this->request->getQuery('uid', 'int'),
            'cid' => $this->request->getQuery('cid', 'int'),
            'tid' => $this->request->getQuery('tid', 'int'),
            'tag' => $this->request->getQuery('tag', 'string'),
            'username' => $this->request->getQuery('username', 'string'),
            'source_name' => $this->request->getQuery('source_name', 'string'),
            'min_created_at' => $this->request->getQuery('min_created_at', 'string'),
            'max_created_at' => $this->request->getQuery('max_created_at', 'string'),
            'order' => $order,
            'limit' => $limit,
            'page' => $this->request->getQuery('page', 'int', 1),
        );

        $form = new Forms\FilterForm();
        $form->setValues($this->request->getQuery());
        $this->view->setVar('form', $form);

        $post = new Models\Post();
        $posts = $post->findPosts($query);
        $paginator = new \Eva\EvaEngine\Paginator(array(
            "builder" => $posts,
            "limit" => $limit,
            "page" => $query['page']
        ));
        $paginator->setQuery($query);
        $pager = $paginator->getPaginate();
        $this->view->setVar('pager', $pager);
    }

    /**
     * @operationName("Show page of post creating")
     * @operationDescription("Show page of post creating")
     */
    public function createAction()
    {
        $form = new Forms\PostForm();
        $post = new Models\Post();
        $form->setModel($post);
        $form->addForm('text', 'Eva\EvaBlog\Forms\TextForm');
        $this->view->setVar('form', $form);
        $this->view->setVar('item', $post);

    }

    /**
     * @operationName("Save post with published status")
     * @operationDescription("Save post with published status")
     */
    public function savePublishedAction()
    {
        $data = $this->request->getPost();
        if ($data['id'] > 0) {
            return $this->editPost();
        }
        return $this->createPost('published');
    }

    /**
     * @operationName("Save post with draft status")
     * @operationDescription("Save post with draft status")
     */
    public function saveDraftAction()
    {
        $data = $this->request->getPost();
        if ($data['id'] > 0) {
            return $this->editPost();
        }
        return $this->createPost('draft');
    }

    protected function createPost($status = 'draft')
    {
        $this->view->changeRender('admin/post/create');

        $form = new Forms\PostForm();
        $post = new Models\Post();
        $form->setModel($post);
        $form->addForm('text', 'Eva\EvaBlog\Forms\TextForm');
        $this->view->setVar('form', $form);
        $this->view->setVar('item', $post);


        $data = $this->request->getPost();
        $data['status'] = $status;
        if (!$form->isFullValid($data)) {
            return $this->showInvalidMessages($form);
        }
        try {
            $form->save('createPost');
        } catch (\Exception $e) {
            return $this->showException($e, $form->getModel()->getMessages());
        }
        $this->flashSession->success('SUCCESS_POST_CREATED');
        return $this->redirectHandler('/admin/post/edit/' . $form->getModel()->id);
    }

    protected function editPost()
    {
        $this->view->changeRender('admin/post/create');
        $data = $this->request->getPost();

        $post = Models\Post::findFirst($data['id']);
        if (!$post) {
            throw new Exception\ResourceNotFoundException('ERR_BLOG_POST_NOT_FOUND');
        }
        $form = new Forms\PostForm();
        $form->setModel($post);
        $form->addForm('text', 'Eva\EvaBlog\Forms\TextForm');
        $this->view->setVar('form', $form);
        $this->view->setVar('item', $post);

        if (!$form->isFullValid($data)) {
            return $this->showInvalidMessages($form);
        }
        try {
            $form->save('updatePost');
        } catch (\Exception $e) {
            return $this->showException($e, $form->getModel()->getMessages());
        }
        $this->flashSession->success('SUCCESS_POST_UPDATED');
        return $this->redirectHandler('/admin/post/edit/' . $post->id);
    }

    /**
     * @operationName("Show page of post editing")
     * @operationDescription("Show page of post editing")
     */
    public function editAction()
    {
        $this->view->changeRender('admin/post/create');
        $post = Models\Post::findFirst($this->dispatcher->getParam('id'));
        if (!$post) {
            throw new Exception\ResourceNotFoundException('ERR_BLOG_POST_NOT_FOUND');
        }

        $form = new Forms\PostForm();
        $form->setModel($post);
        $form->addForm('text', 'Eva\EvaBlog\Forms\TextForm');
        $this->view->setVar('form', $form);
        $this->view->setVar('item', $post);

//        if (!$this->request->isPost()) {
//            return false;
//        }
//        $data = $this->request->getPost();
//        if (!$form->isFullValid($data)) {
//            return $this->showInvalidMessages($form);
//        }
//
//        try {
//            $form->save('updatePost');
//        } catch (\Exception $e) {
//            return $this->showException($e, $form->getModel()->getMessages());
//        }
//        $this->flashSession->success('SUCCESS_POST_UPDATED');
//
//        return $this->redirectHandler('/admin/post/edit/' . $post->id);
    }

    /**
     * @operationName("Save post with published status")
     * @operationDescription("Save post with published status")
     */
    public function saveAndPushPostAction()
    {
        $data = $this->request->getPost();
        return $this->response->redirect('/admin/message/notification?nId=' . $data['id']);
    }
}
