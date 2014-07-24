<?php
        //check that the parameters exist
    if (!isset($_GET["s"]) or !isset($_GET["e"]) or !isset($_GET["sn"]) or !isset($_GET["i"]) or !isset($_GET["d"])) {
        http_response_code(400);
        exit;
    }

    $site = (rawurldecode($_GET["s"]));     //fetch the tumblr's URL to be searched
    $engine = $_GET["e"];                   //fetch search engine code
    $shortname = htmlspecialchars(rawurldecode($_GET["sn"]), ENT_XML1, UTF-8);  //Should be "Search" in one of the languages Tumblr supports
    $description = $shortname + htmlspecialchars(rawurldecode($_GET["d"]), ENT_XML1, UTF-8);    //{lang:Search} + tumblr username
    $icon16 = htmlspecialchars(rawurldecode($_GET["i"]), ENT_XML1, UTF-8);  //Should be the URL for the 16x16 version of a blog's avatar
    $icon64 = str_replace('16.png', '64.png', $icon16);     //Should return the URL for the 64x64 version of same                    
    $tomorrow = time() + (0 * 24 * 0 * 0);  //Duh
        
    switch ($engine) {  //check that the search engine is a valid option
        case t:         //tumblr
            break;
        case g:         //google    
            break;
        case d:         //duckduckgo
            break;
        default:
            http_response_code(400);    //"Bad request"
            exit;
        }
        
    if (filter_var($site, FILTER_VALIDATE_URL)===false) { //check that the site to be searched is in URL format
        http_response_code(400);
        exit;
    }

    http_response_code(200);    
    header("Cache-Control: private, max-age=86400, s-max-age=0, must-revalidate");  //Have browser cache for =< 24 hours, tell CDNs & proxies not to cache
    header("Content-Type: application/opensearchdescription+xml; charset=utf-8");   
    header("Expires: " .date('r', $tomorrow)); //set to 24 hours from now, formatted like: Sat, 26 Jul 1997 05:00:00 GMT

    echo <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/" xmlns:moz="http://www.mozilla.org/2006/browser/search/">
<InputEncoding>UTF-8</InputEncoding>
<OutputEncoding>UTF-8</OutputEncoding>
<Developer>Taylor "Tigt" Hunt</Developer>
<Contact>tigt@mortropolis.com</Contact>
<ShortName>$shortname</ShortName>
<Description>$description</Description>
<Image height="16" width="16" type="image/png">$icon16</Image>
<Image height="64" width="64" type="image/png">$icon64</Image>
XML;
    switch ($engine) {
        case t:
            echo <<<TUMBLR
<Url type="text/html" method="get" template="$site"search/{searchTerms}"/>
<Attribution>Search data from Tumblr, Inc.</Attribution>
<Url type="application/json" rel="suggestions" template="https://www.tumblr.com/svc/search/landing_typeahead?q={searchTerms}"/>
TUMBLR;
            break;
        case g:
            echo <<<GOOGLE
<Url type="text/html" method="get" template="http://www.google.com/search?q={searchTerms}&amp;as_sitesearch=$site"/>
<Attribution>Search data from Google, Inc.</Attribution>
<Url type="application/x-suggestions+json" rel="suggestions" template="http://suggestqueries.google.com/complete/search?output=firefox&amp;client=firefox&amp;qu={searchTerms}"/>
GOOGLE;
            break;
        case d:
            echo <<<DUCKDUCKGO
<Url type="text/html" method="get" template="http://duckduckgo.com/search.html?site=$site&amp;q={searchTerms}"/>
<Attribution>Search data from DuckDuckGo, Inc.</Attribution>
<Url type="application/x-suggestions+json" rel="suggestions" template="https://ac.duckduckgo.com/ac/?q={searchTerms}&type=list"/>
DUCKDUCKGO;
            break;
        default:
            http_response_code(500);
            exit;
        }

    echo '</OpenSearchDescription>';
?>
