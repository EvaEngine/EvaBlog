<?php

namespace Eva\EvaBlog\Models;

use Eva\EvaBlog\Entities;
use Eva\EvaUser\Models\Login as LoginModel;
use Eva\EvaFileSystem\Models\Upload as UploadModel;
use Eva\EvaEngine\Exception;
use Eva\EvaEngine\Mvc\Model\Validator\Uniqueness;
use Eva\CounterRank\utils\CounterRankUtil;

class Post extends Entities\Posts
{
    public static $simpleDump = array(
        'id',
        'title',
        'slug',
        'type',
        'codeType',
        'createdAt',
        'summary',
        'summaryHtml' => 'getSummaryHtml',
        'commentStatus',
        'sourceName',
        'sourceUrl',
        'count',
        'commentCount',
        'url' => 'getUrl',
        'imageUrl' => 'getImageUrl',
        'tags' => array(
            'id',
            'tagName',
        ),
        'user' => array(
            'id',
            'username',
            'screenName',
        ),
    );

    public static $defaultDump = array(
        'id',
        'title',
        'slug',
        'type',
        'codeType',
        'createdAt',
        'summary',
        'summaryHtml' => 'getSummaryHtml',
        'commentStatus',
        'sourceName',
        'sourceUrl',
        'count',
        'url' => 'getUrl',
        'imageUrl' => 'getImageUrl',
        'content' => 'getContentHtml',
        'text' => array(
            'content',
        ),
        'tags' => array(
            'id',
            'tagName',
        ),
        'categories' => array(
            'id',
            'categoryName',
        ),
        'user' => array(
            'id',
            'username',
            'screenName',
            'avatar',
        ),
    );

    public function beforeValidationOnCreate()
    {
        $this->createdAt = $this->createdAt ? $this->createdAt : time();
        if (!$this->slug) {
            $this->slug = \Phalcon\Text::random(\Phalcon\Text::RANDOM_ALNUM, 8);
        }

        $this->validate(
            new Uniqueness(array(
                'field' => 'slug'
            ))
        );
    }

    public function beforeValidationOnUpdate()
    {
        $this->validate(
            new Uniqueness(array(
                'field' => 'slug',
                'conditions' => 'id != :id:',
                'bind' => array(
                    'id' => $this->id
                ),
            ))
        );
    }


    public function beforeCreate()
    {
        if ($userinfo = LoginModel::getCurrentUser()) {
            $this->userId = $this->userId ? $this->userId : $userinfo['id'];
            $this->username = $this->username ? $this->username : $userinfo['username'];
        }
    }

    public function afterCreate()
    {
        $this->getDI()->getEventsManager()->fire('blog:afterCreate', $this);
    }

    public function afterSave()
    {
        $this->getDI()->getEventsManager()->fire('blog:afterSave', $this);
    }

    public function beforeUpdate()
    {
        if ($userinfo = LoginModel::getCurrentUser()) {
            $this->editorId = $userinfo['id'];
            $this->editorName = $userinfo['username'];
        }

        $this->updatedAt = time();
    }

    public function beforeSave()
    {

        if ($this->getDI()->getRequest()->hasFiles()) {
            $upload = new UploadModel();
            $files = $this->getDI()->getRequest()->getUploadedFiles();
            if (!$files) {
                return;
            }
            $file = $files[0];
            $file = $upload->upload($file);
            if ($file) {
                $this->imageId = $file->id;
                $this->image = $file->getLocalUrl();
            }
        }
    }

    public function validation()
    {
        if ($this->validationHasFailed() == true) {
            return false;
        }
    }

