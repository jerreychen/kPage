<?php
/**
 * +-----------------------------------------------------------------------
 * | KPage，是温州市夸克网络科技有限公司内部使用的一套简单的php CMS系统框架。
 * +-----------------------------------------------------------------------
 * | 我们的宗旨是：用最少的代码，做更多的事情！
 * +-----------------------------------------------------------------------
 * | 应用程序入口
 * +-----------------------------------------------------------------------
 * @author          Jerrey
 * @license         MIT (https://mit-license.org)
 * @copyright       Copyright © 2021 KuaKee Team. (https://www.kuakee.com)
 * @version         v3
 */

namespace KuaKee\Web;

final class Request
{
    const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 11_1_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36';

    static private $_instance = null;

    private $url, $encoding, $config;

    private function __construct()
    {
        $this->config = array(
            CURLOPT_USERAGENT => self::USER_AGENT,
            // TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
            CURLOPT_RETURNTRANSFER => true,
            // TRUE to include the header in the output.
            CURLOPT_HEADER => false,
            CURLOPT_AUTOREFERER => true,

            // TRUE to follow any "Location: " header that the server sends as part of the HTTP header (note this is recursive,
            // PHP will follow as many "Location: " headers that it is sent, unless CURLOPT_MAXREDIRS is set).
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 10,

            // The maximum number of seconds to allow cURL functions to execute.
            CURLOPT_TIMEOUT => 60,
            // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
            CURLOPT_CONNECTTIMEOUT => 30
        );
    }

    static public function create($url, $encoding = 'UTF-8')
    {
        if(!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }

        self::$_instance->url = $url;
        self::$_instance->encoding = $encoding;

        return self::$_instance;
    }

    private function __config($key, $value)
    {
        $this->config[$key] = $value;
    }

    public function setHeader($arrHeader = array())
    {
        $this->__config(CURLOPT_HTTPHEADER, $arrHeader);
    }

    public function setReferer($referer)
    {
        $this->__config(CURLOPT_AUTOREFERER, false);
        $this->__config(CURLOPT_REFERER, $referer);
    }

    public function setProxy($proxyHost, $proxyPort)
    {
        $this->__config( CURLOPT_PROXY, $proxyHost );
        $this->__config( CURLOPT_PROXYPORT, $proxyPort );
    }

    public function setPostData($postData)
    {
        $this->__config(CURLOPT_POST, true);
        $this->__config(CURLOPT_POSTFIELDS, $postData);
    }

    public function setTimeout($timeout)
    {
        // The maximum number of seconds to allow cURL functions to execute.
        $this->__config(CURLOPT_TIMEOUT, $timeout);
        // The number of seconds to wait while trying to connect. Use 0 to wait indefinitely.
        $this->__config(CURLOPT_CONNECTTIMEOUT, $timeout / 2);
    }

    public function setCAFile($caFile)
    {
        if(!is_file($caFile)) {
            throw new \Exception('Internal error: 500, cannot find the file: '. $caFile);
        }

        $this->__config(CURLOPT_SSL_VERIFYPEER, TRUE);
        $this->__config(CURLOPT_SSL_VERIFYHOST, 2);//严格校验

        $this->__config(CURLOPT_CAINFO, $caFile);
    }

    public function setCERT($certFile, $keyFile, $certType = 'PEM', $keyType = 'PEM', $certPwd = '', $keyPwd = '')
    {
        if(!is_file($certFile)) {
            throw new \Exception('Internal error: 500, cannot find file: '.$certFile);
        }
        if(!is_file($keyFile)) {
            throw new \Exception('Internal error: 500, cannot find file: '.$keyFile);
        }

        $this->__config(CURLOPT_SSL_VERIFYPEER, TRUE);
        $this->__config(CURLOPT_SSL_VERIFYHOST, 2);//严格校验

        $this->__config(CURLOPT_SSLCERTTYPE, $certType);
        $this->__config(CURLOPT_SSLCERT, $certFile);
        $this->__config(CURLOPT_SSLKEYTYPE, $keyType);
        $this->__config(CURLOPT_SSLKEY, $keyFile);

        if(!empty($certPwd)) {
            $this->__config(CURLOPT_SSLCERTPASSWD, $certPwd);
        }
        if(!empty($keyPwd)) {
            $this->__config(CURLOPT_SSLKEYPASSWD, $keyPwd);
        }
    }

    public function writeCookieFile($cookieFile)
    {
        $this->__config(CURLOPT_COOKIEJAR,  $cookieFile);
    }

    public function readCookieFile($cookieFile)
    {
        if(!is_file($cookieFile)) {
            throw new \Exception('Internal error: 500, cannot find the file: '.$cookieFile);
        }
        $this->__config(CURLOPT_COOKIEFILE, $cookieFile);
    }

    public function deleteCookieFile($cookieFile)
    {
        if(is_file($cookieFile)) {
            unlink($cookieFile);
        }
    }

    public function getResponse($ignoreSSL = false, $recursively = true)
    {
        $this->__config(CURLOPT_URL, $this->url);
        $this->__config(CURLOPT_ENCODING, $this->encoding);

        $SSL = stripos($this->url, 'https') === 0;
        if($SSL && $ignoreSSL) {
            /* DO NOT CHECK THE HOST BY DEFAULT */
            $this->__config(CURLOPT_SSL_VERIFYHOST, false);
            $this->__config(CURLOPT_SSL_VERIFYPEER, false);
        }

        if($recursively) {
            $this->__config(CURLOPT_FOLLOWLOCATION, true);
        }

        $ch = curl_init();

        curl_setopt_array($ch, $this->config);

        $content = curl_exec($ch);

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if($code == 200) {
            return $content;
        }

        return false;
    }
}