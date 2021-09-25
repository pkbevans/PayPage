<?php
final class Response {
    public $rawResponse;
    public $rawHeaders;
    public $headers;
    public $body;
    public $statusLine;
    public $httpCode;

    public function __construct($rawResponse){
        $this->rawResponse = $rawResponse;
        $this->processResponse();
    }

    private function processResponse(){
        $config = parse_ini_file('configuration.ini');
        $explodedResponse = explode(PHP_EOL, $this->rawResponse);
        $this->body = array_pop($explodedResponse);
        if ($this->body === '') {
            $this->body = null;
        }
        array_pop($explodedResponse);
        $this->statusLine = array_shift($explodedResponse);

        if ($this->statusLine === 'HTTP/1.1 200 Connection established' && $config['proxy'] == true) {
            array_shift($explodedResponse);
            $this->statusLine = array_shift($explodedResponse);
        }

        $this->httpCode = explode(' ', $this->statusLine)[1];
        $this->rawHeaders = implode(PHP_EOL, $explodedResponse);

        foreach ($explodedResponse as $value) { //assign headers as associative array
            $explodedValue = explode(': ',$value);
            $this->headers[$explodedValue[0]] = $explodedValue[1];
        }
    }
}

