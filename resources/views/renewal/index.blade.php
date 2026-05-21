@extends('layouts.app')

@section('content')
@php
    use App\Models\SubscriptionPlan;

    // 各層級 UI 預設配置（icon 為 fallback，可由方案資料中的 icon 欄位覆寫）
    $tierUiMeta = [
        SubscriptionPlan::TIER_BASIC    => ['code' => 'BASIC',    'label' => '初級方案', 'icon' => 'fas fa-user-circle'],
        SubscriptionPlan::TIER_ADVANCED => ['code' => 'ADVANCED', 'label' => '進階方案', 'icon' => 'fas fa-chart-line'],
        SubscriptionPlan::TIER_BUSINESS => ['code' => 'BUSINESS', 'label' => '商業方案', 'icon' => 'fas fa-briefcase'],
    ];

    // 已分層方案數（用於判斷是否需要 fallback）
    $tieredPlanCount = collect($tierUiMeta)
        ->keys()
        ->sum(fn($k) => $plansByTier->get($k, collect())->count());

    // 未分層方案（fallback 用）
    $ungroupedPlans = $plansByTier->get('other', collect());
@endphp

<section class="content-header">
    <div class="container-fluid">
        <h1>訂閱方案</h1>
    </div>
</section>
<div class="content px-3">
    @include('flash::message')

    {{-- 到期資訊卡片 --}}
    <div class="card mb-4">
        <div class="card-body">
            <h5>帳號到期資訊</h5>
            @if($user->expires_at)
                @if($daysUntilExpiry < 0)
                    <div class="alert alert-danger mb-0">您的帳號已於 {{ $user->expires_at->format('Y-m-d') }} 過期</div>
                @elseif($daysUntilExpiry <= 7)
                    <div class="alert alert-danger mb-0">帳號將於 {{ $user->expires_at->format('Y-m-d') }} 到期（剩餘 {{ $daysUntilExpiry }} 天）</div>
                @elseif($daysUntilExpiry <= 30)
                    <div class="alert alert-warning mb-0">帳號將於 {{ $user->expires_at->format('Y-m-d') }} 到期（剩餘 {{ $daysUntilExpiry }} 天）</div>
                @else
                    <div class="alert alert-info mb-0">帳號到期日：{{ $user->expires_at->format('Y-m-d') }}（剩餘 {{ $daysUntilExpiry }} 天）</div>
                @endif
            @else
                <div class="alert alert-info mb-0">您的帳號無到期日限制</div>
            @endif
        </div>
    </div>

    {{-- 有 pending 訂單時顯示提醒 --}}
    @if($pendingOrder)
        <div class="alert alert-info">
            您有一筆待付款訂單（{{ $pendingOrder->order_no }}，方案：{{ $pendingOrder->plan->name }}），
            請先完成付款或取消後再建立新訂單。
            @if($pendingOrder->payment_method === 'bank_transfer')
                <a href="{{ route('renewal.bank-transfer', $pendingOrder->id) }}" class="btn btn-sm btn-primary ml-2">查看匯款資訊</a>
            @elseif(in_array($pendingOrder->payment_method, ['ecpay_credit', 'newebpay_credit']))
                <a href="{{ route('renewal.payment-redirect', $pendingOrder->id) }}" class="btn btn-sm btn-primary ml-2">繼續付款</a>
            @endif
        </div>
    @endif

    @if(!$pendingOrder)
    {!! Form::open(['route' => 'renewal.create-order', 'method' => 'POST', 'id' => 'renewal-form']) !!}

    @if($tieredPlanCount === 0 && $ungroupedPlans->isEmpty())
        {{-- 無任何啟用方案 --}}
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            目前尚無可訂閱的方案，請聯繫管理員。
        </div>
    @elseif($tieredPlanCount === 0)
        {{-- Fallback：方案皆未設定 plan_tier，以單欄列出 --}}
        @auth
            @if(Auth::user()->isSuperAdmin() || (method_exists(Auth::user(), 'isMainUser') && Auth::user()->isMainUser()))
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    現有方案尚未設定「方案層級」，前台分層卡片暫不顯示。
                    請至 <a href="{{ route('admin.subscriptionPlans.index') }}" class="alert-link">訂閱方案管理</a>
                    為每個方案選擇 BASIC / ADVANCED / BUSINESS 並儲存。
                </div>
            @endif
        @endauth

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
                            <p class="plan-audience text-center text-muted small">（{{ $plan->target_audience }}）</p>
                        @endif

                        <ul class="plan-duration-list list-unstyled mt-3">
                            <li class="plan-duration-item {{ $plan->is_featured ? 'is-featured' : '' }}">
                                <label class="d-flex align-items-center mb-0">
                                    <input type="radio" name="plan_id" value="{{ $plan->id }}" class="mr-2" required>
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
    {{-- 四欄分層方案卡片 --}}
    <div class="row plan-tier-row">
        @foreach($tierOrder as $tierKey)
            @php
                $plansInTier = $plansByTier->get($tierKey, collect());
                $ui          = $tierUiMeta[$tierKey] ?? null;
                if ($plansInTier->isEmpty() || !$ui) continue;

                // 該層級的代表方案（用於顯示頁數、簡介、適合對象、是否最受歡迎）
                $heroPlan       = $plansInTier->first(fn($p) => (bool) $p->is_popular) ?? $plansInTier->first();
                $tierIsPopular  = $plansInTier->contains(fn($p) => (bool) $p->is_popular);
                $tierPageLimit  = $plansInTier->pluck('page_limit')->filter()->first();
                $tierDescription= $plansInTier->pluck('description')->filter()->first();
                $tierAudience   = $plansInTier->pluck('target_audience')->filter()->first();
                $tierIcon       = $plansInTier->pluck('icon')->filter()->first() ?: $ui['icon'];
            @endphp

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="plan-card {{ $tierIsPopular ? 'is-popular' : '' }}">
                    @if($tierIsPopular)
                        <div class="plan-popular-ribbon">最受歡迎</div>
                    @endif

                    @php
                        $tierIconImg = $plansInTier->pluck('icon')->filter()->first();
                    @endphp
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
                        <p class="plan-audience text-center text-muted small">（{{ $tierAudience }}）</p>
                    @endif

                    <div class="plan-pick-label">選擇訂閱期：</div>
                    <ul class="plan-duration-list list-unstyled">
                        @foreach($plansInTier as $plan)
                            <li class="plan-duration-item {{ $plan->is_featured ? 'is-featured' : '' }}">
                                <label class="d-flex align-items-center mb-0">
                                    <input type="radio" name="plan_id" value="{{ $plan->id }}"
                                           class="mr-2" required>
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

        {{-- CUSTOM 客製化卡片（寫死） --}}
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="plan-card plan-card-custom">
                <div class="plan-card-head text-center">
                    <div class="plan-icon"><i class="fas fa-magic"></i></div>
                    <div class="plan-tier-code">CUSTOM</div>
                    <div class="plan-tier-title">客製化</div>
                </div>
                <p class="plan-desc text-center">適合有特殊需求或進階應用的你</p>
                <p class="plan-audience text-center text-muted small">（品牌商/進階經營者）</p>
                <div class="plan-pick-label">選擇訂閱期：</div>
                <div class="text-center mt-3">
                    <a href="https://lin.ee/xY3mp3n" class="btn btn-outline-success btn-block">
                        <i class="far fa-comment-dots"></i> 聯絡客服詢價
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 付款方式與送出 --}}
    <div class="card">
        <div class="card-body">
            <div class="form-group">
                <label>付款方式</label>
                <select name="payment_method" class="form-control" style="max-width:300px" required>
                    <option value="">請選擇付款方式</option>
                    @foreach($paymentOptions as $opt)
                        <option value="{{ $opt['value'] }}" {{ $opt['value'] === $defaultPaymentMethod ? 'selected' : '' }}>
                            {{ $opt['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-cart"></i> 立即訂閱
            </button>
        </div>
    </div>

    {!! Form::close() !!}
    @endif

    {{-- 備註區（動態使用系統設定） --}}
    <div class="card mt-3 plan-notes-card">
        <div class="card-body">
            <ul class="mb-0 small text-muted">
                <li>第一次設計名片時，需含設計費用 ${{ number_format($designFee) }}</li>
                <li>本服務於到期未續約時，系統將自動備份您的名片資料；若日後重新開通服務時，將酌收設定費用 NT${{ number_format($reactivationFee) }} 元</li>
                <li>未繳費期間，名片仍可保留並可瀏覽 {{ $retentionDays }} 天（期間提供查看，無法進行編輯或變更）；若超過 {{ $retentionDays }} 天，系統將停止名片服務，畫面顯示並將其備份雲端上之相關資料</li>
            </ul>
        </div>
    </div>

    {{-- 訂單歷史連結 --}}
    <div class="mt-3">
        <a href="{{ route('renewal.history') }}" class="btn btn-secondary">
            <i class="fas fa-history"></i> 查看訂單歷史
        </a>
    </div>
</div>

@endsection
