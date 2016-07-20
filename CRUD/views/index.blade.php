@extends('admin.layout')

@set('header',trans('messages.items.list'))

@section('actions')
	@can('create',$item)
	{!! link_to_route('admin.items.create', trans('messages.items.create'), [], ['class' => 'btn btn-sm btn-primary pull-right']) !!}
	@endcan
@endsection

@section('content')

	@embed('inspinia._box')
		@section('title',isset($header) ? $header : $title)
		@section('content')
		<table id="item-table" class="table table-striped table-bordered table-hover dataTable" data-page-length="25" >
			<thead>
			<tr>
				<th class="text-nowrap">ID</th>
				<th class="text-nowrap">{{ trans('messages.name') }}</th>
				<th class="text-nowrap">{{ trans('messages.type') }}</th>
				<th class="text-nowrap">{{ trans('messages.file') }}</th>
				<th class="text-nowrap">{{ trans('messages.image') }}</th>
				<th class="text-nowrap">{{ trans('messages.published') }}</th>
				<th class="text-nowrap"><span class="fa fa-eye"></span> {{ trans('messages.from') }}</th>
				<th class="text-nowrap"><span class="fa fa-eye-slash"></span> {{ trans('messages.to') }}</th>
				{{--<th class="text-nowrap">{{ trans('messages.updated') }}</th>--}}
				<th class="text-nowrap">{{ trans('messages.created') }}</th>
				<th class="text-nowrap">{{ trans('messages.actions') }}</th>
			</tr>
			</thead>
			@foreach($items as $item)
				<tr>
					<td>{{ $item->id }}</td>
					<td>{!! link_to_route('admin.items.show',$item->name,$item) !!}</td>
					<td>{{ $item->types($item->type) }}</td>
					<td>{!!  link_to_asset($item->upload->url(), $item->upload->originalFilename(),['target'=>'_blank']) !!}</td>
					<td><img src="{{  $item->image->url('cropped') }}" alt="{{ $item->image->originalFilename() }}"></td>
					<td>{{ $item->isPublished() ? trans('messages.public') : trans('messages.private')  }}</td>
					<td>{{ $item->publishedFrom() }}</td>
					<td>{{ $item->publishedTo() }}</td>
					{{--						<td>{{ $item->updatedByWithDate('',' dne ') }}</td>--}}
					{{--						<td>{{ $item->updatedAt() }}</td>--}}
					<td>{{ $item->createdAt() }}</td>
					<td>
						@can('edit',$item)
						{!! link_to_route('admin.items.edit',trans('messages.edit'),$item,['class'=>'btn btn-xs btn-primary']) !!}
						@endcan
						@can('destroy',$item)
						{!! link_to_route('admin.items.destroy',trans('messages.delete'),$item,['class'=>'btn btn-xs btn-danger destroy','data-confirm' => trans('messages.are_you_sure')]) !!}
						@endcan
					</td>
				</tr>
			@endforeach

		</table>
	@endsection()
@endembed


@endsection

