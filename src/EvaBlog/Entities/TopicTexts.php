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

class TopicTexts extends Model
{
    protected $tableName = 'blog_topic_texts';

    /**
     *
     * @var integer
     */
    public $topicId;

    /**
     *
     * @var string
     */
    public $metaKeywords;

    /**
     *
     * @var string
     */
    public $metaDescription;

    /**
     *
     * @var string
     */
    public $toc;

    /**
     *
     * @var string
     */
    public $content;

    public function initialize()
    {
        $this->belongsTo(
            'topicId',
            'Eva\EvaBlog\Entities\Topics',
            'id',
            array('alias' => 'topic')
        );

        parent::initialize();
    }
}
