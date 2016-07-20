@extends('admin.layout')

@set('header',trans('messages.items.edit'). " : ". str_limit($item->name,80))

@section('actions')
	@can('destroy',$item)
	{!! link_to_route('admin.items.destroy', trans('messages.items.delete'),$item, ['class' => 'btn btn-sm btn-danger pull-right destroy' ,'data-confirm' => trans('messages.are_you_sure') ]) !!}
	@endcan
@endsection

@section('content')

	@embed('inspinia._box')
		@section('title',trans('messages.items.edit'))
		@section('content')
			@include('admin.items._form')
		@endsection()
	@endembed

@endsection

@section('scripts')
	<script>
//		app.items.initForm();
	</script>
@endsection