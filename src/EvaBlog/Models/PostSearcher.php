<?php

namespace Eva\EvaBlog\Models;

// +----------------------------------------------------------------------
// | [phalcon]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-8-25 18:13
// +----------------------------------------------------------------------

use Elasticsearch\Client;
use Eva\EvaEngine\View\PurePaginator;
use Phalcon\Http\Client\Exception;

class PostSearcher extends Post
{
    /**
     * @var Client
     */
    private $es_client;

    private $es_config;

    public function initialize()
    {
        parent::initialize();
        $this->es_config = $this->getDI()->getConfig()->EvaSearch->elasticsearch->toArray();
        $this->es_client = new Client(array(
            'hosts' => $this->es_config['servers']
        ));
    }

    public function searchPosts(array $query = array())
    {
        $searchParams = array();
        $searchParams['index'] = $this->es_config['index_name'];
        $searchParams['type'] = 'article';

        $searchParams['size'] = isset($query['limit']) && intval($query['limit']) > 0 ? intval($query['limit']) : 15;
        $page = isset($query['page']) && intval($query['page']) > 0 ? intval($query['page']) : 1;
        $searchParams['from'] = ($page - 1) * $searchParams['size'];
        $orderMapping = array(
            'id' => array(
                'id' => array('order' => 'asc')
            ),
            '-id' => array(
                'id' => array('order' => 'desc')
            ),
            'created_at' => array(
                'createdAt' => array('order' => 'asc')
            ),
            '-created_at' => array(
                'createdAt' => array(
                    'order' => 'desc',
                ),
            ),
            'sort_order' => array(
                'sortOrder' => array(
                    'order' => 'asc',
                ),
            ),
            '-sort_order' => array(
                'sortOrder' => array(
                    'order' => 'desc',
                ),
            ),
            'count' => array(
                'count' => array(
                    'order' => 'asc',
                ),
            ),
            '-count' => array(
                'count' => array(
                    'order' => 'desc',
                ),
            ),
        );

        if (!empty($query['columns'])) {
//            $itemQuery->columns($query['columns']);
        }


//        if (!empty($query['id'])) {
//            $idArray = explode(',', $query['id']);
//            $itemQuery->inWhere('id', $idArray);
//        }

        $filters = array();
        if (!empty($query['status'])) {
            $filters[]['term'] = array(
                'status' => $query['status']
            );
        }

        if (!empty($query['has_image'])) {
            $filters[]['range'] = array(
                'imageId' => array('from' => 1)
            );
        }

        if (!empty($query['sourceName'])) {
            $filters[]['term'] = array(
                'sourceName' => $query['sourceName']
            );
        }

        if (!empty($query['uid'])) {
            $filters[]['term'] = array(
                'uid' => $query['uid']
            );
        }

        if (!empty($query['cid'])) {
            $filters[]['term'] = array(
                'categoryId' => $query['cid']
            );
        }

        if (!empty($query['tags'])) {
            $filters[]['term'] = array(
                'tagNames' => $query['tags']
            );
        }
        $sort = array();
        if (!empty($query['order']) && $orderMapping[$query['order']]) {
            $sort = array_merge($sort, $orderMapping[$query['order']]);
        }
        $sort = array_merge(
//            $sort,
            array(
                '_score' => array(
                    'order' => 'desc'
                )
            )
        );
        $searchParams['body']['sort'] = $sort;
        if ($filters) {
            $searchParams['body']['filter']['and'] = array(
                'filters' => $filters,
                "_cache" => true

            );
        }
        if (isset($query['highlight']) && $query['highlight']) {
            $searchParams['body']['highlight'] = array(
                "fields" => array(
                    "title" => array(
                        "type" => "plain"
                    ),
                    "content" => array(
                        "fragment_size" => 50,
                        "number_of_fragments" => 3,
                        "type" => "plain"
                    ),

                )
            );
        }
        $keyword = isset($query['q']) && count(trim($query['q'])) > 0 ? trim($query['q']) : false;
        if ($keyword) {
//            $searchParams['body']['query']['multi_match'] = array(
//                'query' => $query['q'],
//                "fields" => array("content", "title"),
//                'type' => 'phrase',
//                "tie_breaker" => 1.0
//            );
//            $searchParams['body']['min_score'] = 0.9;
            $gravity = 5;
            $now = time();
            $searchParams['body']['query'] = array(
                'function_score' => array(
                    'functions' => array(
                        array(
                            'script_score' => array(
                                "script" => "(_score - 1) / pow(({$now} - doc['createdAt'].value), {$gravity})"
                            )
                        )
                    ),
                    'query' => array(
                        'multi_match' => array(
                            'query' => $query['q'],
                            "fields" => array("content", "title"),
                            'type' => 'best_fields',
//                            "tie_breaker" => 0.3
                        )
                    )
                )
            );
        }
//        header('Content-Type: text/javascript;');
//        echo(json_encode($searchParams, JSON_UNESCAPED_UNICODE));
//        exit();
        $ret = $this->es_client->search($searchParams);
        $pager = new PurePaginator($searchParams['size'], $ret['hits']['total'], $ret['hits']['hits']);
        return $pager;
    }

    public function getRelatedPosts($id, $limit = 5, $days = 30)
    {
        if (!$this->getDI()->getConfig()->EvaSearch->relatedPostsEnable) {
            return array();
        }
        $timeout = '1s';
        $searchParams['index'] = $this->es_config['index_name'];
        $searchParams['type'] = 'article';
        $searchParams['size'] = $limit;
        $searchParams['from'] = 0;
        $searchParams['timeout'] = $timeout;
        $cacheKey = 'Blog_relPosts_' . $id . $limit . $days;
        /** @var \Phalcon\Cache\Backend $cache */
        $cache = $this->getDI()->getGlobalCache();
        $postsCached = $cache->get($cacheKey);
        if ($postsCached != null) {
            return unserialize($postsCached);
        }
        $searchParams['fields'] = array(
            'id',
            'title',
            'createdAt',
            'slug'
        );
        $searchParams['body']['timeout'] = $timeout;
        $searchParams['body']['query']['more_like_this'] = array(
            'fields' => array('title', 'content', 'tagNames'),
            'ids' => array($id)
        );
        $filters = array();
        $filters[]['range'] = array(
            'createdAt' => array('from' => time() - (86400 * $days))
        );
        $filters[]['term'] = array(
            'status' => 'published'
        );
        if ($filters) {
            $searchParams['body']['filter']['and'] = array(
                'filters' => $filters,
                "_cache" => true

            );
        }
        try {
            $ret = $this->es_client->search($searchParams);
        } catch (\Exception $e) {
            $ret = null;
        }
        $posts = array();
        if ($ret) {
            foreach ($ret['hits']['hits'] as $hit) {
                foreach ($hit['fields'] as $_k => $_v) {
                    $hit['fields'][$_k] = $_v[0];
                }

                $posts[] = $hit['fields'];
            }
        }
        $cache->save($cacheKey, serialize($posts), 600);
        return $posts;
    }
}