@if(count($items))
<ul>
    @foreach($items as $item)
        <li>
            <a href="{{ $item['url'] }}" @if($item['active'] ?? false) aria-current="page" @endif>{{ $item['label'] }}</a>
            @if(!empty($item['children']))
                @include('uikit::frontend.menu.items', ['items' => $item['children']])
            @endif
        </li>
    @endforeach
</ul>
@endif
