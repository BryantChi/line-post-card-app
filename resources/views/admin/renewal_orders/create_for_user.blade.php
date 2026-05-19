@extends('layouts.app')

@section('content')
@php
    use App\Models\SubscriptionPlan;

    $tierUiMeta = [
        SubscriptionPlan::TIER_BASIC    => ['code' => 'BASIC',    'label' => '初級方案', 'icon' => 'fas fa-user-circle'],
        SubscriptionPlan::TIER_ADVANCED => ['code' => 'ADVANCED', 'label' => '進階方案', 'icon' => 'fas fa-chart-line'],
        SubscriptionPlan::TIER_BUSINESS => ['code' => 'BUSINESS', 'label' => '商業方案', 'icon' => 'fas fa-briefcase'],
    ];

    $plansByTier = $plans->groupBy(fn($p) => $p->plan_tier ?: 'other');
    $tierOrder = array_keys($tierUiMeta);
    $tieredPlanCount = collect($tierOrder)->sum(fn($k) => $plansByTier->get($k, collect())->count());
    $ungroupedPlans = $plansByTier->get('other', collect());
@endphp

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>為會員建立續約訂單</h1>
            </div>
            <div class="col-sm-6">
                <a href="{{ route('admin.renewalOrders.index') }}" class="btn btn-default float-right">
                    <i class="fas fa-arrow-left"></i> 返回訂單列表
                </a>
            </div>
        </div>
    </div>
</section>

