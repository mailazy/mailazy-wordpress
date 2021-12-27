<?php
class mailazyAPI
{
    public $apiurl;
    public $apikey;
	public $apisecret;

    public function __construct()
    {
        $apiurl = "https://api.mailazy.com/";
        $this->setApiurl($apiurl);
		$this->isHTML(true);
    }
    /**
     * Get API URL
     */
    public function getApiurl()
    {
        return $this->apiurl;
    }
    /**
     * Set API URL
     */
    public function setApiurl($url)
    {
        return $this->apiurl = $url;
    }
    /**
     * Get API key
     */
    public function getApikey()
    {
        return $this->apikey?$this->apikey:"";
    }
    /**
     * Set API key
     */
    public function setApikey($apikey)
    {
        return $this->apikey = $apikey;
    }
	
    /**
     * Get API secret
     */
    public function getApisecret()
    {
        return $this->apisecret?$this->apisecret:"";
    }
    /**
     * Set API secret
     */
    public function setApisecret($apisecret)
    {
        return $this->apisecret = $apisecret;
    }
	/**
     * Get To Address
     */
    public function getAddress()
    {
        return isset($this->addresses)?$this->addresses:array();
    }
    /**
     * Set To Address
     */
    public function addAddress($email,$name="")
    {
		$this->addresses = isset($this->addresses)?$this->addresses:array();
		if(empty($name)){
			$toAddress = array($email);
		}else{
			$toAddress = array($name.'<'.$email.'>');
		}
		return $this->addresses = array_merge($this->addresses, $toAddress);;
    }
	/**
     * Get CC Address
     */
    public function getCC()
    {
        return isset($this->cc)?$this->cc:array();
    }
    /**
     * Set CC Address
     */
    public function addCC($email,$name="")
    {
		$this->cc = isset($this->cc)?$this->cc:array();
		if(empty($name)){
			$toAddress = array($email);
		}else{
			$toAddress = array($name.'<'.$email.'>');
		}
		return $this->cc = array_merge($this->cc, $toAddress);;
    }
	/**
     * Get BCC Address
     */
    public function getBCC()
    {
        return isset($this->bcc)?$this->bcc:array();
    }
    /**
     * Set BCC Address
     */
    public function addBCC($email,$name="")
    {
		$this->bcc = isset($this->bcc)?$this->bcc:array();
		if(empty($name)){
			$toAddress = array($email);
		}else{
			$toAddress = array($name.'<'.$email.'>');
		}
		return $this->bcc = array_merge($this->bcc, $toAddress);;
    }
	/**
     * Get subject
     */
    public function getSubject()
    {
        return isset($this->subject)?$this->subject:"";
    }
    /**
     * Set subject
     */
    public function setSubject($subject)
    {
        return $this->subject = $subject;
    }
	/**
     * Get body
     */
    public function getBody()
    {
        return isset($this->body)?$this->body:"";
    }
    /**
     * Set body
     */
    public function setBody($body)
    {
        return $this->body = $body;
    }
	/**
     * Get from
     */
    public function getFrom()
    {
        return $this->from?$this->from:"";
    }
    /**
     * Set from
     */
    public function setFrom($from,$name="")
    {
		if(empty($name)){
			$fromAddress = $from;
		}else{
			$fromAddress = $name.'<'.$from.'>';
		}
        return $this->from = $fromAddress;
    }
	/**
     * Get replyTo
     */
    public function getReplyTo()
    {
        return isset($this->replyTo)?$this->replyTo:"";
    }
    /**
     * Set replyTo
     */
    public function addReplyTo($replyTo,$name="")
    {
		if(empty($name)){
			$replyToAddress = $replyTo;
		}else{
			$replyToAddress = $name.'<'.$replyTo.'>';
		}
        return $this->replyTo = $replyToAddress;
    }
	/**
     * Get from
     */
    public function getIsHTML()
    {
        return $this->ishtml;
    }
    /**
     * Set from
     */
    public function isHTML($ishtml)
    {
        return $this->ishtml = !$ishtml?false:true;
    }
    /**
     * Get Attachment
     */
    public function getAttachment()
    {
        return isset($this->attachments)?$this->attachments:array();
    }
    /**
     * add Attachment
     */
    public function addAttachment($file, $name = '',  $encoding = 'base64', $type = 'application/pdf')
    {
        $this->attachments = isset($this->attachments)?$this->attachments:array();
        $fileName = basename($file);
        $name = (!empty($name)?$name:$fileName);
        $ContentType = mime_content_type($file)?mime_content_type($file):$type;
        $data = file_get_contents($file);
        $attachment = array(array("type"=> $ContentType,
            "file_name"=> $name,
            "content"=> base64_encode($data)));
        return $this->attachments = array_merge($this->attachments, $attachment);
    }
    /**
     * Send Link on Email
     */
    public function send()
    {
		$payload = array(
			"to"=>$this->getAddress(),
			"from"=>$this->getFrom(),
			"subject"=>$this->getSubject(),
			"content"=>array(
				array(
				"type"=> "text/plain", 
				"value"=> strip_tags($this->getBody())
				)
            )
		);
		if($this->getIsHTML()){
			$payload['content'][] = array(
				"type"=> "text/html", 
				"value"=> $this->getBody()
			);
		}
		if(!empty($this->getBCC())){
			$payload['bcc']=$this->getBCC();
		}
		if(!empty($this->getCC())){
			$payload['cc']=$this->getCC();
		}
		if(!empty($this->getReplyTo())){
			$payload['reply_to']=$this->getReplyTo();
		}
        if(!empty($this->getAttachment())){
			$payload['attachments']=$this->getAttachment();
		}
        return $this->request("v1/mail/send", array(
            "method"     => "POST",
            "headers"=>array("X-Api-Key"=>$this->getApikey(),
			"X-Api-Secret"=>$this->getApisecret(),
            'Content-Type' => 'application/json'),
            "body"=>json_encode($payload)
      ));
    }
    /**
     * http request from cURL and FSOCKOPEN
     */
    public function request($endPointPath, $args = array())
    {
        if (in_array('curl', get_loaded_extensions())) {
            $response = $this->curlRequest($endPointPath, $args);
        } elseif (ini_get('allow_url_fopen')) {
            $response = $this->fsockopenRequest($endPointPath, $args);
        } else {
            $response = array("status_code"=>500,"message"=>'cURL or FSOCKOPEN is not enabled, enable cURL or FSOCKOPEN to get response from mojoAuth API.');
        }
        return $response;
    }
    /**
     * http request from FSOCKOPEN
     */
    private function fsockopenApiMethod($endPointPath, $options)
    {
        $method = isset($options['method']) ? strtoupper($options['method']) : 'GET';
        $data = isset($options['body']) ? $options['body'] : array();
        
        $optionsArray = array('http' =>
            array(
                'method' => strtoupper($method),
                'timeout' => 50,
                'ignore_errors' => true
            ),
            "ssl" => array(
                "verify_peer" => false
            )
        );
        if (!empty($data) || $data === true) {
            $optionsArray['http']['content'] = $data;
        }

        foreach ($options['headers'] as $k => $val) {
            $optionsArray['http']['header'] .= "\r\n" . $k.":".$val;
        }

        $context = stream_context_create($optionsArray);
        $jsonResponse['response'] = file_get_contents($this->getApiurl().$endPointPath, false, $context);
        $parseHeaders = Functions::parseHeaders($http_response_header);
        if (isset($parseHeaders['Content-Encoding']) && $parseHeaders['Content-Encoding'] == 'gzip') {
            $jsonResponse['response'] = gzdecode($jsonResponse['response']);
        }
        $jsonResponse['status_code'] = $parseHeaders['reponse_code'];
        
        return $jsonResponse;
    }
    /**
     * http request from cURL
     */
    private function curlRequest($endPointPath, $options)
    {
        $method = isset($options['method']) ? strtoupper($options['method']) : 'GET';
        $data = isset($options['body']) ? $options['body'] : array();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getApiurl().$endPointPath);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        $headerArray = array();
        foreach ($options['headers'] as $k => $val) {
            $headerArray[] = $k.":".$val;
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);

        if (in_array($method, array('POST'))) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = array();
        $output['response'] = curl_exec($ch);
        $output['status_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            $output['response'] = curl_error($ch);
        }
        curl_close($ch);

        return $output;
    }
}