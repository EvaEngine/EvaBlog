<?php

namespace Eva\EvaBlog\Models;

use Eva\EvaBlog\Entities;
use Eva\EvaBlog\Models\Post;

class Tag extends Entities\Tags
{

    public function getPopularTags($limit = 10)
    {
        /*
        $tags = $this->getModelsManager()->createBuilder()
            ->from(__CLASS__)
            ->columns(array(
                'id', 'tagName', 'COUNT(id) AS tagCount'
            ))
            ->leftJoin('Eva\EvaBlog\Entities\TagsPosts', 'id = r.tagId', 'r')
            ->groupBy('id')
            ->orderBy('COUNT(id) DESC')
            ->limit($limit)
            ->getQuery()
            ->execute();
        */

        $tags = self::find(array(
            'order' => 'count DESC',
            'limit' => $limit,
            'cache' => array(
                "lifetime" => 3600 * 24,
                "key" => "popular-tags-$limit"
            )
        ));
        return $tags;
    }

    public function getRelatedPosts($postId, $limit = 10)
    {
        $phql = <<<QUERY
SELECT B.postId, B.tagId, SUM( LOG( 100 / C.count ) ) AS weight
FROM Eva\EvaBlog\Entities\TagsPosts AS A
LEFT JOIN Eva\EvaBlog\Entities\TagsPosts AS B ON A.tagId = B.tagId
LEFT JOIN Eva\EvaBlog\Entities\Tags AS C ON B.tagId = C.id
WHERE A.postId = $postId
AND B.postId != $postId
GROUP BY B.postId
ORDER BY weight DESC
LIMIT $limit
QUERY;
        $manager = $this->getModelsManager();
        $query = $manager->createQuery($phql);
        $results = $query->execute();
        $posts = null;
        $idArray = array();
        if ($results->count() > 0) {
            foreach ($results as $result) {
                $idArray[] = $result->postId;
            }
            $postModel = new Post();
            $postsQueryBuilder = $postModel->findPosts(array(
                'id' => implode(',', $idArray)
            ));
            $posts = $postsQueryBuilder->getQuery()
            ->execute();
        }
        return $posts;
    }

    public function updateTagCount()
    {
        $phql = <<<QUERY
UPDATE Eva\EvaBlog\Entities\Tags SET count = 
     ( SELECT COUNT(tagId) FROM Eva\EvaBlog\Entities\TagsPosts 
             WHERE Eva\EvaBlog\Entities\TagsPosts.tagId = Eva\EvaBlog\Entities\Tags.id 
     )
QUERY;
        $manager = $this->getModelsManager();
        $query = $manager->createQuery($phql);
        return $results = $query->execute();
    }
}
