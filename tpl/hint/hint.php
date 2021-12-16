<script id="trs_hint" type="text/html">
    <HintHandle/>
    <HintContent>
        {{yield}}
    </HintContent>
</script>

<script id="trs_hint_handle" type="text/html">
    <a class="hint-handle" title="Click to toggle hint" on-click="toggle()"><i class="fa"></i></a>
</script>

<script id="trs_hint_content" type="text/html">
    <div class="hint-content">
        {{yield}}
    </div>
</script>