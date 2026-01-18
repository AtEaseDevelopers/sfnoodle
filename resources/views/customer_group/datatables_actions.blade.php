{!! Form::open(['route' => ['customer_group.destroy', Crypt::encrypt($id)], 'method' => 'delete']) !!}
<div class='btn-group'>
    <!-- View Button -->
    <a href="{{ route('customer_group.show', Crypt::encrypt($id)) }}" class='btn btn-ghost-success' title="{{ trans('customer_group.view') }}">
       <i class="fa fa-eye"></i>
    </a>
    
    <!-- Edit Button -->
    <a href="{{ route('customer_group.edit', Crypt::encrypt($id)) }}" class='btn btn-ghost-info' title="{{ trans('customer_group.edit') }}">
       <i class="fa fa-edit"></i>
    </a>
    
    <!-- Delete Button -->
    {!! Form::button('<i class="fa fa-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-ghost-danger',
        'title' => trans('customer_group.delete'),
        'onclick' => "return confirm('".trans('customer_group.are_you_sure_to_delete_the_group')."')"    
    ]) !!}
</div>
{!! Form::close() !!}