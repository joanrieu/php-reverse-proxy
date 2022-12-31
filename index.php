<?php

// https://zepp133.com/a-minimal-reverse-proxy-using-php-curl/
// + CURLOPT_TIMEOUT
// + 502 on error
// + keep original Host header
// + strip invalid content length in reply
// + toggle ssl cert/sni verification

$proxied_url = 'https://example.com';
$proxy_timeout = 5;
$ssl_verify = true;

function reformat($headers) {
    foreach ($headers as $name => $value) {
        yield "$name: $value";
    }
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_TIMEOUT, $proxy_timeout);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);
curl_setopt($ch, CURLOPT_URL, $proxied_url . $_SERVER['REQUEST_URI']);
if (!$ssl_verify) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
}

$request_headers = iterator_to_array(reformat(getallheaders()));
curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

$request_body = file_get_contents('php://input');
curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);

$response_headers = [];
curl_setopt($ch, CURLOPT_HEADERFUNCTION,
    function($curl, $header) use (&$response_headers) {
        $len = strlen($header);
        $header = explode(':', $header, 2);
        if (count($header) < 2)
          return $len;
        $response_headers[strtolower(trim($header[0]))][] = trim($header[1]);
        return $len;
    }
);
$response_body = curl_exec($ch);
$response_code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
curl_close($ch);

http_response_code($response_code ? $response_code : 502);
foreach($response_headers as $name => $values)
    foreach($values as $value)
        if($name !== 'content-length')
            header("$name: $value", false);
echo $response_body;
