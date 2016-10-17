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
		<ul id="@entity-list" data-entity-name="@entities" class="sortable" >
			@foreach($@entities as $@entity)
				<li data-item-id="{{ $@entity->id }}">
					<span class="fa fa-reorder sortable-handle" style="cursor: move" ></span>
					<span>{!! link_to_route('admin.@entities.edit',$@entity->name,$@entity) !!}</span>
					<span>{{ $@entity->updatedByWithDate('',' dne ') }}</span>
					{{--<span>{{ $@entity->updatedAt() }}</span>--}}
					{{--<span>{{ $@entity->createdAt() }}</span>--}}
					<span>
						@can('edit',$@entity)
						{!! link_to_route('admin.@entities.edit',trans('messages.edit'),$@entity,['class'=>'btn btn-xs btn-primary']) !!}
						@endcan
						@can('destroy',$@entity)
						{!! link_to_route('admin.@entities.destroy',trans('messages.delete'),$@entity,['class'=>'btn btn-xs btn-danger destroy','data-confirm' => trans('messages.are_you_sure')]) !!}
						@endcan
					</span>
				</li>
			@endforeach
		</ul>
	@endsection()
@endembed


@endsection

@push('scripts')
<script>
	app.sortable.initWithDefaultContainers();
	{{--app.@entities.init();--}}
</script>
@endpush