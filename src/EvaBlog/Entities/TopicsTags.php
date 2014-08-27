<?php

namespace Eva\EvaBlog\Entities;

// +----------------------------------------------------------------------
// | [phalcon]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-8-20 10:22
// +----------------------------------------------------------------------

use Eva\EvaEngine\Mvc\Model;

class TopicsTags extends Model
{
    protected $tableName = 'blog_topics_tags';

    public $topicId;
    public $tagId;

    public function initialize()
    {
        $this->belongsTo(
            'tagId',
            'Eva\EvaBlog\Entities\Tags',
            'id',
            array('alias' => 'tag')
        );
        $this->belongsTo(
            'topicId',
            'Eva\EvaBlog\Entities\Topics',
            'id',
            array('alias' => 'topic')
        );
        parent::initialize();
    }
}
