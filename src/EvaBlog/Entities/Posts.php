<?php

namespace Eva\EvaBlog\Entities;

use Eva\EvaBlog\Entities\Texts;
use Eva\EvaEngine\IoC;
use Eva\EvaFileSystem\ViewHelpers\ThumbWithClass;
use Phalcon\Text;

class Posts extends \Eva\EvaEngine\Mvc\Model
{
    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $title;

    /**
     *
     * @var string
     */
    public $status = 'pending';

    /**
     *
     * @var string
     */
    public $flag;

    /**
     *
     * @var string
     */
    public $visibility = 'public';

    /**
     *
     * @var string
     */
    public $type = 'news';

    /**
     *
     * @var string
     */
    public $codeType = 'html';

    /**
     *
     * @var string
     */
    public $language;

    /**
     *
     * @var integer
     */
    public $parentId = 0;

    /**
     *
     * @var string
     */
    public $slug;

    /**
     *
     * @var integer
     */
    public $sortOrder = 0;

    /**
     *
     * @var integer
     */
    public $createdAt;

    /**
     *
     * @var integer
     */
    public $userId = 0;

    /**
     *
     * @var string
     */
    public $username;

    /**
     *
     * @var integer
     */
    public $updatedAt = 0;

    /**
     *
     * @var integer
     */
    public $editorId = 0;

    /**
     *
     * @var string
     */
    public $editorName;

    /**
     *
     * @var string
     */
    public $commentStatus = 'open';

    /**
     *
     * @var string
     */
    public $commentType;

    /**
     *
     * @var integer
     */
    public $commentCount = 0;

    /**
     *
     * @var integer
     */
    public $count = 1;

    /**
     *
     * @var integer
     */
    public $imageId = 0;

    /**
     *
     * @var string
     */
    public $image;

    /**
     *
     * @var string
     */
    public $summary;

    /**
     *
     * @var string
     */
    public $sourceName;

    /**
     *
     * @var string
     */
    public $sourceUrl;

    /**
     *
     * @var decimal
     */
    public $voteScore = 0;

    protected $tableName = 'blog_posts';

    public function initialize()
    {
        $this->hasOne(
            'id',
            'Eva\EvaBlog\Entities\Texts',
            'postId',
            array(
                'alias' => 'text'
            )
        );

        $this->hasOne(
            'id',
            'Eva\EvaBlog\Entities\Votes',
            'postId',
            array(
                'alias' => 'vote'
            )
        );

        $this->hasOne(
            'id',
            'Eva\EvaBlog\Entities\Stars',
            'postId',
            array(
                'alias' => 'star'
            )
        );

        $this->belongsTo(
            'userId',
            'Eva\EvaUser\Entities\Users',
            'id',
            array(
                'alias' => 'user'
            )
        );

        $this->hasMany(
            'id',
            'Eva\EvaBlog\Entities\CategoriesPosts',
            'postId',
            array('alias' => 'categoriesPosts')
        );

        $this->hasManyToMany(
            'id',
            'Eva\EvaBlog\Entities\CategoriesPosts',
            'postId',
            'categoryId',
            'Eva\EvaBlog\Entities\Categories',
            'id',
            array('alias' => 'categories')
        );

        $this->hasMany(
            'id',
            'Eva\EvaBlog\Entities\TagsPosts',
            'postId',
            array('alias' => 'tagsPosts')
        );

        $this->hasManyToMany(
            'id',
            'Eva\EvaBlog\Entities\TagsPosts',
            'postId',
            'tagId',
            'Eva\EvaBlog\Entities\Tags',
            'id',
            array('alias' => 'tags')
        );

        $this->hasMany(
            'id',
            'Eva\EvaBlog\Entities\Connections',
            'sourceId',
            array('alias' => 'postConnects')
        );

        $this->hasManyToMany(
            'id',
            'Eva\EvaBlog\Entities\Connections',
            'sourceId',
            'targetId',
            'Eva\EvaBlog\Entities\Posts',
            'id',
            array('alias' => 'connections')
        );


        $this->hasOne(
            'imageId',
            'Eva\EvaFileSystem\Entities\Files',
            'id',
            array(
                'alias' => 'thumbnail'
            )
        );
        $this->setReadConnectionService('dbUserSlave');
        parent::initialize();
    }

    public function getTagString()
    {
        if (!$this->tags) {
            return '';
        }

        $tags = $this->tags;
        $tagArray = array();
        foreach ($tags as $tag) {
            $tagArray[] = $tag->tagName;
        }

        return implode(',', $tagArray);
    }

