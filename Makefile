castblock-php.phar: box.phar
    # Composer must be installed globally for box to work.
	composer install --no-dev
	php box.phar compile

box.phar:
	curl -Ls https://github.com/box-project/box/releases/download/3.13.0/box.phar -o box.phar