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

    public function getStars($userId)
    {
        $itemQuery = $this->getDI()->getModelsManager()->createBuilder();
        $itemQuery->from(__CLASS__);
        $itemQuery->andWhere('userId = :userId:', array('userId' => $userId));
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
