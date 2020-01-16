jQuery(function ($) {
    function search_comuna(region) {
        var comunas = [];
        for (let i in window.comunas) {
            var comuna = window.comunas[i];

            if (region === comuna.region) {
                comunas.push(comuna);
            }
        }

        return comunas;
    }

    $('#billing_city').change(function (e) {
        var region = this.value;
        var $comunas = $('#billing_state');
        var comunas = search_comuna(region);
        $comunas.html('');

        for (let i in comunas) {
            var comuna = comunas[i];

            $comunas.append('<option value="' + comuna.comuna + '">' + comuna.comuna + '</option>');
        }
    });
});