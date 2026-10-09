<?php
$urls = array(
    'https://www.homej.top/index.html',
    'https://www.homej.top/sql-tools-pc.html',
    'https://www.homej.top/sql-tools-h5.html',
    'https://www.homej.top/ai-agent.html',
    'https://www.homej.top/dbridge.html'
);
$api = 'http://data.zz.baidu.com/urls?site=https://www.homej.top&token=5qtjDTNuNesMQXAw';
$ch = curl_init();
$options =  array(
    CURLOPT_URL => $api,
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POSTFIELDS => implode("\n", $urls),
    CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
);
curl_setopt_array($ch, $options);
$result = curl_exec($ch);
echo $result;

?>