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
 * SQLCache is a SQL specific extension of the Cache class.
 * Currently uses native drupal 5 database abstraction and MySQL specific SQL (REPLACE INTO).
 *
 * @author jleider
 */
class SQLCache extends Cache {

  /**
   * Make sure we call Cache->__destruct() first so variables arent unset too early
   */
  public function __destruct() {
    parent::__destruct();
  }

  /**
   * Set the data into the SQL cache.
   */
  protected function _setCache() {
    $values = array();
    $query = "REPLACE INTO data_cache SET ";
    foreach($this->key as $col => $val) {
      $values[] = $col;
      $values[] = $val;
      $query .= "%s = '%s', ";
    }
    $values[] = 'data';
    $values[] = gzcompress($this->data);
    $values[] = 'expiration';
    $values[] = $this->expiration;
    $query .= "%s = '%s', %s = %d";

    db_query($query, $values);
  }

  /**
   * Delete the data from the SQL cache.
   */
  protected function _deleteCache() {
    $values = array();
    $query = "DELETE FROM data_cache WHERE ";
    foreach($this->key as $col => $val) {
      $values[] = $col;
      $values[] = $val;
      $query .= "%s = '%s' AND ";
    }
    $query = substr($query, 0, -4);

    db_query($query, $values);
  }

  /**
   * Fetch the cached data from SQL.
   */
  protected function _getCache() {
    $values = array();
    $query = "SELECT expiration, data FROM data_cache WHERE ";
    foreach($this->key as $col => $val) {
      $values[] = $col;
      $values[] = $val;
      $query .= "%s = '%s' AND ";
    }
    $query = substr($query, 0, -4);
    $query .= 'LIMIT 1';

    $cachedData = db_fetch_object(db_query($query, $values));
    if($cachedData) {
      $this->expiration = $cachedData->expiration;
      $this->data = gzuncompress($cachedData->data);
    }
  }

}


class SQLCacheException extends CacheException {}


