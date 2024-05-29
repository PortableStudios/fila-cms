<div class="border rounded">
    <div class="p-4 font-bold related-block-heading border-b block">
        <h3>{{ $heading }}</h3>
    </div>
    <div class="p-4">
        <div class="flex flex-wrap gap-4">
            @foreach ($selectedContents as $key => $item)
                <div class="w-1/3 overflow-hidden border rounded border-md">
                    <a href="{{ $item['url'] }}">
                        <div class="px-4 py-1 uppercase semibold">{{ \Str::studly($item['source']) }}</div>
                        <div class="px-4 py-4">
                            <h4>{{ $item['source'] }}</h4>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
