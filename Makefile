castblock-php.phar: box.phar
	composer install --no-dev
	php box.phar compile

box.phar:
	curl -Ls https://github.com/box-project/box/releases/download/3.11.1/box.phar -o box.phar