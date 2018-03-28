<?php


namespace App\Services;


use Illuminate\Support\Facades\Response;

class CsvExport {

	/** @var array */
	private $data;

	/** @var string */
	private $name;

	/** @var bool */
	public $addHeading;

	/** @var string */
	public $glue;

	/** @var string */
	private $charset;

	/** @var string */
	private $contentType;

	/**
	 * převede název  nazev_sloupce => Nazev Slupce
	 * @var bool
	 */
	public $camelCaseHeader =  false;

	/**
	 * @param  string  data (array of arrays - rows/columns)
	 * @param  string  imposed file name
	 * @param  bool	return array keys as the first row (column headings)
	 * @param  string  glue between columns (comma or a semi-colon)
	 * @param  string  MIME content type
	 */
	public function __construct($data, $name = NULL, $addHeading = TRUE, $glue = ';', $charset = "cp1250", $contentType = 'text/csv') {
		// ----------------------------------------------------
		$this->data = $data;
		$this->name = $name;
		$this->addHeading = $addHeading;
		$this->glue = $glue;
		$this->charset = $charset;
		$this->contentType = $contentType;
	}


	/**
	 * Returns the file name.
	 * @return string
	 */
	final public function getName() {
		// ----------------------------------------------------
		return $this->name;
	}

	/**
	 * Returns the MIME content type of a downloaded content.
	 * @return string
	 */
	final public function getContentType() {
		// ----------------------------------------------------
		return $this->contentType;
	}


	/**
	 * Sends response to output.
	 * @return \Illuminate\Http\Response
	 */
	public function response() {
		$data = $this->formatCsv();
		$headers = [
			'Content-Disposition'=> ( $this->name ?  'attachment; filename="' . $this->name . '"' : 'attachment' ),
			'Content-Type' => $this->contentType. '; charset='.  $this->charset,
			'Content-Length' => strlen($data),
		];
		return \Response::make($data,200,$headers);
	}

	public function formatCsv() {
		if (empty($this->data)) {
			return '';
		}

		$csv = array();

		if (!is_array($this->data)) {
			$this->data = iterator_to_array($this->data);
		}
		$firstRow = reset($this->data);

		if ($this->addHeading) {
			if (!is_array($firstRow)) {
				$firstRow = iterator_to_array($firstRow);
			}

			$labels = array();
			foreach (array_keys($firstRow) as $key) {
				if ( $this->camelCaseHeader) {
					$labels[] = ucwords(str_replace("_", ' ', $key));
				} else {
					$labels[] = $key;
				}
			}
			$csv[] = '"'.join('"'.$this->glue.'"', $labels).'"';
		}

		foreach ($this->data as $row) {
			if (!is_array($row)) {
				$row = iterator_to_array($row);
			}
			foreach ($row as $key => &$value) {
				if ( $value instanceof \DateTime) {
					$value = $value->format("Y-m-d H:i");
				} else {
					//$value = preg_replace('/'.$this->glue.'/', ':', $value);  // remove glue
					$value = preg_replace('/[\r\n]+/', ' ', $value);  // remove line endings
					$value = str_replace('"', '""', $value);		  // escape double quotes
				}
				if ( $this->charset != "UTF-8")
					$value = iconv( "UTF-8", $this->charset."//IGNORE",$value);
			}
			$csv[] = '"'.join('"'.$this->glue.'"', $row).'"';
		}

		return join(PHP_EOL, $csv);
	}
}
