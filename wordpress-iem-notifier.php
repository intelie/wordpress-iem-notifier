<?php

class HolmesNotify {
	
	private $queue = '/queue/events';
	private $host = '';
	private $port = 61613;
	
	function __construct() {
		$this->HolmesNotify();
	}

	/**
	 * Constructor.
	 */
	function HolmesNotify() {

		$this->hosts = array('192.168.42.94', 'localhost');

		// register hooks
		if ( function_exists('add_action') ) {
			
			add_filter( 'wp_cache_meta', array(&$this, 'page_generation') );

			add_action('publish_post', array(&$this, 'publish_post'));
			add_action('comment_post', array(&$this, 'comment_post'));
			add_action('wp_login', array(&$this, 'wp_login'));
			add_action('wp_logout', array(&$this, 'wp_logout'));
		}
	}
	
	/**
	 * Simple Stomp data
	 */
	private function send_stomp($data, $event_type = '') {

		$port = $this->port;
		$queue = $this->queue;

		// event type.
		$event_type = "Wordpress$event_type";

		foreach( $this->hosts as $host ) {
			$l = @fsockopen($host, $port, $errno, $errstr, 0.1);
			if (!$l) {
				continue;
			}
		}
		// is a network handler.
		if ( !$l ) {
			// TODO: error_log
			return false;
		}
		
		// stomp handshake.
		$msg = "CONNECT\n\n\x00";
		$msg .= "SEND\ndestination:$queue\ntimestamp:$timestamp\neventtype:$event_type\n\n$data\x00";
		$msg .= "DISCONNECT\n\n\x00";
		
		if(!fwrite($l, $msg)) {
			return false;
		}

		// 
		if(!('CONNECTED' == fread($l,9))){
			return false;
		}
		
		fread($l, 44);

		// timeout!?
		$md = stream_get_meta_data($l);
		if($md['timed_out']) {
			return false;
		}
		fclose($l);
		
		// worked!.
		return true;
	}

	// 
	public function page_generation( $wp_cache_meta ) {
		global $wp_start_time;
		
		// $wp_cache_meta['uri']
		// $wp_cache_meta['blog_id']
		// $wp_cache_meta['post']
		// $wp_cache_meta['key']

		$duration = wp_cache_microtime_diff($wp_start_time, microtime());
		$duration = sprintf("%0.3f", $duration);

		return $wp_cache_meta;
	}

	/**
	 * When a post was published
	 */
	function publish_post($post_ID) {
		global $current_blog;
		// #TODO: add blog_ID. IDEA: get globals.
		$this->send_stomp("{post_ID: ${post_ID}}", 'PublishPost');
	}

	function comment_post($comment_id) {
		global $current_blog;
		
		$comment = get_comment($comment_id, ARRAY_A);
		$postid = $comment['comment_post_ID'];
		
		// #TODO: get blog_id and post_id
		$this->send_stomp("{'comment_id': ${comment_id}, 'post_id': ${postid}}", 'CommentPost');
	}

	function wp_logout() {
		// TODO: get current logged user.
		// $this->send_stomp('{data: ""}', 'Logout');
	}

	function wp_login($login) {
		$this->send_stomp("{login: '${login}'}", 'Login');
	}
	// TODO: find blog cache generator.
}

$holmes_notify = new HolmesNotify();






