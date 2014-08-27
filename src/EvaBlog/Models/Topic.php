<?php

namespace Eva\EvaBlog\Models;

// +----------------------------------------------------------------------
// | [phalcon]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-8-20 10:31
// +----------------------------------------------------------------------

use Eva\EvaBlog\Entities\Tags;
use Eva\EvaBlog\Entities\Topics;
use Eva\EvaBlog\Entities\TopicTexts;
use Eva\EvaEngine\Exception;

use Eva\EvaEngine\Mvc\Model;
use Eva\EvaUser\Models\Login as LoginModel;
use Eva\EvaFileSystem\Models\Upload as UploadModel;

class Topic extends Topics
{
    public function beforeCreate()
    {
        $this->createdAt = $this->updatedAt = time();

        $user = new LoginModel();
        if ($userinfo = $user->isUserLoggedIn()) {
            $this->userId = $this->userId ? $this->userId : $userinfo['id'];
            $this->username = $this->username ? $this->username : $userinfo['username'];
        }
    }

    public function beforeUpdate()
    {
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

    public function createTopic($data)
    {
        $textData = isset($data['text']) ? $data['text'] : array();
        $tagData = isset($data['tags']) ? $data['tags'] : array();
//        $categoryData = isset($data['categories']) ? $data['categories'] : array();

        if ($textData) {
            unset($data['text']);
            $text = new TopicTexts();
            $text->assign($textData);
            $this->text = $text;
        }

        $tags = array();
        if ($tagData) {
            unset($data['tags']);
            $tagArray = is_array($tagData) ? $tagData : explode(',', $tagData);
            foreach ($tagArray as $tagName) {
                $tag = Tags::findFirst(
                    array(
                        "conditions" => "tagName = :tagName:",
                        "bind" => array('tagName' => $tagName)
                    )
                );
                if (!$tag) {
                    $tag = new Tags();
                    $tag->tagName = $tagName;
                }
                $tags[] = $tag;
            }
            if ($tags) {
                $this->tags = $tags;
            }
        }


        $this->assign($data);
        $this->createdAt = $this->updatedAt = time();

        if (!$this->save()) {
            throw new Exception\RuntimeException('Create topic failed');
        }

        return $this;
    }

    /**
     * 检测 slug 是否存在
     *
     * @param string $slug slug 字符串
     * @param int $topicId 专题 ID，用于修改的时候的重复性判断。
     * @return bool
     */
    public static function checkSlugExists($slug, $topicId = 0)
    {
        $topic = Topic::findFirst("slug='{$slug}'");
        return $topic != null && $topic->id != $topicId;
    }
}
