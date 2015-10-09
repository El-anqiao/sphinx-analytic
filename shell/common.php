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
function packPrefix(string $path)
{
    $path = str_replace('_', '__', trim($path, '/'));
    $arr = explode('/', $path);
    $ret = array();
    for($i = 0, $l = count($arr); $i < $l; $i++) {
        $sliceArr = array_slice($arr, 0, $i + 1);
        $ret[] = implode('_', $sliceArr).'_';
    }
    return $ret;
}

/**
 * 格式化查找的网址
 * @param $path
 * @return string
 */
function packSearchPath($path) {
    $path = str_replace('_', '__', trim($path, '/'));
    $arr = explode('/', $path);
    return implode('_', $arr).'_';
}