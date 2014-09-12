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
}
