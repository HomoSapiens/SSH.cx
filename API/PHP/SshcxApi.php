<?php
/**
 * User: Homo_Sapiens
 * Date: 18.02.18
 * Time: 23:28
 *
 * Requires PHP 5.1+
 */

class SshcxApi {

    /**
     * Secret Token
     *
     * @var string
     */
    protected $token;

    /**
     * URL To API
     *
     * @var string
     */
    protected $url = 'https://api.ssh.cx/v1/';

    private static $_instance = null;

    /**
     * Check SSL certificate
     * Prevents connection termination with self-signed certificates.
     *
     * @var bool
     */
    public $checkSSLCerts = false;

    /**
     * Default query options
     *
     * @var array
     */
    protected $optRequest = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => '',
        )
    );

    /**
     * Get short code from SSH.cx URL
     *
     * @param $url
     * @return string
     */
    public static function getShortFromUrl($url)
    {
        return str_replace('/', '', parse_url($url, PHP_URL_PATH));
    }

    public static function getInstance($token)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($token);
        }

        return self::$_instance;
    }

    /**
     * Send request to API server
     *
     * @param $options
     * @param string $path
     * @return bool|mixed|string
     */
    protected function send($options, $path = 'url')
    {
        if (empty($this->token)) return false;

        if (!$this->checkSSLCerts) {
            $options["ssl"] = array(
                "verify_peer" => false,
                "verify_peer_name" => false,
            );
        }

        $url = isset($options['optUrl']) ? $this->url.$path.'?'.$options['optUrl'] : $this->url.$path;
        unset($options['optUrl']);

        $context  = stream_context_create($options);
        return $this->result(file_get_contents($url, false, $context));
    }

    /**
     * General function for getting user URLs/Files
     *
     * @param array $data
     * @return bool|mixed|string
     */
    private function receive(array $data, $url)
    {
        $data['token'] = $this->token;

        $optGet = $this->optRequest;
        $optGet['http']['method'] = 'GET';
        $optGet['optUrl'] = http_build_query($data);
        return $this->send($optGet, $url);
    }

    /**
     * General function for deleting user URLs/Files
     *
     * @param array $data
     * @return bool|mixed|string
     */
    private function delete(array $data, $url)
    {
        $data['token'] = $this->token;

        $optDelete = $this->optRequest;
        $optDelete['http']['method'] = 'DELETE';
        $optDelete['http']['content'] = http_build_query($data);

        return $this->send($optDelete, $url);
    }

    /**
     * Parse SSH.cx response
     *
     * @param $response
     * @return mixed|string
     */
    protected function result($response) {
        if (empty($response)) return '[resp]';

        return json_decode($response);
    }

    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Create short link for URL
     *
     * @param $url
     * @return bool|mixed|string
     */
    public function postUrl($url)
    {
        $data = array(
            'token' => $this->token,
            'url' => $url,
        );

        $optPost = $this->optRequest;
        $optPost['http']['content'] = http_build_query($data);
        return $this->send($optPost, 'url');
    }

    ################ GET
    /**
     * Get all user URLs
     *
     * @return bool|mixed|string
     */
    public function getUrls()
    {
        return $this->receive(array(), 'url');
    }

    /**
     * Get specific user URL by id
     *
     * @param int $id
     * @return bool|mixed|string
     */
    public function getUrlById($id = 0)
    {
        return $this->receive(array(
            'id' => $id,
        ), 'url');
    }

    /**
     * Get specific user URL by short code
     *
     * @param string $short
     * @return bool|mixed|string
     */
    public function getUrlByShort($short)
    {
        return $this->receive(array(
            'short' => $short,
        ), 'url');
    }

    ################ DELETE

    /**
     * Delete specific user URL by short code
     *
     * @param $short
     * @return bool|mixed|string
     */
    public function deleteUrlByShort($short)
    {
        return $this->delete(array(
            'short' => $short,
        ), 'url');
    }

    /**
     * Delete specific user URL by id
     *
     * @param int $id
     * @return bool|mixed|string
     */
    public function deleteUrlById($id = 0)
    {
        return $this->delete(array(
            'id' => intval($id),
        ), 'url');
    }

    /**
     * Delete all user URLs form SSH.cx
     *
     * @return bool|mixed|string
     */
    public function deleteAllUrls()
    {
        return $this->delete(array(
            'all' => 1,
        ), 'url');
    }

    // *************** FILE ***************
    /**
     * Upload file to SSH.cx
     *
     * @param $filename
     * @return string
     */
    public function postFile($filename)
    {
        $optPost = $this->optRequest;

        define('MULTIPART_BOUNDARY', '--------------------------'.microtime(true));
        $header = 'Content-Type: multipart/form-data; boundary='.MULTIPART_BOUNDARY;

        $file_contents = file_get_contents($filename);

        $content = "--".MULTIPART_BOUNDARY."\r\n".
            'Content-Disposition: form-data; name="sfile"; filename="'.basename($filename)."\"\r\n".
            "Content-Type: ".mime_content_type($filename)."\r\n\r\n".
            $file_contents."\r\n";

        $content .= "--".MULTIPART_BOUNDARY."--\r\n";

        $optPost['http']['header'] = $header;
        $optPost['http']['content'] = $content;
        $optPost['optUrl'] = http_build_query(array('token' => $this->token));

        return $this->send($optPost, 'file');
    }

    /**
     * Get information about all user files
     *
     * @return bool|mixed|string
     */
    public function getFiles()
    {
        return $this->receive(array(), 'file');
    }

    /**
     * Get information about specific user file by id
     *
     * @param int $id
     * @return bool|mixed|string
     */
    public function getFileById($id = 0)
    {
        return $this->receive(array(
            'id' => $id,
        ), 'file');
    }

    /**
     * Get information about specific user file by short code
     *
     * @param string $short
     * @return bool|mixed|string
     */
    public function getFileByShort($short)
    {
        return $this->receive(array(
            'short' => $short,
        ), 'file');
    }

    ############ DELETE FILES

    /**
     * Delete specific user file by short code
     *
     * @param $short
     * @return bool|mixed|string
     */
    public function deleteFileByShort($short)
    {
        return $this->delete(array(
            'short' => $short,
        ), 'file');
    }

    /**
     * Delete specific user file by id
     *
     * @param int $id
     * @return bool|mixed|string
     */
    public function deleteFileById($id = 0)
    {
        return $this->delete(array(
            'id' => intval($id),
        ), 'file');
    }

    /**
     * Delete all user files form SSH.cx
     *
     * @return bool|mixed|string
     */
    public function deleteAllFiles()
    {
        return $this->delete(array(
            'all' => 1,
        ), 'file');
    }

    ############## DEMO WORK

    /**
     * Getting demo token with lifetime of 7 days.
     *
     * @param $url
     * @return bool|mixed|string
     */
    public function getDemoToken($url) {
        $this->token = true;
        $data = array(
            'url' => $url,
        );

        $optPost = $this->optRequest;
        $optPost['http']['content'] = http_build_query($data);
        return $this->send($optPost, 'demo/new');
    }

    /**
     * Setting production token instead of demo token
     *
     * @param string $demoToken
     * @param string $prodToken
     * @return bool|mixed|string
     */
    public function setProdToken($demoToken, $prodToken) {
        $this->token = $prodToken;
        $data = array(
            'token' => $prodToken,
            'demo_token' => $demoToken,
        );

        $optPost = $this->optRequest;
        $optPost['http']['content'] = http_build_query($data);
        return $this->send($optPost, 'demo/reassign');
    }
}