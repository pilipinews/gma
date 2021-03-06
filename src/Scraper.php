<?php

namespace Pilipinews\Website\Gma;

use Pilipinews\Common\Article;
use Pilipinews\Common\Client;
use Pilipinews\Common\Converter;
use Pilipinews\Common\Crawler as DomCrawler;
use Pilipinews\Common\Interfaces\ScraperInterface;
use Pilipinews\Common\Scraper as AbstractScraper;

/**
 * GMA News Scraper
 *
 * @package Pilipinews
 * @author  Rougin Gutib <rougingutib@gmail.com>
 */
class Scraper extends AbstractScraper implements ScraperInterface
{
    /**
     * Returns the contents of an article.
     *
     * @param  string $link
     * @return \Pilipinews\Common\Article
     */
    public function scrape($link)
    {
        $this->prepare(mb_strtolower($link));

        $title = $this->json['headline'];

        $title = str_replace(' | News |', '', $title);

        $converter = new Converter;

        $title = $converter->convert($title);

        $body = $this->tweet($this->crawler);

        $html = (string) $this->html($body);

        return new Article($title, $html, $link);
    }

    /**
     * Initializes the crawler instance.
     *
     * @param  string $link
     * @return void
     */
    protected function prepare($link)
    {
        $response = (string) Client::request((string) $link);

        $html = trim(preg_replace('/\s+/', ' ', $response));

        $html = str_replace('<p> <strong>', '<p><strong>', $html);

        $html = str_replace('<br /> ', '<br />', $html);

        preg_match('/<script type="application\/ld\+json"\>(.*?)<\/script\>/i', $html, $match);

        $this->json = json_decode($match[1], true);

        $content = (string) $this->json['articleBody'];

        $this->crawler = new DomCrawler((string) $content);
    }
}
