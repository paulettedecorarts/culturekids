{{-- Organisation theme: separate token sets for light vs dark (data-*-theme on <html>) --}}
<style id="portal-org-theme">
    [data-cms-theme="light"],
    [data-sa-theme="light"],
    [data-th-theme="light"] {
@foreach ($portalThemeCssVarsLight ?? [] as $var => $value)
        {{ $var }}: {{ $value }};
@endforeach
    }

    [data-cms-theme="dark"],
    [data-sa-theme="dark"],
    [data-th-theme="dark"] {
@foreach ($portalThemeCssVarsDark ?? [] as $var => $value)
        {{ $var }}: {{ $value }};
@endforeach
    }
</style>
<script>
    window.portalThemeCssVarsLight = @json($portalThemeCssVarsLight ?? []);
    window.portalThemeCssVarsDark = @json($portalThemeCssVarsDark ?? []);

    function applyPortalThemeCssVars(light, dark) {
        var lightVars = light || window.portalThemeCssVarsLight;
        var darkVars = dark || window.portalThemeCssVarsDark;
        if (!lightVars || !darkVars) return;

        var style = document.getElementById('portal-org-theme');
        if (!style) return;

        var lightSel = '[data-cms-theme="light"], [data-sa-theme="light"], [data-th-theme="light"]';
        var darkSel = '[data-cms-theme="dark"], [data-sa-theme="dark"], [data-th-theme="dark"]';
        var lightBlock = Object.keys(lightVars).map(function (k) { return '        ' + k + ': ' + lightVars[k] + ';'; }).join('\n');
        var darkBlock = Object.keys(darkVars).map(function (k) { return '        ' + k + ': ' + darkVars[k] + ';'; }).join('\n');

        style.textContent = lightSel + ' {\n' + lightBlock + '\n    }\n\n    ' + darkSel + ' {\n' + darkBlock + '\n    }';

        window.portalThemeCssVarsLight = lightVars;
        window.portalThemeCssVarsDark = darkVars;
    }

    document.addEventListener('livewire:init', function () {
        Livewire.on('portal-theme-updated', function (data) {
            if (!data) return;
            applyPortalThemeCssVars(data.cssVarsLight || data.cssVars, data.cssVarsDark);
        });
    });
</script>
