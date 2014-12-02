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
use Eva\EvaSundries\Utils\BaiduAnalysisUrl;

class PostRSS
{
    private $timeFormat = 'D, j M Y G:i:s P';
    private $urlMaker = null;

    public function __construct($urlMaker = null, $timeFormat = 'D, j M Y G:i:s P')
    {
        if (!is_callable($urlMaker)) {
            $urlMaker = array($this, 'defaultUrlMaker');
        }
        $this->urlMaker = $urlMaker;
        $this->timeFormat = $timeFormat;
    }

    public function defaultUrlMaker($post)
    {
        return $post->getAbsoluteUrl();
    }

    public function getFeedOutput($limit = 30)
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
            $description = $post->summary;
            $pubdate = date($this->timeFormat, $post->createdAt);
            $url = call_user_func($this->urlMaker, $post);

            //用百度统计对rss阅读量进行统计
            $rssUrl = $url . '?read-via=rss';
            $baiduAnalysisId = IoC::get('config')->blog->baiduAnalysisId;
            $baiduAnalysis = new BaiduAnalysisUrl($baiduAnalysisId, $rssUrl);
            $baiduAnalysisUrl = $baiduAnalysis->getFirstRequestUrl();
            $baiduAnalysisImg = "<img src=\"$baiduAnalysisUrl\" />";
            $baiduAnalysisUrl = $baiduAnalysis->getSecondRequestUrl();
            $baiduAnalysisImg .= "<img src=\"$baiduAnalysisUrl\" />";

            $baiduAnalysisSwitch = Ioc::get('config')->blog->baiduAnalysisSwitch;
            if(!$baiduAnalysisSwitch) {
                $baiduAnalysisImg = '';
            }

            $items .= <<<XML
\n<item>
    <title><![CDATA[ {$post->title} ]]></title>
    <link>{$url}</link>
    <description><![CDATA[ {$description}{$baiduAnalysisImg} ]]></description>
    <pubDate>{$pubdate}</pubDate>
    <dc:creator>{$post->username}</dc:creator>
    <guid isPermaLink="false">{$post->id} at {$baseUrl}</guid>
</item>\n
XML;
        }


        $feed = <<<XML
<?xml version="1.0" encoding="utf-8" ?><rss version="2.0" xml:base="{$baseUrl}/feed" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title><![CDATA[ {$siteName} ]]></title>
        <link>{$baseUrl}/feed</link>
        <description><![CDATA[ 这个RSS的订阅是显示文章摘要的 ]]></description>
        <language>zh-hans</language>
        {$items}
    </channel>
</rss>
XML;
        return $feed;
    }

    public function getRssDotXmlOutput($limit = 75)
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
            $description = $post->getContentHtml();
            $description .= '<p>（更多精彩财经资讯，<a href="http://activity.wallstreetcn.com/app/index.html">点击这里下载华尔街见闻App</a>)</p>';
            $pubdate = date($this->timeFormat, $post->createdAt);
            $url = call_user_func($this->urlMaker, $post);

            //用百度统计对rss阅读量进行统计
            $rssUrl = $url . '?read-via=rss';
            $baiduAnalysisId = IoC::get('config')->blog->baiduAnalysisId;
            $baiduAnalysis = new BaiduAnalysisUrl($baiduAnalysisId, $rssUrl);
            $baiduAnalysisUrl = $baiduAnalysis->getFirstRequestUrl();
            $baiduAnalysisImg = "<img src=\"$baiduAnalysisUrl\" />";
            $baiduAnalysisUrl = $baiduAnalysis->getSecondRequestUrl();
            $baiduAnalysisImg .= "<img src=\"$baiduAnalysisUrl\" />";

            $baiduAnalysisSwitch = Ioc::get('config')->blog->baiduAnalysisSwitch;
            if(!$baiduAnalysisSwitch) {
                $baiduAnalysisImg = '';
            }

            $items .= <<<XML
\n<item>
    <title><![CDATA[ {$post->title} ]]></title>
    <link>{$url}</link>
    <description><![CDATA[ {$description}{$baiduAnalysisImg} ]]></description>
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
 <title><![CDATA[ {$siteName} ]]></title>
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
