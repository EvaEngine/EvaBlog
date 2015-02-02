<?php
/**
 * Created by PhpStorm.
 * User: wscn
 * Date: 15-1-27
 * Time: 上午9:54
 */

namespace Eva\EvaBlog\Models;

//暴露数据源，优化百度统计
use Eva\EvaEngine\IoC;
use Eva\EvaFileSystem\ViewHelpers\ThumbWithClass;

class PostSitemap {

    private $timeFormat = "Y-m-d\TH:i:s";

    public function getListOutput($limit = 100) {
        $post = new Post();
        $config = IoC::get('config');
        $baseUrl = $config->baseUri;

        $posts = $post->findPosts(
            array(
                'order' => '-created_at',
                'status' => 'published'
            )
        );

        $locs = '';

        $paginator = new \Eva\EvaEngine\Paginator(array(
            'builder' => $posts,
            'limit' => $limit,
            'page' => 1
        ));

        $pager = $paginator->getPaginate();
        $total_pages = $pager->total_pages;
        $lastmod = date('Y-m-d', time() - 3600*24);


        $index = 1;
        while($index <= $total_pages) {
            $locs .= "\n<sitemap><loc>$baseUrl/sitemap/$index.xml</loc><lastmod>$lastmod</lastmod></sitemap>\n";
            $index++;
        }

        $sitemap = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<sitemapindex>
    $locs
</sitemapindex>
XML;

        return $sitemap;
    }

    public function getIndexOutput($page, $limit = 100) {
        $post = new Post();
        $config = IoC::get('config');
        $baseUrl = $config->baseUri;

        $posts = $post->findPosts(
            array(
                'order' => '-created_at',
                'status' => 'published'
            )
        );

        $urls = '';

        $paginator = new \Eva\EvaEngine\Paginator(array(
            'builder' => $posts,
            'limit' => $limit,
            'page' => $page,
        ));

        $pager = $paginator->getPaginate();

        $thumb = new ThumbWithClass();

        foreach($pager->items as $item) {

            $title = $item->title;
            $content = $item->getContentHtml();

            $tagString = $item->getTagString();
            $tagArray = explode(',', $tagString);
            $tags = '';
            foreach($tagArray as $tag) {
                $tags .= <<<XML
<tag><![CDATA[ $tag ]]></tag>
XML;
            }

            $pubTime = date($this->timeFormat, $item->createdAt);

            $thumbloc = $item->image ? $thumb($item->image, 'index-news-cover') : '/img/article.jpg';

            $author_nickname = $item->user->username;
            $author_url = $baseUrl . '/news?uid=' . $item->user->id;

            $lastmod = date('Y-m-d', time() - 3600*24);

            $url = $item->getUrl();

            $urls .= <<<XML
<url>
    <loc>$url</loc>
    <lastmod>$lastmod</lastmod>
    <changefreq>hourly</changefreq>
    <priority>1.0</priority>
    <data>
        <display>
            <title><![CDATA[ $title ]]></title>
            <content><![CDATA[ $content ]]></content>
            $tags
            <pubTime>$pubTime</pubTime>
            <thumbnail loc="$thumbloc"></thumbnail>
            <author nickname="$author_nickname" url="$author_url"></author>
            <replyCount>$post->commentCount</replyCount>
            <property></property>
        </display>
    </data>
</url>
XML;
        }

        $urlset = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<urlset>
$urls
</urlset>
XML;

        return $urlset;
    }
} 