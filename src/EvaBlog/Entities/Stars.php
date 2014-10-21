<?php

namespace Eva\EvaBlog\Entities;

/**
 * @package
 * @category
 * @subpackage
 *
 * @SWG\Model(id="StarPost")
 */
class Stars extends \Eva\EvaEngine\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $userId;

    /**
     * @SWG\Property(
     *   name="postId",
     *   type="integer",
     *   description="Post ID"
     * )
     * @var integer
     */
    public $postId;

    /**
     * @SWG\Property(
     *   name="createdAt",
     *   type="integer",
     *   description="Stared time"
     * )
     * @var integer
     */
    public $createdAt = 0;


    protected $tableName = 'blog_stars';

    public function initialize()
    {
        $this->hasOne('postId', 'Eva\EvaBlog\Entities\Posts', 'id', array(
            'alias' => 'post'
        ));
    }
}
