<?php

$postLocation = $_POST['text'];
$location = "Liverpool,uk";

if($postLocation != NULL) {
	$location = ucfirst($postLocation);
}

$slackWebhook = "MyWeatherWebhookURL";

//Get weather
$apiKey = "MyOpenWeatherOrgAPIKey";
		
//http://www.openweathermap.org
$url = "http://api.openweathermap.org/data/2.5/weather?q=$location&appid=$apiKey&units=metric";

$response = file_get_contents($url);

$json = json_decode($response);

//Temperature: Celsius
//Wind Speed: metres/second
$weatherMain = $json->weather[0]->main;
$weatherDescription = $json->weather[0]->description;
$weatherIcon = $json->weather[0]->icon;
$iconUrl = "http://www.openweathermap.org/img/w/$weatherIcon.png";
$tempNow = round($json->main->temp);
$tempMin = round($json->main->temp_min);
$tempMax = round($json->main->temp_max);
$windSpeedMPS = $json->wind->speed;

$windSpeedMPH = round(2.237 * $windSpeedMPS);

$stringReplacement = "Temperature Now: %dC \n Minimum Temperature: %dC \n Maximum Temperature: %dC \n Wind Speed: %dmph";
$output = sprintf($stringReplacement, $tempNow, $tempMin, $tempMax, $windSpeedMPH);

//Build JSON for Slack request
$attachments = array([
	"fallback" => "Unable to fetch Weather",
	"pretext" => "$location Weather",
	"mrkdown" => "true",
	"title" => "$weatherMain, $weatherDescription",
	"title_link" => $iconUrl,
	"text" => $output,
	"image_url" => $iconUrl,
	"thumb_url" => $iconUrl
]);

$data = json_encode(
array(
"attachments" => $attachments
));

//Execute Slack Webhook
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $slackWebhook);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

$result = curl_exec($ch);

?>