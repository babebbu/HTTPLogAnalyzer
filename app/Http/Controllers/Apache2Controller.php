<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\ElasticSearchQuery;

class Apache2Controller extends Controller
{
	private $countries;
	private $dateStart;
	private $dateEnd;
	private $events;
	private $histogram;
	private $limitStart;
	private $limitEnd;
	private $os;
	private $range;
	private $size;
	private $topSites;
	private $topPages;
	private $browsers;
	
	public function __construct ()
	{
		$page = intval(1);
		$this->size = intval(25);
		
		$this->limitEnd = $page * 25;
		$this->limitStart = $this->limitEnd - 25;
		
		$this->range = '7d';
	}
	
	private function queryDispatcher($domain = '*')
	{
		$this->queryLatestEvent($domain);
		$this->queryTopSites($domain);
		$this->queryEventHistrogram($domain);
		$this->queryCountryCount($domain);
		
		if ($domain != '*') {
			$this->queryBrowserCount($domain);
			$this->queryOSCount($domain);
			$this->queryTopPages($domain);
		}
	}
	
	private function queryLatestEvent ($domain)
	{
		$latestEventQuery = new ElasticSearchQuery('GET', '/filebeat*/_search', '
            {
			    "sort" : [{
			        "@timestamp" : {
			            "order" : "desc"
			        }
			    }],
			    "from" : '.$this->limitStart.', "size" : '.$this->size.',
			    "query": {
			        "bool": {
			            "filter": [{
			                "exists": {
			                    "field": "apache2.access"
			                }
			            },{
			                "wildcard": {
			                    "source": "*'.$domain.'*"
			                }
			            },{
			                "range": {
			                    "@timestamp": {
			                        "gte": "now-'.$this->range.'",
			                        "lt": "now",
			                        "time_zone": "+07:00"
			                    }
			                }
			            }]
			        }
			    }
			}
        ');
		$this->events = $latestEventQuery->execute()->hits->hits;
		
		foreach ($this->events as $id => $value) {
			$siteName = explode('/', $value->_source->source);
			if (in_array($siteName[6], ['proxy_access_log', 'proxy_access_ssl_log', 'access_log', 'access_ssl_log'])){
				$siteName = $siteName[4]; // Main Domain
			}
			else {
				$siteName = $siteName[6]; // Sub Domain
			}
			$this->events[$id]->_source->siteName = $siteName;
		}
	}
	
	private function queryTopSites ()
	{
		
		/**
		 * Top Sites
		 * TODO: NEED an OPTIMIZATION to query all unique IP of each website in a single query!
		 */
		
		$topSitesQuery = new ElasticSearchQuery('GET', '/filebeat*/_search', '
	        {
			    "sort" : [{
			        "@timestamp" : {
			            "order" : "desc"
			        }
			    }],
			    "size" : 0,
			    "aggs" : {
			      "uniq_source" : {
			        "terms" : { "field" : "source" }
			      }
			    },
			    "query": {
			        "bool": {
			            "filter": [{
			                "exists": {
			                    "field": "apache2.access"
			                }
			            },{
			                "wildcard": {
			                    "source": "*"
			                }
			            },{
			                "range": {
			                    "@timestamp": {
			                        "gte": "now-'.$this->range.'",
			                        "lt": "now",
			                        "time_zone": "+07:00"
			                    }
			                }
			            }]
			        }
			    }
			}
	    ');
		
		$topSitesQuery = $topSitesQuery->execute();
		
		$topSites = [];
		foreach ($topSitesQuery->aggregations->uniq_source->buckets as $item) {
			// Domain or Sub-Domain?
			$siteName = explode('/', $item->key);
			if (in_array($siteName[6], ['proxy_access_log', 'proxy_access_ssl_log', 'access_log', 'access_ssl_log'])){
				$siteName = $siteName[4]; // Main Domain
			}
			else {
				$siteName = $siteName[6]; // Sub Domain
			}
			$item->key = $siteName;
			$topSites[] = $item;
		}
		
		$topSitesAggregate = [];
		
		foreach($topSites as $id => $item){;
			if (isset($topSitesAggregate[$item->key]['requestCount'])) {
				$topSitesAggregate[$item->key]['requestCount'] += $item->doc_count;
			}
			else {
				$topSitesAggregate[$item->key]['requestCount'] = $item->doc_count;
			}
		}
		
		//dd($topSitesAggregate);
		
		/**
		 * Fetch Unique Remote IP per Sites
		 */
		
		foreach ($topSitesAggregate as $siteName => $value) {
			$propertiesQuery = new ElasticSearchQuery('GET', '/filebeat-*/_search', '
		        {
				    "sort" : [{
				        "@timestamp" : {
				            "order" : "desc"
				        }
				    }],
				    "size" : 10,
				    "aggs" : {
				        "distinct_remote_ip" : {
				            "cardinality" : { "field" : "apache2.access.remote_ip" }
				        },
				        "uniq_country" : {
				            "terms" : { "field" : "apache2.access.geoip.country_iso_code" }
				        }
				    },
				    "query": {
				        "bool": {
				            "filter": [{
				                "exists": {
				                    "field": "apache2.access"
				                }
				            },{
				                "wildcard": {
				                    "source": "*' . $siteName . '*"
				                }
				            },{
				                "range": {
				                    "@timestamp": {
				                        "gte": "now-7d/d",
				                        "lt": "now/d",
				                        "time_zone": "+07:00"
				                    }
				                }
				            }]
				        }
				    }
				}
	        ');
			
			$properties = $propertiesQuery->execute();
			$uniqueIP = $properties->aggregations->distinct_remote_ip->value;
			$mostVisitedCountry = $properties->aggregations->uniq_country->buckets[0]->key;
			$topSitesAggregate[$siteName]['uniqueIP'] = $uniqueIP;
			$topSitesAggregate[$siteName]['mostVisitedCountry'] = (new \League\ISO3166\ISO3166)->alpha2($mostVisitedCountry)['name'];
		}
		
		$this->topSites = $topSitesAggregate;
	}
	
	private function queryEventHistrogram ($domain)
	{
		/**
		 * TODO Optimize the query to limit in range
		 */
		$histogramQuery = new ElasticSearchQuery('GET', '/filebeat-*/_search?size=0', '
			{
			    "aggs" : {
			        "timestamp_over_time" : {
			            "date_histogram" : {
			                "field" : "@timestamp",
			                "interval" : "60m",
			                "order": {
			                  "_key" : "desc"
			                }
			            }
			        }
			    },
			    "query": {
			        "bool": {
			            "filter": [{
			                "exists": {
			                    "field": "apache2.access"
			                }
			            },{
			                "wildcard": {
			                    "source": "*'.$domain.'*"
			                }
			            },{
			                "range": {
			                    "@timestamp": {
			                        "gte": "now-'.$this->range.'",
			                        "lt": "now",
			                        "time_zone": "+07:00"
			                    }
			                }
			            }]
			        }
			    }
			}
		');
		$histogramQuery = $histogramQuery->execute();
		
		/**
		 * NOTE: This is a workaround for heavy query. Reducing memory by unset a heavy array
		 */
		$histogramAll = $histogramQuery->aggregations->timestamp_over_time->buckets;
		$histogramData = [];
		
		for ($i=0; $i<24; $i++) {
			$histogramData[$i] = $histogramAll[$i];
		}
		unset($histogramAll);
		
		$this->histogram = $histogramData;
		
		//dd($this->histogram);
		
	}
	
	private function queryBrowserCount ($domain)
	{
		$browsers = (new ElasticSearchQuery('GET', '/filebeat-*/_search', '
			{
			    "sort" : [{
			        "@timestamp" : {
			            "order" : "desc"
			        }
			    }],
			    "from" : 0, "size" : 0,
			    "query": {
			        "bool": {
			            "filter": [{
			                "exists": {
			                    "field": "apache2.access"
			                }
			            },{
			                "wildcard": {
			                    "source": "*'.$domain.'*"
			                }
			            },{
			                "range": {
			                    "@timestamp": {
			                        "gte": "now-'.$this->range.'",
			                        "lt": "now",
			                        "time_zone": "+07:00"
			                    }
			                }
			            }]
			        }
			    },
			    "aggs": {
			      "uniq_browser" : {
			        "terms" : {
			          "field" : "apache2.access.user_agent.name"
			        }
			      }
			    }
			}
		'))->execute()->aggregations->uniq_browser->buckets;
		
		$this->browsers = $browsers;
	}
	
	private function queryOSCount ($domain)
	{
		$os = (new ElasticSearchQuery('GET', '/filebeat-*/_search', '
			{
			    "sort" : [{
			        "@timestamp" : {
			            "order" : "desc"
			        }
			    }],
			    "from" : 0, "size" : 0,
			    "query": {
			        "bool": {
			            "filter": [{
			                "exists": {
			                    "field": "apache2.access"
			                }
			            },{
			                "wildcard": {
			                    "source": "*'.$domain.'*"
			                }
			            },{
			                "range": {
			                    "@timestamp": {
			                        "gte": "now-'.$this->range.'",
			                        "lt": "now",
			                        "time_zone": "+07:00"
			                    }
			                }
			            }]
			        }
			    },
			    "aggs": {
			      "uniq_os" : {
			        "terms" : {
			          "field" : "apache2.access.user_agent.os"
			        }
			      }
			    }
			}
		'))->execute()->aggregations->uniq_os->buckets;
		
		$this->os = $os;
	}
	
	private function queryCountryCount ($domain)
	{
		$this->countries = (new ElasticSearchQuery('GET', '/filebeat-*/_search', '
			{
			    "sort" : [{
			        "@timestamp" : {
			            "order" : "desc"
			        }
			    }],
			    "from" : 0, "size" : 0,
			    "query": {
			        "bool": {
			            "filter": [{
			                "exists": {
			                    "field": "apache2.access"
			                }
			            },{
			                "wildcard": {
			                    "source": "*'.$domain.'*"
			                }
			            },{
			                "range": {
			                    "@timestamp": {
			                        "gte": "now-'.$this->range.'",
			                        "lt": "now",
			                        "time_zone": "+07:00"
			                    }
			                }
			            }]
			        }
			    },
			    "aggs": {
			      "uniq_country" : {
			        "terms" : {
			          "field" : "apache2.access.geoip.country_iso_code"
			        }
			      }
			    }
			}
		'))->execute()->aggregations->uniq_country->buckets;
	}
	
	private function queryTopPages($domain)
	{
		$this->topPages = (new ElasticSearchQuery('GET', '/filebeat-*/_search', '
			{
			    "sort" : [{
			        "@timestamp" : {
			            "order" : "desc"
			        }
			    }],
			    "from" : 0, "size" : 0,
			    "query": {
			        "bool": {
			            "filter": [{
			                "exists": {
			                    "field": "apache2.access"
			                }
			            },{
			                "wildcard": {
			                    "source": "*'.$domain.'*"
			                }
			            },{
			                "range": {
			                    "@timestamp": {
			                        "gte": "now-'.$this->range.'",
			                        "lt": "now",
			                        "time_zone": "+07:00"
			                    }
			                }
			            }]
			        }
			    },
			    "aggs": {
			      "uniq_page" : {
			        "terms" : {
			          "field" : "apache2.access.url"
			        }
			      }
			    }
			}
		'))->execute()->aggregations->uniq_page->buckets;
	}
	
	public function index(Request $request)
	{
		$page = intval($request->query('page', 1));
		$this->size = intval($request->query('size', 25));
		
		$this->limitEnd = $page * 25;
		$this->limitStart = $this->limitEnd - 25;
		
		$this->range = $request->query('range', '7d');
		
		$this->queryDispatcher();
		
		return view('apache2.dashboard')->with([
			'page' => $page,
			'events' => $this->events,
			'range' => $this->range,
			'topSites' => $this->topSites,
			'histogram' => $this->histogram,
			'countries' => $this->countries,
		]);
	}
	
	public function show (Request $request, $domain)
	{
		$page = $request->query('page', 1);
		$this->queryDispatcher($domain);
		return view('apache2.deep')->with([
			'domain' => $domain,
			'browsers' => $this->browsers,
			'events' => $this->events,
			'histogram' => $this->histogram,
			'operatingSystems' => $this->os,
			'page' => $page,
			'topPages' => $this->topPages,
			'countries' => $this->countries,
		]);
	}
	
	public function search (Request $request)
	{
		return redirect(url('/apache2/'.$request->input('domain')));
	}
}
