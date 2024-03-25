<div class="flex items-center gap-6">
    <div class="text-5xl">
    </div>
    <div>
        <h3>{{$heading}}</h3>
        <hr/>
        
        <div class="flex py-2">
            @foreach($selectedContents as $key => $content)
                <div class="w-1/4 px-2 py-4 mx-2 border border-md">
                    <h5>{{$content['title']}}</h5>
                </div>
            @endforeach
        </div>
    </div>
</div>