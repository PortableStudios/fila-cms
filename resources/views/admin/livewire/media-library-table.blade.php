<div>
  <ol class="flex flex-wrap items-center mb-2 fi-breadcrumbs-list gap-x-2">
        @foreach ($this->breadcrumbs() as $link => $breadcrumb)
      <li class="flex fi-breadcrumbs-item gap-x-2">
                @if (!$loop->first)
          <svg class="flex w-5 h-5 text-gray-400 fi-breadcrumbs-item-separator dark:text-gray-500 rtl:hidden"
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path fill-rule="evenodd"
                            d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
                            clip-rule="evenodd"></path>
                    </svg>
          <svg class="flex w-5 h-5 text-gray-400 fi-breadcrumbs-item-separator dark:text-gray-500 ltr:hidden"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                        data-slot="icon">
                        <path fill-rule="evenodd"
                            d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z"
                            clip-rule="evenodd"></path>
                    </svg>
                @endif
                <a href="javascript:setParent('{{ $link }}')"
          class="text-sm font-medium text-gray-500 transition duration-75 fi-breadcrumbs-item-label hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    {{ $breadcrumb }}
                </a>
            </li>
        @endforeach
    </ol>
    @script
        <script>
            window.setParent = function(id) {
                $wire.call('setParent', id);
                $wire.$refresh();
            }
            Livewire.on('set-current-parent-cookie', (id) => {
                var currentDate = new Date();
                // Calculate the expiry time for 1 day
                var expiryDate = new Date(currentDate.getTime() + (24 * 60 * 60 * 1000));        
                var expires = expiryDate.toUTCString();
                document.cookie = "{{ $this->cookieKey }}=" + encodeURIComponent(id) + "; expires=" + expires +"; path=/";
            });
        </script>
    @endscript
    {{ $this->table }}
</div>
