{!! AppForm::open([
	'model' => $@entity,
	'store' => 'admin.@entities.store',
	'update' => 'admin.@entities.update',
	'ajax' => false,
	'files' => true,
	//'left_column_class'  => 'col-sm-2 col-md-2',
	//'right_column_class' => 'col-sm-10 col-md-10',
	//'left_column_offset_class' => 'col-sm-offset-2 col-md-offset-2',
]) !!}

{!! AppForm::errors() !!}

{!! AppForm::text('name',trans('messages.name')) !!}

{!! AppForm::select('type',trans('messages.type'), $@entity->types(), null,['class'=>'chosen'] ) !!}

<div class="hr-line-dashed"></div>

{!! AppForm::submit(trans('@messages.save')) !!}

{!! AppForm::close() !!}