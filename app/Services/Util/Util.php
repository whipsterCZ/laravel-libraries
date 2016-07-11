<?php
/**
 * @author: Daniel Kouba
 */

namespace App\Services\Util;

use Illuminate\Contracts\Support\MessageProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;

class Util
{

	/**
	 *
	 * get array of LABELS for html SELECT or return selected OPTION
	 * @param $options
	 * @param $selected
	 * @param bool $throwException
	 * @return Collection|mixed|string
	 * @throws \Exception
	 */
	public function optionsOrLabel($options, $selected, $throwException = false){
		$options = collect($options);
		if (isset($selected)) {
			if ( $options->has($selected) ) {
				return $options->get($selected);
			}
			$message = "Missing value for '{$selected}'";
			if ( $throwException ) {
				throw new \Exception($message);
			}
			return $message;
		}
		return $options;
	}

	/**
	 * @param $array
	 * @param bool $useKeys
	 * @return string for laravel validator
	 */
	public function validationIn($array, $useKeys = true){
		$collection = collect($array);
		$items = $useKeys ? $collection->keys() : $collection;
		$in = $items->implode(',');
		return 'in:' . $in;
	}

	public function validationUnique(Model $model, $column = 'name',$allowedId = null){
		$table = $model->getTable();
		if (is_null($allowedId) ) {
			$allowedId = (int)$model->id;
		}
		if ($allowedId) {
			return "unique:{$table},{$column},{$allowedId}";
		}
		return "unique:{$table},{$column}";
	}

	public function months($selected = null){
		$options = [
			1 => "January",   2 => "February",   3 => "March",
			4 => "April",     5 => "May",         6 => "June",
			7 => "July",      8 => "August",     9 => "September",
			10=> "October",  11=> "Novemeber",  12=> "December"
		];

		return Util::optionsOrLabel($options,$selected);
	}

	public function monthQuarterArray(){
		return [1=>1,   2=>1,   3=>1,
			4=>2,   5=>2,   6=>2,
			7=>3,   8=>3,   9=>3,
			10=>4,  11=>4,  12=>4,
		];
	}

	/**
	 * @param int $quarter
	 * @return array with months in quarter
	 */
	public function quarterMonths($quarter){
		return collect($this->monthQuarterArray())
			->filter(function ($v) use ($quarter){
				return $v==$quarter;
			})->keys();
	}

	public function monthQuarter($month){
		return $this->monthQuarterArray()[(int)$month];
	}

	public function prefixedMessageBag(MessageProvider $messageProvider,$prefix){
		$messageBag = $messageProvider->getMessageBag();

		$messages = [];
		foreach ($messageBag->toArray() as $key => $message) {
			$messages[$key.$prefix] = $message;
		}
//		dd($messageBag->toArray());
		return new MessageBag($messages);
	}

	public function YesNoOptions($selected = null){
		$options = [
			0 => "No",
			1 => "Yes",
		];
		return $this->optionsOrLabel($options,$selected);
	}

	public function divide($a,$b, $fallbackValue = "-"){
		if ($a==0 && $b!==0) return 0;
		if ($a==0 || $b ==0) return $fallbackValue;
		return $a/$b;
	}

	public function wpSlug($text) {
		$url = $text;
		$url = preg_replace('~[^\\pL0-9_]+~u', '-', $url);
		$url = trim($url, "-");
		$url = iconv("utf-8", "us-ascii//TRANSLIT", $url);
		$url = strtolower($url);
		$url = preg_replace('~[^-a-z0-9_]+~', '', $url);
		return $url;
	}

	// -----------------------  Migrations  -----------------------

	public function addCreator(Blueprint $table){
		$table->unsignedInteger('creator_id');
		$table->foreign('creator_id')->references('id')->on('users');
	}

	public function addUpdator(Blueprint $table){
		$table->unsignedInteger('updator_id');
		$table->foreign('updator_id')->references('id')->on('users');
	}

	public function addCreatorAndUpdator(Blueprint $table){
		$this->addCreator($table);
		$this->addUpdator($table);
	}

	public function addPublishDates(Blueprint $table){
		$table->date('published_from')->index();
		$table->date('published_to')->index();
	}

	public function addStaplerFile(Blueprint $table,$name){
		$table->string($name.'_file_name')->nullable();
		$table->integer($name.'_file_size')->nullable();//->after($name.'_file_name');
		$table->string($name.'_content_type')->nullable();//->after($name.'_file_size');
		$table->timestamp($name.'_updated_at')->nullable();//->after($name.'_content_type');
	}
}