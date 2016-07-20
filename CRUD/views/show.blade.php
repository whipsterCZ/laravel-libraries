@extends('admin.layout')

@set('header',trans('Item'). ": ".$item->name )

@section('content')

	@embed('inspinia._box')
		@section('title',isset($header) ? $header : $title)
		@section('content')
			{{ d($item) }}
		@endsection()
	@endembed

@endsection

@section('scripts')
	<script>
//		app.items.initForm();
	</script>
@endsection