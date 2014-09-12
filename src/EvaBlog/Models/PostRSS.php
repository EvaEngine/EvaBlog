<?php

namespace Eva\EvaBlog\Models;

// +----------------------------------------------------------------------
// | [wallstreetcn]
// +----------------------------------------------------------------------
// | Author: Mr.5 <mr5.simple@gmail.com>
// +----------------------------------------------------------------------
// + Datetime: 14-8-28 11:09
// +----------------------------------------------------------------------

use Eva\EvaEngine\IoC;

class PostRSS
{
    private static $timeFormat = 'D, j M Y G:i:s P';

    public static function getFeedOutput($limit = 30)
    {
        $post = new Post();
        $posts = $post->findPosts(
            array(
                'type' => 'news',
                'order' => '-created_at',
                'status' => 'published',
            )
        );
        $items = '';
        $paginator = new \Eva\EvaEngine\Paginator(array(
            "builder" => $posts,
            "limit" => $limit,
            "page" => 1
        ));
        $pager = $paginator->getPaginate();
        $config = IoC::get('config');
        $baseUrl = $config->baseUri;
        $siteName = $config->siteName;
        foreach ($pager->items as $post) {
            $description = htmlspecialchars($post->summary);
            $pubdate = date(self::$timeFormat, $post->createdAt);
            $url = $baseUrl . $post->getUrl();
            $items .= <<<XML
\n<item>
    <title>{$post->title}</title>
    <link>{$url}</link>
    <description>{$description}</description>
    <pubDate>{$pubdate}</pubDate>
    <dc:creator>{$post->username}</dc:creator>
    <guid isPermaLink="false">{$post->id} at {$baseUrl}</guid>
</item>\n
XML;
        }


        $feed = <<<XML
<?xml version="1.0" encoding="utf-8" ?><rss version="2.0" xml:base="{$baseUrl}/feed" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>{$siteName}</title>
        <link>{$baseUrl}/feed</link>
        <description>这个RSS的订阅是显示文章摘要的</description>
        <language>zh-hans</language>
        {$items}
    </channel>
</rss>
XML;
        return $feed;
    }

    public static function getRssDotXmlOutput($limit = 75)
    {
        $post = new Post();
        $posts = $post->findPosts(
            array(
                'order' => '-created_at',
                'status' => 'published'
            )
        );
        $items = '';
        $paginator = new \Eva\EvaEngine\Paginator(array(
            "builder" => $posts,
            "limit" => $limit,
            "page" => 1
        ));
        $pager = $paginator->getPaginate();
        $config = IoC::get('config');
        $baseUrl = $config->baseUri;
        $siteName = $config->siteName;
        foreach ($pager->items as $post) {
            $description = htmlspecialchars($post->getContentHtml());
            $pubdate = date(self::$timeFormat, $post->createdAt);
            $url = $baseUrl . $post->getUrl();
            $items .= <<<XML
\n<item>
    <title>{$post->title}</title>
    <link>{$url}</link>
    <description>{$description}</description>
    <pubDate>{$pubdate}</pubDate>
    <dc:creator>Shox</dc:creator>
    <guid isPermaLink="false">{$post->id} at {$baseUrl}</guid>
    <comments>{$url}#comments</comments>
</item>\n
XML;
        }


        $feed = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xml:base="{$baseUrl}"  xmlns:dc="http://purl.org/dc/elements/1.1/">
<channel>
 <title>{$siteName}</title>
 <link>{$baseUrl}</link>
 <description></description>
 <language>zh-hans</language>
        {$items}
    </channel>
</rss>
XML;
        return $feed;
    }

}
