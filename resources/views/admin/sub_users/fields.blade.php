<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', '帳號:') !!}
    {{-- {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'name', 'required' => true]) !!} --}}
    <input type="text" name="name" id="name" value="{{ $subUser->name ?? '' }}" class="form-control" required {{ Request::is('admin/sub-users/edit*') ? 'disabled' : '' }}>
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email:') !!}
    {{-- {!! Form::text('email', $subUser->email ?? '', ['class' => 'form-control', 'id' => 'email', 'required' => true, 'disabled' => Request::is('admin/sub-users/edit*')]) !!} --}}
    <input type="text" name="email" id="email" value="{{ $subUser->email ?? '' }}" class="form-control" required {{ Request::is('admin/sub-users/edit*') ? 'disabled' : '' }}>
</div>

<!-- Password Field -->
<div class="form-group col-sm-6">
    {!! Form::label('password', '密碼:') !!}
    {{-- {!! Form::text('password', null, ['class' => 'form-control', 'id' => 'password', 'type' => 'password']) !!} --}}
    <input type="password" name="password" id="password" class="form-control" placeholder="請輸入密碼，最少6碼" minlength="6">
    @if (Request::is('admin/sub-users/*/edit'))
    <span class="help-block text-danger">★若欲變更密碼，才需輸入密碼，最少6碼</span>
    @endif
</div>

<div class="form-group col-sm-6">
    {!! Form::label('password_confirmation', '確認密碼:') !!}
    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="請輸入密碼，最少6碼" minlength="6">
    @if (Request::is('admin/sub-users/*/edit'))
    <span class="help-block text-danger">★若欲變更密碼，才需輸入密碼，最少6碼</span>
    @endif
</div>

<!-- Parent Id Field only for Super Admin User -->
@if (Auth::user()->isSuperAdmin())
<div class="form-group col-sm-6">
    {!! Form::label('parent_id', '主帳號:') !!}
    <select name="parent_id" class="form-control">
        <option value="">請選擇</option>
        @foreach ($mainUsers ?? [] as $mainUser)
            <option value="{{ $mainUser->id }}" {{ (($subUser->parent_id ?? 0) == $mainUser->id) ? 'selected' : '' }}>
                {{ $mainUser->name }} ({{ $mainUser->email }})
            </option>
        @endforeach
    </select>
    <span class="help-block text-danger">★若不設定，預設直屬Super Admin</span>
</div>
@endif

<!-- remarks Field -->
<div class="form-group col-sm-12">
    {!! Form::label('remarks', '備註:') !!}
    <textarea name="remarks" id="remarks" class="form-control" rows="3">{{ $subUser->remarks ?? '' }}</textarea>
    <span class="help-block text-danger">★備註欄位，可用於記錄其他資訊</span>
</div>

<!-- expires_at Field -->
<div class="form-group col-sm-6">
    {!! Form::label('expires_at', '到期日:') !!}
    <input type="date" name="expires_at" id="expires_at" value="{{ \Carbon\Carbon::parse(($subUser->expires_at ?? \Carbon\Carbon::now()->addYear()))->format('Y-m-d') }}" class="form-control datepicker">
    <span class="help-block text-danger">★若不設定，預設有效期限一年</span>
</div>


<!-- Active Field -->
<div class="form-group col-sm-6">
    {!! Form::label('active', '啟用狀態:') !!}
    <select name="active" class="form-control">
        <option value="1" {{ ($subUser->active ?? true) == 1 ? 'selected' : '' }}>是</option>
        <option value="0" {{ ($subUser->active ?? true) == 0 ? 'selected' : '' }}>否</option>
    </select>
</div>

<!-- Max Business Cards Field -->
<div class="form-group col-sm-6">
    {!! Form::label('max_business_cards', '名片數量上限:') !!}
    <input type="number"
           name="max_business_cards"
           id="max_business_cards"
           value="{{ old('max_business_cards', $subUser->max_business_cards ?? $defaultMaxBusinessCards ?? 1) }}"
           class="form-control @error('max_business_cards') is-invalid @enderror"
           min="{{ isset($subUser) ? $subUser->businessCards()->count() : 1 }}"
           required>
    @if(isset($subUser))
        <small class="form-text text-muted">
            目前已建立: {{ $subUser->businessCards()->count() }} 張名片
        </small>
    @else
        <small class="form-text text-muted">
            該子帳號可建立的AI數位名片數量上限
        </small>
    @endif
    @error('max_business_cards')
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror
</div>

<!-- Max Card Bubbles Field -->
<div class="form-group col-sm-6">
    {!! Form::label('max_card_bubbles', '每張名片的卡片數量上限:') !!}
    <input type="number"
           name="max_card_bubbles"
           id="max_card_bubbles"
           value="{{ old('max_card_bubbles', $subUser->max_card_bubbles ?? $defaultMaxCardBubbles ?? 10) }}"
           class="form-control @error('max_card_bubbles') is-invalid @enderror"
           min="1"
           max="10"
           required>
    <small class="form-text text-muted">
        每張名片內可新增的卡片數量上限 (系統最大限制為 10 張)
    </small>
    @error('max_card_bubbles')
        <span class="invalid-feedback d-block">{{ $message }}</span>
    @enderror
</div>

@if (Auth::user()->isSuperAdmin())
<!-- Signature Field (僅超級管理員可編輯) -->
<div class="form-group col-sm-12">
    {!! Form::label('signature', '自訂署名:') !!}
    <div class="input-group">
        <div class="input-group-prepend">
            <span class="input-group-text">Design by</span>
        </div>
        <input type="text" name="signature" id="signature" value="{{ old('signature', $subUser->signature ?? '') }}" class="form-control" maxlength="100" placeholder="誠翊資訊網路應用事業">
    </div>
    <span class="help-block text-muted">
        此署名會顯示在該帳號名片分享頁面底部。留空則使用預設值「誠翊資訊網路應用事業」。
    </span>
</div>
@endif

