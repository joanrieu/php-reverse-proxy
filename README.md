# php-reverse-proxy

A quick and dirty reverse proxy you can use on shared hosting infrastructure.

## Configuration

Set the proxy target and timeout at the top of the file.

You can disable the target SSL verification if your target uses a self-signed certificate.

## How it works

The `.htaccess` file redirects all requests to the `index.php` file.

The `index.php` file rebuilds a CURL request with what PHP received and sends it somewhere else.

## Notes

An `HTTP 502` will be returned if the request takes too long or CURL returns an error.

Request headers are not rewritten, you might want to add `X-Forwarded-...` headers if your hosting provider does not set them.

Response headers are not rewritten except `Content-Length`, you might want to replace host names in other headers.
