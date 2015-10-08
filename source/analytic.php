<?php

$datas = array(
    crc32('/bbs/32'),
    crc32('/bbs/25/32'),
    crc32('/product/bbs/25/32'),
);

echo <<<XML
<?xml version="1.0" encoding="utf-8"?>
<sphinx:docset>

<sphinx:schema>
    <sphinx:field name="host"></sphinx:field>
    <sphinx:field name="url" attr="string"></sphinx:field>
    <sphinx:attr name="time" type="timestamp"></sphinx:attr>
</sphinx:schema>

<sphinx:document id="1">
    <host>bj.jiehun.com.cn</host>
    <url>_bbs_,bbs_25</url>
    <time>1444317720</time>
</sphinx:document>

<sphinx:document id="2">
    <host>bj.jiehun.com.cn</host>
    <url>_bbs_,_bbs_25_,bbs_25_32</url>
    <time>1444328520</time>
</sphinx:document>

<sphinx:document id="3">
    <host>bj.jiehun.com.cn</host>
    <url>_product_,_product_bbs_,_product_bbs_25_,product_bbs_25_32</url>
    <time>1444385522</time>
</sphinx:document>

<sphinx:document id="4">
    <host>bj.jiehun.com.cn</host>
    <url>_bbs_,_bbs_25_,bbs_25_32</url>
    <time>1444385530</time>
</sphinx:document>

</sphinx:docset>
XML;
