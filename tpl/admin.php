<style>
    #trs_admin_bar {
        position: fixed;
        left: 50%;
        top: 50%;
        background: #bbb;
        border: 1px solid red;
        box-shadow: 10px 10px 10px rgba(0,0,0,0.5);
        padding: 50px;
        z-index: 999999;
        transform: translate(-50%, -50%);
        width: 256px; /* fixes blurry text due to transform */
        height: 92px; /* fixes blurry text due to transform */
    }

    #trs_ab_textarea {
        width: 100%;
    }
</style>

<div id="trs_admin_bar" style="display: none">
    <button id="trs_ab_copy">Read & Copy</button>
    <button id="trs_ab_write">Write</button>
    <button id="trs_ab_clear">Remove all rules</button>
    <br><br>
    <textarea id="trs_ab_textarea"></textarea>
</div>

<script>
    const bar = document.getElementById('trs_admin_bar');
    const readButton = document.getElementById('trs_ab_copy');
    const textarea = document.getElementById('trs_ab_textarea');

    document.body.appendChild(bar);

    document.addEventListener("keydown", function(e) {
        if ((e.ctrlKey || e.metaKey) && e.altKey && e.shiftKey && e.code === "KeyT") {
            const style = bar.style;
            style.display = style.display === 'block' ? 'none' : 'block';
            if (style.display === 'block') {
                readButton.focus();
            }
        }
    });

    readButton.addEventListener('click', (e) => {
        e.preventDefault();
        textarea.value = window.trsSaveRulesToJson();
        textarea.select();
        document.execCommand("copy");
    });

    document.getElementById('trs_ab_write').addEventListener('click', (e) => {

        e.preventDefault();

        if (!textarea.value) {
            alert('Paste rules JSON in the textarea.');
            return;
        }

        try {
            window.trsLoadRulesFromJson(textarea.value);
        } catch (e) {
            alert(e);
        }
    });

    document.getElementById('trs_ab_clear').addEventListener('click', (e) => {
        e.preventDefault();
        window.trsLoadRulesFromJson(null);
    });
</script>