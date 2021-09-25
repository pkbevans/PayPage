<?php
final class Request {
    const CONTENT_TYPE_APPLICATION_JSON = 'application/json';
    const CONTENT_TYPE_APPLICATION_XML = 'application/xml';

    public $url;
    public $method;
    public $path;
    public $headers;
    public $body;
    public $query;
    public $completeUrl;
    public $target;
    public $requestLine;
    public $rawHeaders;
    public $rawRequest;

    public function __construct($url, $method, $api){
        $this->url = $url;
        $this->method = $method;
        $this->path = $api;
    }

    public function prepareHeaders($mid, $authentication='signature'){
        //content type detection, needs to be improved and xml added
        json_decode($this->body);
        if (json_last_error() === JSON_ERROR_NONE) {
            $this->headers['Content-Type'] = Request::CONTENT_TYPE_APPLICATION_JSON;
        }
        if ($authentication !== 'bearer') {
            $this->headers['host'] = substr($this->url,8);
            $this->headers['date'] = gmdate('D, d M Y H:i:s T');
            $this->headers['v-c-merchant-id'] = $mid;
            if ($this->body != null){
                $this->headers['digest'] = 'SHA-256='.base64_encode(hash('sha256',$this->body,true));
            }
        }
    }

    public function prepareUrl() {
        $completeUrl = $this->url.$this->path;
        if ($this->query !== null) {
            $completeUrl .= '?'.http_build_query(json_decode($this->query));
        }
        $this->completeUrl = $completeUrl;
    }

    public function prepareTarget() {
        $target = strtolower($this->method).' '.$this->path;
        if ($this->query !== null) {
            $target .= '?'.http_build_query(json_decode($this->query));
        }
        $this->target = $target;
    }

    public function processHeaders($rawHeaders){
        $this->rawHeaders = $rawHeaders;
        $explodedHeaders = explode(PHP_EOL, $this->rawHeaders);
        $this->requestLine = array_shift($explodedHeaders);
        $this->rawRequest = $this->rawHeaders.$this->body;
        $explodedHeaders = array_filter($explodedHeaders);
        foreach ($explodedHeaders as $value) {
            $explodedValue = explode(': ',$value);
            $this->headers[$explodedValue[0]] = $explodedValue[1];
        }
        $this->rawHeaders = implode(PHP_EOL, $explodedHeaders);
    }
}