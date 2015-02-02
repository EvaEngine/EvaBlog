<?php
/**
 * Created by PhpStorm.
 * User: wscn
 * Date: 15-1-27
 * Time: 上午9:54
 */

namespace Eva\EvaBlog\Models;

//暴露数据源，优化百度搜索 sitemap
use Eva\EvaEngine\IoC;
use Eva\EvaFileSystem\ViewHelpers\ThumbWithClass;

class BaiduPostSitemap {

    private $timeFormat = "Y-m-d\TH:i:s";

    public function getListOutput($limit = 100) {
        $post = new Post();
        $config = IoC::get('config');
        $baseUrl = $config->baseUri;

        $lastmod = date('Y-m-d', time());

        $posts = $post->findPosts(
            array(
                'order' => '-created_at',
                'status' => 'published'
            )
        );

        $locs = '';
        $locs .= "\n<sitemap><loc>$baseUrl/sitemap/channel.xml</loc><lastmod>$lastmod</lastmod></sitemap>\n";

        $paginator = new \Eva\EvaEngine\Paginator(array(
            'builder' => $posts,
            'limit' => $limit,
            'page' => 1
        ));

        $pager = $paginator->getPaginate();
        $total_pages = $pager->total_pages;

        $index = 1;
        while($index <= $total_pages) {
            $locs .= "\n<sitemap><loc>$baseUrl/sitemap/index_$index.xml</loc><lastmod>$lastmod</lastmod></sitemap>\n";
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

        $lastmod = date('Y-m-d', time());

        foreach($pager->items as $item) {

            $title = $item->title;
            $content = $item->getContentHtml();
            $url = $item->getUrl();

            $urls .= <<<XML
<url>
    <loc>$url</loc>
    <lastmod>$lastmod</lastmod>
    <changefreq>hourly</changefreq>
    <priority>1.0</priority>
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

    public function getChannel() {
        $urls = '';
        $lastmod = date('Y-m-d', time());

        $urls .= <<<XML
<url>
    <loc>http://live.wallstreetcn.com/</loc>
    <lastmod>$lastmod</lastmod>
    <changefreq>hourly</changefreq>
    <priority>1.0</priority>
</url>
<url>
    <loc>http://markets.wallstreetcn.com/</loc>
    <lastmod>$lastmod</lastmod>
    <changefreq>hourly</changefreq>
    <priority>1.0</priority>
</url>
<url>
    <loc>http://calendar.wallstreetcn.com/</loc>
    <lastmod>$lastmod</lastmod>
    <changefreq>hourly</changefreq>
    <priority>1.0</priority>
</url>
<url>
    <loc>http://wallstreetcn.com/columns</loc>
    <lastmod>$lastmod</lastmod>
    <changefreq>hourly</changefreq>
    <priority>1.0</priority>
</url>
<url>
    <loc>http://wallstreetcn.com/activities/stocktrading</loc>
    <lastmod>$lastmod</lastmod>
    <changefreq>hourly</changefreq>
    <priority>1.0</priority>
</url>
<url>
    <loc>http://activity.wallstreetcn.com/app/index.html</loc>
    <lastmod>$lastmod</lastmod>
    <changefreq>hourly</changefreq>
    <priority>1.0</priority>
</url>
XML;

        $urlset = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<urlset>
$urls
</urlset>
XML;

        return $urlset;
    }
} 