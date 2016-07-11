<?php

/**
 * @author: Daniel Kouba
 *
 * Trait with updation date and user helpers
 * It should be used in EloquentModel
 */


namespace App\Traits;


trait UpdatedBy
{
	public function updatedAt($format = null){
		$columnName = self::UPDATED_AT;
		if ( isset($this->{$columnName}) && $this->{$columnName} ) {
			if (is_null($format)) {
				$format = defined('DATE_HUMAN') ? DATE_HUMAN : 'd.m.Y';
			}
			return $this->{$columnName}->format($format);
		}
		return '';
	}

	public function updatedBy(){
		$columnName = $this->updatorIdField();
		if ( isset($this->{$columnName}) && $this->{$columnName} ) {
			return $this->updator->name;
		}
		return '';
	}

	public function updatedByWithDate($prefix = "Updated by ", $separator = " | at " ,$format=null){
		$columnName = $this->updatorIdField();
		return $prefix . implode($separator, [$this->updatedBy($columnName), $this->updatedAt($format)]);
	}

	public static function bootUpdatedBy() {
		$columnName = self::updatorIdField();
		static::saving(function($model) use ($columnName){
			if ( auth()->check() ) {
				$model->{$columnName} = auth()->user()->id;
			}
		});
	}

	public function updator(){
		return $this->belongsTo('App\Models\User', $this->updatorIdField()  );
	}

	public static function updatorIdField()	{
		return isset(static::$updator_id_field) ? static::$updator_id_field : 'updator_id';
	}


}