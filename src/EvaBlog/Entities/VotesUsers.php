<?php

namespace Eva\EvaBlog\Entities;

class VotesUsers extends \Eva\EvaEngine\Mvc\Model
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
    public $userId;

    /**
     *
     * @var string
     */
    public $voteType;

    /**
     *
     * @var integer
     */
    public $createdAt;

    protected $tableName = 'blog_votes_users';
}
