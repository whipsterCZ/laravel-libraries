<?php
/**
 * Created by PhpStorm.
 * User: whipstercz
 * Date: 15/09/15
 * Time: 12:25
 */

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Collection;

trait Filter {

	/**
	 * Should return value which represents getting value from filter.
	 * All other values causes filter to save value
	 * @return string
	 */
	abstract protected function filterGetValue();

	/**
	 * Get or set dateTime
	 * @param string $key
	 * @param mixed $value Carbon|DateTime|int
	 * @param int|null $default
	 * @return Carbon|null
	 */
	protected function filterDateTime($key, $value, $default = null){
		$key = 'filter.'.$key;
		if ( $value !== $this->filterGetValue() ) {
			//Forget value
			if ($value === null || empty($value)) {
				\Session::forget($key);
				return null;
			}
			//Set value
			if ( $value instanceof \DateTime ) {
				$value = $value->getTimestamp();
			}
			if ( (int)$value != $value ) {
				throw new \InvalidArgumentException($key.' value is not timestamp');
			}
			\Session::set($key, $value);
		}

		//get Value
		$timestamp = \Session::get($key,$default);
		if ($timestamp) {
			return Carbon::createFromTimestamp($timestamp);
		}
		return null;
	}

	/**
	 * Get or set bool
	 * @param string $key
	 * @param mixed $value
	 * @param bool $default
	 * @return bool|null
	 */
	protected function filterBool($key, $value, $default = false){
		$key = 'filter.'.$key;
		if ( $value !== $this->filterGetValue() ) {
			//Forget value
			if ($value === null || $value ==="") {
				\Session::forget($key);
				return null;
			}
			//Set value
			\Session::set($key, (bool)$value);
		}
		//get Value
		return \Session::get($key, (bool)$default);
	}

	/**
	 * set or get string
	 * @param string $key
	 * @param string $value
	 * @param string $default
	 * @return string|null
	 */
	protected function filterString($key, $value, $default = null){
		$key = 'filter.'.$key;
		if ( $value !== $this->filterGetValue() ) {
			//Forget value
			if ($value === null || $value==='') {
				\Session::forget($key);
				return null;
			}
			//Set value
			\Session::set($key, (string)$value);
		}
		//get Value
		if ($default !== null) {
			$default = (string)$default;
		}
		return \Session::get($key,$default);
	}

	/**
	 * set or get interger
	 * @param string $key
	 * @param int|string $value
	 * @param int $default
	 * @return int|null
	 */
	protected function filterInteger($key, $value, $default = null){
		$key = 'filter.'.$key;
		if ( $value !== $this->filterGetValue() ) {
			//Forget value
			if ($value === null || $value==='') {
				\Session::forget($key);
				return null;
			}
			//Set value
			if ( (int)$value != $value ) {
				throw new \InvalidArgumentException($key.' value is not integer');
			}
			\Session::set($key, (int)$value);
		}
		//get Value
		if ($default !== null) {
			$default = (int)$default;
		}
		return \Session::get($key,$default);
	}

	/**
	 * set or get float
	 *
	 * @param string $key
	 * @param float|string $value
	 * @param float|null $default
	 * @return float|null
	 */
	protected function filterFloat($key, $value, $default = null){
		$key = 'filter.'.$key;
		if ( $value !== $this->filterGetValue() ) {
			//Forget value
			if ($value === null || $value==='') {
				\Session::forget($key);
				return null;
			}
			//Set value
			if ( (float)$value != $value) {
				throw new \InvalidArgumentException($key.' value is not float');
			}
			\Session::set($key, (float)$value);
		}
		//get Value
		if (null !== $default) {
			$default = (float)$default;
		}
		return \Session::get($key, $default);
	}

	protected function filterArray($key, $value, $default=[]) {
		$key = 'filter.'.$key;
		if ( $value !== $this->filterGetValue() ) {
			//Forget value
			if ($value === null) {
				\Session::forget($key);
				return null;
			}
			//Set value
			if ( $value instanceof Collection) {
				$value = $value->toArray();
			}
			if ( !is_array($value)) {
				throw new \InvalidArgumentException($key.' value is not array');
			}
			\Session::set($key, (array)$value);
		}
		//get Value
		if ( $default instanceof Collection) {
			$default = $default->toArray();
		}
		if ( !is_array($default)) {
			throw new \InvalidArgumentException($key.' - default value is not array');
		}
		return \Session::get($key, $default);
	}

	/**
	 * forget all stored data
	 */
	protected function filterForgetAll(){
		\Session::forget('filter');
	}

	/*
	 * forget stored value(s) for section - use laravel dot arrays
	 * @param string $key
	 */
	protected function filterForgetSection($key){
		$key = 'filter.'.$key;
		\Session::forget($key);
	}
}