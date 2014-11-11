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


    public $cachePrefix = 'eva_blog_stars_';

    public $cacheTime = 86400;  //一天

    public function getCache()
    {
        /** @var \Phalcon\Cache\Backend\Libmemcached $cache */
        $cache =  $this->getDI()->get('modelsCache');
        return $cache;
    }

    public function createCacheKey($params){
        ksort($params);
        $str = $this->cachePrefix;
        foreach($params as $k=>$v){
            $str .= $k.'_'.$v.'_';
        }

        return $str;
    }

    public function refreshCache($params)
    {
        $cacheKey = $this->createCacheKey($params);
        if($this->getCache()->exists($cacheKey)){
            $this->getCache()->delete($cacheKey);
        }
    }


    public function afterSave()
    {
        $this->refreshCache(array('userId'=>$this->userId));
        $this->refreshCache(array('postId'=>$this->userId));
        $this->refreshCache(array('userId'=>$this->userId,'postId'=>$this->postId));
    }

    public function afterDelete()
    {
        $this->refreshCache(array('userId'=>$this->userId));
        $this->refreshCache(array('postId'=>$this->userId));
        $this->refreshCache(array('userId'=>$this->userId,'postId'=>$this->postId));
    }

    public function initialize()
    {
        $this->hasOne('postId', 'Eva\EvaBlog\Entities\Posts', 'id', array(
            'alias' => 'post'
        ));

        $this->hasOne('userId', 'Eva\EvaUser\Entities\Users', 'id', array(
            'alias' => 'user'
        ));
    }
}
