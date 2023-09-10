<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->

    </head>
    <body class="antialiased">
        @if($article)
            <h1>{{ $article->filename }}</h1>
            <p>{{ $article->path }}</p>
            <button id="play-button">Play</button>
        @endif

        </body>
        <script src="{{ asset('js/text-to-speech.js') }}"></script>
</html>
