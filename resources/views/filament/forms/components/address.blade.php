<x-dynamic-component :component="$getFieldWrapperView()" :label="$getLabel()">
  <div
    {{ $attributes->merge(
            [
                'id' => $getId(),
            ],
            escape: false,
        )->merge($getExtraAttributes(), escape: false) }}>
    {{ $getChildComponentContainer() }}
  </div>


</x-dynamic-component>
