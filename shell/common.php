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