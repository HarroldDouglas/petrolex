<div style="max-width: 420px;">
    <div class="text-truncate" title="{{ $log->message }}">{{ Str::limit($log->message, 120) }}</div>
    @if ($log->stack_trace || $log->context)
        <details class="mt-1">
            <summary class="text-muted f-s-12" style="cursor: pointer;">Détails</summary>
            @if ($log->message && strlen($log->message) > 120)
                <pre class="f-s-12 mt-1 mb-1 text-wrap">{{ $log->message }}</pre>
            @endif
            @if ($log->stack_trace)
                <pre class="f-s-12 mt-1 mb-1"
                    style="max-height: 240px; overflow: auto; white-space: pre-wrap;">{{ $log->stack_trace }}</pre>
            @endif
            @if ($log->context)
                <pre class="f-s-12 mt-1 mb-0"
                    style="max-height: 160px; overflow: auto; white-space: pre-wrap;">{{ json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
            @endif
        </details>
    @endif
</div>
