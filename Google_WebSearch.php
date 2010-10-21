<?


class Google_WebSearch {
	
	// Each of these values can be set using constructor or set_options() function. 
	
	public $version = "1.0";  	// As of writing, 1.0 is the only valid version.
	public $rsz = false;		// (optional) Result size. 'small' = result set of 4 (default); 'large' = result set of 8.
	public $hl = false;			// (optional) Host language of application making request.
	public $key = false;		// (optional) If supplied it must be a valid key associated with your site.
	public $cx = false;			// (optional) The unique id for the custom search engine that should be used for this request.
	public $cref = false;		// (optional) The url of a linked Custom Search Engine specification that should be used to satisfy this request.
	public $safe = false;		// (optional) 'active' = highest level of filtering; 'moderate' = moderate safe search (default); 'off' = disables filtering.
	public $lr = false;			// (optional) Restrich search results to documents in this language. (options: http://www.google.com/cse/docs/resultsxml.html#languageCollections)
	public $filter = false;		// (optional) 0 or 1. Turns off / on the duplicate content filter
	
	public $start = false;
	public $referer = false;
	

	function __construct( $options=array()) {
		if ($options) {
			$this->set_options($options);
		}
	}
	
	function set_options($options=array()) {
		if ($options) {
			if ( array_key_exists('version',$options) ) $this->version = $options['version'];
			if ( array_key_exists('rsz',$options) ) $this->rsz = $options['rsz'];
			if ( array_key_exists('hl',$options) ) $this->hl = $options['hl'];
			if ( array_key_exists('key',$options) ) $this->key = $options['key'];
			if ( array_key_exists('cx',$options) ) $this->cx = $options['cx'];
			if ( array_key_exists('cref',$options) ) $this->cref = $options['cref'];
			if ( array_key_exists('safe',$options) ) $this->safe = $options['safe'];
			if ( array_key_exists('lr',$options) ) $this->lr = $options['lr'];
			if ( array_key_exists('filter',$options) ) $this->filter = $options['filter'];
			if ( array_key_exists('referer',$options) ) $this->referer = $options['referer'];
		}		
	}
	
	function search($query,$start=false) {
		if (!$start) $start = $this->start;
		// you'll want to see this page. http://code.google.com/apis/ajaxsearch/documentation/reference.html#_intro_fonje
		$url = "http://ajax.googleapis.com/ajax/services/search/web?v=".$this->version.'&q='.urlencode($query).''; // v-version (req), q-query(req)
		if ($this->key !== false) {
			$url.= '&key='.urlencode($this->key);
		}
		if ($this->rsz !== false &&  in_array($this->rsz, array('small','medium','large'))) {
			$url.= '&rsz='.urlencode($this->rsz);
		}
		if ($this->hl !== false) {
			$url.= '&hl='.urlencode($this->hl);
		}
		if ($this->cx !== false) {
			$url.= '&cx='.urlencode($this->cx);
		}
		if ($this->cref !== false) {
			$url.= '&cref='.urlencode($this->cref);
		}
		if ($this->safe !== false) {
			$url.= '&safe='.urlencode($this->safe);
		}
		if ($this->lr !== false) {
			$url.= '&lr='.urlencode($this->lr);
		}
		if ($this->filter !== false) {
			$url.= '&filter='.urlencode($this->filter);
		}
		if ($start) {
			$url.= '&start='.urlencode($start);
		}
		
		$result_json = $this->makeRequest($url);
		
		if ($result_json && $result_json['page'] && $result_json['httpcode']==200) {
			$this->data = json_decode($result_json['page'],true);
			$this->result = $this->data['responseData'];
			if ($this->data['responseStatus']==200)	return $this->result;
			else return false;
		}		
		else {
			return false;
		}
	}
	
	private function makeRequest($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		
		// need to set referer correctly or else you'll get throttled after several requests.
		if ($this->referer) $referer = $this->referer;
		else $referer = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		curl_setopt($ch, CURLOPT_REFERER, $referer);

		$tries=0;
		$page=false;
		while( $page===false && $tries<= 5) {
			$page = curl_exec($ch);			
			$tries++;
		}
		$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);				
		return array('page'=>$page, 'tries'=>$tries, 'httpcode' => $httpcode);		
	}
}

?>