<?php
extract($_GET);

empty($url) && die("url is empty!");

$ch = curl_init();
//url
curl_setopt($ch, CURLOPT_URL, $url);
//instead of outputting it out directly
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//automatically set the Referer
curl_setopt($ch, CURLOPT_AUTOREFERER, true);
//TRUE to follow any "Location: " header that the server sends
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
//maximum amount of HTTP redirections to follow
curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
//The number of seconds to wait whilst trying to connect. Use 0 to wait indefinitely
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
//set the maximum seconds to download image
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
//Set User-agent
curl_setopt($ch, CURLOPT_USERAGENT, 
'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)');
//TRUE to include the header in the output
curl_setopt($ch, CURLOPT_HEADER, false);
//Add refer to get pictures
! empty($refer) && curl_setopt($ch, CURLOPT_REFERER, $refer);
//HTTPS
if (stripos($url, "https://") !== FALSE) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
}
$data = curl_exec($ch);
if (curl_errno($ch) > 0) {
    echo "1";
}
curl_close($ch);
echo $data;