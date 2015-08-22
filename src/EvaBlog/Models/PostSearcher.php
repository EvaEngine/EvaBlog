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
use Eva\CounterRank\Utils\CounterRankUtil;
use Eva\EvaCache\CacheManager;
use Eva\EvaEngine\View\PurePaginator;
use Eva\EvaSearch\Entities\KeywordCounts;
use Eva\EvaSearch\Models\KeywordCount;
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
        $params = [
            'hosts' => $this->es_config['servers']
        ];
        $params['guzzleOptions'] = [
            'curl.options'=> [
                CURLOPT_CONNECTTIMEOUT => 1.0,
                CURLOPT_TIMEOUT => 1.0,
            ]
        ];
        $this->es_client = new Client($params);
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
//        $sort = array();
//        if (!empty($query['order']) && $orderMapping[$query['order']]) {
//            $sort = array_merge($sort, $orderMapping[$query['order']]);
//        }
//        $sort = array_merge(
//            $sort,
//            array(
//                '_score' => array(
//                    'order' => 'desc'
//                )
//            )
//        );
        $searchParams['body']['sort'] = array(
            '_score' => array(
                'order' => 'desc'
            )
        );
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
            $_query = array(
                'multi_match' =>
                    array(
                        'query' => $query['q'],
                        "fields" => array("content", "title"),
                        'type' => 'best_fields',
                        "tie_breaker" => 0.3
                    )
            );
//            $searchParams['body']['query'] = $_query;
            /*
              "function_score": {
            "query": {
                "multi_match": {
                    "query": "华尔街见闻",
                    "fields": [
                        "content",
                        "title"
                    ],
                    "type": "best_fields"
                }
            },
            "functions": [
                {
                    "script_score": {
                        "script": "(_score - 1) / pow((1419864850 - doc['createdAt'].value), 5)"
                    }
                }
            ]
        }
             * */
            // 防止文章创建时间和当前时间一样时，计算公式的分母为0
            $now = time() + 7200;
            $searchParams['body']['query'] = array(
                'function_score' => array(
                    'functions' => array(
                        array(
                            'script_score' => array(
                                'script' => "_score / atan(({$now} - doc['createdAt'].value) / 1296000)"
                            )
                        )
                    ),
                    'query' => $_query
                ),
            );

//            $searchParams['body']['min_score'] = 0.3;

//            $gravity = 5;
//            $now = time();
//            $searchParams['body']['query'] = array(
//                'function_score' => array(
//                    'functions' => array(
//                        array(
//                            'script_score' => array(
//                                "script" => "(_score - 1) / pow(({$now} - doc['createdAt'].value), {$gravity})"
//                            )
//                        )
//                    ),
//                    'query' => array(
//                        'multi_match' => array(
//                            'query' => $query['q'],
//                            "fields" => array("content", "title"),
//                            'type' => 'best_fields',
//                            "tie_breaker" => 0.3
//                        )
//                    )
//                )
//            );
        }
//        header('Content-Type: text/javascript;');
//        echo(json_encode($searchParams, JSON_UNESCAPED_UNICODE));
//        exit();
        $ret = $this->es_client->search($searchParams);
        foreach ($ret['hits']['hits'] as $_k => $_v) {
            $ret['hits']['hits'][$_k]['_source']['image'] = $this->getImageUrlByUri($ret['hits']['hits'][$_k]['_source']['image']);
            //getImageUrl
        }
        $pager = new PurePaginator($searchParams['size'], $ret['hits']['total'], $ret['hits']['hits']);


        if (isset($query['increase']) && $query['increase'] !== false) {
            //使用redis进行关键词统计
            $countrRankUtil = new CounterRankUtil();
            $countrRank = $countrRankUtil->getCounterRank("keywords");

            //被搜索的关键词加1,如果不存在,则新创建
            if (!$countrRank->increase($keyword, 1)) {
                $countrRank->create($keyword, 1);
            }
        }

        return $pager;
    }

    public function getRelatedPosts($id, $limit = 5, $days = 30)
    {
        if (!$this->getDI()->getConfig()->EvaSearch->relatedPostsEnable) {
            return array();
        }
        $timeout = '1s';
        $cacheKey = implode('_', array(__CLASS__, __FUNCTION__, $id, $limit, $days));
        $cacheManager = new CacheManager($this->getDI()->getGlobalCache());
        return $cacheManager->getOrSave(
            $cacheKey,
            function () use ($id, $timeout, $days, $limit) {
                $searchParams['index'] = $this->es_config['index_name'];
                $searchParams['type'] = 'article';
                $searchParams['size'] = $limit;
                $searchParams['from'] = 0;
                $searchParams['timeout'] = $timeout;
                $searchParams['fields'] = array(
                    'id',
                    'title',
                    'createdAt',
                    'slug',
                    'image',
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
                        $hit['fields']['image'] = $this->getImageUrlByUri($hit['fields']['image']);
                        $posts[] = $hit['fields'];
                    }
                }
                return $posts;
            },
            600
        );
    }
}
