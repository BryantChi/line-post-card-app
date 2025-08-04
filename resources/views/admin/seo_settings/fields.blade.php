<!-- Url Field -->
<div class="form-group col-sm-6">
    {!! Form::label('url', 'Url:') !!}
    {!! Form::text('url', null, ['class' => 'form-control', 'placeholder' => 'Url']) !!}
</div>

<!-- Title Field -->
<div class="form-group col-sm-6">
    {!! Form::label('title', 'Title:') !!}
    {!! Form::text('title', null, ['class' => 'form-control', 'placeholder' => 'Title', 'rows' => '5']) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-6">
    {!! Form::label('description', 'Description:') !!}
    {!! Form::textarea('description', null, [
        'class' => 'form-control',
        'placeholder' => 'Description',
        'rows' => '5',
    ]) !!}
</div>

<!-- Keywords Field -->
<div class="form-group col-sm-6">
    {!! Form::label('keywords', 'Keywords:') !!}
    {!! Form::textarea('keywords', null, ['class' => 'form-control', 'placeholder' => 'Keywords', 'rows' => '5']) !!}
</div>

<!-- Og:title Field -->
<div class="form-group col-sm-6">
    {!! Form::label('og:title', 'Og:title:') !!}
    {!! Form::text('og_title', null, ['class' => 'form-control', 'placeholder' => 'Og:title', 'rows' => '5']) !!}
</div>

<!-- Og:site Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('og:site_name', 'Og:site Name:') !!}
    {!! Form::text('og_site_name', null, [
        'class' => 'form-control',
        'placeholder' => 'Og:site Name',
        'rows' => '5',
    ]) !!}
</div>

<!-- Og:description Field -->
<div class="form-group col-sm-6">
    {!! Form::label('og:description', 'Og:description:') !!}
    {!! Form::textarea('og_description', null, [
        'class' => 'form-control',
        'placeholder' => 'Og:description',
        'rows' => '5',
    ]) !!}
</div>

<!-- Ga_header Field -->
<div class="form-group col-sm-12">
    {!! Form::label('ga_header', 'Google Analytics Header:') !!}
    {!! Form::textarea('ga_header', null, [
        'class' => 'form-control',
        'placeholder' => 'Google Analytics Header',
        'rows' => '7',
        'id' => 'ga_header',
    ]) !!}
</div>

<!-- Ga_body Field -->
<div class="form-group col-sm-12">
    {!! Form::label('ga_body', 'Google Analytics Body:') !!}
    {!! Form::textarea('ga_body', null, [
        'class' => 'form-control',
        'placeholder' => 'Google Analytics Body',
        'rows' => '7',
        'id' => 'ga_body',
    ]) !!}
</div>


<h5 class="w-100">即時預覽清洗後的程式碼</h5>
<div class="col-md-6">
    <h6>Header 預覽</h6>
    <pre id="preview_header" style="background: #f8f9fa; padding: 15px;"></pre>
</div>
<div class="col-md-6">
    <h6>Body 預覽</h6>
    <pre id="preview_body" style="background: #f8f9fa; padding: 15px;"></pre>
</div>


@push('page_scripts')
    <script>
        $(document).ready(function(){
            // 以 jQuery 監聽 textarea 的 input 事件，並透過 AJAX 即時取得清洗後的結果
            function updatePreview(textareaId, previewId) {
                $('#' + textareaId).on('input', function(){
                    var code = $(this).val();
                    $.ajax({
                        url: "{{ route('admin.seo.preview') }}",
                        type: "POST",
                        data: JSON.stringify({ code: code }),
                        contentType: "application/json",
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        success: function(data) {
                            $('#' + previewId).text(data.clean_code);
                        },
                        error: function(xhr, status, error) {
                            $('#' + previewId).text('');
                            console.error('AJAX Error:', error);
                        }
                    });
                });
            }
            updatePreview('ga_header', 'preview_header');
            updatePreview('ga_body', 'preview_body');
        });
    </script>
@endpush
