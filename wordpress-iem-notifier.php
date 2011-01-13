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
		
		// add_action('publish_post ', array(&$this, 'publish_post'));
		// add_action('comment_post ', array(&$this, 'comment_post'));
		// add_action('wp_login ', array(&$this, 'wp_login'));
		// add_action('wp_logout ', array(&$this, 'wp_logout'));
	}
	
	function send_stomp($data) {
		$port = 61613;
		$queue = $this->queue;
		$host = $this->host;
		
		// echo $queue;
		
		$msg = "CONNECT\n\n\x00";
		$msg .= "SEND\ndestination:$queue\ntimestamp:1\n\n$data\x00";
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

	function publish_post() {
		$this->send_stomp('{title: "alo-você"}');
	}

	function comment_post() {
		$this->send_stomp('{title: "alo-você"}');
	}

	function wp_logout() {
		$this->send_stomp('{title: "alo-você"}');
	}

	function wp_login() {
		$this->send_stomp('{title: "alo-você"}');
	}
}
$holmes_notify = new HolmesNotify();
$holmes_notify->wp_login();






