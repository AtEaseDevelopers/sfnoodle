{!! Form::open(['route' => ['tasks.destroy', encrypt($id)], 'method' => 'delete']) !!}
<div class='btn-group'>
    <a href="{{ route('tasks.show', encrypt($id)) }}" class='btn btn-ghost-success'>
       <i class="fa fa-eye"></i>
    </a>
    <a href="{{ route('tasks.edit', encrypt($id)) }}" class='btn btn-ghost-info'>
       <i class="fa fa-edit"></i>
    </a>
    {!! Form::button('<i class="fa fa-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-ghost-danger',
        'onclick' => "return confirm('Are you sure to delete the Task?')"
    ]) !!}
</div>
{!! Form::close() !!}
