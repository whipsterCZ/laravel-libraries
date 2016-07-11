<?php


namespace App\Services;


use Illuminate\Contracts\Pagination\Presenter;
use Illuminate\Pagination\LengthAwarePaginator;

class SnakePaginator extends LengthAwarePaginator {

	/**
	 * Render the paginator using the given presenter.
	 *
	 * param  \Illuminate\Contracts\Pagination\Presenter|null  $presenter
	 * return string
	 */
	public function render(Presenter $presenter = null)
	{
		if ($this->hasPages()) {
			$links = "";
			$curPage = $this->currentPage();
			$pageCount = $this->lastPage();

			if ($curPage < 4) {
				for ($page = ($curPage == 1 ? 1 : $curPage - 2); $page <= ($pageCount > 5 ? 5 : $pageCount); $page++) {
					$links .= $this->getPageWrapper($page);
				}
			} else {
				$links .= $this->getAvailablePageWrapper(1);
				$links .= $this->getDots();

				for($page = ($curPage == $pageCount ? $pageCount - 2 : $curPage - 1); $page <= ($curPage == $pageCount ? $pageCount : $curPage + 1); $page++) {
					$links .= $this->getPageWrapper($page);
				}
			}
			if(($pageCount) > $curPage) {
				$links .= $this->getNextPage($curPage);
			}

			return sprintf(	'<ul class="pagination">%s</ul>', $links);
		}
		return '';
	}



	protected function getPageWrapper($page,$rel=null) {
		if ($page == $this->currentPage()) {
			return $this->getActivePageWrapper($page);
		}
		return  $this->getAvailablePageWrapper($page,$rel);
	}

	/**
	 * Get HTML wrapper for an available page link.
	 *
	 * param  string  $url
	 * param  int  $page
	 * param  string|null  $rel
	 * return string
	 */
	protected function getAvailablePageWrapper($page, $rel = null)
	{
		$rel = is_null($rel) ? '' : ' rel="'.$rel.'"';
		$url = $this->url($page);
		return '<li><a href="'.htmlentities($url).'"'.$rel.'>'.$page.'</a></li> ';
	}

	/**
	 * Get HTML wrapper for disabled text.
	 *
	 * param  string  $text
	 * return string
	 */
	protected function getDisabledTextWrapper($text)	{
		return '<li class="disabled"><span>'.$text.'</span></li> ';
	}

	/**
	 * Get HTML wrapper for active text.
	 *
	 * param  string  $text
	 * return string
	 */
	protected function getActivePageWrapper($text)	{
		return '<li class="active"><span>'.$text.'</span></li> ';
	}

	protected function getDots()	{
		return '<li class="pagination-dots">&hellip;</li>';
	}

	protected function getNextPage($page)	{
		return sprintf('<li class="pagination-more"><a href="%s">%s</a></li> ',
			$this->url(++$page),
			trans('homepage.button-load-more') );
	}

}