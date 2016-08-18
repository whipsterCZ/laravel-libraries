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
                $locale->id = $POEditorApiService->getLocaleId($locale->code,true);
                $locale->translationsLink = $locale->id ? $POEditorApiService->getLinkToTranslations($locale->id) : false;
		    }
	//	    dd($locales);
            $termLink = $POEditorApiService->getLinkToTerms();
	    }

        $addMissingTerms = config('services.poeditor.add_missing_terms');
        $reservedDomains = $POEditorApiService->getReservedDomains();

	    return view('poeditor',compact('apiToken','projectId','locales','reservedDomains','termLink','addMissingTerms'));
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
			return \Response::redirectToRoute('poeditor.index');

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
			return \Response::redirectToRoute('poeditor.index');

		} catch (\Exception $e) {
			return \Response::json(array('status' => 'error', 'message' => $e->getMessage()));
		}
		return \Response::json(array('status' => 'success', 'message' => $POEditorApiService->getProjectLanguages()->list));
	}

    public function showTerms(){
        return redirect('https://poeditor.com/projects/view_terms?per_page=5&id='.config('services.poeditor.project_id'));
    }
    public function showEnTranslations(){
        return redirect('https://poeditor.com/projects/po_edit?order=ut&id_language=189&id='.config('services.poeditor.project_id'));
    }
    public function showCsTranslations(){
        return redirect('https://poeditor.com/projects/po_edit?order=ut&id_language=38&id='.config('services.poeditor.project_id'));
    }

    public function showPoeditorLinks(){
        $links = [
            'update-all'=>route('poeditor.updateAll'),
            'show terms' => route('poeditor.terms'),
            'show cs-translations' => route('poeditor.cs'),
            'show en-translations' => route('poeditor.en'),
        ];
        $html = [];
        foreach ($links as $label => $link) {
            $html[] = link_to($link,$label,['target'=>'_blank']);
        }
        return "<h1>Poeditor</h1>".implode("<br/>",$html);
    }

    public function addMissingTerm($term, $translationCs = null,$translationEn = null){

    }
}
