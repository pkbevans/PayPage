<?php
$accessToken = 'NzAyZDdiZmU3OWJmZmFiNDRhN2E5MGNhZWZmZTRkOTJiMDg5MWE4NzZjZTJhMDg01671531328';
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://site.test/payPage/v1/orders?status=AUTHORIZED');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization:'. $accessToken));
curl_setopt($curl, CURLOPT_HEADER, false);
echo curl_exec($curl);
curl_close($curl);