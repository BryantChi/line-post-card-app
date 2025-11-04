<!-- Title Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', '標題:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Content Field -->
{{-- <div class="form-group col-sm-12">
    {!! Form::label('content', '內容:') !!}
    {!! Form::textarea('content', null, ['class' => 'form-control', 'id' => 'contents']) !!}
</div> --}}

<!-- Category Id Field -->
{{-- <div class="form-group col-sm-6">
    {!! Form::label('category_id', '分類:') !!}
    {!! Form::select('category_id', (['' => '請選擇'] + $categories->toArray()), null, ['class' => 'form-control', 'required']) !!}
</div> --}}

<!-- bs_card Field select $businessCards pluck , 加上請選擇 -->
<div class="form-group col-sm-6">
    {!! Form::label('bs_card', '商業名片:') !!}
    {!! Form::select('bs_card', (['' => '請選擇'] + $businessCards->toArray()), null, ['class' => 'form-control', 'required']) !!}
</div>

<div class="col-12"></div>

<!-- Cover Front Image Field -->
<div class="form-group col-sm-6 d-none">
    {!! Form::label('image', '封面:') !!}
    <div class="custom-file">
        <input type="file" class="custom-file-input" id="image" name="image" accept="image/*">
        <label class="custom-file-label" for="image">Choose file</label>
    </div>
    <div class="img-preview-cover mt-2">
        @if ($caseInfo->image ?? null)
            <p for="">預覽</p>
            <img src="{{ asset('uploads/' . $caseInfo->image) ?? env('APP_URL', 'https://business.cheni.tw/') . '/uploads/' . $caseInfo->image }}"
                class="max-w-200 max-h-200">
        @endif
    </div>
</div>
<div class="clearfix"></div>

<!-- Status Field  -->
<div class="form-group col-sm-6">
    {!! Form::label('status', '狀態:') !!}
    <div class="form-check">
        {!! Form::checkbox('status', 1, isset($caseInfo) ? $caseInfo->status : true, ['class' => 'form-check-input']) !!}
        <label class="form-check-label" for="status">啟用</label>
    </div>
</div>


@push('third_party_scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.26.1/axios.min.js"
        integrity="sha512-bPh3uwgU5qEMipS/VOmRqynnMXGGSRv+72H/N260MQeXZIK4PG48401Bsby9Nq5P5fz7hy5UGNmC/W1Z51h2GQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- <script src="https://cdn.tiny.cloud/1/1ugon3r0i7rnpx6jhdz4moygn9knxfai212wbqlixzr9hpi8/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script> --}}
    {{-- <script src="{!! asset('vendor/tinymce/js/tinymce/tinymce.js') !!}"></script> --}}
@endpush
@push('page_scripts')
{{-- <script src="{{ asset('assets/admin/js/cases.js') }}" referrerpolicy="no-referrer"></script> --}}
<script @cspNonce>
    $(document).ready(function() {
        $(document).on('change', '#image', function () {
            let fileInput = this;
            let fileReader = new FileReader();

            fileReader.onload = function(e) {
                let previewHtml = `<p for="">預覽</p><img src="${e.target.result}" class="max-w-200 max-h-200">`;
                $(fileInput).closest('.form-group').find('.img-preview-cover').html(previewHtml);
            };

            fileReader.readAsDataURL(fileInput.files[0]);
        });
        // $(document).on('change', '[id^="plan_style_"]', function () {
        //     let fileInput = this;
        //     let fileReader = new FileReader();
        //     let id = $(fileInput).attr('id'); // 獲取元素的ID
        //     let previewClass = `.img-preview-s${id.split('_').pop()}`; // 根據ID動態生成對應的預覽class

        //     fileReader.onload = function (e) {
        //         let previewHtml = `<p>預覽</p><img src="${e.target.result}" class="max-w-200 max-h-200">`;
        //         $(fileInput).closest('.form-group').find(previewClass).html(previewHtml);
        //     };

        //     fileReader.readAsDataURL(fileInput.files[0]);
        // });
    });
</script>
@endpush
