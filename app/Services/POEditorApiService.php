<?php

namespace App\Services;

use jyggen\Curl;
use jyggen\Curl\Request;
use \Exception;

class POEditorApiService
{
	protected $apiUrl = "http://poeditor.com/api/";

	protected $apiToken;
	protected $projectId;
	protected $locales;
	protected $localePath;

	const EOL = "\r\n";
	const TAB = "  ";

	protected $reservedDomains = ['auth','date','messages','passwords','validation'];


	/**
	 * POEditorApiService konstruktor
	 *
	 * @param string $apiToken
	 * @param string $projectId
	 * @param optional array $locales
	 */
	public function __construct($apiToken, $projectId, $locales = array())
	{
		$this->apiToken = $apiToken;
		$this->projectId = $projectId;
		$this->locales = $locales;
		$this->localePath = "../resources/lang";
	}

	/**
	 * Update vsech jazyku
	 * - podle konstruktoru nebo vse, co je na serveru
	 *
	 */
	public function updateAll()
	{
		if (empty($this->locales)) {
			foreach ($this->getProjectLanguages()->list as $language) {
                // en => en-us
				$this->locales[$this->getLocaleName($language->code)] = $language->code;
			}
		}

		foreach ($this->locales as $locale => $country) {
			$this->updateLocale($locale);
		}
	}

	/**
	 * Updatuje vsechny texty pro jeden locale
	 *
	 * @param string $locale
	 * @throws Exception
	 */
	public function updateLocale($locale)
	{

		$retrievedJson = $this->doRequest([
			'action'   => 'export',
			'language' => $this->locales[$locale],
			'type'     => 'json',
		]);
		$decodedContents = json_decode($retrievedJson->getContent());

		if (empty($decodedContents)) {
			throw new Exception("POEditor: empty export result - {$locale}");
		}

		if (!$this->checkLangFolder($locale)) {
			throw new Exception(error_get_last() . ' - ' . $locale);
		}

		//If there are some terms
		if(isset($decodedContents->item)) {
			$request = new Request($decodedContents->item);
			$request->execute();

			if (!$request->isSuccessful()) {
				throw new Exception($request->getErrorMessage());
			}

			$response = $request->getResponse();
			if (empty($response)) {
				throw new Exception("Empty POEditor item result - {$locale}");
			}

			$transData = json_decode($response->getContent());
			$this->parseToPhp(empty($transData) ? array() : $transData, $locale);
		}
	}

	public function getLocaleName($localeCode) {
        if ( ($parts = explode('-',$localeCode)) && count($parts)==2 ) {
            list($locale,$country) = $parts;
            return $locale;
        }
        return $localeCode;
	}

	/**
	 * Volani API
	 *
	 * @param array $postFields
	 * @return string json response
	 * @throws Exception
	 */
	protected function doRequest($postFields)
	{
		$request = new Request($this->apiUrl);
		$request->setOption(CURLOPT_POSTFIELDS,
			$postFields + ['api_token' => $this->apiToken, 'id' => $this->projectId]);
		$request->execute();

		if ($request->isSuccessful()) {
			return $request->getResponse();
		} else {
			throw new Exception($request->getErrorMessage());
		}
	}

	/**
	 * Ulozi polozky k danemu jazyku do php souboru podle prefixu v klici poeditoru
	 * POEditor keys syntaxe: prefix.klic
	 *
	 * @param array $data
	 * @param string $locale
	 */
	protected function parseToPhp($data, $locale)
	{
		$fileList = array();
		$termList = array();
		$statCount = 0;
		$statTranslatedCount = 0;

		foreach ($data as $dataItem) {
			$itemKey = explode(".", $dataItem->term);
			$filename = $itemKey[0];
			$term = $itemKey[1];
			$statCount++;

			if (!in_array($filename, $fileList)) {
				array_push($fileList, $filename);
			}

			if (!isset($termList[$filename])) {
				$termList[$filename] = array();
			}


			if (is_string($dataItem->definition)) {

				// Jednoduchy vyraz
				$value = str_replace("'", "\\'", $dataItem->definition);
				array_push($termList[$filename], "'{$term}' => '{$value}'");
				if(trim($value)) {
					$statTranslatedCount++;
				}

			} else {
				if (is_object($dataItem->definition)) {

					// Obsahuje plural
					$one = str_replace("'", "\\'", $dataItem->definition->one);
					array_push($termList[$filename], "'{$term}' => '{$one}'");

					if (isset($dataItem->definition->few)) {
						if ($dataItem->definition->few != '') {
							$few = str_replace("'", "\\'", $dataItem->definition->few);
							array_push($termList[$filename], "'{$term}-few' => '{$few}'");
						} else {
							$few = $dataItem->term . '-few';
						}
					} else {
						//pokud jazyk nema FEW pouzijeme OTHER
						$few = $this->getOtherHelper($dataItem);
					}

					$other = $this->getOtherHelper($dataItem);
					array_push($termList[$filename], "'{$term}-other' => '{$other}'");

					//laravel trans_choice() format
					$itemKey = explode(".", $dataItem->term_plural);
					$filename = $itemKey[0];
					$term = $itemKey[1];
					array_push($termList[$filename],
						sprintf("'%s' => '{0} %s|{1} %s|[2,4] %s|[5,Inf] %s'", $term, $other, $one, $few, $other));

					if(trim($one)) {
						$statTranslatedCount++;
					}
				}
			}

		}


		foreach ($fileList as $fileItem) {
			if ( in_array($fileItem,$this->reservedDomains)) {
				throw new \Exception(sprintf('bAdmin: Some terms has reserved domain `%s`. Please fix your terms. Alternatively You can allow this domain by `POEditorApiService::setReservedDomains()` ',$fileItem));
			}

			$phpData = "<?php\r\n return [\r\n";
			$phpData .= implode(", \r\n", $termList[$fileItem]);
			$phpData .= '];';

			$filename = $this->localePath . "/{$locale}/{$fileItem}.php";
			$this->savePHP($filename, $phpData);
		}


		$this->saveStats($locale,$statCount,$statTranslatedCount);


	}

