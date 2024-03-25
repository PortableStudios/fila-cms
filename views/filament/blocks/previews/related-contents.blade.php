@php
    $modelString = 'Portable\FilaCms\Models\Page';
    $model = new $modelString();

    $ids = [];
    foreach ($selectedContents as $key => $item) {
        $ids[] = $item['content'];
    }
    $selectedModels = $model->whereIn('id', $ids)->get();
@endphp

<div class="flex items-center">

    <div>
        <h3>{{ $heading }}</h3>

        <div class="flex flex-wrap gap-4 mt-4">
            @foreach ($selectedModels as $key => $item)
                <div class="w-1/3 overflow-hidden border rounded border-md">
                    <div class="px-4 py-1 uppercase bg-gray-200 semibold">Page</div>
                    <div class="px-4 py-4">
                        <h4>{{ $item->title }}</h4>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