    public function getPrevPost()
    {
        if (!$this->id) {
            return false;
        }

        return self::findFirst(
            array(
                'conditions' => 'status = :status: AND createdAt < :createdAt:',
                'bind' => array(
                    'createdAt' => $this->createdAt,
                    'status' => 'published',
                ),
                'order' => 'createdAt DESC'
            )
        );
    }

    public function getNextPost()
    {
        if (!$this->id) {
            return false;
        }

        return self::findFirst(
            array(
                'conditions' => 'status = :status: AND createdAt > :createdAt:',
                'bind' => array(
                    'createdAt' => $this->createdAt,
                    'status' => 'published',
                ),
                'order' => 'createdAt ASC'
            )
        );
    }

    protected function replaceStaticFiles($contentHtml)
    {
        $thumbnail = $this->getDI()->getConfig()->thumbnail->default;
        $staticUri = $thumbnail->baseUri;

        $filesystemBaseUrl  = $this->getDI()->getConfig()->filesystem->default->baseUrl;

        if (!$thumbnail->enable || !$staticUri || false === strpos($contentHtml, '<img')) {
            return $contentHtml;
        }

        $contentHtml = preg_replace_callback(
            '/href="(.+?(png|jpg|jpeg|gif))(!article\.foil)?"/',
//            '/href="(\/.+(png|jpg|jpeg|gif))?"/',

            function ($matches) use ($staticUri) {
                $thumb = new ThumbWithClass();
                $imageUrl = $thumb->__invoke($matches[1], '');

                return 'target="_blank" href="' . $imageUrl . '"';
            },
            $contentHtml
        );

        $contentHtml = preg_replace_callback(
            '/src="(.+?(png|jpg|jpeg|gif))(!article\.foil)?"/',
//            '/src="(\/.+(png|jpg|jpeg|gif))?"/',

            function ($matches) use ($staticUri, $filesystemBaseUrl) {
                $thumb = new ThumbWithClass();
                if (starts_with($matches[1], 'http://') && (!starts_with($matches[1], $filesystemBaseUrl))) {
                    //站外资源不加缩略图后缀后缀 如 http://www.baidu.com/abc.jpg
                    $imageUrl = $thumb->__invoke($matches[1], '');
                } else {
                    //站内(posts.cdn.wallstcn.com)资源，添加缩略图后缀 article.foil
                    $imageUrl = $thumb->__invoke($matches[1], 'article.foil');
                }

                return 'src="' . $imageUrl . '"';
            },
            $contentHtml
        );

        return $contentHtml;
    }

    public function getSummaryHtml()
    {
        if (!$this->summary) {
            return '';
        }

        $html = '';
        if ($this->codeType == 'markdown') {
            $parsedown = new \Parsedown();
            $html = $parsedown->text($this->summary);
        } else {
            $html = $this->summary;
        }

        return $this->replaceStaticFiles($html);
    }

    public function getContentHtml()
    {
        if (empty($this->text->content)) {
            return '';
        }

        $html = '';
        if ($this->codeType == 'markdown') {
            $parsedown = new \Parsedown();
            $html = $parsedown->text($this->text->content);
        } else {
            $html = $this->text->content;
        }

        return $this->replaceStaticFiles($html);
    }

    public function getUrlPath()
    {
        $self = $this;

        return preg_replace_callback(
            '/{{(.+?)}}/',
            function ($matches) use ($self) {
                return empty($self->$matches[1]) ? '' : $self->$matches[1];
            },
            $this->getDI()->getConfig()->blog->postPath
        );
    }

    public function getUrl()
    {
        $url = $this->getDI()->getUrl();
        $self = $this;

        return $url->get($this->getUrlPath());
    }

    public function getAbsoluteUrl()
    {
        $postDomain = trim($this->getDI()->getConfig()->blog->postDomain);

        $postDomain = $postDomain && !preg_match('/http(s?):\/\//i',
            $postDomain) ? 'http://' . $postDomain : $postDomain;

        return $postDomain . $this->getUrlPath();
    }

    /**
     * 通过 Post 数组来生成 URL
     *
     * @param $post
     * @return mixed
     */
    public static function getUrlByPostArr($post)
    {
        return preg_replace_callback(
            '/{{(.+?)}}/',
            function ($matches) use ($post) {
                return empty($post[$matches[1]]) ? '' : $post[$matches[1]];
            },
            IoC::get('config')->blog->postPath
        );
    }

    public function getImageUrl($style = '')
    {
        if (!$this->image) {
            return null;
        }

        return $this->getImageUrlByUri($this->image, $style);

    }

    public function getImageUrlByUri($uri, $style = '')
    {
        if (!$uri) {
            return null;
        }
        $thumbWithClass = new ThumbWithClass();

        return $thumbWithClass->__invoke($uri, $style);
    }
}
