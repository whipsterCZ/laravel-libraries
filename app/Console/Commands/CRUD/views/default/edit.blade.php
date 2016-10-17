@extends('admin.layout')

@set('header',trans('@messages.edit'). " : ". str_limit($@entity->name,80))

@section('actions')
	@can('destroy',$@entity)
	{!! link_to_route('admin.@entities.destroy', trans('@messages.delete'),$@entity, ['class' => 'btn btn-sm btn-danger pull-right destroy' ,'data-confirm' => trans('messages.are_you_sure') ]) !!}
	@endcan
@endsection

@section('content')

	@embed('inspinia._box')
		@section('title',trans('@messages.edit'))
		@section('content')
			@include('admin.@entities._form')
		@endsection()
	@endembed

@endsection

@push('scripts')
	<script>
//		app.@entities.initForm();
	</script>
@endpush