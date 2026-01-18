<!-- Sales Invoice Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('sales_invoice_id', 'Sales Invoice') !!}<span class="asterisk"> *</span>
    {!! Form::select('sales_invoice_id', $salesInvoiceItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Sales Invoice...', 'autofocus']) !!}
</div>

<!-- Product Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('product_id', 'Product') !!}<span class="asterisk"> *</span>
    {!! Form::select('product_id', $productItems, null, ['class' => 'form-control', 'placeholder' => 'Pick a Product...']) !!}
</div>

<!-- Quantity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('quantity', 'Quantity') !!}<span class="asterisk"> *</span>
    {!! Form::number('quantity', null, ['class' => 'form-control', 'min' => 0, 'step' => 1]) !!}
</div>

<!-- Price Field -->
<div class="form-group col-sm-6">
    {!! Form::label('price', 'Price') !!}<span class="asterisk"> *</span>
    {!! Form::text('price', null, ['class' => 'form-control', 'min' => 0, 'step' => 0.01]) !!}
</div>

<!-- Remark Field -->
<div class="form-group col-sm-6">
    {!! Form::label('remark', 'Remark') !!}
    {!! Form::text('remark', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- Submit Field -->
<div class="form-group col-sm-12">
    {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
    <a href="{{ route('salesInvoiceDetails.index') }}" class="btn btn-secondary">Cancel</a>
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
        $("#sales_invoice_id").change(function(){
            getprice();
        });
        $("#product_id").change(function(){
            getprice();
        });
        function getprice(){
            var sales_invoice_id = $('#sales_invoice_id').val();
            var product_id = $('#product_id').val();
            if(sales_invoice_id != '' && product_id != ''){
                ShowLoad();
                var url = '{{ config("app.url") }}/salesInvoiceDetails/getprice/'+sales_invoice_id+'/'+product_id;
                $.get(url, function(data, status){
                    if(status == 'success'){
                        if(data.status){
                            $('#price').val(data.data);
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
    </script>
@endpush