	/**
	 * Helper pro vraceni OTHER
	 * @param $dataItem
	 * @return string
	 */
	private function getOtherHelper($dataItem)
	{
		$other = '';
		if (isset($dataItem->definition->other)) {
			if ($dataItem->definition->other != '') {
				$other = addslashes($dataItem->definition->other);
			} else {
				$other = $dataItem->term . '-other';
			}
		} else {
			$other = $dataItem->term . '-other';
		}
		return $other;
	}

	/**
	 * Ulozi php soubor
	 *
	 * @param string $filename
	 * @param string $phpData
	 * @return bool result
	 * @throws Exception
	 */
	protected function savePHP($filename, $phpData)
	{
		$fp = fopen($filename, "w+");
		if ($fp === false) {
			throw new Exception(error_get_last() . ' ' . $filename);
		}
		fwrite($fp, $phpData);
		return fclose($fp);
	}

	/**
	 * Zkontroluje / vytvori adresar pro preklady
	 *
	 * @param string $languageCode
	 * @return bool result
	 */
	protected function checkLangFolder($languageCode)
	{
		if (!realpath($this->localePath . "/{$languageCode}")) {
			if (!mkdir($this->localePath . "/{$languageCode}", 0777, true)) {
				return false;
			}
		}
		return true;
	}

	protected function saveStats($locale, $count,$translatedCount){
		$progress = (float) $translatedCount ?  100*$count / $translatedCount : 0;
		$progress = min($progress,100);
		$jsonData = (object) [
			'updated' => date('Y-m-d H:i:s'),
			'keys' => (int)$count,
			'translated' => (int)$translatedCount,
			'progress' => $progress,
			'locale' => $locale,
		];
		$json = json_encode($jsonData);
		$filename = $this->localePath . "/{$locale}/_stats.json";
		file_put_contents($filename,$json);
	}

	public function getStats($locale)
	{
		$filename = $this->localePath . "/{$locale}/_stats.json";
		$jsonData = @file_get_contents($filename);
		$json = json_decode($jsonData);
		if(!$json) {
			$json = (object) ['updated'=>null,'keys'=>0,'translated'=>0,'progress'=>0];
		}
		return $json;
	}

	/**
	 * |
	 * | Gettery a Settery
	 * |
	 */

	/**
	 * Vrati list jazyku
	 *
	 * @return array languages
	 * @throws Exception
	 */
	public function getProjectLanguages()
	{
		return json_decode($this->doRequest(['action' => 'list_languages'])->getContent());
	}

	/**
	 * Vrati info o projektu
	 *
	 * @return array info
	 * @throws Exception
	 */
	public function getProjectDetails()
	{
		return json_decode($this->doRequest(['action' => 'view_project'])->getContent());
	}


	/**
	 * @return string
	 */
	public function getApiUrl()
	{
		return $this->apiUrl;
	}

	/**
	 * @param string $apiUrl
	 * @return POEditorApiService
	 */
	public function setApiUrl($apiUrl)
	{
		$this->apiUrl = $apiUrl;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getApiToken()
	{
		return $this->apiToken;
	}

	/**
	 * @param string $apiToken
	 * @return POEditorApiService
	 */
	public function setApiToken($apiToken)
	{
		$this->apiToken = $apiToken;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getProjectId()
	{
		return $this->projectId;
	}

	/**
	 * @param string $projectId
	 * @return POEditorApiService
	 */
	public function setProjectId($projectId)
	{
		$this->projectId = $projectId;
		return $this;
	}


	/**
	 * @param array $locales ['en'=>'en-us']
	 * @return POEditorApiService
	 */
	public function setLocales($locales)
	{
		$this->locales = $locales;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLocalePath()
	{
		return $this->localePath;
	}

	/**
	 * @param string $localePath
	 * @return POEditorApiService
	 */
	public function setLocalePath($localePath)
	{
		$this->localePath = $localePath;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getReservedDomains()
	{
		return $this->reservedDomains;
	}

	/**
	 * @param array $reservedDomains
	 * @return POEditorApiService
	 */
	public function setReservedDomains($reservedDomains)
	{
		$this->reservedDomains = $reservedDomains;
		return $this;
	}




}
