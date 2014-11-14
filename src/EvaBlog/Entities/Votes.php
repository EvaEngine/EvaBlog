<?php
namespace Eva\EvaBlog\Entities;

class Votes extends \Eva\EvaEngine\Mvc\Model
{

    const TYPE_UP = 'upVote';

    const TYPE_DOWN = 'downVote';

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

    public static $defaultDump = array(
        'postId',
        'upVote',
        'downVote',
        'lastVotedAt',
    );

    public function onConstruct()
    {
        $this->upVote = 0;
        $this->downVote = 0;
    }

}
