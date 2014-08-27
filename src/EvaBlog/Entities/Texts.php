<?php

namespace Eva\EvaBlog\Entities;

class Texts extends \Eva\EvaEngine\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $postId;

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

    /**
     * Independent Column Mapping.
     */
    public function columnMap()
    {
        return array(
            'postId' => 'postId',
            'metaKeywords' => 'metaKeywords',
            'metaDescription' => 'metaDescription',
            'toc' => 'toc',
            'content' => 'content'
        );
    }

    protected $tableName = 'blog_texts';

    public function initialize()
    {
        $this->belongsTo('postId', 'Eva\EvaBlog\Entities\Posts', 'id', array(
            'alias' => 'Post'
        ));
        parent::initialize();
    }
}
