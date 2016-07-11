<?php


namespace App\Services;


use Illuminate\Contracts\Pagination\Presenter;
use Illuminate\Pagination\LengthAwarePaginator;

class CataloguePaginator extends LengthAwarePaginator {

	/**
	 * Get a URL for a given page number.
	 *
	 * @param  int  $page
	 * @return string
	 */
	public function url($page)
	{
		if ($page <= 0) {
			$page = 1;
		}

		if (count($this->query) > 0) {
			return $this->path .$page   .'?'
			.urldecode(http_build_query( $this->query, null, '&'))
			.$this->buildFragment();
		}

		return $this->path .$page. $this->buildFragment();
	}
	/**
	 * Render the paginator using the given presenter.
	 *
	 * @param  \Illuminate\Contracts\Pagination\Presenter|null  $presenter
	 * @return string
	 */
	public function render(Presenter $presenter = null)
	{
		if ($this->hasPages()) {
			$links = "";
			foreach ( $this->getUrlRange(1, $this->lastPage()) as $page => $url) {
				$links .= $this->getPageWrapper($url,$page);
			}
			return sprintf(	'<ul class="pagination">%s</ul>', $links);
		}
		return '';
	}



	protected function getPageWrapper($url,$page,$rel=null) {
		if ($page == $this->currentPage()) {
			return $this->getActivePageWrapper($page);
		}
		return  $this->getAvailablePageWrapper($url,$page,$rel);
	}

	/**
	 * Get HTML wrapper for an available page link.
	 *
	 * @param  string  $url
	 * @param  int  $page
	 * @param  string|null  $rel
	 * @return string
	 */
	protected function getAvailablePageWrapper($url, $page, $rel = null)
	{
		$rel = is_null($rel) ? '' : ' rel="'.$rel.'"';

		return '<li><a href="'.htmlentities($url).'"'.$rel.'>'.$page.'</a></li> ';
	}

	/**
	 * Get HTML wrapper for disabled text.
	 *
	 * @param  string  $text
	 * @return string
	 */
	protected function getDisabledTextWrapper($text)
	{
		return '<li class="disabled"><span>'.$text.'</span></li> ';
	}

	/**
	 * Get HTML wrapper for active text.
	 *
	 * @param  string  $text
	 * @return string
	 */
	protected function getActivePageWrapper($text)
	{
		return '<li class="active"><span>'.$text.'</span></li> ';
	}

}