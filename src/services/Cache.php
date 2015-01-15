<?php
/**
 * @link http://buildwithcraft.com/
 * @copyright Copyright (c) 2013 Pixel & Tonic, Inc.
 * @license http://buildwithcraft.com/license
 */

namespace craft\app\services;

use Craft;
use craft\app\cache\ApcCache;
use craft\app\cache\DbCache;
use craft\app\cache\FileCache;
use craft\app\cache\MemCache;
use craft\app\cache\WinCache;
use craft\app\cache\XCache;
use craft\app\cache\ZendDataCache;
use craft\app\enums\CacheMethod;
use craft\app\enums\ConfigFile;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Class Cache service.
 *
 * An instance of the Cache service is globally accessible in Craft via [[Application::cache `Craft::$app->getCache()`]].
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0
 */
class Cache extends Component
{
	// Properties
	// =========================================================================

	/**
	 * @var null
	 */
	private $_cacheComponent = null;

	// Public Methods
	// =========================================================================

	/**
	 * Do the ole' Craft::$app->getCache() switcharoo.
	 *
	 * @return null
	 * @throws InvalidConfigException
	 */
	public function init()
	{
		$cacheMethod = Craft::$app->config->get('cacheMethod');

		switch ($cacheMethod)
		{
			case CacheMethod::APC:
			{
				$this->_cacheComponent = new ApcCache();
				break;
			}

			case CacheMethod::Db:
			{
				$this->_cacheComponent = new DbCache();
				$this->_cacheComponent->gcProbability = Craft::$app->config->get('gcProbability', ConfigFile::DbCache);
				$this->_cacheComponent->cacheTableName = Craft::$app->getDb()->getNormalizedTablePrefix().Craft::$app->config->get('cacheTableName', ConfigFile::DbCache);
				$this->_cacheComponent->autoCreateCacheTable = true;
				break;
			}

			case CacheMethod::File:
			{
				$this->_cacheComponent = new FileCache();
				$this->_cacheComponent->cachePath = Craft::$app->config->get('cachePath', ConfigFile::FileCache);
				$this->_cacheComponent->gcProbability = Craft::$app->config->get('gcProbability', ConfigFile::FileCache);
				break;
			}

			case CacheMethod::MemCache:
			{
				$this->_cacheComponent = new MemCache();
				$this->_cacheComponent->servers = Craft::$app->config->get('servers', ConfigFile::Memcache);
				$this->_cacheComponent->useMemcached = Craft::$app->config->get('useMemcached', ConfigFile::Memcache);
				break;
			}

			case CacheMethod::WinCache:
			{
				$this->_cacheComponent = new WinCache();
				break;
			}

			case CacheMethod::XCache:
			{
				$this->_cacheComponent = new XCache();
				break;
			}

			case CacheMethod::ZendData:
			{
				$this->_cacheComponent = new ZendDataCache();
				break;
			}

			default:
			{
				throw new InvalidConfigException('Unsupported cacheMethod config setting value: '.$cacheMethod);
			}
		}

		// Init the cache component.
		$this->_cacheComponent->init();

		// Init the cache service.
		parent::init();
	}

	/**
	 * Stores a value identified by a key into cache.  If the cache already contains such a key, the existing value and
	 * expiration time will be replaced with the new ones.
	 *
	 * @param string            $id         The key identifying the value to be cached.
	 * @param mixed             $value      The value to be cached.
	 * @param int $expire                   The number of seconds in which the cached value will expire. 0 means never
	 *                                      expire.
	 * @param \ICacheDependency $dependency Dependency of the cached item. If the dependency changes, the item is
	 *                                      labeled invalid.
	 *
	 * @return bool true if the value is successfully stored into cache, false otherwise.
	 */
	public function set($id, $value, $expire = null, $dependency = null)
	{
		if ($expire === null)
		{
			$expire = Craft::$app->config->getCacheDuration();
		}

		return $this->_cacheComponent->set($id, $value, $expire, $dependency);
	}

	/**
	 * Stores a value identified by a key into cache if the cache does not contain
	 * this key. Nothing will be done if the cache already contains the key.
	 *
	 * @param string            $id         The key identifying the value to be cached.
	 * @param mixed             $value      The value to be cached.
	 * @param int $expire                   The number of seconds in which the cached value will expire. 0 means never
	 *                                      expire.
	 * @param \ICacheDependency $dependency Dependency of the cached item. If the dependency changes, the item is
	 *                                      labeled invalid.
	 *
	 * @return bool true if the value is successfully stored into cache, false otherwise.
	 */
	public function add($id, $value, $expire = null, $dependency = null)
	{
		if ($expire === null)
		{
			$expire = Craft::$app->config->getCacheDuration();
		}

		return $this->_cacheComponent->add($id, $value, $expire, $dependency);
	}

	/**
	 * Retrieves a value from cache with a specified key.
	 *
	 * @param string $id A key identifying the cached value
	 *
	 * @return mixed The value stored in cache, false if the value is not in the cache, expired if the dependency has
	 *               changed.
	 */
	public function get($id)
	{
		// In case there is a problem un-serializing the data.
		try
		{
			$value = $this->_cacheComponent->get($id);
		}
		catch (\Exception $e)
		{
			Craft::log('There was an error retrieving a value from cache with the key: '.$id.'. Error: '.$e->getMessage());
			$value = false;
		}

		return $value;
	}

	/**
	 * Retrieves multiple values from cache with the specified keys. Some caches (such as memcache, apc) allow
	 * retrieving multiple cached values at one time, which may improve the performance since it reduces the
	 * communication cost. In case a cache does not support this feature natively, it will be simulated by this method.
	 *
	 * @param array $ids The list of keys identifying the cached values
	 *
	 * @return array The list of cached values corresponding to the specified keys. The array is returned in terms of
	 *               (key,value) pairs. If a value is not cached or expired, the corresponding array value will be false.
	 */
	public function mget($ids)
	{
		// In case there is a problem un-serializing the data.
		try
		{
			$value = $this->_cacheComponent->get($ids);
		}
		catch (\Exception $e)
		{
			Craft::log('There was an error retrieving a value from cache with the keys: '.implode(',', $ids).'. Error: '.$e->getMessage());
			$value = false;
		}

		return $value;
	}

	/**
	 * Deletes a value with the specified key from cache.
	 *
	 * @param string $id The key of the value to be deleted.
	 *
	 * @return bool If no error happens during deletion.
	 */
	public function delete($id)
	{
		return $this->_cacheComponent->delete($id);
	}

	/**
	 * Deletes all values from cache. Be careful of performing this operation if the cache is shared by multiple
	 * applications.
	 *
	 * @return bool Whether the flush operation was successful.
	 */
	public function flush()
	{
		return $this->_cacheComponent->flush();
	}
}