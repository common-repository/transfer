<?php

require_once 'Zend/XmlRpc/Client.php';

class XmlRpc
{

	private $wpUrl;		// transfer server URL
	private $wpUser;		// transfer server login name
	private $wpPassword;	// transfer server login password

	public function __construct ($url, $username, $password)
	{
		$this->wpUrl = $url;
		$this->wpUser = $username;
		$this->wpPassword = $password;
	}

	public function transferBlogPost ($title, $categories, $content, $excerpt, $tags, $customFields, $publish)
	{
		$xmlRpcUrl = $this->wpUrl . '/xmlrpc.php';

		$client = new Zend_XmlRpc_Client($xmlRpcUrl);

		$params [] = "n/a";
		$params [] = $this->wpUser;
		$params [] = $this->wpPassword;
		$params [] = array(
						'title' => $title,
						'categories' => $categories,
						'description' => $content,
						'mt_excerpt' => $excerpt,
						'mt_keywords' => $tags,
						'custom_fields' => $customFields
					);
		$params [] = $publish;

		$result = $client->call('metaWeblog.newPost', $params);

	return $result;
	}

	public function updateBlogPost ($id, $title, $categories, $content, $excerpt, $tags, $customFields, $publish)
	{
		$xmlRpcUrl = $this->wpUrl . '/xmlrpc.php';

		$client = new Zend_XmlRpc_Client($xmlRpcUrl);

		$params [] = $id;
		$params [] = $this->wpUser;
		$params [] = $this->wpPassword;
		$params [] = array(
						'title' => $title,
						'categories' => $categories,
						'description' => $content,
						'mt_excerpt' => $excerpt,
						'mt_keywords' => $tags,
						'custom_fields' => $customFields
					);
		$params [] = $publish;

		$result = $client->call('metaWeblog.editPost', $params);

	return $result;
	}

	public function transferMediaObject ($name, $file)
	{
		$xmlRpcUrl = $this->wpUrl . '/xmlrpc.php';

		$client = new Zend_XmlRpc_Client($xmlRpcUrl);

		$params [] = "n/a";
		$params [] = $this->wpUser;
		$params [] = $this->wpPassword;
		$params [] = array(
						'name' => $name,
						'bits' => new Zend_XmlRpc_Value_Base64($file)
					);
		$params [] = $publish;

		$result = $client->call('metaWeblog.newMediaObject', $params);

	return $result;
	}
}
?>