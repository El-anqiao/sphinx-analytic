<?php

//ini_set('display_errors', false);

include __DIR__.'/common.php';
include __DIR__.'/sphinxapi.php';


$sc = new SphinxClient();
$sc->setServer('localhost', 10003);

//$hosts = searchHosts($sc);
//print_r($hosts);

//$url = 'http://sh.jiehun.com.cn/score/';
//$total = searchUrl($sc, $url);
//echo($url."\nnum:".$total."\n");


$urls = sortByUrlHits($sc, 'gz.jiehun.com.cn');
print_r($urls);

////$sc->setLimits(0, 0);
//$sc->setArrayResult(true);
//$host = 'sh.jiehun.com.cn';
//$host = crc32(strtolower($host));
////$sc->addQuery('@host ^'.$host.'$', 'analytic');
////$sc->addQuery('@url bbs', 'analytic');
////$sc->addQuery('', 'analytic');
//
////$sc->setFilter('host', array($host));
//$sc->setGroupBy('host', SPH_GROUPBY_ATTR, '@count desc');
//
//var_dump($sc->query('', 'analytic'));
////var_dump($sc->RunQueries());

/**
 * 搜索指定url的查询次数
 * @param $url
 */
function searchUrl($sc, $url)
{
    $info = parse_url($url);
    if (!$info || !isset($info['host'])) {
        throw new Exception('url illegal');
    }

    $host = packHost($info['host']);
    $path = packPath($info['path']);

    $sc->setMatchMode(SPH_MATCH_BOOLEAN);
    $sc->setLimits(0, 0);
    $sc->setFilter('host', array($host));
    $sc->addQuery('@url '.$path, SEARCH_INDEX);
    $ret = $sc->runQueries();
    if ($ret) {
        $ret = $ret[0];
    }
    return $ret['total_found'];
}

/**
 * 搜索指定前缀的网址的查询次数
 * @param $prefix
 */
function searchPrefix($prefix)
{

}


function sortByUrlHits($sc, $host)
{
    $host = packHost($host);

    $sc->setMatchMode(SPH_MATCH_BOOLEAN);
    $sc->setFilter('host', array($host));
    $sc->setGroupBy('url', SPH_GROUPBY_ATTR, '@count desc');
    $ret = $sc->query('', SEARCH_INDEX);
    if (!$ret) {
        return array();
    }
    $urls = array();
    foreach($ret['matches'] as $row) {
        $url = $row['attrs']['url'];
        $urls[$url] = $row['attrs']['@count'];
    }
    return $urls;
}

/**
 * 搜索每个主机分别有多少次请求
 * @param $sc
 * @return array
 */
function searchHosts($sc)
{
    $sc->setMatchMode(SPH_MATCH_BOOLEAN);
    $sc->setArrayResult(true);
    $sc->setGroupBy('host', SPH_GROUPBY_ATTR, '@count desc');
    $ret = $sc->query('', SEARCH_INDEX);
    if (!$ret) {
        return array();
    }

    $hosts = array();
    $hostsMap = include __DIR__.'/hosts.php';
    foreach($ret['matches'] as $row) {
        $host = $row['attrs']['host'];
        if (isset($hostsMap[$host])) {
            $hosts[$hostsMap[$host]] = $row['attrs']['@count'];
        }
    }
    return $hosts;
}

