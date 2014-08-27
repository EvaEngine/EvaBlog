<?php

namespace Eva\EvaBlog\Models;

use Eva\EvaBlog\Entities;
use Eva\EvaFileSystem\Models\Upload as UploadModel;
use Eva\EvaEngine\Exception;

class Category extends Entities\Categories
{
    public function beforeValidationOnCreate()
    {
        $this->createdAt = time();
        if (!$this->slug) {
            $this->slug = \Phalcon\Text::random(\Phalcon\Text::RANDOM_ALNUM, 8);
        }
    }

    public function beforeCreate()
    {
        if (!$this->parentId) {
            $this->parentId = 0;
            $this->rootId = 0;
        } else {
            $parentCategory = self::findFirst($this->parentId);
            if ($parentCategory) {
                if ($parentCategory->parentId) {
                    $this->rootId = $parentCategory->rootId;
                } else {
                    $this->rootId = $parentCategory->id;
                }
            } else {
                $this->rootId = 0;
            }
        }
    }

    public function beforeUpdate()
    {
        /*
        //not allow set self to parent
        if (!$this->parentId || $this->parentId == $this->id) {
            $this->parentId = 0;
            $this->rootId = 0;
        } else {
            $parentCategory = self::findFirst($this->parentId);
            if ($parentCategory) {
                if (
                    $parentCategory->rootId == $this->id  //not allow move to child node
                    || $parentCategory->rootId == $this->rootId
                ) {
                    throw new Exception\InvalidArgumentException('ERR_BLOG_CATEGORY_NOT_ALLOW_MOVE');

                } else {
                    if ($parentCategory->parentId) {
                        $this->rootId = $parentCategory->rootId;
                    } else {
                        $this->rootId = $parentCategory->id;
                    }
                }

            } else {
                $this->rootId = 0;
            }
        }
        */
    }

    public function createCategory()
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
                $this->image = $file->getFullUrl();
            }
        }
        $this->save();
    }

    public function updateCategory()
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
                $this->image = $file->getFullUrl();
            }
        }
        $this->save();
    }
}
