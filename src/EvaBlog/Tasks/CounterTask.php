<?php

namespace Eva\EvaBlog\Tasks;

// +----------------------------------------------------------------------
// | [phalcon]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-08-27 15:48
// +----------------------------------------------------------------------
// + CounterTask.php  文章计数器相关任务
// +----------------------------------------------------------------------

use Eva\CounterRank\Utils\CounterRankUtil;
use Eva\EvaBlog\Models\Post;
use mr5\CounterRank\CounterIterator;
use Eva\EvaEngine\Tasks\TaskBase;
use Phalcon\Mvc\Model\Query;

class CounterTask extends TaskBase
{
    public function mainAction($params)
    {
        $this->persistAction($params);
    }

    public function persistAction($params)
    {

        $counterRank = new CounterRankUtil();
        $counterRank = $counterRank->getCounterRank('posts');
        $post = new Post();
        $count = 0;
        $tableName = $post->getSource();
        foreach ($counterRank->getIterator(100, CounterIterator::PERSIST_WITH_DELETING) as $items) {
            $values = '';
            $count += count($items);
            $ids = '';
            foreach ($items as $post_id => $heat) {

                if ($ids != '') {
                    $ids .= ',';
                }
                $ids .= $post_id;
                $values .= " WHEN id={$post_id} THEN `count`+{$heat} ";
//                    $values .= "({$post_id}, {$heat}, '', 'private', '', 0)";
            }
            $sql = <<<SQL
UPDATE {$tableName} SET `count` = CASE
    {$values}
    ELSE `count`
END
WHERE `id` IN({$ids})
SQL;
            $post->getWriteConnection()->execute($sql);
        }
        $this->output->writelnComment('Done! Persist ' . $count . ' items;');
    }
}
