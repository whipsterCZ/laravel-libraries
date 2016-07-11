<?php

/**
 * @author: Daniel Kouba
 *
 * This is helper for handling publishable models.
 * It should be used in EloquentModel
 */


namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class PublishableTrait.
 *
 * @traitUses \Illuminate\Database\Eloquent\Model
 */
trait Publishable {

	public function isPublished($timestamp = null) {
		if ( !isset($timestamp)) {
			$timestamp = time();
		}
		$fromField = $this->getPublishedFromField();
		$toField = $this->getPublishedToField();
		$dateFromPass = true;
		$dateToPass = true;

		if( $this->attributes[$fromField] ) {
			$dateFromPass = date('Y-m-d',strtotime($this->attributes[$fromField])) <= date('Y-m-d',$timestamp);
		}
		if( $this->attributes[$toField] ) {
			$dateToPass = date('Y-m-d',strtotime($this->attributes[$toField])) >= date('Y-m-d',$timestamp);
		}
		return $this->isActive() && $dateFromPass && $dateToPass;
	}

	public function isActive(){
		return isset($this->active) ? $this->active : true;
	}

	public function publishedFrom($format = null){
		$field = $this->getPublishedFromField();
		$this->dates[] = $field;
		if ( isset($this->{$field}) && $this->{$field} ) {
			if ( is_string($this->{$field} ) ) {
				throw new \Exception(sprintf('To use publishable trait add your `%s` field to models `$dates[]` property.',$field));
			}
			if (is_null($format)) {
				$format = defined('DATE_HUMAN') ? DATE_HUMAN : 'd.m.Y';
			}
			return  $this->{$field}->format($format);
		}
		return '';
	}

	public function publishedTo($format = null){
		$field = $this->getPublishedToField();
		if ( isset($this->{$field}) && $this->{$field} ) {
			if ( is_string($this->{$field} ) ) {
				throw new \Exception(sprintf('To use publishable trait add your `%s` field to models `$dates[]` property.',$field));
			}
			if (is_null($format)) {
				$format = defined('DATE_HUMAN') ? DATE_HUMAN : 'd.m.Y';
			}
			return  $this->{$field}->format($format);
		}
		return '';
	}

	public static function getPublishedFromField()	{
		return isset(static::$publishedFromField) ? static::$publishedFromField : 'published_from';
	}

	public static function getPublishedToField()	{
		return isset(static::$publishedToField) ? static::$publishedToField : 'published_to';
	}

	/**
	 * Scope a query to only include active users.
	 *
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public function scopePublished($query)	{

		if ( isset($this->active) ) {
			$query->where('active',1);
		}
		$fromField = $this->getPublishedFromField();
		$toField = $this->getPublishedToField();
		$now = date('Y-m-d');
		if ( $fromField) {
			$query->whereRaw("($fromField IS NULL OR DATE($fromField) <= '$now')");
		}
		if ( $toField ) {
			$query->whereRaw("($toField IS NULL OR DATE($toField) >= '$now')");
		}
		return $query;

	}



}