    /**
     * @param array $query
     * @return \Phalcon\Mvc\Model\Query\BuilderInterface
     */
    public function findPosts(array $query = array())
    {
        $itemQuery = $this->getDI()->getModelsManager()->createBuilder();

        $itemQuery->from(__CLASS__);

        $orderMapping = array(
            'id' => 'id ASC',
            '-id' => 'id DESC',
            'created_at' => 'createdAt ASC',
            '-created_at' => 'createdAt DESC',
            'sort_order' => 'sortOrder ASC',
            '-sort_order' => 'sortOrder DESC',
            'count' => 'count ASC',
            '-count' => 'count DESC',
        );

        if (!empty($query['alias'])) {
            $methodName = 'alias' . ucfirst($query['alias']);
            if (method_exists($this, $methodName)) {
                $alias_query = $this->$methodName();
                $query = array_merge($query, $alias_query);
            }
        }

        if (!empty($query['columns'])) {
            $itemQuery->columns($query['columns']);
        }

        if (!empty($query['q'])) {
            $itemQuery->andWhere('title LIKE :q:', array('q' => "%{$query['q']}%"));
        }

        if (!empty($query['id'])) {
            $idArray = explode(',', $query['id']);
            $itemQuery->inWhere('id', $idArray);
        }

        if (!empty($query['type'])) {
            $typeArray = explode(',', $query['type']);
            if (count($typeArray) > 1) {
                $itemQuery->inWhere('type', $typeArray);
            } else {
                $itemQuery->andWhere('type = :type:', array('type' => $query['type']));
            }
        }

        if (!empty($query['flag'])) {
            $itemQuery->andWhere('flag = :flag:', array('flag' => $query['flag']));
        }

        if (!empty($query['status'])) {
            $itemQuery->andWhere('status = :status:', array('status' => $query['status']));
        }

        if (!empty($query['has_image'])) {
            $itemQuery->andWhere('imageId > 0');
        }

        if (!empty($query['min_created_at'])) {
            $itemQuery->andWhere('createdAt > :minCreatedAt:', array('minCreatedAt' => $query['min_created_at']));
        }

        if (!empty($query['max_created_at'])) {
            $itemQuery->andWhere('createdAt < :maxCreatedAt:', array('maxCreatedAt' => $query['max_created_at']));
        }

        if (!empty($query['source_name'])) {
            $itemQuery->andWhere('sourceName = :sourceName:', array('sourceName' => $query['source_name']));
        }

        if (!empty($query['uid'])) {
            $itemQuery->andWhere('userId = :uid:', array('uid' => $query['uid']));
        }

        if (!empty($query['cid'])) {
            $itemQuery->join('Eva\EvaBlog\Entities\CategoriesPosts', 'id = _cate.postId', '_cate')
                ->andWhere('_cate.categoryId = :cid:', array('cid' => $query['cid']));
        }

        if (!empty($query['tag'])) {
            $tag = Entities\Tags::findFirst("tagName = '{$query['tag']}'");
            if ($tag) {
                $query['tid'] = $tag->id;
            } else {
                $query['tid'] = -1;
            }
        }

        if (!empty($query['tid'])) {
            $tidArray = explode(',', $query['tid']);
            if (count($tidArray) > 1) {
                $itemQuery->join('Eva\EvaBlog\Entities\TagsPosts', 'id = _tag.postId', '_tag')
                    ->inWhere('_tag.tagId', $tidArray);
            } else {
                $itemQuery->join('Eva\EvaBlog\Entities\TagsPosts', 'id = _tag.postId', '_tag')
                    ->andWhere('_tag.tagId = :tid:', array('tid' => $query['tid']));
            }

        }

        $order = 'createdAt DESC';
        if (!empty($query['order'])) {
            $orderArray = explode(',', $query['order']);
            if (count($orderArray) > 1) {
                $order = array();
                foreach ($orderArray as $subOrder) {
                    if ($subOrder && !empty($orderMapping[$subOrder])) {
                        $order[] = $orderMapping[$subOrder];
                    }
                }
            } else {
                $order = empty($orderMapping[$orderArray[0]]) ? array('createdAt DESC') : array($orderMapping[$query['order']]);
            }

            //Add default order as last order
            array_push($order, 'createdAt DESC');
            $order = array_unique($order);
            $order = implode(', ', $order);
        }
        $itemQuery->orderBy($order);
        return $itemQuery;
    }

    /**
     * 热门新闻
     *
     * @return array
     */
    public function aliasHotNews()
    {
        return array(
            'min_created_at' => strtotime('-2 days'),
            'order' => '-count'
        );
    }

