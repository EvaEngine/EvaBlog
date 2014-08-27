<?php

namespace Eva\EvaBlog\Entities;

class TagsPosts extends \Eva\EvaEngine\Mvc\Model
{
    protected $tableName = 'blog_tags_posts';

    /**
     *
     * @var integer
     */
    public $tagId;

    /**
     *
     * @var integer
     */
    public $postId;

    /**
     * Independent Column Mapping.
     */
    public function columnMap()
    {
        return array(
            'tagId' => 'tagId',
            'postId' => 'postId'
        );
    }

    public function initialize()
    {
        $this->belongsTo(
            'tagId', 'Eva\EvaBlog\Entities\Tags', 'id',
            array('alias' => 'Tag')
        );
        $this->belongsTo(
            'postId', 'Eva\EvaBlog\Entities\Posts', 'id',
            array('alias' => 'Post')
        );
    }
}
