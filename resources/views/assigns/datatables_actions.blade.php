{!! Form::open(['route' => ['assigns.destroy', encrypt($id)], 'method' => 'delete']) !!}
<div class='btn-group'>
    <a href="{{ route('assigns.show', encrypt($id)) }}" class='btn btn-ghost-success'>
       <i class="fa fa-eye"></i>
    </a>
    <a href="{{ route('assigns.edit', encrypt($id)) }}" class='btn btn-ghost-info'>
       <i class="fa fa-edit"></i>
    </a>
    {!! Form::button('<i class="fa fa-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-ghost-danger',
        'onclick' => "return confirm('Are you sure to delete the Assign?')"
    ]) !!}
</div>
{!! Form::close() !!}
