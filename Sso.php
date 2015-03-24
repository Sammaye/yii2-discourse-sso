<?php

namespace sammaye\discourse;

use Yii;
use yii\base\Component;

class Sso extends Component
{
	public $secret;

	public function validate($payload, $sig)
	{
		$payload = urldecode($payload);
		if(hash_hmac("sha256", $payload, $this->secret) === $sig) {
			return true;
		} else {
			return false;
		}
	}

	public function getNonce($payload)
	{
		$payload = urldecode($payload);
		$query = array();
		parse_str(base64_decode($payload), $query);
		if(isset($query["nonce"])) {
			return $query["nonce"];
		} else {
			throw new Exception("Nonce not found in payload!");
		}
	}
	
	public function buildLoginString($params)
	{
		if(!isset($params["external_id"])) {
			throw new Exception("Missing required parameter 'external_id'");
		}
		if(!isset($params["nonce"])) {
			throw new Exception("Missing required parameter 'nonce'");
		}
		if(!isset($params["email"])) {
			throw new Exception("Missing required parameter 'email'");
		}
		$payload = base64_encode(http_build_query($params));
		$sig = hash_hmac("sha256", $payload, $this->secret);

		return http_build_query(array("sso" => $payload, "sig" => $sig));
	}
}
