@php
    $items = $items ?? [];
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top:22px;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;background:#ffffff;">
    @foreach($items as $item)
        @php
            $value = $item['value'] ?? null;
            if ($value instanceof \Illuminate\Support\HtmlString) {
                $displayValue = $value->toHtml();
            } elseif ($value instanceof \Stringable) {
                $displayValue = (string) $value;
            } elseif (is_scalar($value)) {
                $displayValue = (string) $value;
            } elseif (is_array($value)) {
                $displayValue = implode('، ', array_map(static fn ($entry) => is_scalar($entry) ? (string) $entry : json_encode($entry, JSON_UNESCAPED_UNICODE), $value));
            } else {
                $displayValue = null;
            }

            $displayValue = filled($displayValue) ? $displayValue : 'غير متوفر';
        @endphp
        <tr>
            <td class="detail-label" width="34%" style="padding:13px 16px;background:#f8fafc;color:#64748b;font-size:13px;font-weight:800;line-height:1.7;border-bottom:1px solid #e2e8f0;vertical-align:top;">
                {{ $item['label'] ?? '' }}
            </td>
            <td class="detail-value" style="padding:13px 16px;background:#ffffff;color:#0f172a;font-size:14px;font-weight:600;line-height:1.8;border-bottom:1px solid #e2e8f0;vertical-align:top;">
                @if(($item['is_html'] ?? false) === true)
                    {!! $displayValue !!}
                @else
                    {{ $displayValue }}
                @endif
            </td>
        </tr>
    @endforeach
</table>
