<?

/* a nice little class that grabs search results from google
using the RESTful API documented on this page
http://code.google.com/apis/ajaxsearch/documentation/reference.html#_intro_fonje
 */ 

class Google_WebSearch {
	
	public $version = "1.0";  // as of writing, 1.0 is the only valid version.
	public $key = false;
	public $cref = false;
	public $safe = false; // options: active, moderate (default), off
	public $rsz = false;
	public $start = false;
	public $referer = false;
	

	function __construct( $options=array()) {
		if ($options) {
			if ($options['key']) $this->key = $options['key'];
			if ($options['cref']) $this->cref = $options['cref'];
			if ($options['version']) $this->version = $options['version'];
			if ($options['safe']) $this->start = $options['safe'];
			if ($options['rsz']) $this->rsz = $options['rsz'];
			if ($options['start']) $this->start = $options['start'];
			if ($options['referer']) $this->referer = $options['referer'];
			$cref = false;
		}
	}
	
	function search($query,$start=false) {
		if (!$start) $start = $this->start;
		// you'll want to see this page. http://code.google.com/apis/ajaxsearch/documentation/reference.html#_intro_fonje
		$url = "http://ajax.googleapis.com/ajax/services/search/web?v=".$this->version.'&q='.urlencode($query).''; // v-version (req), q-query(req)
		if ($this->key !== false) {
			$url.= '&key='.urlencode($this->key);
		}
		if ($this->cref) {
			$url.= '&cref='.urlencode($this->cref) ;
		}
		if ($this->safe &&  in_array($this->safe,array('active','moderate','off')) ) {
			$url.= '&safe='.urlencode($this->safe);
		}
		if ($start) {
			$url.= '&start='.urlencode($start);
		}
		if ($this->rsz &&  in_array($this->rsz, array('small','medium','large')) ) {
			$url.= '&rsz='.urlencode($this->rsz);
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
		//curl_setopt($ch, CURLOPT_USERPWD, "{$this->user}:{$this->pass}");	// HTTP auth username/password
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);		// we want the data passed back
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);
		
		// need to set referer correctly or else you'll get throttled after several requests.
		if ($this->referer) $referer = $this->referer;
		else $referer = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		curl_setopt($ch, CURLOPT_REFERER, $referer); // needs to be set or else you'll get throttled right away.


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