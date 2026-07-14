<?php

function getAccessToken(int $maxRetries = 2)
{
    $tokenUrl = 'https://auth.skeepers.io/realms/skeepers/protocol/openid-connect/token';

    $clientId     = '90ac57a7-defc-480b-a99e-27d0bc986f81';
    $clientSecret = 'Qv7kAVrepopWCLw3MqcWJWnvXa82RkbS';

    $basicAuth = base64_encode($clientId . ':' . $clientSecret);

    $attempt = 0;

    while ($attempt <= $maxRetries) {
        $attempt++;

        $ch = curl_init($tokenUrl);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . $basicAuth,
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type' => 'client_credentials',
                'audience'   => 'verified-reviews',
            ]),
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            curl_close($ch);

            if ($attempt > $maxRetries) {
                return false;
            }

            continue;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $tokenData = json_decode($response, true);
            return $tokenData['access_token'] ?? false;
        }

        // Retry if not 200
        if ($attempt > $maxRetries) {
            return false;
        }
    }

    return false;
}

$accessToken = getAccessToken();

if ($accessToken === false) {
    die('Unable to retrieve access token after retries.');
}
echo $accessToken;
/********************************************************************************************/

date_default_timezone_set('Asia/Kolkata');

$sTime = 'T00:00:00Z';
$eTime = 'T23:59:59Z';

$currentDate = date('Y-m-d', time());
$sDate = '2018-06-01';
$eDate = '2018-12-31';

/* $currentDate = '09/08/2024'; */
echo $apiUrl="https://api.skeepers.io/verified-reviews/v1/published/products/reviews?publish_date.gte=$sDate$sTime&publish_date.lt=$eDate$eTime&limit=200";
echo "<hr>";
//$apiUrl = "https://api.etimeoffice.com/api/DownloadInOutPunchData?Empcode=ALL&FromDate=$currentDate&ToDate=$currentDate";

$token = $accessToken;
$ch = curl_init($apiUrl);
$headers = array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . ($token)
);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$responseDatas = json_decode(curl_exec($ch), true);

if (curl_errno($ch)) {
    // throw the an Exception.
    throw new Exception(curl_error($ch));
}

curl_close($ch);
echo "<pre>";
print_r($responseData);
foreach($responseDatas as $responseData){
    echo 'DH- '.$responseData['product_sku']."<br/>";
    /*$responseData['product_upc']
            $responseData['order_reference']
            $responseData['product_mpn']
            $responseData['author_lastname']
            $responseData['product_variation_id']
            $responseData['incentivization']
            $responseData['product_image_url']
            $responseData['locale']
            $responseData['product_sku']
            $responseData['syndicated_review']
            $responseData['author_firstname']
            $responseData['product_jan']
            $responseData['product_brand']
            $responseData['product_ean']
            $responseData['product_page_url']
            $responseData['review_content']
            $responseData['review_rate']
            $responseData['is_verified']
            $responseData['product_name']
            $responseData['order_date']
            $responseData['review_date']
            $responseData['is_personal_data_disclosed']
            $responseData['publish_date']
            $responseData['order_id']*/
}