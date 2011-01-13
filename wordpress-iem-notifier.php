<?php

class HolmesNotify {
	
	private $queue = '';
	private $host = '';
	
	
	function __construct() {
		$this->HolmesNotify();
	}

	function HolmesNotify() {
		$this->host = '192.168.42.94';
		$this->queue = '/queue/foo';
		if (function_exists('add_action') ) {
			add_action('publish_post ', array(&$this, 'publish_post'));
			add_action('comment_post ', array(&$this, 'comment_post'));
			add_action('wp_login ', array(&$this, 'wp_login'));
			add_action('wp_logout ', array(&$this, 'wp_logout'));
		}
	}
	
	function send_stomp($data, $type = '') {
		$port = 61613;
		$queue = $this->queue;
		$host = $this->host;
		$timestamp = (int)microtime(true);
		
		// echo $queue;
		
		$msg = "CONNECT\n\n\x00";
		// add $type.
		$msg .= "SEND\ndestination:$queue\ntimestamp:$timestamp\n\n$data\x00";
		$msg .= "DISCONNECT\n\n\x00";

		$l = fsockopen($host, $port, $errno, $errstr, 1);
		if (!$l) {
			// die('fsockopen failed');
			return false;
		}

		if(!fwrite($l, $msg)) {
			return false;
			// $md = stream_get_meta_data($l);
			// if($md['timed_out']) {
			// 	return false;
			// 	// die('connection timed out');
			// 	        }
			// return false;
			// // throw die('fwrite failed');
		}

		if(!('CONNECTED' == fread($l,9))){
			return false;
			// $md = stream_get_meta_data($l);
			// if($md['timed_out']) {
			// 	die('connection timed out');
			// 	        }
			// die('did not get response CONNECTED');
		}
		// echo "-";
		// var_dump(fread($l, 44));
		fread($l, 44);
		$md = stream_get_meta_data($l);
		// print_r($md);
		if($md['timed_out']) {
			return false;
			// die('connection timed out');
		}
		fclose($l);
		// echo "ok";
		return true;
	}

	/**
	 * When a post was published
	 */
	function publish_post($post_ID) {
		// #TODO: add blog_ID. IDEA: get globals.
		$this->send_stomp("{post_ID: ${post_ID}}", 'publish_post');
	}

	function comment_post($comment_id) {
		// #TODO: get blog_id and post_id
		$this->send_stomp("{comment_id: ${comment_id}}", 'comment_post');
	}

	function wp_logout() {
		// TODO: get current logged user.
		// $this->send_stomp('{data: ""}');
	}

	function wp_login($login) {
		$this->send_stomp("{login: '${login}'}", 'wp_login');
	}
}
$holmes_notify = new HolmesNotify();
// if you want test.
$holmes_notify->wp_login('josé');






