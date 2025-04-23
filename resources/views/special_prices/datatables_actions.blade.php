{!! Form::open(['route' => ['specialPrices.destroy', encrypt($id)], 'method' => 'delete']) !!}
<div class='btn-group'>
    <a href="{{ route('specialPrices.show', encrypt($id)) }}" class='btn btn-ghost-success'>
       <i class="fa fa-eye"></i>
    </a>
    <a href="{{ route('specialPrices.edit', encrypt($id)) }}" class='btn btn-ghost-info'>
       <i class="fa fa-edit"></i>
    </a>
    {!! Form::button('<i class="fa fa-trash"></i>', [
        'type' => 'submit',
        'class' => 'btn btn-ghost-danger',
        'onclick' => "return confirm('Are you sure to delete the Special Price?')"
    ]) !!}
</div>
{!! Form::close() !!}
