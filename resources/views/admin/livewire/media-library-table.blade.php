<div>
  <ol class="fi-breadcrumbs-list flex flex-wrap items-center gap-x-2 mb-2">
    @foreach ($this->breadcrumbs() as $link => $breadcrumb)
      <li class="fi-breadcrumbs-item flex gap-x-2">
        @if (!$loop->first)
          <svg class="fi-breadcrumbs-item-separator flex h-5 w-5 text-gray-400 dark:text-gray-500 rtl:hidden"
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
            <path fill-rule="evenodd"
              d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
              clip-rule="evenodd"></path>
          </svg>
          <svg class="fi-breadcrumbs-item-separator flex h-5 w-5 text-gray-400 dark:text-gray-500 ltr:hidden"
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
            data-slot="icon">
            <path fill-rule="evenodd"
              d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z"
              clip-rule="evenodd"></path>
          </svg>
        @endif
        <a href="javascript:setParent('{{ $link }}')"
          class="fi-breadcrumbs-item-label text-sm font-medium text-gray-500 transition duration-75 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
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
        // Calculate the expiry time for one week
        var expiryDate = new Date(currentDate.getTime() + (24 * 60 * 60 * 1000));
        // Convert the expiry date to UTC format
        var expires = expiryDate.toUTCString();
        // Set the cookie with the specified key, value, and expiry
        document.cookie = "{{$this->cookieKey}}=" + encodeURIComponent(id) + "; expires=" + expires + "; path=/";  
      });
    </script>
  @endscript
  {{ $this->table }}
</div>
