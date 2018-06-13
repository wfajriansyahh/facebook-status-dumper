<?php
/*
	Facebook Status/Post Scrapper
	Waktu dibuat 04 Juni 2018
*/
$fbid  = $argv[1];
$token = "";
function request($url, $cookie = 0, $data = 0, $httpheader = array()){
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64; rv:56.0) Gecko/20100101 Firefox/56.0");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	if($httpheader) curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	if($cookie) curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	if ($data):
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	endif;
	$response = curl_exec($ch);
	$httpcode = curl_getinfo($ch);
	if(!$httpcode) return false; else{
		$header = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		$body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		curl_close($ch);
		return array($header, $body);
	}
}
function getFeed($url){
	global $fbid;
	$a = json_decode(request($url)[1]);
	if (isset($a->data)) {
		foreach($a->data as $data){
			if ($data->from->id == $fbid) {
				$id_post = $data->id;
				$text_post = (isset($data->message) ? $data->message : '[No Text]');
				$type_post = $data->type;
				$linkPhoto = (isset($data->picture) ? $data->picture : '[No Picture]');
				$from_post = $data->from->name;
				$linkpost = "https://facebook.com/$id_post";
				$tgl = date_parse($data->created_time);
				echo $tgl['day']."/".$tgl['month']."/".$tgl['year']." => [$id_post] ($from_post) [$linkpost] [$type_post] $linkPhoto $text_post".PHP_EOL;
				$file = "status-$fbid.html";    
				$handle = fopen($file, 'a');
				fwrite($handle, $tgl['day']."/".$tgl['month']."/".$tgl['year']." => [$id_post] ($from_post) [$linkpost] [$type_post] $linkPhoto $text_post<br/>");
				fclose($handle);
			}
		}
	}
}
function getPagination($url) {
	$a = json_decode(request($url)[1]);
	if (isset($a->paging)) {
		return $a->paging->next;
	} else {
		return false;
	}
}
$url = "https://graph.facebook.com/$fbid/feed?access_token=$token&limit=100";
getFeed($url);
$cursor = getPagination($url);
while(true) {
	if ($cursor == "") {
		break;
	}
	getFeed($cursor);
	$cursor = getPagination($cursor);
	echo "Sleeping 5sec\n";
	sleep(5);
}