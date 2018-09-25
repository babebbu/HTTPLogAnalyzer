<?php

namespace App\Library;

class ElasticSearchQuery {
	
	private $method;
	private $endpoint;
	private $queryString;
	
	public function __construct($method, $endpoint, $queryString)
	{
		$this->method = $method;
		$this->endpoint = env('ELASTICSEARCH_HOST').$endpoint;
		$this->queryString = $queryString;
	}
	
	public function validateHttpMethod () : bool
	{
		return in_array($this->method, ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS']);
	}
	
	public function execute() : \StdClass
	{
		if($this->validateHttpMethod()){
			$session = curl_init();
			curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($session, CURLOPT_CUSTOMREQUEST, $this->method);
			curl_setopt($session, CURLOPT_URL, $this->endpoint);
			curl_setopt($session, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json'
			));
			curl_setopt($session, CURLOPT_POSTFIELDS, $this->queryString);
			return json_decode(curl_exec($session));
		}
		else {
			throw new \Exception();
		}
	}
}