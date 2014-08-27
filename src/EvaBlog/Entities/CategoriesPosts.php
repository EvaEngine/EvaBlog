<?php

namespace Eva\EvaBlog\Entities;

class CategoriesPosts extends \Eva\EvaEngine\Mvc\Model
{
    protected $tableName = 'blog_categories_posts';

    /**
     *
     * @var integer
     */
    public $categoryId;

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
            'categoryId' => 'categoryId',
            'postId' => 'postId'
        );
    }

    public function initialize()
    {
        $this->belongsTo(
            'categoryId', 'Eva\EvaBlog\Entities\Categories', 'id',
            array('alias' => 'category')
        );
        $this->belongsTo(
            'postId', 'Eva\EvaBlog\Entities\Posts', 'id',
            array('alias' => 'post')
        );
    }
}
