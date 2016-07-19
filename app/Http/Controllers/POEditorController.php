<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\POEditorApiService;

class POEditorController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(POEditorApiService $POEditorApiService)
    {
	    $apiToken =  $POEditorApiService->getApiToken();
	    $projectId = $POEditorApiService->getProjectId();


	    $locales = [];
	    if($projectId) {
		    $locales = $POEditorApiService->getProjectLanguages()->list;
		    foreach ($locales as $locale) {
			    $stats = $POEditorApiService->getStats( $POEditorApiService->getLocaleName($locale->code) );
			    $locale->keys = $stats->keys;
			    $locale->progress = $stats->progress;
			    $locale->translated = $stats->translated;
			    $locale->localUpdated =  $stats->updated ? new Carbon($stats->updated) : null;
			    $locale->updated = new Carbon($stats->updated);
			    $locale->shouldUpdate = $locale->localUpdated && $locale->localUpdated->lte($locale->updated);
		    }
	//	    dd($locales);
	    }

	    $reservedDomains = $POEditorApiService->getReservedDomains();

	    return view('admin.poeditor',compact('apiToken','projectId','locales','reservedDomains'));
    }


	/**
	 * Stahne z POEditoru vsechny překlady
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function updateLanguages(POEditorApiService $POEditorApiService)
	{
		try {
			$POEditorApiService->updateAll();

			\Session::flash('success',trans('messages.poeditor.dataUpdated'));
			return \Response::redirectToRoute('admin.poeditor.index');

		} catch (\Exception $e) {
			return \Response::json(array('status' => 'error', 'message' => $e->getMessage()));
		}
		return \Response::json(array('status' => 'success', 'message' => $POEditorApiService->getProjectLanguages()->list));
	}

	/**
	 *  Stahne z POEditoru vsechny překlady konkrítního jazyku
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function updateLanguage(POEditorApiService $POEditorApiService,$language)
	{
		try {
			$POEditorApiService->updateLocale($language);

			\Session::flash('success',trans('messages.poeditor.dataUpdated'));
			return \Response::redirectToRoute('admin.poeditor.index');

		} catch (\Exception $e) {
			return \Response::json(array('status' => 'error', 'message' => $e->getMessage()));
		}
		return \Response::json(array('status' => 'success', 'message' => $POEditorApiService->getProjectLanguages()->list));
	}
}
