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

class PostSitemap {

    public function getListOutput($limit = 75) {
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

        $index = 1;
        while($index <= $total_pages) {
            $locs .= "\n<sitemap><loc>$baseUrl/sitemap/$index.xml</loc></sitemap>\n";
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

    public function getIndexOutput($page, $limit = 75) {
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

        foreach($pager->items as $item) {
            $urls .= <<<XML
<url>
    <loc>$baseUrl/node/$item->id</loc>
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
} 