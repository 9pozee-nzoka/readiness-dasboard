@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel' || trim($slot) === config('app.name'))
<table cellpadding="0" cellspacing="0" border="0" style="margin: 0 auto;">
    <tr>
        <td style="padding: 0 10px;"><img src="{{ url('assets/logos/1.png') }}" alt="KSG" style="height: 60px; display: block;"></td>
        <td style="padding: 0 10px;"><img src="{{ url('assets/logos/2.png') }}" alt="KSG" style="height: 60px; display: block;"></td>
        <td style="padding: 0 10px;"><img src="{{ url('assets/logos/3.png') }}" alt="KSG" style="height: 60px; display: block;"></td>
    </tr>
</table>
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
