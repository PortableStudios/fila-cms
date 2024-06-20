<div class="fi-ta-text grid w-full gap-y-1 px-3 py-4">
  <div class="flex">
    <div class="fi-ta-text-item inline-flex items-center gap-1.5 ">
      <span class="fi-ta-text-item-label text-sm leading-6 text-gray-950">
        {{ $getRecord()->userResponsible() ? $getRecord()->userResponsible()->name : '' }}
      </span>
    </div>
  </div>
</div>
