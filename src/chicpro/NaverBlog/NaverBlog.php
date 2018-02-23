<?php
namespace chicpro\NaverBlog;

use PhpXmlRpc\Value;
use PhpXmlRpc\Request;
use PhpXmlRpc\Client;

class NaverBlog
{
    protected $endPoint = 'https://api.blog.naver.com/xmlrpc';
    protected $apiUser;
    protected $apiPass;
    protected $request;
    protected $response;
    protected $return_type = 'xml';

    public function __construct($apiUser = null, $apiPass = null)
    {
        $this->setCredentials($apiUser, $apiPass);
    }

    public function setCredentials($apiUser, $apiPass)
    {
        $this->apiUser = $apiUser;
        $this->apiPass = $apiPass;
    }

    public function setReturnType($type)
    {
        $this->return_type = $type;
    }

    private function result()
    {
        $result = new \stdClass;

        $result->xml    = $this->response->value();
        $result->errno  = $this->response->faultCode();
        $result->errstr = $this->response->faultString();

        return $result;        
    }

    public function newPost($title, $content, $category='', $tags='')
    {
        if(!$title || !$content)
            return false;
        
        $client = new Client($this->endPoint);
        $client->return_type = $this->return_type;
        $client->setSSLVerifyPeer(false);
        
        $method  = 'metaWeblog.newPost';
        $publish = true;

        $struct = array(
            'title'       => new Value($title,   "string"), 
            'description' => new Value($content, "string") 
        );

        if($category)
            $struct['categories'] = new Value(strip_tags(trim($category)), "string");
        
        if($tags)
            $struct['tags'] = new Value(strip_tags(trim($tags)), "string");
            
        $post = array( 
            new Value($this->apiUser, "string"),
            new Value($this->apiUser, "string"),
            new Value($this->apiPass, "string"),
            new Value($struct,        "struct"), 
            new Value($publish,       "boolean") 
        );         
        
        $request = new Request($method, $post);

        $this->response = $client->send($request);
        $result = $this->result();
        
        $xml = new \DOMDocument;
        $xml->loadXML($result->xml);

        $postId = '';
        $val = $xml->getElementsByTagname('value');
        if($val->length > 0)
            $postId = $val->item(0)->nodeValue;
        
        return array('post' => $postId, 'errno' => $result->errno, 'errstr' => $result->errstr);
            
    }

    public function uploadMedia($file)
    {
        if(!$file || !is_file($file))
            return false;
        
        $name = basename($file);
        $mime = mime_content_type($file);
        $bits = file_get_contents($file);
        
        $client = new Client($this->endPoint);
        $client->return_type = $this->return_type;
        $client->setSSLVerifyPeer(false);
        
        $method  = 'metaWeblog.newMediaObject';

        $struct = array(
            'bits' => new Value($bits, "base64"),
            'type' => new Value($mime, "string"),
            'name' => new Value($name, "string")
        );

        $media = array( 
            new Value($this->apiUser, "string"),
            new Value($this->apiUser, "string"),
            new Value($this->apiPass, "string"),
            new Value($struct,        "struct") 
        );

        $request = new Request($method, $media);

        $this->response = $client->send($request);
        $result = $this->result();
        
        $xml = new \DOMDocument;
        $xml->loadXML($result->xml);

        $imgUrl = '';
        $val = $xml->getElementsByTagname('string');
        if($val->length > 0)
            $imgUrl = $val->item(0)->nodeValue;

        return array('url' => $imgUrl, 'errno' => $result->errno, 'errstr' => $result->errstr);
    }
}