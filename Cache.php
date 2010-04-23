<?php

/**
 *  Extensible PHP Caching Library - This collection of classes makes is
 *  easy to customize keys/column names as well as switching from one
 *  back end data store to another.
 *
 *  Copyright (C) 2010  Justin D. Leider
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


/**
 * Base abstract Cache class. Extend this class instead of using it directly.
 *
 * @author jleider
 */
abstract class Cache {

  protected $time;

  protected $key          = array();
  protected $data;
  protected $expiration;
  protected $cacheNow     = false;
  protected $dataPrimed   = false;

  protected $debug = false;


  /**
   * Error messages
   */
  protected static $INVALID_KEY   = 'Key must be an associative array of column => value pairs';
  protected static $NO_CONNECTION = 'Can not connect to cache';
  protected static $NO_KEY        = 'Key has not been set';


  /**
   * Set the cache key.
   *
   * @see Extending class' for further information.
   *
   * @param array $key An associative array with index => value pairs
   */
  public function __construct($key) {
    $this->time = time();
    $this->setKey($key);
  }


  /**
   * Set $this->data. If $data is an array or object, serialize it.
   * Generally called by setCache().
   *
   * @see setCache()
   *
   * @param mixed $data
   */
  public function setData($data) {
    if(is_object($data) || is_array($data)) {
      $this->data = serialize($data);
    } else {
      $this->data = $data;
    }
    $this->dataPrimed = true;
  }


  /**
   * Return the data unseriailzed.
   * Generally called by getCache().
   *
   * @see getCache()
   *
   * @return mixed $data
   */
  public function getData() {
    $data = unserialize($this->data);
    return ($data) ? $data : $this->data;
  }


  /**
   * Set the cache. This is the suggested way of setting the cached data.
   *
   * @param mixed $data
   * @param mixed $expriation unix timestamp or formatted date string
   * @param boolean $cacheNow (Optional) Set to true to force a cache write else defaults to write on __destruct().
   * @see setExpiration() for accepted values
   */
  public function setCache($data, $expiration = null, $cacheNow = false) {
    $this->setData($data);
    $this->setExpiration($expiration);
    $this->cacheNow = $cacheNow;

    if(empty($this->key)) {
      throw new CacheException(Cache::$NO_KEY);
    }

    // Query the respective cache types.
    if($cacheNow) {
      $this->_setCache();
      $this->dataPrimed = false;
    }
  }


  /**
   * Delete data from the cache.
   * @param array $key An associative array of column => value pairs (Optional if key already set)
   */
  public function deleteCache($key = null) {
    if($key) {
      $this->setKey($key);
    }

    // Query the respective cache types
    $this->_deleteCache();
  }


  /**
   * Fetch the data from the cache. This is the suggested way of getting cached data.
   *
   * @param array $key An associative array of column => value pairs (Optional if key already set)
   * @see setKey()
   * @return mixed $data
   */
  public function getCache($key = null) {
    if($key) {
      $this->setKey($key);
    }

    // Query the respective cache types.
    $this->_getCache();
    if($this->is_expired()) {
      return false;
    } else {
      return $this->getData();
    }
  }


  /**
   * Set the $this->expiration time to a future unix timestamp, defaults to 1 day if no expiration given.
   *
   * @param mixed $expriation unix timestamp or formatted date string
   * @see strtotime() for accepted string inputs.
   */
  public function setExpiration($expriation) {
    if(is_numeric($expiration) && $expiration > $this->time) {
      $this->expiration = $expiration;
    } else {
      $this->expiration = strtotime($expiration, $this->time);
      if(!$this->expiration || $this->expiration < $this->time) {
        // Set to daily if something goes wrong
        $this->expiration = $this->time + 86400;
      }
    }
  }


  /**
   * Get $this->expiration time in unix timestamp or pass in a valid date format for a date string.
   *
   * @param string $format @see date()
   * @return mixed unix timestamp or formatted date string
   */
  public function getExpiration($format = null) {
    if($format) {
      return strfrtime($format, $this->expiration);
    } else {
      return $this->expiration;
    }
  }


  /**
   * Set the key for accessing the cache via an associative array.
   * Array must contain column => value pairs for accessing indexes in SQL queries.
   * Any keys set with this function will override any existing keys where column is the same.
   *
   * @param array $key An associative array of column => value pairs
   */
  public function setKey($key) {
    if(empty($key)) {
      throw new CacheException(Cache::$NO_KEY);
    }
    if(!is_array($key)) {
      throw new CacheException(Cache::$INVALID_KEY);
    }
    $this->key = array_merge($this->key, $key);
  }


  /**
   * Returns the current key
   *
   * @return $this->key
   */
  public function getKey() {
    return $this->key;
  }


  protected function is_expired() {
    if(!$this->expiration || $this->expiration < $this->time) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Save the cache if it hasnt been saved already.
   *
   * The destructor will be called even if script execution is stopped using exit()
   */
  public function __destruct() {
    if($this->dataPrimed) {
      $this->_setCache();
    }
  }


  /**
   * Cache type specific queries to fetch data.
   * Sets all returned values from the cache into variables belonging to $this.
   */
  abstract protected function _getCache();

  /**
   * Sets the cache with type specific queries.
   * Pulls all values from already instantiated variables belonging to $this.
   */
  abstract protected function _setCache();

  /**
   * Delete the cache with type specific queries.
   * Pulls all values from already instantiated variables belonging to $this.
   */
  abstract protected function _deleteCache();

}


class CacheException extends Exception {}




