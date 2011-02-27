<?php

include "wordpress-iem-notifier.php";

$timestamp = (int)microtime(true);

// if you want test.
$holmes_notify->wp_login('josé');

echo (microtime(true) - $timestamp) . " seconds ";


