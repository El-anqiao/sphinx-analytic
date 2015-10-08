<?php

include __DIR__.'/sphinxapi.php';

$sc = new SphinxClient();
$sc->SetServer('localhost', 10003);
$sc->setMatchMode(SPH_MATCH_EXTENDED);
$sc->AddQuery('@url _bbs_', 'analytic');
//$sc->SetGroupBy('@url');
var_dump($sc->RunQueries());