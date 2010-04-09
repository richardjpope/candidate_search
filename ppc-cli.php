<?php

    require_once('ppc_search.php');

    //options
    $swiches = getopt('s:p:m:');
    $search_term = isset($swiches['s']) ? $swiches['s'] : null;
    $party = isset($swiches['p']) ? $swiches['p'] : null;
    $mode = isset($swiches['m']) ? $swiches['m'] : 'web';

    if(!isset($search_term) || $search_term == ''){
        print " \n\n";
        print "Search google for PPC names, their constituency and a search term of your choosing\n\n";        
        print "-s term to search for(requried)\n";
        print "-p name of the party (defaults to all)\n";
        print "-m search mode, web, blogs or video (defaults to web)\n";        
        print " \n\n";
        exit();
    }

    $ppc_search = new PPCSearch($party);
    $results = $ppc_search->search($search_term, $mode, true);
    vardump($results);

?>