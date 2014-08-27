<?php

namespace Eva\EvaBlog\Entities;

class Tags extends \Eva\EvaEngine\Mvc\Model
{
    protected $tableName = 'blog_tags';

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $tagName;

    /**
     *
     * @var integer
     */
    public $parentId = 0;

    /**
     *
     * @var integer
     */
    public $rootId = 0;

    /**
     *
     * @var integer
     */
    public $sortOrder = 0;

    /**
     *
     * @var integer
     */
     public $count = 0;

    /**
     * Independent Column Mapping.
     */
    public function columnMap()
    {
        return array(
            'id' => 'id',
            'tagName' => 'tagName',
            'parentId' => 'parentId',
            'rootId' => 'rootId',
            'sortOrder' => 'sortOrder',
            'count' => 'count'
        );
    }

    public function initialize()
    {
        $this->hasManyToMany(
            'id',
            'Eva\EvaBlog\Entities\PostsTags',
            'tagId',
            'postId',
            'Eva\EvaBlog\Entities\Posts',
            'id',
            array('alias' => 'Posts')
        );

    }
}
