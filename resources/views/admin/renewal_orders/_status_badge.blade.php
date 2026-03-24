@php
    $statusMap = [
        'pending'   => ['class' => 'badge-warning',   'label' => '待付款'],
        'paid'      => ['class' => 'badge-success',   'label' => '已付款'],
        'cancelled' => ['class' => 'badge-secondary', 'label' => '已取消'],
        'expired'   => ['class' => 'badge-danger',    'label' => '已逾期'],
    ];
    $info = $statusMap[$status] ?? ['class' => 'badge-light', 'label' => $status];
@endphp
<span class="badge {{ $info['class'] }}">{{ $info['label'] }}</span>
