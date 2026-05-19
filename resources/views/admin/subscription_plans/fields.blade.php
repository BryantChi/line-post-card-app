{{-- 方案名稱 --}}
<div class="form-group col-sm-6">
    {!! Form::label('name', '方案名稱 *') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => '例：初級方案 1 個月', 'maxlength' => 100]) !!}
</div>

{{-- 方案層級 --}}
<div class="form-group col-sm-3">
    {!! Form::label('plan_tier', '方案層級') !!}
    {!! Form::select('plan_tier', ['' => '— 不分層 —'] + \App\Models\SubscriptionPlan::TIER_OPTIONS, null, ['class' => 'form-control']) !!}
    <small class="form-text text-muted">未選擇則不會顯示於前台分層卡片</small>
</div>

{{-- 頁數限制（僅顯示用） --}}
<div class="form-group col-sm-3">
    {!! Form::label('page_limit', '頁數') !!}
    <div class="input-group">
        {!! Form::number('page_limit', null, ['class' => 'form-control', 'min' => 1, 'max' => 999, 'placeholder' => '例：5']) !!}
        <div class="input-group-append">
            <span class="input-group-text">頁</span>
        </div>
    </div>
    <small class="form-text text-muted">僅於前台卡片顯示，不限制實際名片數量</small>
</div>

{{-- 卡片圖示（圖片上傳） --}}
<div class="form-group col-sm-6">
    {!! Form::label('icon', '卡片圖示（圖片）') !!}
    <div class="input-group">
        <div class="custom-file">
            {!! Form::file('icon', ['class' => 'custom-file-input', 'id' => 'icon_input', 'accept' => 'image/*']) !!}
            {!! Form::label('icon', '選擇圖片', ['class' => 'custom-file-label']) !!}
        </div>
    </div>
    <div class="mt-2 d-flex align-items-center">
        @if(isset($plan) && $plan->icon)
            <img src="{{ asset('uploads/' . $plan->icon) }}" id="icon_preview"
                 class="img-thumbnail mr-2" style="max-width:90px; max-height:90px;">
            <div class="custom-control custom-checkbox">
                <input type="checkbox" name="remove_icon" value="1" id="remove_icon" class="custom-control-input">
                <label class="custom-control-label small text-danger" for="remove_icon">移除目前圖示</label>
            </div>
        @else
            <img src="" id="icon_preview" class="img-thumbnail mr-2 d-none" style="max-width:90px; max-height:90px;">
        @endif
    </div>
    <small class="form-text text-muted">建議 200×200px 以內，支援 jpg/png/gif/webp，最大 2MB；未上傳則使用該層級預設圖示。</small>
</div>

{{-- 價格 --}}
<div class="form-group col-sm-3">
    {!! Form::label('price', '價格（NT$）*') !!}
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text">NT$</span>
        </div>
        {!! Form::number('price', null, ['class' => 'form-control', 'min' => 1, 'max' => 999999]) !!}
    </div>
</div>

{{-- 有效天數 --}}
<div class="form-group col-sm-3">
    {!! Form::label('duration_days', '有效天數 *') !!}
    <div class="input-group">
        {!! Form::number('duration_days', null, ['class' => 'form-control', 'min' => 1, 'max' => 3650]) !!}
        <div class="input-group-append">
            <span class="input-group-text">天</span>
        </div>
    </div>
    <small class="form-text text-muted">30=1 個月、365=1 年</small>
</div>

{{-- 適合對象 --}}
<div class="form-group col-sm-12">
    {!! Form::label('target_audience', '適合對象') !!}
    {!! Form::text('target_audience', null, ['class' => 'form-control', 'maxlength' => 255, 'placeholder' => '例：個人品牌商/自由接案者/小型店家']) !!}
</div>

{{-- 方案說明（TinyMCE） --}}
<div class="form-group col-sm-12">
    {!! Form::label('description', '方案說明') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 6, 'id' => 'plan_description', 'placeholder' => '例：適合剛起步或需求單純的你']) !!}
</div>

{{-- 排序 --}}
<div class="form-group col-sm-3">
    {!! Form::label('sort_order', '排序') !!}
    {!! Form::number('sort_order', 0, ['class' => 'form-control', 'min' => 0, 'max' => 999]) !!}
    <small class="form-text text-muted">數字越小排越前面</small>
</div>

{{-- 狀態 --}}
<div class="form-group col-sm-3">
    {!! Form::label('active', '狀態') !!}
    {!! Form::select('active', ['1' => '啟用', '0' => '停用'], 1, ['class' => 'form-control']) !!}
</div>

{{-- 最受歡迎（層級標籤） --}}
<div class="form-group col-sm-3 d-flex align-items-end">
    <div class="custom-control custom-checkbox">
        {!! Form::hidden('is_popular', 0) !!}
        {!! Form::checkbox('is_popular', 1, null, ['class' => 'custom-control-input', 'id' => 'is_popular']) !!}
        <label class="custom-control-label" for="is_popular">最受歡迎（層級頂部橘色標籤，同層級僅勾一個）</label>
    </div>
</div>

{{-- 特別優惠（訂閱期標籤） --}}
<div class="form-group col-sm-3 d-flex align-items-end">
    <div class="custom-control custom-checkbox">
        {!! Form::hidden('is_featured', 0) !!}
        {!! Form::checkbox('is_featured', 1, null, ['class' => 'custom-control-input', 'id' => 'is_featured']) !!}
        <label class="custom-control-label" for="is_featured">特別優惠（該訂閱期主推價，同層級僅勾一個）</label>
    </div>
</div>

@push('third_party_scripts')
    <script src="{!! asset('vendor/tinymce/js/tinymce/tinymce.js') !!}"></script>
@endpush

@push('page_scripts')
<script @cspNonce>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#plan_description',
                language: 'zh_TW',
                height: 320,
                menubar: false,
                plugins: 'advlist autolink link lists charmap preview searchreplace wordcount visualblocks code fullscreen table',
                toolbar: 'undo redo | blocks fontsize | bold italic underline strikethrough | forecolor backcolor | bullist numlist outdent indent | link | removeformat | code fullscreen preview',
                promotion: false,
                branding: false,
                relative_urls: true,
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Microsoft JhengHei", sans-serif; font-size: 14px; line-height: 1.7; }',
            });
        }

        // Icon 上傳即時預覽
        var iconInput = document.getElementById('icon_input');
        var iconPreview = document.getElementById('icon_preview');
        if (iconInput && iconPreview) {
            iconInput.addEventListener('change', function (e) {
                var file = e.target.files && e.target.files[0];
                if (!file) return;
                var label = iconInput.nextElementSibling;
                if (label && label.classList.contains('custom-file-label')) {
                    label.textContent = file.name;
                }
                var reader = new FileReader();
                reader.onload = function (ev) {
                    iconPreview.src = ev.target.result;
                    iconPreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            });
        }
    });
</script>
@endpush
