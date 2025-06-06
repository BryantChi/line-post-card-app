<!-- 標題 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('title', '卡片名稱:') !!}
    {!! Form::text('title', null, ['class' => 'form-control', 'maxlength' => 255, 'required' => 'required']) !!}
    <small class="form-text text-muted">例如：我的業務名片、公司簡介等</small>
</div>

<!-- 副標題 Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subtitle', '副標題:') !!}
    {!! Form::text('subtitle', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- 頭像/Logo Field -->
<div class="form-group col-sm-6">
    {!! Form::label('profile_image', '頭像/Logo:') !!}
    <div class="input-group">
        <div class="custom-file">
            {!! Form::file('profile_image', ['class' => 'custom-file-input', 'id' => 'profile_image_input', 'accept' => 'image/*']) !!}
            {!! Form::label('profile_image', '選擇檔案', ['class' => 'custom-file-label']) !!}
        </div>
    </div>
    <div class="mt-2">
        @if(isset($businessCard) && $businessCard->profile_image)
            <img src="{{ asset('uploads/' . $businessCard->profile_image) }}" id="profile_image_preview" style="max-height: 100px" class="img-thumbnail">
        @else
            <img src="" id="profile_image_preview" style="max-height: 100px; display: none;" class="img-thumbnail">
        @endif
    </div>
</div>

<!-- 啟用狀態 Field -->
<div class="form-group col-sm-6">
    <div class="form-check mt-4">
        {!! Form::hidden('active', 0) !!}
        {!! Form::checkbox('active', '1', isset($businessCard) ? $businessCard->active : true, ['class' => 'form-check-input']) !!}
        {!! Form::label('active', '啟用此電子名片', ['class' => 'form-check-label']) !!}
    </div>
</div>

<!-- 內容 Field -->
<div class="form-group col-sm-12">
    {!! Form::label('content', '簡介內容:') !!}
    {!! Form::textarea('content', null, ['class' => 'form-control', 'rows' => 3]) !!}
    <small class="form-text text-muted">簡單介紹此電子名片的用途或內容</small>
</div>

@if(isset($businessCard))
    <!-- 重新生成 Flex JSON Field -->
    <div class="form-group col-sm-12">
        <div class="form-check">
            {!! Form::hidden('regenerate_flex', 0) !!}
            {!! Form::checkbox('regenerate_flex', '1', false, ['class' => 'form-check-input']) !!}
            {!! Form::label('regenerate_flex', '重新生成 Flex JSON', ['class' => 'form-check-label']) !!}
            <small class="form-text text-muted">勾選此項將根據現有電子名片-卡片重新生成 Flex JSON 結構</small>
        </div>
    </div>
@endif

@push('page_scripts')
<script>
    $(document).ready(function() {
        // 初始化 Bootstrap 自訂檔案輸入
        bsCustomFileInput.init();

        // 圖片預覽功能
        $('#profile_image_input').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#profile_image_preview').attr('src', e.target.result);
                    $('#profile_image_preview').show();
                }
                reader.readAsDataURL(file);
            } else {
                $('#profile_image_preview').attr('src', '');
                $('#profile_image_preview').hide();
            }
        });
    });
</script>
@endpush
