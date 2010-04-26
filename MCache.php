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


require_once('Cache.php');


/**
 * MCache is a Memcached specific extension of the Cache class.
 *
 * @author jleider
 */
class MCache extends Cache {

  private $memcache;
  private $server = "localhost";
  private $port = 11211;


  /**
   * Connect to memcache and set key.
   */
  public function __construct($key) {
    $this->memcache = new Memcache;
    $this->memcache->connect($this->server, $this->port);
    parent::__construct($key);
  }

  /**
   * Make sure we call Cache->__destruct() first so variables arent unset too early
   */
  public function __destruct() {
    parent::__destruct();
  }

  /**
   * Set the data into Memcached.
   */
  protected function _setCache() {
    $this->memcache->set($this->_getKey(), $this->data, MEMCACHE_COMPRESSED, $this->expiration);
  }

  /**
   * Delete the data from Memcached.
   */
  protected function _deleteCache() {
    $this->memcache->delete($this->_getKey());
  }

  /**
   * Fetch the cached data from Memcached.
   */
  protected function _getCache() {
    $this->data = $this->memcache->get($this->_getKey());
    if(!$this->data) {
      $this->expiration = 0;
    }
  }


  private function _getKey() {
    asort($this->key);
    $key = '';
    foreach($this->key as $k => $v) {
      $key .= $k.':'.$v;
    }
    return $key;
  }

}


class MCacheException extends CacheException {}


