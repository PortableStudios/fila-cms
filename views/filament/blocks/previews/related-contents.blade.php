<div class="flex items-center">

    <div>
        <h3>{{ $heading }}</h3>

        <div class="flex flex-wrap gap-4 mt-4">
            @foreach ($selectedContents as $key => $item)
                <div class="w-1/3 overflow-hidden border rounded border-md">
                    <div class="px-4 py-1 uppercase semibold">{{ \Str::studly($item['source']) }}</div>
                    <div class="px-4 py-4">
                        <h4>{{ $item['source'] }}</h4>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
