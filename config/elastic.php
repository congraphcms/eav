<?php
/*
 * This file is part of the cookbook/eav package.
 *
 * (c) Nikola Plavšić <nikolaplavsic@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


return array(

	'hosts' => [
	    // This is effectively equal to: "https://username:password!#$?*abc@foo.com:9200/"
	    [
	        'host' => 'localhost',
	        'port' => '9200',
	        'scheme' => 'http',
	        'user' => 'elastic',
	        'pass' => 'changeme'
	    ]
	],

	'index_prefix' => 'congraph_'

);