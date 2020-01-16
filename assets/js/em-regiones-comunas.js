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

    var $billingCity = $('#billing_city');
    var $comunas = $('#billing_state');
    var $selectedComuna = $comunas.find('option:selected');

    $billingCity.change(function (e) {
        var region = this.value;
        var comunas = search_comuna(region);
        $comunas.html('');

        for (let i in comunas) {
            var comuna = comunas[i];

            $comunas.append('<option value="' + comuna.comuna + '">' + comuna.comuna + '</option>');
        }
    });

    window.setTimeout(function () {
        $billingCity.trigger('change');

        if ($selectedComuna.length) {
            var selectedComuna = $selectedComuna.text();
            $('#billing_state option').prop('selected', false);
            $('#billing_state option[value="' + selectedComuna + '"]').prop('selected', true);
            $comunas.trigger('change');
        }
    }, 500);
});