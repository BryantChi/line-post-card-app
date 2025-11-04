<!-- 標題 Field -->
<div class="form-group col-sm-6" data-step="1" data-intro="請為您的AI數位名片設定一個明確的名稱，方便您辨識。">
    {!! Form::label('title', '卡片名稱:') !!}
    {!! Form::text('title', null, ['class' => 'form-control', 'maxlength' => 255, 'required' => 'required']) !!}
    <small class="form-text text-muted">例如：我的業務名片、公司簡介等</small>
</div>

<!-- 副標題 Field -->
<div class="form-group col-sm-6" data-step="2" data-intro="可選填。如果您的名片需要一個副標題，請在此輸入。">
    {!! Form::label('subtitle', '副標題:') !!}
    {!! Form::text('subtitle', null, ['class' => 'form-control', 'maxlength' => 255]) !!}
</div>

<!-- 頭像/Logo Field -->
<div class="form-group col-sm-6" data-step="3" data-intro="上傳代表此AI數位名片的頭像或Logo圖片。">
    {!! Form::label('profile_image', '頭像/Logo:') !!}
    <div class="input-group">
        <div class="custom-file">
            {!! Form::file('profile_image', ['class' => 'custom-file-input', 'id' => 'profile_image_input', 'accept' => 'image/*']) !!}
            {!! Form::label('profile_image', '選擇檔案', ['class' => 'custom-file-label']) !!}
        </div>
    </div>
    <div class="mt-2">
        @if(isset($businessCard) && $businessCard->profile_image)
            <img src="{{ asset('uploads/' . $businessCard->profile_image) }}" id="profile_image_preview" class="img-thumbnail max-h-100">
        @else
            <img src="" id="profile_image_preview" class="img-thumbnail max-h-100 d-none">
        @endif
    </div>
</div>

<!-- 啟用狀態 Field -->
<div class="form-group col-sm-6" data-step="4" data-intro="勾選此項以啟用此AI數位名片，使其可被分享和查看。">
    <div class="form-check mt-4">
        {!! Form::hidden('active', 0) !!}
        {!! Form::checkbox('active', '1', isset($businessCard) ? $businessCard->active : true, ['class' => 'form-check-input']) !!}
        {!! Form::label('active', '啟用此AI數位名片', ['class' => 'form-check-label']) !!}
    </div>
</div>

<!-- 內容 Field -->
<div class="form-group col-sm-12" data-step="5" data-intro="在此輸入關於此AI數位名片的簡短介紹或說明。">
    {!! Form::label('content', '簡介內容:') !!}
    <small class="form-text text-muted">可點選右邊[AI生成按鈕]重複生成您要的商務文案</small>
    <div class="input-group">
        {!! Form::textarea('content', null, ['class' => 'form-control', 'rows' => 3, 'id' => 'content_textarea']) !!}
        <div class="input-group-append">
            <button class="btn btn-outline-secondary" type="button" id="generate_content_btn" data-intro="點擊此按鈕，AI 將會協助您生成簡介內容。" data-step="5a">AI 生成</button>
        </div>
    </div>
    <small class="form-text text-muted">簡單介紹此AI數位名片的用途或內容</small>
</div>

@if (Auth::user()->isMainUser() || Auth::user()->isSuperAdmin())
    <!-- Views Field -->
    <div class="form-group col-sm-12">
        {!! Form::label('views', '瀏覽次數:') !!}
        {!! Form::number('views', null, ['class' => 'form-control', 'min' => 0]) !!}
    </div>

    <!-- Shares Field -->
    <div class="form-group col-sm-12">
        {!! Form::label('shares', '分享次數:') !!}
        {!! Form::number('shares', null, ['class' => 'form-control', 'min' => 0]) !!}
    </div>
@endif

@if(isset($businessCard))
    <!-- 重新生成 Flex JSON Field -->
    <div class="form-group col-sm-12" data-step="6" data-intro="如果您對內含的卡片進行了修改，並希望更新整個AI數位名片的LINE Flex Message JSON結構，請勾選此項。">
        <div class="form-check">
            {!! Form::hidden('regenerate_flex', 0) !!}
            {!! Form::checkbox('regenerate_flex', '1', false, ['class' => 'form-check-input']) !!}
            {!! Form::label('regenerate_flex', '重新生成 Flex JSON', ['class' => 'form-check-label']) !!}
            <small class="form-text text-muted">勾選此項將根據現有AI數位名片-卡片重新生成 Flex JSON 結構</small>
        </div>
    </div>
@endif

@push('page_scripts')
<script @cspNonce>
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

        // AI 生成內容按鈕事件
        $('#generate_content_btn').click(function() {
            const title = $('input[name="title"]').val();
            const subtitle = $('input[name="subtitle"]').val();
            const $btn = $(this);
            const originalButtonText = $btn.text();

            if (!title) {
                alert('請先輸入卡片名稱，以利 AI 生成更精準的內容。');
                return;
            }

            $btn.prop('disabled', true).text('生成中...');
            $('#content_textarea').val(''); // 清空現有內容或顯示載入提示

            $.ajax({
                url: '{{ route("admin.ai.generateBusinessCardContent") }}', // 假設您的路由名稱
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    title: title,
                    subtitle: subtitle,
                    // 您可以根據需要傳遞更多上下文信息
                },
                success: function(response) {
                    console.log(response);
                    if (response.success && response.content) {
                        $('#content_textarea').val(response.content);
                    } else {
                        $('#content_textarea').val('無法生成內容，請稍後再試。');
                        alert(response.message || 'AI 生成失敗，請檢查後台日誌。');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AI 生成錯誤:", xhr.responseText);
                    $('#content_textarea').val('生成內容時發生錯誤。');
                    alert('AI 生成請求失敗，請檢查網絡連接或聯繫管理員。');
                },
                complete: function() {
                    $btn.prop('disabled', false).text(originalButtonText);
                }
            });
        });
    });
</script>
@endpush
