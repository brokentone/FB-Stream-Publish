<?php
class fbStreamPublish {
	private $pubUrl,$appId,$appSecret,$authToken;
	public function __construct($pubUrl, $appId, $appSecret) {
        if(!preg_match('/^http/', $pubUrl)) die('Please enter a fully qualified URL');
		$this->pubUrl = $pubUrl;
        $this->appId = $appId;
        $this->appSecret = $appSecret;
	}

	private function makeRequest($url,$post) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        return trim($response);
	}
	
    private function getAuth() {
        $post = array(
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->appId,
            'client_secret' => $this->appSecret
        );
        $url = 'https://graph.facebook.com/oauth/access_token';
        $response = $this->makeRequest($url,$post);
        if(strpos($response, 'access_token=') === 0) $this->authToken = substr($response, 13);
        else die('Unexpected response on auth token, please verify app ID and secret');
    }

	public function publish($url,$message) {
		if(!$this->authToken) $this->getAuth();
        $post = array(
            'access_token'  => $this->authToken,
            'message'       => $message,
            'id'            => $url
        );
        $fb_url = 'https://graph.facebook.com/feed';
        $pubResponse = $this->makeRequest($fb_url,$post);
        if(strpos($pubResponse, '{') === 0) {
            $pubResponse = json_decode($pubResponse);
            if(isset($pubResponse->error)) die('Error: ' . $pubResponse->error->message);
            if(isset($pubResponse->id)) return $pubResponse->id;
        }
	}
}
