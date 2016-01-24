<?php

class UserAgentTest extends PHPUnit_Framework_TestCase
{
    protected $CrawlerDetect;

    public function setUp()
    {
        $this->CrawlerDetect = new Jaybizzle\CrawlerDetect\CrawlerDetect();
    }

    public function testBots()
    {
        $lines = file(__DIR__.'/crawlers.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $test = $this->CrawlerDetect->isCrawler($line);
            $this->assertEquals(true, $test, $line);
        }
    }

    public function testDevices()
    {
        $lines = file(__DIR__.'/devices.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $test = $this->CrawlerDetect->isCrawler($line);
            $this->assertEquals(false, $test, $line);
        }
    }

    public function testReturnsCorrectMatchedBotName()
    {
        $crawlerName = $this->CrawlerDetect->getCrawlerName('Mozilla/5.0 (iPhone; CPU iPhone OS 7_1 like Mac OS X) AppleWebKit (KHTML, like Gecko) Mobile (compatible; Yahoo Ad monitoring; https://help.yahoo.com/kb/yahoo-ad-monitoring-SLN24857.html)');
        $this->assertEquals($crawlerName, 'Yahoo Ad monitoring', 'Returned name was "' . $crawlerName . '"');
    }
}
