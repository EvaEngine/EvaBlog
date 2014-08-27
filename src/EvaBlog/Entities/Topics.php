<?php

namespace Eva\EvaBlog\Entities;

// +----------------------------------------------------------------------
// | [phalcon]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-8-20 10:21
// +----------------------------------------------------------------------
// + Topic.php
// +----------------------------------------------------------------------

use Eva\EvaEngine\Mvc\Model;

class Topics extends Model
{
    protected $tableName = 'blog_topics';

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $title;

    /**
     *
     * @var string
     */
    public $status = 'pending';


    /**
     *
     * @var string
     */
    public $codeType = 'html';


    /**
     *
     * @var string
     */
    public $slug;

    /**
     *
     * @var integer
     */
    public $sortOrder = 0;

    /**
     *
     * @var integer
     */
    public $createdAt;

    /**
     *
     * @var integer
     */
    public $userId = 0;

    /**
     *
     * @var string
     */
    public $username;

    /**
     *
     * @var integer
     */
    public $updatedAt = 0;

    /**
     *
     * @var integer
     */
    public $editorId = 0;


    /**
     *
     * @var string
     */
    public $commentStatus;


    /**
     *
     * @var integer
     */
    public $commentCount = 0;

    /**
     *
     * @var integer
     */
    public $count = 0;

    /**
     *
     * @var integer
     */
    public $imageId = 0;

    /**
     *
     * @var string
     */
    public $image;

    /**
     *
     * @var string
     */
    public $summary;
    /**
     *
     * @var float
     */
    public $voteScore = 0;

    public function initialize()
    {
        $this->hasOne(
            'id',
            'Eva\EvaBlog\Entities\TopicTexts',
            'topicId',
            array(
                'alias' => 'text'
            )
        );

        $this->belongsTo(
            'userId',
            'Eva\EvaUser\Entities\Users',
            'id',
            array(
                'alias' => 'user'
            )
        );


        $this->hasMany(
            'id',
            'Eva\EvaBlog\Entities\TopicsTags',
            'topicId',
            array('alias' => 'topicsTags')
        );

        $this->hasManyToMany(
            'id',
            'Eva\EvaBlog\Entities\TopicsTags',
            'topicId',
            'tagId',
            'Eva\EvaBlog\Entities\Tags',
            'id',
            array('alias' => 'tags')
        );


        $this->hasOne(
            'imageId',
            'Eva\EvaFileSystem\Entities\Files',
            'id',
            array(
                'alias' => 'thumbnail'
            )
        );

        parent::initialize();
    }

    public function getTagString()
    {
        if (!$this->tags) {
            return '';
        }

        $tags = $this->tags;
        $tagArray = array();
        foreach ($tags as $tag) {
            $tagArray[] = $tag->tagName;
        }

        return implode(',', $tagArray);
    }
}
