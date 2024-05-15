<td class="text-{{ $align }} @if (!$width) text-truncate @endif"
    data-column="{{ $slug }}" colspan="{{ $colspan }}"
    @empty(!$width)style="width:{{ is_numeric($width) ? $width . 'px' : $width }}" @endempty>
    <label class="form-check-label fill-cell">
        <div class="form-check">
            <input class="form-check-input multiselect" {{ $checkbox }}
                @if (isset($checkbox['value']) && $checkbox['value'] && (!isset($checkbox['checked']) || $checkbox['checked'] !== false)) checked @endif
                data-column="{{$columnKey}}">
        </div>
    </label>
</td>
