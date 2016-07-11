<?php

/**
 * @author: Daniel Kouba
 *
 * This should be used in FormRequests - Models can utilize it too, but this logic should be done outside model  IMHO
 * It provide usefull helpers for retrieving formatted nad sanitized data for models
 * @see UserRequest
 */
namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

trait InputHelpers {

	/**
	 * @param $name
	 * @param null $format
	 * @return Carbon
	 * @throws \Exception
	 */
	public function carbonFromInput($name, $format = null){
		$value = \Input::get($name);
		if(!isset($value)) {
			throw new \Exception("Input {$name} has not been found");
		}
		if (!isset($format)) {
			$format = \Input::get($name.'_format');
		}
		if (!isset($format)) {
			throw new \Exception("Input {$name}_format has not been found");
		}
		return $this->carbon($value,$format);
	}

	/**
	 * @param $value
	 * @param $format
	 * @return null|Carbon
	 */
	public function carbon($value, $format){
		if ( $value ) {
			if ( is_numeric($value)) {
				return \Carbon\Carbon::createFromTimestamp($value);
			}
			return \Carbon\Carbon::createFromFormat($format,$value);
		}
		return null;

	}


	/**
	 * get and format value for ID usage
	 * @param $name
	 * @return null|id
	 */
	public function idFromInput($name) {
		$value = \Input::get($name,null);
		$value = $this->nullIfBlank($value);
		return $this->idValue($value);
	}

	public function boolFromInput($name){
		return (bool) \Input::get($name, false);
	}

	public function idValue($value){
		return $value ? (int)$value : null;
	}

	public function nullIfBlank($value)	{
		return trim($value) !== '' ? $value : null;
	}

	public function intValue($value)	{
		return trim($value) !== '' ? (int)$value : null;
	}

	public function intFromInput($name){
		$value = \Input::get($name,null);
		$value = $this->nullIfBlank($value);
		return $this->intValue($value);
	}

	protected $_model = null;
	/**
	 * @return Model
	 */
	public function modelFromRouteOr($defaultInstance = null){
		if ( is_null($this->_model)) {
			$route = \Route::current();
			$model = collect($route->parameters())->first(function ($key, $value) use ($defaultInstance) {
				if ($defaultInstance instanceof Model) {
					return $value instanceof $defaultInstance;
				}
				return $value instanceof Model;
			});
			$this->_model = $model ?: $defaultInstance;
		}
		return $this->_model;
	}

	public function idFromRoute($default = 0){
		$model = $this->modelFromRouteOr();
		if ($model && $model->exists) {
			return $model->id;
		}
		return $default;
	}
}
