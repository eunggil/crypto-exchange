<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>STOCK</title>

</head>
<body>

<div id="container">
	<h1>Welcome to STOCK!</h1>

	<div id="body">
		<code>
		</code>

		<?php
		//echo $foo;
		//echo "<br>";
		//echo var_dump($tables);
		?>
		<pre>
			$ sudo apt update
			$ sudo apt upgrade
			$ sudo apt dist-upgrade


			* REDIS
			$ sudo apt-get install redis-server
			$ sudo systemctl enable redis-server.service

			$ sudo vim /etc/redis/redis.conf
				maxmemory 256mb
				maxmemory-policy allkeys-lru

			$ sudo systemctl restart redis-server.service
			<!--
			$ sudo apt-get install php-redis

			$ wget http://download.redis.io/redis-stable.tar.gz
			$ tar xvzf redis-stable.tar.gz
			$ cd redis-stable
			$ make
			$ sudo make install
			$ redis-server
			//localhost:6379 에서 시작된다.
			$ redis-cli
			-->

			* PHP-REDIS
			$ apt-get install php-redis
			<!--
			libphp-predis - Flexible and feature-complete PHP client library for the Redis key-value store

			* Apache-PHP
			$ apt-get install libapache2-mod-php7.0
			-->

			* NODEJS
			$ apt-get install curl
			$ curl -sL https://deb.nodesource.com/setup_10.x | bash -
			$ apt-get install -y nodejs
			$ nodejs -v
			$ npm -v
			$ sudo npm install -g npm

			$ npm install -g forever


		* redis
		- 주문
		- 호가

		- 체결

		* db
		- 주문
		- 호가

		- 체결


		* websocket
		- 호가
		- 체결, 미체결
		- 자산 : 잔고

		* 시세
		- 고가, 저가, 종가, 현재가, 전일대비, 거래대금



		==================
		참고 :
		https://bkim.tistory.com/18

		https://github.com/ericoc/old-stock-quotes-php-lib/blob/master/stock_quotes.class.php

		https://bluenoon.tistory.com/27
	</pre>
	</div>

</div>

</body>
</html>
