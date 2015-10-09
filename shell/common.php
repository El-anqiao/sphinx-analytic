<?php

/**
 * ===========公共文件===================
 */

define('SEARCH_INDEX', 'analytic');
/**
 * 封装主机名
 * @param $host
 * @return int
 */
function packHost($host)
{
    return crc32(strtolower($host));
}

/**
 * 封装网址路径
 * @param $path
 * @return mixed
 */
function packPath($path)
{
    return crc32(trim($path, '/'));
}

/**
 * 封装网址前缀
 * @param $path
 * @return array
 */
function packPrefix($path)
{
    $path = trim($path, '/');
    $arr = explode('/', $path);
    $ret = array();

    $sliceArr = array();
    $sliceUnifiedArr = array();
    $l = count($arr);
    for($i = 0; $i < $l; $i++) {
        $sliceArr[] = $arr[$i];
        $sliceUnifiedArr[] = preg_replace('#\d{1,}#', '*', $arr[$i]);

        $ret[] = implode('/', $sliceArr);

        if ($i > 0 && $i < $l && strpos($sliceUnifiedArr[$i - 1],'*') !== false) {
            $ret[] = implode('/', $sliceUnifiedArr);
        }
    }
    if (preg_match('#(.+)_p\d+$#', $sliceArr[$l - 1], $ms)) {
        $sliceArr[$l - 1] = $ms[1];
        $ret[] = implode('/', $sliceArr);
    }
    if (strpos($sliceUnifiedArr[$l - 1], '*') !== false) {
        $ret[] = implode('/', $sliceUnifiedArr);
    }
    $ret = array_map('crc32', $ret);
    return $ret;
}

/**
 * 格式化查找的网址
 * @param $path
 * @return string
 */
function packSearchPath($path) {
    return crc32(trim($path, '/'));
}