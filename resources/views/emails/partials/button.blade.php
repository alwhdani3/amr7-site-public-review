@php
    $url = $url ?? '#';
    $label = $label ?? 'فتح';
@endphp

<table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin:24px auto 0;">
    <tr>
        <td align="center" bgcolor="#1fa7a2" style="border-radius:10px;background:#1fa7a2;">
            <a href="{{ $url }}" target="_blank" style="display:inline-block;padding:13px 26px;color:#ffffff;font-size:14px;font-weight:800;text-decoration:none;border-radius:10px;">
                {{ $label }}
            </a>
        </td>
    </tr>
</table>
