<?php

namespace Eva\EvaBlog\ViewHelpers;

// +----------------------------------------------------------------------
// | [phalcon]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-8-26 15:56
// +----------------------------------------------------------------------

use Eva\EvaBlog\Entities\Posts;
use Eva\EvaBlog\Models\Post;

class PostUrl
{
    public function __invoke($post)
    {
        if ($post instanceof Posts) {
            return $post->getUrl();
        }
        return Post::getUrlByPostArr($post);
    }
}