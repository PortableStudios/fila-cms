  <meta charset="utf-8" />

  <meta name="application-name" content="{{ config('app.name') }}" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>{{ config('app.name') }}</title>

  <style>
    [x-cloak] {
      display: none !important;
    }
  </style>
  @filamentStyles
  @filaCmsStyles
