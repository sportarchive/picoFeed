<?php

namespace PicoFeed\Http\Adapter;

use PicoFeed\Http\Exception\InvalidCertificateException;
use PicoFeed\Http\Exception\InvalidUrlException;
use PicoFeed\Http\Exception\MaxRedirectException;
use PicoFeed\Http\Exception\MaxSizeException;
use PicoFeed\Http\Exception\TimeoutException;
use PicoFeed\Http\Header;
use PicoFeed\Http\Request;
use PicoFeed\Http\Response;
use PicoFeed\Logging\Logger;

/**
 * Class CurlAdapter
 *
 * @package PicoFeed\Http\Adapter
 * @author  Frederic Guillot
 */
class CurlAdapter extends BaseAdapter
{
    /**
     * HTTP response body.
     *
     * @var string
     */
    protected $body = '';

    /**
     * Body size.
     *
     * @var int
     */
    protected $bodyLength = 0;

    /**
     * Request headers.
     *
     * @var array
     */
    protected $headerLines = array();

    /**
     * Counter on the number of header received.
     *
     * @var int
     */
    protected $headerIndex = 0;

    /**
     * Execute HTTP request
     *
     * @param  Request $request
     * @return Response
     */
    public function execute(Request $request)
    {
        $this->request = $request;
        $this->response = new Response();

        $this->executeContext();

        list($status, $headers) = Header::parse(explode("\n", $this->headerLines[$this->headerIndex - 1]));

        $this->response->setStatus($status);
        $this->response->setLastModified($headers['Last-Modified']);
        $this->response->setEtag($headers['ETag']);
        return $this->response;
    }

    /**
     * Prepare HTTP headers.
     *
     * @return string[]
     */
    protected function prepareHeaders()
    {
        $headers = array(
            'Connection: close',
        );

        if ($this->request->getEtag() !== '') {
            $headers[] = 'If-None-Match: '.$this->request->getEtag();
        }

        if ($this->request->getLastModified() !== '') {
            $headers[] = 'If-Modified-Since: '.$this->request->getLastModified();
        }

        return $headers;
    }

