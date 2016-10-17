@extends('admin.layout')

@set('header',trans('@messages.list'))

@section('actions')
	@can('create',$@entity)
	{!! link_to_route('admin.@entities.create', trans('@messages.create'), [], ['class' => 'btn btn-sm btn-primary pull-right']) !!}
	@endcan
@endsection

@section('content')

	@embed('inspinia._box')
		@section('title',isset($header) ? $header : $title)
		@section('content')
		<table id="@entity-table" class="table table-striped table-bordered table-hover dataTable" data-page-length="25" >
			<thead>
			<tr>
				<th class="text-nowrap">ID</th>
				<th class="text-nowrap">{{ trans('messages.name') }}</th>
				<th class="text-nowrap">{{ trans('messages.updated') }}</th>
				{{--<th class="text-nowrap">{{ trans('messages.created') }}</th>--}}
				<th class="text-nowrap">{{ trans('messages.actions') }}</th>
			</tr>
			</thead>
			@foreach($@entities as $@entity)
				<tr>
					<td>{{ $@entity->id }}</td>
					<td>{!! link_to_route('admin.@entities.edit',$@entity->name,$@entity) !!}</td>
					<td>{{ $@entity->updatedByWithDate('',' dne ') }}</td>
					{{--<td>{{ $@entity->updatedAt() }}</td>--}}
					{{--<td>{{ $@entity->createdAt() }}</td>--}}
					<td>
						@can('edit',$@entity)
						{!! link_to_route('admin.@entities.edit',trans('messages.edit'),$@entity,['class'=>'btn btn-xs btn-primary']) !!}
						@endcan
						@can('destroy',$@entity)
						{!! link_to_route('admin.@entities.destroy',trans('messages.delete'),$@entity,['class'=>'btn btn-xs btn-danger destroy','data-confirm' => trans('messages.are_you_sure')]) !!}
						@endcan
					</td>
				</tr>
			@endforeach

		</table>
	@endsection()
@endembed


@endsection

