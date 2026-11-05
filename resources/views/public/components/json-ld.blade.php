@if ($schema)
    <script type="application/ld+json">
        {!! json_encode($schema, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endif
