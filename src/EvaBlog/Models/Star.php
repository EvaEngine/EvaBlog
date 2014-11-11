<?php

namespace Eva\EvaBlog\Models;

use Eva\EvaBlog\Entities;
use Eva\EvaEngine\Exception;

class Star extends Entities\Stars
{
    public static $simpleDump = array(
        'id',
        'createdAt',
        'post' => array(
            'id',
            'title',
            'type',
            'codeType',
            'createdAt',
            'summary',
            'summaryHtml' => 'getSummaryHtml',
            'commentStatus',
            'sourceName',
            'sourceUrl',
            'url' => 'getUrl',
            'imageUrl' => 'getImageUrl',
            'user' => array(
                'id',
                'username',
                'screenName',
            ),
        )
    );

    public function getStars($params)
    {
        $cacheKey = $this->createCacheKey($params);

//        dd($cacheKey);
        if($this->getCache()->exists($cacheKey)){
            return $this->getCache()->get($cacheKey);
        }

        $results = $this->getStarsBuilder($params)->getQuery()->execute();

        if(!count($results)){
            $results = array();
        }

        $this->getCache()->save($cacheKey,$results,$this->cacheTime);
        return $results;
    }

    public function getStarsBuilder($params)
    {
        $itemQuery = $this->getModelsManager()->createBuilder();
        $itemQuery->from(__CLASS__);
        if($params['userId']){
            $itemQuery->andWhere('userId = :userId:', array('userId' => $params['userId']));
        }

        if($params['postId']){
            $itemQuery->andWhere('postId = :postId:', array('postId' => $params['postId']));
        }
        $order = 'createdAt DESC';
        $itemQuery->orderBy($order);

        return $itemQuery;
    }

    public function importStars(array $stars, $userId)
    {
        $count = 0;
        foreach($stars as $star) {
            if (empty($star['postId']) || empty($star['createdAt'])) {
                throw new Exception\InvalidArgumentException('Star format incorrect');
            }
            $count++;
        }

        if ($count > 100) {
            throw new Exception\OutOfRangeException('Import too many stars');
        }

        $starEntities = array();
        foreach($stars as $starArray) {
            $postId = $starArray['postId'];
            if (!$post = Entities\Posts::findFirst("id = $postId")) {
                continue;
            }
            if ($star = Entities\Stars::findFirst("postId = $postId AND userId = $userId")) {
                $starEntities[] = $star;
                continue;
            }
            $star = new Entities\Stars();
            $star->userId = $userId;
            $star->postId = $postId;
            $star->createdAt = $starArray['createdAt'];
            if (!$star->save()) {
                throw new Exception\RuntimeException('Create post star failed');
            }
            $starEntities[] = $star;
        }
        return $starEntities;
    }
}
