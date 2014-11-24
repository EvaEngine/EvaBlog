<?php

namespace Eva\EvaBlog\Controllers\Admin;

use Eva\EvaBlog\Models;
use Eva\EvaEngine\Mvc\Controller\JsonControllerInterface;
use Eva\EvaEngine\Exception;

/**
 * @resourceName("Category Managment Assists")
 * @resourceDescription("Category Managment Assists (Ajax json format)")
 */
class ProcessController extends ControllerBase implements JsonControllerInterface
{
    /**
    * @operationName("Get post info by url")
    * @operationDescription("For post connection feature")
    */
    public function connectionAction()
    {
        if (!$this->request->isPost()) {
            return $this->showErrorMessageAsJson(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }
        $url = $this->request->getPost('url');
        $url = parse_url($url);
        $path = $url['path'];
        $post = new \stdclass();
        if (preg_match('/(\d+)/', $path, $matches)) {
            $id = $matches[1];
            $post = Models\Post::findFirst("id = $id");
        }
        return $this->response->setJsonContent($post);
    }

    /**
    * @operationName("Remove a post")
    * @operationDescription("Remove a post")
    */
    public function deleteAction()
    {
        if (!$this->request->isDelete()) {
            return $this->showErrorMessageAsJson(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }

        $id = $this->dispatcher->getParam('id');
        $post =  new Models\Post();
        try {
            $post->removePost($id);
        } catch (\Exception $e) {
            return $this->showExceptionAsJson($e, $post->getMessages());
        }

        return $this->response->setJsonContent($post);
    }

    /**
    * @operationName("Change post status")
    * @operationDescription("Change post status")
    */
    public function statusAction()
    {
        if (!$this->request->isPut()) {
            return $this->showErrorMessageAsJson(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }

        $id = $this->dispatcher->getParam('id');
        $post =  Models\Post::findFirst($id);
        try {
            $post->status = $this->request->getPut('status');
            $post->save();
        } catch (\Exception $e) {
            return $this->showExceptionAsJson($e, $post->getMessages());
        }

        return $this->response->setJsonContent($post);
    }

    /**
    * @operationName("Change post sort order")
    * @operationDescription("Change post sort order")
    */
    public function sortAction()
    {
        if (!$this->request->isPut()) {
            return $this->showErrorMessageAsJson(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }

        $id = $this->dispatcher->getParam('id');
        $post =  Models\Post::findFirst($id);
        try {
            $post->sortOrder = (int) $this->request->getPut('sortOrder');
            $post->save();
        } catch (\Exception $e) {
            return $this->showExceptionAsJson($e, $post->getMessages());
        }

        return $this->response->setJsonContent($post);
    }

    /**
    * @operationName("Change post status by batch")
    * @operationDescription("Change post status by batch")
    */
    public function batchAction()
    {
        if (!$this->request->isPut()) {
            return $this->showErrorMessageAsJson(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }

        $idArray = $this->request->getPut('id');
        if (!is_array($idArray) || count($idArray) < 1) {
            return $this->showErrorMessageAsJson(401, 'ERR_REQUEST_PARAMS_INCORRECT');
        }

        $status = $this->request->getPut('status');
        $posts = array();

        try {
            foreach ($idArray as $id) {
                $post =  Models\Post::findFirst($id);
                if ($post) {
                    $post->status = $status;
                    $post->save();
                    $posts[] = $post;
                }
            }
        } catch (\Exception $e) {
            return $this->showExceptionAsJson($e, $post->getMessages());
        }

        return $this->response->setJsonContent($posts);
    }

    /**
    * @operationName("Check post slug unique")
    * @operationDescription("Check post slug unique")
    */
    public function slugAction()
    {
        $slug = $this->request->get('slug');
        $exclude = $this->request->get('exclude');
        if ($slug) {
            $conditions = array(
                "columns" => array('id'),
                "conditions" => 'slug = :slug:',
                "bind" => array(
                    'slug' => $slug
                )
            );
            if ($exclude) {
                $conditions['conditions'] .= ' AND id != :id:';
                $conditions['bind']['id'] = $exclude;
            }
            $post = Models\Post::findFirst($conditions);
        } else {
            $post = array();
        }

        if ($post) {
            $this->response->setStatusCode('409', 'Post Already Exists');
        }

        return $this->response->setJsonContent(array(
            'exist' => $post ? true : false,
            'id' => $post ? $post->id : 0,
        ));
    }
}
