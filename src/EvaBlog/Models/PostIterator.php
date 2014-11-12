<?php

namespace Eva\EvaBlog\Models;

// +----------------------------------------------------------------------
// | [phalcon]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-8-15 13:59
// +----------------------------------------------------------------------
// + PostIterator.php
// +----------------------------------------------------------------------
use Eva\EvaBlog\Entities\CategoriesPosts;
use Eva\EvaBlog\Entities\Tags;
use Eva\EvaBlog\Entities\TagsPosts;
use Eva\EvaBlog\Entities\Texts;
use Eva\EvaBlog\Entities\Votes;
use Eva\EvaEngine\IoC;
use Phalcon\Db;

/**
 * Post 的 foreach 迭代器，可以使用 foreach 语法迭代出所有 Post
 *
 * Class PostIterator
 * @package Eva\EvaBlog\Models
 */
class PostIterator implements \Iterator
{
    protected $position = 0;
    protected $total_items = 0;
    protected $perSize = 100;
    protected $endPosition = 0;
    protected $postsTableName;
    protected $textsTableName;
    protected $votesTableName;
    protected $tagsTableName;
    protected $categoriesTableName;
    protected $sql;
    /**
     * @var \Phalcon\Db\AdapterInterface
     */
    private $dbConnection;
    /**
     * @var Post
     */
    private $post;

    /**
     *
     * @param int $perSize 每次迭代的数据条数
     * @param bool $withRelations 是否带上关联查询
     * @param int $limit 总条数限制
     */
    public function __construct($perSize = 100, $withRelations = true, $limit = 0)
    {
        $this->post = new Post();
        $this->perSize = $perSize;
        $this->total_items = $this->post->count();
        if ($limit > 0) {
            $this->total_items = max($this->total_items, $limit);
        }
        $this->postsTableName = $this->post->getSource();
        $this->dbConnection = $this->post->getReadConnection();
        $this->textsTableName = with(new Texts())->getSource();
        $this->votesTableName = with(new Votes())->getSource();
        $this->tagsPostsTableName = with(new TagsPosts())->getSource();
        $this->tagsTableName = with(new Tags())->getSource();

        $this->categoriesTableName = with(new CategoriesPosts())->getSource();
        $this->endPosition = ceil($this->total_items / $this->perSize);
        if ($withRelations) {
            $this->sql = <<<SQL
SELECT
	post.*,
	vote.upVote,
	vote.downVote,
	(SELECT GROUP_CONCAT(`categoryId`) FROM `{$this->categoriesTableName}` WHERE postId=post.id) as categoryIds,
	(SELECT GROUP_CONCAT(`tagName`) FROM  `{$this->tagsTableName}`  WHERE id IN (SELECT `tagId` FROM `{$this->tagsPostsTableName}` WHERE `postId`=post.id)) as tagNames,
    text.content
FROM `{$this->postsTableName}` as post
LEFT JOIN `{$this->textsTableName}` as text
	ON text.postId=post.id
LEFT JOIN `{$this->votesTableName}` as vote
  ON vote.postId=post.id
SQL;
        } else {
            $this->sql = "SELECT * FROM {$this->postsTableName}";
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        $limit_start = $this->position * $this->perSize;
        $this->dbConnection->connect();
        $result = $this->dbConnection->query(
            $this->sql . " LIMIT {$limit_start},{$this->perSize}"
        );
        $result->setFetchMode(Db::FETCH_ASSOC);
        return $result->fetchAll();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->position <= $this->endPosition;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->position = 0;
    }
}
