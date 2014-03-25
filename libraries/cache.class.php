<?php 

//This file cannot be called directly, only included.
if (str_replace(DIRECTORY_SEPARATOR, "/", __FILE__) == $_SERVER['SCRIPT_FILENAME']) {
    exit;
}

class EfrontCacheException extends Exception
{
    const KEY_NOT_FOUND = 1401;
    const KEY_EXPIRED   = 1402;
    const ENTRY_INVALID = 1403;    
}

abstract class EfrontCache 
{
    public static $cacheTimeout = 604800;    //3600*24*7, 1 week
    
    protected static $_instance = null;
    
    /**
     * 
     * @return EfrontCache
     */
    public static function getInstance() {
    	
    	if (is_null(self::$_instance)) {
    		if (function_exists('apc_store')) {
    			self::$_instance = EfrontCache::factory('apc');
    		} else if (function_exists('wincache_ucache_set')) {
    			self::$_instance = EfrontCache::factory('wincache');
    		} else {
    			self::$_instance = EfrontCache::factory('db');
    		}
    	}
    	
    	return self::$_instance;
    }

    public static function factory($method) {
    	if (!$GLOBALS['configuration']['cache_enabled']) {
    		$method = null;	//force dummy cache, which equals to disabled
    	}
    	switch ($method) {
    		case 'apc':      $cache = new EfrontCacheAPC();      break;
    		case 'wincache': $cache = new EfrontCacheWincache(); break;
    		case 'db':		 $cache = new EfrontCacheDB();       break;
    		default:         $cache = new EfrontCacheDummy();    break;
    	}
    	 
    	return $cache;
    }
    
    public abstract function setCache($key, $entity, $timeout = null);
    public abstract function getCache($key);
    public abstract function deleteCache($key);
    
    
    protected static function _encode($parameters) {
    	$key = hash('sha256', G_DBNAME.$parameters);
    	return $key;
    }
}

class EfrontCacheDummy extends EfrontCache
{
	public function setCache($key, $entity, $timeout = null) {
		return false;
	}
	public function getCache($key) {
		return false;
	}
	public function deleteCache($key) {
		return false;
	}	
}

class EfrontCacheDB extends EfrontCache
{
	public function getCache($key) {
		$key = self::_encode($key);
	
		$result = eF_getTableData("cache", "value, timestamp, timeout", "cache_key='".$key."'");
		if (sizeof($result) > 0 && time() - $result[0]['timestamp'] <= self :: $cacheTimeout && ($result[0]['timeout'] && time() - $result[0]['timestamp'] <= $result[0]['timeout'])) {
			return unserialize($result[0]['value']);
		} else {
			return false;
		}
	}
	
	public function setCache($key, $data, $timeout = null) {
		$key    = self :: _encode($parameters);
		
		$values = array("cache_key" => $key, "value" => serialize($data), "timestamp" => time());
		if ($timeout && eF_checkParameter($timeout, 'int')) {
			$values['timeout'] = $timeout;
		}
	
		if (sizeof(eF_getTableData("cache", "value", "cache_key='".$key."'")) > 0) {
			$result = eF_updateTableData("cache", $values, "cache_key='$key'");
		} else {
			$result = eF_insertTableData("cache", $values);
		}
	
		return $result;
	}
	
	public function deleteCache($parameters) {
		$key = self :: _encode($parameters);
	
		eF_deleteTableData("cache", "cache_key='".$key."'");
	}
	
	  
}

class EfrontCacheAPC extends EfrontCache
{
    public function setCache($key, $entity, $timeout = null) {
    	$key = self::_encode($key);
    	return apc_store($key, $entity, $timeout);
    }
    
    public function deleteCache($key) {
    	$key = self::_encode($key);
    	return apc_delete($key);
    }
    
    public function getCache($key) {
    	$key = self::_encode($key);
    	return apc_fetch($key);
    }
    
}

class EfrontCacheWincache extends EfrontCache
{
	public function setCache($key, $entity, $timeout = null) {
		$key = self::_encode($key);
		return wincache_ucache_set($key, $entity, $timeout);
	}

	public function deleteCache($key) {
		$key = self::_encode($key);
		return wincache_ucache_delete($key);
	}

	public function getCache($key) {
		$key = self::_encode($key);
		return wincache_ucache_get($key);
	}

}