    public function createPost(array $data)
    {
        $textData = isset($data['text']) ? $data['text'] : array();
        $tagData = isset($data['tags']) ? $data['tags'] : array();
        $categoryData = isset($data['categories']) ? $data['categories'] : array();
        $connectionData = isset($data['connectids']) ? $data['connectids'] : array();

        if ($textData) {
            unset($data['text']);
            $text = new Text();
            $text->assign($textData);
            $this->text = $text;
        }

        $tags = array();
        if ($tagData) {
            unset($data['tags']);
            $tagArray = is_array($tagData) ? $tagData : explode(',', $tagData);
            foreach ($tagArray as $tagName) {
                $tag = Entities\Tags::findFirst(
                    array(
                        "conditions" => "tagName = :tagName:",
                        "bind" => array('tagName' => $tagName)
                    )
                );
                if (!$tag) {
                    $tag = new Entities\Tags();
                    $tag->tagName = $tagName;
                }
                $tags[] = $tag;
            }
            if ($tags) {
                $this->tags = $tags;
            }
        }

        $categories = array();
        if ($categoryData) {
            unset($data['categories']);
            foreach ($categoryData as $categoryId) {
                $category = Category::findFirst($categoryId);
                if ($category) {
                    $categories[] = $category;
                }
            }
            $this->categories = $categories;
        }

        $connections = array();
        //remove old relations
        if ($this->postConnects) {
            $this->postConnects->delete();
        }
        if ($connectionData) {
            unset($data['connectids']);
            foreach ($connectionData as $connectionId) {
                $connection = new Entities\Connections();
                $connection->sourceId = $this->id;
                $connection->targetId = $connectionId;
                $connection->createdAt = time();
                $connections[] = $connection;
            }
            $this->postConnects = $connections;
        }


        $this->assign($data);
        if (!$this->save()) {
            throw new Exception\RuntimeException('Create post failed');
        }

        return $this;
    }

    public function updatePost($data)
    {
        $textData = isset($data['text']) ? $data['text'] : array();
        $tagData = isset($data['tags']) ? $data['tags'] : array();
        $categoryData = isset($data['categories']) ? $data['categories'] : array();
        $connectionData = isset($data['connectids']) ? $data['connectids'] : array();

        if ($textData) {
            unset($data['text']);
            $text = new Text();
            $text->assign($textData);
            $this->text = $text;
        }


        $tags = array();
        //remove old relations
        if ($this->tagsPosts) {
            $this->tagsPosts->delete();
        }
        if ($tagData) {
            unset($data['tags']);
            $tagArray = is_array($tagData) ? $tagData : explode(',', $tagData);
            foreach ($tagArray as $tagName) {
                $tag = Entities\Tags::findFirst(
                    array(
                        "conditions" => "tagName = :tagName:",
                        "bind" => array('tagName' => $tagName)
                    )
                );
                if (!$tag) {
                    $tag = new Entities\Tags();
                    $tag->tagName = $tagName;
                }
                $tags[] = $tag;
            }
            if ($tags) {
                $this->tags = $tags;
            }
        }

        //remove old relations
        $categories = array();
        if ($this->categoriesPosts) {
            $this->categoriesPosts->delete();
        }
        if ($categoryData) {
            unset($data['categories']);
            foreach ($categoryData as $categoryId) {
                $category = Category::findFirst($categoryId);
                if ($category) {
                    $categories[] = $category;
                }
            }
            $this->categories = $categories;
        }

        $connections = array();
        //remove old relations
        if ($this->postConnects) {
            $this->postConnects->delete();
        }
        if ($connectionData) {
            unset($data['connectids']);
            foreach ($connectionData as $connectionId) {
                $connection = new Entities\Connections();
                $connection->sourceId = $this->id;
                $connection->targetId = $connectionId;
                $connection->createdAt = time();
                $connections[] = $connection;
            }
            $this->postConnects = $connections;
        }

        $this->assign($data);
        if (!$this->save()) {
            throw new Exception\RuntimeException('Update post failed');
        }

        return $this;
    }

    public function removePost($id)
    {
        $this->id = $id;
        //remove old relations
        if ($this->tagsPosts) {
            $this->tagsPosts->delete();
        }
        //remove old relations
        if ($this->categoriesPosts) {
            $this->categoriesPosts->delete();
        }
        $this->text->delete();
        $this->delete();
    }

    /*
     * 判断post是否属于category分类
     */
    public function hasCategory($categoryId)
    {
        $postId = $this->id;

        $categoriesPosts = $this->getModelsManager()->executeQuery("SELECT * FROM Eva\EvaBlog\Entities\CategoriesPosts AS c WHERE c.postId=$postId AND c.categoryId=$categoryId LIMIT 1");

        foreach ($categoriesPosts as $categoriesPost) {
            return true;
        }

        return false;
    }
}
