<?php

namespace Jaybizzle\CrawlerDetect;

class CrawlerDetect
{
    /**
     * The user agent.
     * 
     * @var null
     */
    protected $userAgent = null;

    /**
     * Headers that contain a user agent.
     * 
     * @var array
     */
    protected $httpHeaders = [];

    /**
     * List of crawler names found within the User-Agent string
     *
     * @var array
     */
    private $crawlerNames = [];

    /**
     * All possible HTTP headers that represent the User-Agent string
     *
     * @var array
     */
    protected static $uaHttpHeaders = [
        // The default User-Agent string.
        'HTTP_USER_AGENT',
        // Header can occur on devices using Opera Mini.
        'HTTP_X_OPERAMINI_PHONE_UA',
        // Vodafone specific header: http://www.seoprinciple.com/mobile-web-community-still-angry-at-vodafone/24/
        'HTTP_X_DEVICE_USER_AGENT',
        'HTTP_X_ORIGINAL_USER_AGENT',
        'HTTP_X_SKYFIRE_PHONE',
        'HTTP_X_BOLT_PHONE_UA',
        'HTTP_DEVICE_STOCK_UA',
        'HTTP_X_UCBROWSER_DEVICE_UA',
    ];

    /**
     * Class constructor
     */
    public function __construct(array $headers = null, $userAgent = null)
    {
        $this->setHttpHeaders($headers);
        $this->setUserAgent($userAgent);

        $this->loadCrawlerNames();
    }

    /**
     * Pull crawler names from a file and keep them locally
     */
    private function loadCrawlerNames()
    {
        $filename = __DIR__ . '/data/crawler-names.txt';

        $crawlerNames = explode("\n", trim(file_get_contents($filename)));
        if (empty($crawlerNames)) {
            throw new \Exception($filename . ' is empty, can\'t continue');
        }

        $this->crawlerNames = $crawlerNames;
    }

    /**
     * Set HTTP headers.
     * 
     * @param array $httpHeaders
     */
    public function setHttpHeaders($httpHeaders = null)
    {
        // use global _SERVER if $httpHeaders aren't defined
        if (! is_array($httpHeaders) || ! count($httpHeaders)) {
            $httpHeaders = $_SERVER;
        }
        // clear existing headers
        $this->httpHeaders = [];
        // Only save HTTP headers. In PHP land, that means only _SERVER vars that
        // start with HTTP_.
        foreach ($httpHeaders as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $this->httpHeaders[$key] = $value;
            }
        }
    }


    /**
     * Set the user agent.
     * 
     * @param string $userAgent
     */
    public function setUserAgent($userAgent = null)
    {
        if (! empty($userAgent)) {
            return $this->userAgent = $userAgent;
        } else {
            $this->userAgent = null;
            foreach (self::$uaHttpHeaders as $altHeader) {
                if (! empty($this->httpHeaders[$altHeader])) { // @todo: should use getHttpHeader(), but it would be slow.
                    $this->userAgent .= $this->httpHeaders[$altHeader] . ' ';
                }
            }

            return $this->userAgent = (! empty($this->userAgent) ? trim($this->userAgent) : null);
        }
    }


    /**
     * Check user agent string against known crawler names
     * 
     * @param string $userAgent
     *
     * @return string
     */
    public function getCrawlerName($userAgent = null)
    {
        $agent = is_null($userAgent) ? $this->userAgent : $userAgent;

        foreach($this->crawlerNames as $name) {
            if ($name[0] == '@' && preg_match($name, $userAgent, $match)) {
                return trim($name, '@');
            }

            if (stripos($agent, $name) !== false) {
                return rtrim($name, '/');
            }
        }

        if (preg_match('@\b[\w_]*((?<!cu)bot|crawler|archiver|transcoder|spider)@i', $agent, $match)) {
            return $match[0];
        }

        return '';
    }

    /**
     * Check user agent string to see whether or not it is a web crawler
     * 
     * @param string $userAgent
     *
     * @return boolean
     */
    public function isCrawler($userAgent = null)
    {
        $agent = is_null($userAgent) ? $this->userAgent : $userAgent;
        return $this->getCrawlerName($agent) !== '';
    }

}