    /**
     * Set write/header functions.
     *
     * @param resource $ch
     * @return resource $ch
     */
    protected function prepareDownloadMode($ch)
    {
        $writeCallback = 'readBody';

        if ($this->request->hasPassthroughModeActivated()) {
            $writeCallback = 'passthroughBody';
        }

        curl_setopt($ch, CURLOPT_WRITEFUNCTION, array($this, $writeCallback));
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this,'readHeaders'));

        return $ch;
    }

    /**
     * Prepare curl proxy context.
     *
     * @param resource $ch
     * @return resource $ch
     */
    protected function prepareProxyContext($ch)
    {
        if ($this->request->getProxyHostname() !== '') {
            Logger::setMessage(get_called_class().' Using proxy: '.$this->request->getProxyHostname().':'.$this->request->getProxyPort());

            curl_setopt($ch, CURLOPT_PROXY, $this->request->getProxyHostname());
            curl_setopt($ch, CURLOPT_PROXYPORT, $this->request->getProxyPort());
            curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');

            if ($this->request->getProxyUsername() !== '') {
                Logger::setMessage(get_called_class().' Proxy credentials: Yes');
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->request->getProxyUsername().':'.$this->request->getProxyPassword());
            } else {
                Logger::setMessage(get_called_class().' Proxy credentials: No');
            }
        }

        return $ch;
    }

    /**
     * Prepare curl auth context.
     *
     * @param resource $ch
     * @return resource $ch
     */
    private function prepareAuthContext($ch)
    {
        if ($this->request->getUsername() !== '' && $this->request->getPassword() !== '') {
            curl_setopt($ch, CURLOPT_USERPWD, $this->request->getUsername().':'.$this->request->getPassword());
        }

        return $ch;
    }

    /**
     * Prepare curl context.
     *
     * @return resource
     */
    private function prepareContext()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->request->getUrl());
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->request->getTimeout());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->request->getTimeout());
        curl_setopt($ch, CURLOPT_USERAGENT, $this->request->getUserAgent());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->prepareHeaders());
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'php://memory');
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'php://memory');

        // Disable SSLv3 by enforcing TLSv1.x for curl >= 7.34.0 and < 7.39.0.
        // Versions prior to 7.34 and at least when compiled against openssl
        // interpret this parameter as "limit to TLSv1.0" which fails for sites
        // which enforce TLS 1.1+.
        // Starting with curl 7.39.0 SSLv3 is disabled by default.
        $version = curl_version();
        if ($version['version_number'] >= 467456 && $version['version_number'] < 468736) {
            curl_setopt($ch, CURLOPT_SSLVERSION, 1);
        }

        $ch = $this->prepareDownloadMode($ch);
        $ch = $this->prepareProxyContext($ch);
        $ch = $this->prepareAuthContext($ch);

        return $ch;
    }

    /**
     * Execute curl context.
     */
    protected function executeContext()
    {
        $ch = $this->prepareContext();
        curl_exec($ch);

        Logger::setMessage(get_called_class().' cURL total time: '.curl_getinfo($ch, CURLINFO_TOTAL_TIME));
        Logger::setMessage(get_called_class().' cURL dns lookup time: '.curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME));
        Logger::setMessage(get_called_class().' cURL connect time: '.curl_getinfo($ch, CURLINFO_CONNECT_TIME));
        Logger::setMessage(get_called_class().' cURL speed download: '.curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD));
        Logger::setMessage(get_called_class().' cURL effective url: '.curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));

        $errorCode = curl_errno($ch);

        if ($errorCode !== 0) {
            Logger::setMessage(get_called_class().' cURL error: '.curl_error($ch));
            curl_close($ch);

            $this->handleError($errorCode);
        }

        $this->response->setUrl(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));

        curl_close($ch);
    }

    /**
     * cURL callback to read HTTP headers.
     *
     * @param resource $ch     cURL handler
     * @param string   $buffer Header line
     * @return int Length of the buffer
     */
    public function readHeaders($ch, $buffer)
    {
        $length = strlen($buffer);

        if ($buffer === "\r\n" || $buffer === "\n") {
            ++$this->headerIndex;
        } else {
            if (! isset($this->headerLines[$this->headerIndex])) {
                $this->headerLines[$this->headerIndex] = '';
            }

            $this->headerLines[$this->headerIndex] .= $buffer;
        }

        return $length;
    }

    /**
     * cURL callback to read the HTTP body.
     *
     * If the function return -1, curl stop to read the HTTP response
     *
     * @param  resource $ch     cURL handler
     * @param  string   $buffer Chunk of data
     * @return int              Length of the buffer
     */
    public function readBody($ch, $buffer)
    {
        $length = strlen($buffer);
        $this->bodyLength += $length;

        if ($this->bodyLength > $this->request->getMaxBodySize()) {
            return -1;
        }

        $this->body .= $buffer;

        return $length;
    }

    /**
     * cURL callback to passthrough the HTTP body to the client.
     *
     * If the function return -1, curl stop to read the HTTP response
     *
     * @param  resource $ch     cURL handler
     * @param  string   $buffer Chunk of data
     * @return int              Length of the buffer
     */
    public function passthroughBody($ch, $buffer)
    {
        if ($this->bodyLength === 0) {
            list(, $headers) = Header::parse(explode("\n", $this->headerLines[$this->headerIndex - 1]));

            if (isset($headers['Content-Type'])) {
                header('Content-Type:' .$headers['Content-Type']);
            }
        }

        $length = strlen($buffer);
        $this->bodyLength += $length;

        echo $buffer;

        return $length;
    }

    /**
     * Handle cURL errors (throw individual exceptions).
     *
     * We don't use constants because they are not necessary always available
     * (depends of the version of libcurl linked to php)
     *
     * @see    http://curl.haxx.se/libcurl/c/libcurl-errors.html
     * @param int $errno cURL error code
     * @throws InvalidCertificateException
     * @throws InvalidUrlException
     * @throws MaxRedirectException
     * @throws MaxSizeException
     * @throws TimeoutException
     */
    protected function handleError($errno)
    {
        switch ($errno) {
            case 78: // CURLE_REMOTE_FILE_NOT_FOUND
                throw new InvalidUrlException('Resource not found', $errno);
            case 6:  // CURLE_COULDNT_RESOLVE_HOST
                throw new InvalidUrlException('Unable to resolve hostname', $errno);
            case 7:  // CURLE_COULDNT_CONNECT
                throw new InvalidUrlException('Unable to connect to the remote host', $errno);
            case 23: // CURLE_WRITE_ERROR
                throw new MaxSizeException('Maximum response size exceeded', $errno);
            case 28: // CURLE_OPERATION_TIMEDOUT
                throw new TimeoutException('Operation timeout', $errno);
            case 35: // CURLE_SSL_CONNECT_ERROR
            case 51: // CURLE_PEER_FAILED_VERIFICATION
            case 58: // CURLE_SSL_CERTPROBLEM
            case 60: // CURLE_SSL_CACERT
            case 59: // CURLE_SSL_CIPHER
            case 64: // CURLE_USE_SSL_FAILED
            case 66: // CURLE_SSL_ENGINE_INITFAILED
            case 77: // CURLE_SSL_CACERT_BADFILE
            case 83: // CURLE_SSL_ISSUER_ERROR
                $msg = 'Invalid SSL certificate caused by CURL error number ' . $errno;
                throw new InvalidCertificateException($msg, $errno);
            case 47: // CURLE_TOO_MANY_REDIRECTS
                throw new MaxRedirectException('Maximum number of redirections reached', $errno);
            case 63: // CURLE_FILESIZE_EXCEEDED
                throw new MaxSizeException('Maximum response size exceeded', $errno);
            default:
                throw new InvalidUrlException('Unable to fetch the URL', $errno);
        }
    }
}
