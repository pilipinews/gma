<?php

namespace Pilipinews\Website\Gma;

use Pilipinews\Common\Client;
use Pilipinews\Common\Crawler as DomCrawler;
use Pilipinews\Common\Interfaces\CrawlerInterface;

/**
 * GMA News Crawler
 *
 * @package Pilipinews
 * @author  Rougin Gutib <rougingutib@gmail.com>
 */
class Crawler implements CrawlerInterface
{
    /**
     * @var string[]
     */
    protected $allowed = array('news/nation', 'news/regions');

    /**
     * @var string
     */
    protected $link = 'https://data.gmanetwork.com/gno/pages/home_1a_json.gz';

    /**
     * Returns an array of articles to scrape.
     *
     * @return string[]
     */
    public function crawl()
    {
        $response = Client::request($this->link);

        $json = json_decode($response, true);

        $items = $json['story_lists_just_in'];

        return $this->verify((array) $items);
    }

    /**
     * Returns the allowed article URLs to scrape.
     *
     * @param  string[] $items
     * @return string[]
     */
    protected function verify($items)
    {
        $base = 'https://www.gmanetwork.com/news/';

        $allowed = (array) $this->allowed;

        $callback = function ($item) use ($base, $allowed)
        {
            $link = null;

            foreach ((array) $allowed as $keyword)
            {
                $result = strpos($item['link'], (string) $keyword);

                $result !== false && $link = $base . $item['link'];
            }

            return (string) $link;
        };

        $result = array_map($callback, (array) $items);

        return array_values(array_filter($result));
    }
}
