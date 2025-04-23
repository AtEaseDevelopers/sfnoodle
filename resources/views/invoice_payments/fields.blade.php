<!-- Invoice Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('invoice_id', 'Invoice:') !!}
    {!! Form::select('invoice_id', $invoiceItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Invoice...','autofocus']) !!}
</div>


<!-- Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('type', 'Type:') !!}<span class="asterisk"> *</span>
    {{ Form::select('type', array(1 => 'Cash' , 3 => 'Online BankIn' , 4 => 'E-wallet', 5 => 'Cheque'), null, ['class' => 'form-control']) }}
</div>

<!-- ChequeNo Field -->
<div class="form-group col-sm-6" id='cheque-container' style='display:none;'>
    {!! Form::label('chequeno', 'Cheque No.:') !!}
    {!! Form::text('chequeno', null, ['class' => 'form-control','maxlength' => 20,'maxlength' => 20]) !!}
</div>

<!-- Customer Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('customer_id', 'Customer:') !!}<span class="asterisk"> *</span>
    {!! Form::select('customer_id', $customerItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Customer...']) !!}
</div>


<!-- Amount Field -->
<div class="form-group col-sm-6">
    {!! Form::label('amount', 'Amount:') !!}<span class="asterisk"> *</span>
    {!! Form::text('amount', null, ['class' => 'form-control','min' => 0, 'step' => 0.01]) !!}
</div>


@can('paymentapprove')
<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {{ Form::select('status', array(0 => 'New', 1 => 'Completed', 2 => 'Canceled'), null, ['class' => 'form-control']) }}
</div>
@endcan

<!-- Attachment Field -->
<div class="form-group col-sm-6">
    {!! Form::label('attachment', 'Attachment:') !!}
    <div class="custom-file">
        <input type="file" class="custom-file-input" name="attachment" id="attachment" enctype="multipart/form-data" accept=".jpg, .jpeg, .png, .pdf">
        <label id="attachment-label" class="custom-file-label" for="attachment" accept=".jpg, .jpeg, .png, .pdf">Choose file</label>
    </div>
</div>

<!-- Approve By Field -->
<!-- <div class="form-group col-sm-6">
    {!! Form::label('approve_by', 'Approve By:') !!}
    {!! Form::text('approve_by', null, ['class' => 'form-control','maxlength' => 255]) !!}
</div> -->

<!-- Approve At Field -->
<!-- <div class="form-group col-sm-6">
    {!! Form::label('approve_at', 'Approve At:') !!}
    {!! Form::text('approve_at', null, ['class' => 'form-control','id'=>'approve_at']) !!}
</div> -->

<!-- @push('scripts')
   <script type="text/javascript">
           $('#approve_at').datetimepicker({
               format: 'YYYY-MM-DD HH:mm:ss',
               useCurrent: true,
               icons: {
                   up: "icon-arrow-up-circle icons font-2xl",
                   down: "icon-arrow-down-circle icons font-2xl"
               },
               sideBySide: true
           })
       </script>
@endpush -->


<!-- Remark Field -->
<div class="form-group col-sm-6">
    {!! Form::label('remark', 'Remark:') !!}
    {!! Form::text('remark', null, ['class' => 'form-control','maxlength' => 255,'maxlength' => 255]) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('invoicePayments.index') }}" class="btn btn-secondary">Cancel</a>
</div>

@push('scripts')
    <script>
        $(document).keyup(function(e) {
            if (e.key === "Escape") {
                $('form a.btn-secondary')[0].click();
            }
        });
        $(document).ready(function () {
            HideLoad();
        });
        $("#attachment").on("change", function(){
            if(this.value != ''){
                $('#attachment-label').html(this.value);
            }else{
                $('#attachment-label').html('Choose file');
            }
        })
        $("#invoice_id").on("change", function(){
            getinvoice();
        })
        function getinvoice(){
            var invoice_id = $('#invoice_id').val();
            if(invoice_id != ''){
                ShowLoad();
                var url = '{{ config("app.url") }}/invoicePayments/getinvoice/'+invoice_id;
                $.get(url, function(data, status){
                    if(status == 'success'){
                        if(data.status){
                            var customer_id = data.data.customer_id;
                            var amount = 0;
                            data.data.invoicedetail.forEach((element, index, array) => {
                                amount = amount + element.totalprice;
                            });
                            $('#customer_id').val(customer_id);
                            $('#amount').val(amount);
                        }else{
                            noti('e','Please contact your administrator',data.message);
                        }
                        HideLoad();
                    }else{
                        noti('e','Please contact your administrator','')
                        HideLoad();
                    }
                }); 

            }
        }
        $('#type').change(function(){
      
            if($(this).val() == "5")
            {
                $('#cheque-container').show();
            }
            else
            {
                $('#cheque-container').hide();
            }
        });
    </script>
@endpush