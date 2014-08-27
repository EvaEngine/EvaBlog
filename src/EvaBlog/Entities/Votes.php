<?php

namespace Eva\EvaBlog\Entities;

class Votes extends \Eva\EvaEngine\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $postId;

    /**
     *
     * @var integer
     */
    public $upVote;

    /**
     *
     * @var integer
     */
    public $downVote;

    /**
     *
     * @var integer
     */
    public $lastVotedAt;

    protected $tableName = 'blog_votes';
}
