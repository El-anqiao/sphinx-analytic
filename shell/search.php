<?php

//ini_set('display_errors', false);

include __DIR__.'/common.php';
include __DIR__.'/sphinxapi.php';


$sc = new SphinxClient();
$sc->setServer('localhost', 10003);
$sc->setArrayResult(true);

//$hosts = searchHosts($sc);
//print_r($hosts);

$url = 'http://bj.jiehun.com.cn/hunshasheying/';
$total = searchUrl($sc, $url);
echo "通过网址精确匹配：\n";
echo($url."\nnum:".$total."\n\n");



$url = 'http://bj.jiehun.com.cn/zhimingsheying/*/map/';
$total = searchPrefix($sc, $url);
echo "通过网址前缀匹配：\n$url\n";
print_r($total);

$rhosts = sortByRefererHits($sc, 'gz.jiehun.com.cn');
echo "访问次数最多的来源网站：\n";
print_r($rhosts);

//$urls = sortByUrlHits($sc, 'gz.jiehun.com.cn');
//echo "PV最高的网址：\n";
//print_r($urls);
//
//$ips = sortByIpHits($sc, 'gz.jiehun.com.cn');
//echo "访问量最高的IP：\n";
//print_r($ips);



/**
 * 搜索指定url的查询次数
 * @param $sc
 * @param $url
 * @throws
 * @return int
 */
function searchUrl($sc, $url)
{
    $sc->resetFilters();
    $sc->resetGroupBy();

    $info = parse_url($url);
    if (!$info || !isset($info['host'])) {
        throw new Exception('url illegal');
    }

    $host = packHost($info['host']);
    $path = packPath($info['path']);

    $sc->setLimits(0, 0);
    $sc->setMatchMode(SPH_MATCH_EXTENDED);
    $sc->setFilter('host', array($host));
    $sc->setFilter('url', array($path));
    $ret = $sc->query('', SEARCH_INDEX);
    return $ret['total_found'];
}

/**
 * 搜索指定前缀的网址的查询次数
 * @param $sc
 * @param $url
 * @throws
 * @return int
 */
function searchPrefix($sc, $url)
{
    $sc->resetFilters();
    $sc->resetGroupBy();

    $info = parse_url($url);
    if (!$info || !isset($info['host'])) {
        throw new Exception('url illegal');
    }

    $host = packHost($info['host']);
    $maxNum = 20;

    $sc->setMatchMode(SPH_MATCH_EXTENDED);
    $sc->setLimits(0, $maxNum);

    $path = packSearchPath($info['path']);

    $sc->setFilter('host', array($host));
    $sc->setGroupBy('url', SPH_GROUPBY_ATTR, '@count desc');
    $ret = $sc->query('@purl '.$path, SEARCH_INDEX);

    $num = $ret['total_found'];
    $list = array();

    $total = 0;
    if ($num > 0) {
        $tmpTotal = 0;
        foreach($ret['matches'] as $row) {
            $list[] = array(
                'id' => $row['id'],
                'url' => $row['attrs']['url'],
                'count' => $row['attrs']['@count'],
            );
            $tmpTotal += $row['attrs']['@count'];
        }
        if ($num > $maxNum) {
            $total = $tmpTotal + '+';
        } else {
            $total = $tmpTotal;
        }
    }
    return array(
        'host' => $info['host'],
        'prefix' => $info['path'],
        'total' => $total,
        'list'  => $list,
    );
}

/**
 * 通过pv进行排序
 * @param $sc
 * @param $host
 * @return array
 */
function sortByUrlHits($sc, $host)
{
    $sc->resetFilters();
    $sc->resetGroupBy();

    $host = packHost($host);
    $sc->setLimits(0, 20);
    $sc->setMatchMode(SPH_MATCH_BOOLEAN);
    $sc->setFilter('host', array($host));
    $sc->setGroupBy('url', SPH_GROUPBY_ATTR, '@count desc');
    $ret = $sc->query('', SEARCH_INDEX);
    if (!$ret || !$ret['total_found']) {
        return array();
    }
    $urls = array();
    foreach($ret['matches'] as $row) {
        $urls[] = array(
            'id' => $row['id'],
            'url' => $row['attrs']['url'],
            'count' => $row['attrs']['@count'],
        );
    }
    return $urls;
}

/**
 * 按ip浏览数排序
 * @param $sc
 * @param $host
 * @return array
 */
function sortByIpHits($sc, $host)
{
    $sc->resetFilters();
    $sc->resetGroupBy();

    $host = packHost($host);
    $sc->setLimits(0, 20);
    $sc->setMatchMode(SPH_MATCH_BOOLEAN);
    $sc->setFilter('host', array($host));
    $sc->setGroupBy('ip', SPH_GROUPBY_ATTR, '@count desc');
    $ret = $sc->query('', SEARCH_INDEX);

    $ips = array();
    if ($ret && $ret['total_found']) {
        foreach($ret['matches'] as $row) {
            $ip = $row['attrs']['ip'];
            if ($ip < 0) {
                $ip = sprintf('%u', $ip);
            }
            $ip = long2ip($ip);
            $ips[] = array(
                'id' => $row['id'],
                'ip' => $ip,
                'count' => $row['attrs']['@count'],
            );
        }
    }
    return $ips;
}

/**
 * 通过来源进行排序
 * @param $sc
 * @param $host
 */
function sortByRefererHits($sc, $host)
{
    $sc->resetFilters();
    $sc->resetGroupBy();

    $host = packHost($host);
    $sc->setLimits(0, 20);
    $sc->setMatchMode(SPH_MATCH_BOOLEAN);
    $sc->setFilter('host', array($host));
    $sc->setGroupBy('rhost', SPH_GROUPBY_ATTR, '@count desc');
    $ret = $sc->query('', SEARCH_INDEX);

    $rhosts = array();
    $hostsMap = include __DIR__.'/hosts.php';
    if ($ret && $ret['total_found']) {
        foreach($ret['matches'] as $row) {
            $rhost = $row['attrs']['rhost'];

            if (!$rhost) {
                $hostName = '直达';
            } elseif (isset($hostsMap[$rhost])) {
                $hostName = $hostsMap[$rhost];
            } else {
                $hostName = '未知:'.$rhost;
            }

            $rhosts[] = array(
                'id' => $row['id'],
                'host' => $hostName,
                'count' => $row['attrs']['@count'],
            );

        }
    }
    return $rhosts;
}

/**
 * 搜索每个主机分别有多少次请求
 * @param $sc
 * @return array
 */
function searchHosts($sc)
{
    $sc->resetFilters();
    $sc->resetGroupBy();

    $sc->setMatchMode(SPH_MATCH_BOOLEAN);
    $sc->setArrayResult(true);
    $sc->setGroupBy('host', SPH_GROUPBY_ATTR, '@count desc');
    $ret = $sc->query('', SEARCH_INDEX);
    if (!$ret|| !$ret['total_found']) {
        return array();
    }

    $hosts = array();
    $hostsMap = include __DIR__.'/hosts.php';
    foreach($ret['matches'] as $row) {
        $host = $row['attrs']['host'];
        if (isset($hostsMap[$host])) {
            $hosts[] = array(
                'id' => $row['id'],
                'host' => $hostsMap[$host],
                'count' => $row['attrs']['@count'],
            );
        }
    }
    return $hosts;
}
