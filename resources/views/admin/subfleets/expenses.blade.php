<div id="subfleet-expenses-wrapper" class="col-12">
    <div class="header">
        <h3>expenses</h3>
        @component('admin.components.info')
            These expenses are only applied to this subfleet
        @endcomponent
    </div>
    <br/>
    <table class="table table-responsive" id="subfleet-expenses">
        @if(count($subfleet->expenses))
        <thead>
        <th>Name</th>
        <th>Cost&nbsp;<span class="small">{{ currency(config('phpvms.currency')) }}</span></th>
        <th>Type</th>
        <th></th>
        </thead>
        @endif
        <tbody>
        @foreach($subfleet->expenses as $expense)
            <tr>
                <td>
                    <p>
                        <a class="text" href="#" data-pk="{{ $expense->id }}"
                           data-name="name">{{ $expense->name }}</a>
                    </p>
                </td>
                <td>
                    <p>
                        <a class="text" href="#" data-pk="{{ $expense->id }}"
                           data-name="amount">{{ $expense->amount }}</a>
                    </p>
                </td>
                <td>
                    <p>
                        <a href="#"
                           class="dropdown"
                           data-pk="{{ $expense->id }}"
                           data-name="type">{{ \App\Models\Enums\ExpenseType::label($expense->type) }}</a>
                    </p>
                </td>
                <td align="right">
                    {{ Form::open(['url' => url('/admin/subfleets/'.$subfleet->id.'/expenses'),
                                'method' => 'delete', 'class' => 'modify_expense form-inline']) }}
                    {{ Form::hidden('expense_id', $expense->id) }}
                    {{ Form::button('<i class="fa fa-times"></i>', ['type' => 'submit',
                                     'class' => 'btn btn-sm btn-danger btn-icon',
                                     'onclick' => "return confirm('Are you sure?')",
                                     ]) }}
                    {{ Form::close() }}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <hr/>
    <div class="row">
        <div class="col-sm-12">
            <div class="text-right">
                {{ Form::open(['url' => url('/admin/subfleets/'.$subfleet->id.'/expenses'),
                                'method' => 'post', 'class' => 'modify_expense form-inline']) }}
                {{ Form::text('name', null, ['class' => 'form-control input-sm', 'placeholder' => 'Name']) }}
                {{ Form::number('amount', null, ['class' => 'form-control input-sm', 'placeholder' => 'Amount']) }}
                {{ Form::select('type', \App\Models\Enums\ExpenseType::select(), null, ['class' => 'select2']) }}
                {{ Form::button('<i class="fa fa-plus"></i> Add', ['type' => 'submit',
                                 'class' => 'btn btn-success btn-small']) }}
                {{ Form::close() }}

                <p class="text-danger">{{ $errors->first('name') }}</p>
                <p class="text-danger">{{ $errors->first('amount') }}</p>
            </div>
        </div>
    </div>
</div>
