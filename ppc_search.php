<?php

class PPCSearch{

    private $ppcs = null;
    private $ignore = null;
    private $results = array();

    public function __construct($party){
        $this->set_ppcs($party);
        $this->set_ignore();
    }

    public function search($search_term, $mode, $echo = false){
        
        if ($mode != 'web' && $mode != 'blogs' && $mode != 'video' && $mode != 'news' && $mode != 'twitter'){
            trigger_error("Invalid search type");
            exit;
        }
        
        
        //do a google search
        foreach ($this->ppcs as $ppc) {
            if(!in_array($ppc['name'], $this->ignore)){

                $url = "";
                
                if($mode == "twitter"){
                    //TWITTER                           
                    $twitter_results = $this->search_twitter($ppc['name'], $search_term, $ppc['name']);
                    foreach ($twitter_results as $twitter_result) {
                        $url = 'http://twitter.com/' . $twitter_result->from_user . '/status/' . $twitter_result->id;
                        
                        array_push($this->results, array("name" => $ppc['name'],"seat" => $ppc['seat'],"url" => $url));                 

                        //echo?
                        if($echo){
                            print $ppc['name'] . ' (' . $ppc['seat']. ') | ' . $url . "\n";
                        }                        
                    }
                }else{
                    //GOOGLE
                    $search = '"' . $ppc['name'] . '"' . ' + ' . $search_term . " " . $ppc['seat'];                                
                    $google_results = $this->search_google($search, $mode);
                    if(count($google_results) > 0){
                        foreach ($google_results as $google_result) {

                            if($mode == 'blogs'){
                                $url = $google_result->postUrl;
                            }else if ($mode == 'video'){
                                $url = $google_result->url; 
                            }else if ($mode == 'news'){
                                    $url = $google_result->unescapedUrl;                                                       
                            }else if ($mode == 'web'){
                                    $url = $google_result->url;                            
                                }
                            array_push($this->results, array("name" => $ppc['name'],"seat" => $ppc['seat'],"url" => $url));                 

                            //echo?
                            if($echo){
                                print $ppc['name'] . ' (' . $ppc['seat']. ') | ' . $url . "\n";
                            }       
                    
                        }
                    }
                }
            }
        }
    }

    //search google
    function search_google($search, $mode){

        $result = file_get_contents('http://ajax.googleapis.com/ajax/services/search/' . $mode . '?v=1.0&q=' . urlencode($search));
        $result = json_decode($result);
        return $result->responseData->results;
    }

    //search twitter
    function search_twitter($name, $search, $seat){

        $return = array();

        //try and find the twitter page
        $google_results = $this->search_google('"' . $name . '" ' . $seat, 'web');
        $twitter_url = '';
        if(count($google_results) > 0){
            foreach ($google_results as $google_result) {
                if($google_result->visibleUrl == 'twitter.com'){
                    $twitter_url = $google_result->url;
                }
            }
        }

        //if we have a twitter account then try and search it
        if($twitter_url != ''){
            $username =  str_replace('http://twitter.com/', '', $twitter_url);

            $result = file_get_contents('http://search.twitter.com/search.json?q=' . urlencode($search . ' ' . $username));
            $result = json_decode($result); 
            if(count($result->results) > 0){
                foreach ($result->results as $tweet) {
                    array_push($return, $tweet);
                }       
            }
        }
        
        return $return;
    }

    function set_ppcs ($party){
        $result = array();

        if (($handle = fopen("./data/ppcs.csv", "r")) !== FALSE) {
            $count = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if($count > 0){
                    if(!isset($party) || strtolower($data[5]) == strtolower($party)){
                        $item = array('id' => $data[0], 'name' => $data[1], 'email' => $data[2], 'phone' => $data[3], 'address' => $data[4], 'party' => $data[5], 'seat' => $data[6], 'url' => $data[7]);
                        array_push($result, $item);
                    }
                }

                $count ++;
            }
            fclose($handle);
        }

        $this->ppcs = $result;
    }

    function set_ignore (){
        $result = array();

        if (($handle = fopen("./data/ignore.csv", "r")) !== FALSE) {
            $count = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if($count > 0){
                    array_push($result, $data[0]);
                }

                $count ++;
            }
            fclose($handle);
        }
    
        $this->ignore = $result;
    }

 }  

?>