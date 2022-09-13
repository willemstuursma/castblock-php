castblock-php.phar: box.phar
    # Composer must be installed globally for box to work.
	composer install --no-dev
	php box.phar compile

.PHONY: clean
clean:
	rm -f castblock-php.phar box.phar

box.phar:
	curl -Ls https://github.com/box-project/box/releases/download/3.16.0/box.phar -o box.phar
	chmod +x box.phar