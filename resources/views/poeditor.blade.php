@extends('admin.layout')


@set('header', trans('messages.poeditor.title'))

@section('actions')
	@if($projectId)
	{!! link_to_route('admin.poeditor.updateAll', trans('messages.poeditor.downloadAll'),null,['class'=>'pull-right btn btn-primary'])  !!}
	{!! link_to($termLink, trans('messages.poeditor.show-terms'),['class'=>'pull-right btn btn-default m-r', 'target'=>'_blank']  ) !!}
	@endif
@endsection

@section('content')

	<div class="ibox float-e-margins">
		<div class="ibox-content">

			@if($projectId)

				<h1>POEditor</h1>
				<ul>
					<li>API KEY:  <strong>{{ $apiToken }}</strong>  </li>
					<li>Project ID: <strong>{{ $projectId }}</strong></li>
					<li>{!! link_to('https://poeditor.com/projects/view?id='.$projectId,null,['target'=>'_blank']) !!}</li>
					<li>Auto adding missing terms : {{ trans($addMissingTerms ? 'messages.yes' : 'messages.no') }}</li>
				</ul>

				@if(count($locales))
				<table id="poeditor-table" class="table table-striped table-bordered table-hover" >
					<thead>
					<tr>
						<th class="text-nowrap">Locale</th>
						<th class="text-nowrap">Code</th>
{{--					<th class="text-nowrap">{{ trans('messages.poeditor.keys') }}</th>--}}
						<th class="text-nowrap">{{ trans('messages.poeditor.translated') }}</th>
						<th class="text-nowrap">{{ trans('messages.poeditor.localUpdated') }}</th>
						<th class="text-nowrap">{{ trans('messages.poeditor.updated') }}</th>
						<th class="text-nowrap" style="width: 30%">{{ trans('messages.poeditor.progress') }}</th>
						<th>{{ trans('messages.actions') }}</th>
					</tr>
					</thead>
					@foreach($locales as $locale)
					<tr>
						<td>{{ $locale->name }}</td>
						<td>{{ $locale->code }}</td>
{{--					<td>{{ $locale->keys }}</td>--}}
						<td>{{ $locale->translated }} / {{ $locale->keys }}</td>
						<td>{{ $locale->localUpdated ? $locale->localUpdated->format(DATETIME_HUMAN) : '-' }}</td>
						<td>{{ $locale->updated->format(DATETIME_HUMAN) }}</td>
						<td>
							@if( $locale->progress > 0)
							<div class="progress">
								<div style="width: {{ $locale->progress }}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="35" role="progressbar"
								     class="progress-bar @if($locale->progress<30) progress-bar-danger @elseif($locale->progress<70) progress-bar-warning @else progress-bar-success @endif">
									<span >{{ $locale->progress }}% </span>
								</div>
							</div>
							@else
								0%
							@endif
						</td>
						<td>
							@if($locale->translationsLink)
								{!! link_to($locale->translationsLink, trans('messages.poeditor.show-translations'),['class'=>'btn btn-sm btn-default', 'target'=>'_blank']  ) !!}
							@endif
							@if($locale->shouldUpdate)
								{!! link_to_route('admin.poeditor.update', trans('messages.poeditor.download'),
									 $locale->code,
									 ['class'=>'btn btn-sm btn-primary'])  !!}
							@else
								{{ trans('messages.poeditor.upToDate') }}
							@endif
						</td>
					</tr>
					@endforeach
				</table>
				@endif

			@else
				<div class="alert alert-danger">{{ trans('messages.poeditor.provideProjectID') }}</div>
			@endif

			<div class="alert alert-warning">
				<ul>
					<li>Term definition should look like <code>{domain}.{section_optional}-{term_name}</code> - example  `customers.meta_title`, `customer.detail-h1` or `welcome.h1`</li>
					<li>Some term domains are <strong>reserved</strong> for bAdmin <strong>{{ implode($reservedDomains,", ")}}</strong></li>
				</ul>
			</div>

		</div>
	</div>


@endsection
