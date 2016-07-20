@extends('admin.layout')

@set('header',trans('messages.items.create'))

@section('content')

	@embed('inspinia._box')
		@section('title',isset($header) ? $header : $title)
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