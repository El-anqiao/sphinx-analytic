<?php

include dirname(__DIR__).'/shell/common.php';

error_reporting(E_ALL);
ini_set('display.errors', true);

// 生成数据源

$path = dirname(__DIR__).'/tmp/20150524.log';
$hostsPath = dirname(__DIR__).'/shell/hosts.php';

$hosts = array();

$fh = fopen($path, 'r');

echo '<?xml version="1.0" encoding="utf-8"?>';
echo '<sphinx:docset>';

echo <<<SCHEMA
<sphinx:schema>
    <sphinx:field name="rhost"></sphinx:field>
    <sphinx:attr name="url" type="int"></sphinx:attr>
    <sphinx:attr name="host" type="int"></sphinx:attr>
    <sphinx:attr name="time" type="timestamp"></sphinx:attr>
    <sphinx:attr name="ip" type="bigint"></sphinx:attr>
</sphinx:schema>
SCHEMA;

$id = 1000;

while ( ($line = fgets($fh)) !== false) {
    $line = trim($line);
    if (!$line) {
        continue;
    }
    // ip
    $pos = strpos($line, ' ');
    $row = array();
    $row['ip'] = ip2long(substr($line, 0, $pos));

    $line = substr($line, $pos + 1);

    // 访问时间
    $pos = strpos($line, ' ');
    $day = substr($line, 0, $pos);
    $line = substr($line, $pos + 1);

    $pos = strpos($line, ' ');
    $time = substr($line, 0, $pos);
    $line = substr($line, $pos + 1);
    $row['time'] = strtotime($day.$time);

    // 访问地址
    $pos1 = strpos($line, '"GET ') + 5;
    $pos = strpos($line, '"', $pos1);
    $url = 'http://'.substr($line, $pos1, $pos - $pos1);
    $line = substr($line, $pos + 1);

    $info = parse_url($url);
    if (!$info || !isset($info['host']) || !isset($info['path'])) {
        continue;
    }
    $row['host'] = packHost($info['host']);

    if (!isset($hosts[$row['host']])) {
        $hosts[$row['host'].''] = strtolower($info['host']);
    }

    $row['url'] = packPath($info['path']);
    if (isset($info['query'])) {
        parse_str($info['query'], $args);
        $row['qs'] = $args;
    } else {
        $row['qs'] = array();
    }

    // referer
    $pos1 = strpos($line, '"') + 1;
    $pos = strpos($line, '"', $pos1);
    $url = trim(substr($line, $pos1, $pos - $pos1));
    $line = substr($line, $pos + 1);
    $info = parse_url($url);

    if (!$info || !isset($info['host'])) {
        $row['rhost'] = '';
        $row['referer'] = '';
    } else {
        $row['rhost'] = $info['host'];
        $row['referer'] = $url;
    }

    // ua
    $pos1 = strpos($line, '"') + 1;
    $pos = strpos($line, '"', $pos1);
    $row['ua'] = substr($line, $pos1, $pos - $pos1);
    $line = trim(substr($line, $pos + 1));

    // 用户标识
    $row['uuid'] = !$line ? '' : $line;

    $id++;
    writeDoc($id, $row);
}

echo '</sphinx:docset>';

fclose($fh);

file_put_contents($hostsPath, "<?php\nreturn ".var_export($hosts, true).';');

function writeDoc($id, $data) {
    echo "<sphinx:document id=\"{$id}\">\n";
    foreach($data as $key => $value) {
        if ($key == 'referer' || $key == 'ua') {
            continue;
        }
        if (!is_array($value)) {
            if (strpos($value, '&') !== false) {
                printf("<%s><![CDATA[%s]]></%s>\n", $key, $value, $key);
            } else {
                printf("<%s>%s</%s>\n", $key, $value, $key);
            }
        }
    }
    echo "</sphinx:document>\n";
}