<div class="content px-3">
    @include('flash::message')

    {{-- 會員資訊卡 --}}
    <div class="card mb-3">
        <div class="card-body d-flex flex-wrap align-items-center">
            <div class="mr-auto">
                <span class="text-muted">會員：</span>
                <strong>{{ $subUser->name }}</strong>
                <small class="text-muted ml-2">{{ $subUser->email }}</small>
            </div>
            <div>
                @if($subUser->expires_at)
                    <span class="badge badge-{{ $subUser->expires_at->isPast() ? 'danger' : 'info' }}">
                        目前到期日：{{ $subUser->expires_at->format('Y-m-d') }}
                    </span>
                @else
                    <span class="badge badge-secondary">尚無到期日</span>
                @endif
            </div>
        </div>
    </div>

    {!! Form::open(['route' => ['admin.renewalOrders.storeForUser', $subUser->id], 'method' => 'POST', 'id' => 'admin-renewal-form']) !!}

        {{-- 方案選擇區 --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-tags mr-1"></i>選擇訂閱方案 <span class="text-danger">*</span>
                </h3>
            </div>
            <div class="card-body">
                @if($plans->isEmpty())
                    <div class="alert alert-warning mb-0">目前沒有啟用中的訂閱方案。請至「訂閱方案管理」新增。</div>
                @elseif($tieredPlanCount === 0)
                    {{-- Fallback：方案皆未設定 plan_tier --}}
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        現有方案尚未設定「方案層級」，以下以單欄列出。
                        <a href="{{ route('admin.subscriptionPlans.index') }}" class="alert-link">前往訂閱方案管理</a> 設定後將以分層卡片顯示。
                    </div>
                    <div class="row plan-tier-row">
                        @foreach($ungroupedPlans as $plan)
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="plan-card">
                                    <div class="plan-card-head text-center">
                                        <div class="plan-icon">
                                            @if($plan->icon)
                                                <img src="{{ asset('uploads/' . $plan->icon) }}" alt="">
                                            @else
                                                <i class="fas fa-tag"></i>
                                            @endif
                                        </div>
                                        <div class="plan-tier-title">{{ $plan->name }}</div>
                                    </div>
                                    @if($plan->description)
                                        <div class="plan-desc">{!! $plan->description !!}</div>
                                    @endif
                                    @if($plan->target_audience)
                                        <p class="plan-audience text-muted small">（{{ $plan->target_audience }}）</p>
                                    @endif

                                    <ul class="plan-duration-list list-unstyled mt-3">
                                        <li class="plan-duration-item {{ $plan->is_featured ? 'is-featured' : '' }}">
                                            <label class="d-flex align-items-center mb-0">
                                                <input type="radio" name="plan_id" value="{{ $plan->id }}" class="mr-2"
                                                       {{ old('plan_id') == $plan->id ? 'checked' : '' }} required>
                                                <span class="duration-label">{{ $plan->getDurationLabel() }}</span>
                                                @if($plan->is_featured)
                                                    <span class="badge badge-warning ml-2">特別優惠</span>
                                                @endif
                                                <span class="ml-auto text-right">
                                                    <span class="duration-price">${{ number_format($plan->price) }}</span>
                                                    <br>
                                                    <small class="text-muted">平均每月 ${{ number_format($plan->getAveragePerMonth()) }}</small>
                                                </span>
                                            </label>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- 分層方案卡片 --}}
                    <div class="row plan-tier-row">
                        @foreach($tierOrder as $tierKey)
                            @php
                                $plansInTier = $plansByTier->get($tierKey, collect());
                                $ui          = $tierUiMeta[$tierKey] ?? null;
                                if ($plansInTier->isEmpty() || !$ui) continue;

                                $tierIsPopular  = $plansInTier->contains(fn($p) => (bool) $p->is_popular);
                                $tierPageLimit  = $plansInTier->pluck('page_limit')->filter()->first();
                                $tierDescription= $plansInTier->pluck('description')->filter()->first();
                                $tierAudience   = $plansInTier->pluck('target_audience')->filter()->first();
                                $tierIcon       = $ui['icon'];
                                $tierIconImg    = $plansInTier->pluck('icon')->filter()->first();
                            @endphp

                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="plan-card {{ $tierIsPopular ? 'is-popular' : '' }}">
                                    @if($tierIsPopular)
                                        <div class="plan-popular-ribbon">最受歡迎</div>
                                    @endif
                                    <div class="plan-card-head text-center">
                                        <div class="plan-icon">
                                            @if($tierIconImg)
                                                <img src="{{ asset('uploads/' . $tierIconImg) }}" alt="">
                                            @else
                                                <i class="{{ $tierIcon }}"></i>
                                            @endif
                                        </div>
                                        <div class="plan-tier-code">{{ $ui['code'] }}</div>
                                        <div class="plan-tier-title">
                                            {{ $ui['label'] }}
                                            @if($tierPageLimit)
                                                <span class="text-muted">| {{ $tierPageLimit }}頁</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($tierDescription)
                                        <div class="plan-desc">{!! $tierDescription !!}</div>
                                    @endif
                                    @if($tierAudience)
                                        <p class="plan-audience text-muted small">（{{ $tierAudience }}）</p>
                                    @endif

                                    <div class="plan-pick-label">選擇訂閱期：</div>
                                    <ul class="plan-duration-list list-unstyled">
                                        @foreach($plansInTier as $plan)
                                            <li class="plan-duration-item {{ $plan->is_featured ? 'is-featured' : '' }}">
                                                <label class="d-flex align-items-center mb-0">
                                                    <input type="radio" name="plan_id" value="{{ $plan->id }}" class="mr-2"
                                                           {{ old('plan_id') == $plan->id ? 'checked' : '' }} required>
                                                    <span class="duration-label">{{ $plan->getDurationLabel() }}</span>
                                                    @if($plan->is_featured)
                                                        <span class="badge badge-warning ml-2">特別優惠</span>
                                                    @endif
                                                    <span class="ml-auto text-right">
                                                        <span class="duration-price">${{ number_format($plan->price) }}</span>
                                                        <br>
                                                        <small class="text-muted">平均每月 ${{ number_format($plan->getAveragePerMonth()) }}</small>
                                                    </span>
                                                </label>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                @error('plan_id')
                    <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- 付款方式與管理員備註 --}}
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label for="payment_method" class="font-weight-bold">付款方式 <span class="text-danger">*</span></label>
                    <select name="payment_method" id="payment_method"
                            class="form-control @error('payment_method') is-invalid @enderror"
                            style="max-width: 300px;" required>
                        <option value="">請選擇付款方式</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>匯款</option>
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>現金</option>
                        <option value="ecpay_credit" {{ old('payment_method') === 'ecpay_credit' ? 'selected' : '' }}>信用卡（綠界）</option>
                    </select>
                    @error('payment_method')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="admin_note">管理員備註</label>
                    <textarea name="admin_note" id="admin_note" rows="3"
                              class="form-control @error('admin_note') is-invalid @enderror"
                              placeholder="選填，用於記錄付款相關說明..."
                              style="max-width: 600px;">{{ old('admin_note') }}</textarea>
                    @error('admin_note')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4">
                    {!! Form::submit('建立訂單', ['class' => 'btn btn-primary btn-lg']) !!}
                    <a href="{{ route('admin.renewalOrders.index') }}" class="btn btn-default ml-2">取消</a>
                </div>
            </div>
        </div>

    {!! Form::close() !!}
</div>
@endsection
