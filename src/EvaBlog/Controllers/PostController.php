<?php

namespace Eva\EvaBlog\Controllers;

use Eva\EvaBlog\Models;
use Eva\EvaBlog\Models\Post;

class PostController extends \Phalcon\Mvc\Controller
{
    public function listAction()
    {
        $limit = $this->dispatcher->getParam('limit');
        $limit = $limit ? $limit : 25;
        /** @noinspection PhpDuplicateArrayKeysInspection */
        $query = array(
            'q' => $this->dispatcher->getParam('q'),
            'type' => $this->dispatcher->getParam('type'),
            'status' => $this->dispatcher->getParam('status'),
            'uid' => $this->dispatcher->getParam('uid'),
            'cid' => $this->dispatcher->getParam('cid'),
            'tid' => $this->dispatcher->getParam('tid'),
            'has_image' => $this->dispatcher->getParam('has_image', 'int'),
            'min_created_at' => $this->dispatcher->getParam('min_created_at', 'int'),
            'username' => $this->dispatcher->getParam('username'),
            'order' => $this->dispatcher->getParam('order'),
            'limit' => $limit,
            'page' => $this->dispatcher->getParam('page'),
        );
        $post = new Models\Post();
        $posts = $post->findPosts($query);
        $paginator = new \Eva\EvaEngine\Paginator(array(
            "builder" => $posts,
            "limit" => $query['limit'],
            "page" => $query['page']
        ));
        $paginator->setQuery($query);
        $pager = $paginator->getPaginate();
        return $pager;
    }

    public function searchAction()
    {
        $limit = $this->dispatcher->getParam('limit', 'int', 25);
        $limit = $limit > 100 ? 100 : $limit;
        $limit = $limit < 3 ? 3 : $limit;
        $order = $this->dispatcher->getParam('order', 'string', '-created_at');
        $query = array(
            'q' => $this->dispatcher->getParam('q', 'string'),
            'status' => 'published',
            'uid' => $this->dispatcher->getParam('uid', 'int'),
            'cid' => $this->dispatcher->getParam('cid', 'int'),
            'tid' => $this->dispatcher->getParam('tid', 'int'),
            'username' => $this->dispatcher->getParam('username', 'string'),
            'order' => $order,
            'limit' => $limit,
            'page' => $this->dispatcher->getParam('page', 'int', 1),
        );

        $postSearcher = new Models\PostSearcher();
        $pager = $postSearcher->searchPosts($query);

        return $pager;
    }

    public function relatedPostsByTextAction()
    {
        $text = $this->dispatcher->getParam('text');
        $limit = $this->dispatcher->getParam('limit', 'int', 5);
        $days = $this->dispatcher->getParam('days', 'int', 30);
        $postSearcher = new Models\PostSearcher();
        return $postSearcher->getRelatedPostsByText($text, $limit, $days);
    }
}
