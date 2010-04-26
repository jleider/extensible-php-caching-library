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
class FileCache extends Cache {

  private $cacheDir;

  /**
   * Set key and cacheDir
   */
  public function __construct($key, $dir) {
    $dir = trim($dir);
    $this->cacheDir = (substr($dir, -1) == '/') ? rtrim($dir, '/') : $dir;
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
    $fp = fopen($this->cacheDir.'/'.$this->_getKey().'.cache', 'w');
    $data = $this->expiration."\n".$this->data;
    fwrite($fp, $data);
    fclose($fp);
  }

  /**
   * Delete the data from Memcached.
   */
  protected function _deleteCache() {
    @unlink($this->cacheDir.'/'.$this->_getKey().'.cache');
  }

  /**
   * Fetch the cached data from Memcached.
   */
  protected function _getCache() {
    $file = @file_get_contents($this->cacheDir.'/'.$this->_getKey().'.cache');
    if($file) {
      $pos = strpos($file, "\n");
      $this->expiration = substr($file, 0, $pos);
      $this->data = substr($file, $pos+1);
    }
  }


  private function _getKey() {
    asort($this->key);
    $key = '';
    foreach($this->key as $k => $v) {
      $key .= $k.'-'.$v;
    }
    return $key;
  }

}


class FileCacheException extends CacheException {}


