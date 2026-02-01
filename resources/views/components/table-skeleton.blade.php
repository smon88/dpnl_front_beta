@props(['rows' => 5, 'cols' => 4])

<div {{ $attributes->merge(['class' => 'table-skeleton']) }}>
    @for($i = 0; $i < $rows; $i++)
    <div class="skeleton-row">
        @for($j = 0; $j < $cols; $j++)
        <div class="skeleton-cell">
            <div class="skeleton skeleton-text" style="width: {{ rand(60, 100) }}%"></div>
        </div>
        @endfor
        <div class="skeleton-cell-actions">
            <div class="skeleton skeleton-btn"></div>
            <div class="skeleton skeleton-btn"></div>
        </div>
    </div>
    @endfor
</div>
