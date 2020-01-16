<?php

/**
 * Plugin Name: Regiones y Comunas de Chile
 * Plugin URI: https://estudiomoca.cl/
 * Description: Comunas y regiones para Woocommerce
 * Version: 1.0.20201501
 * Author: Estudio MOCA
 * Author URI: https://estudiomoca.cl/
 * Text Domain: em_regiones_comunas
 * Requires at least: 5.2.0
 * Requires PHP: 5.6.20

 * WC requires at least: 3.6.0
 * WC tested up to: 3.7.0
 * @package Em_Regiones_Comunas
*/

class Em_Regiones_Comunas
{
    private $comunas = null;

    public function __construct()
    {
        add_filter('woocommerce_get_country_locale', [$this, 'filter_woocommerce_get_country_locale'], 10);
        add_filter('woocommerce_states', [$this, 'custom_woocommerce_states']);
        add_filter('woocommerce_checkout_fields', [$this, 'woocommerce_checkout_fields']);
        add_action('wp_enqueue_scripts', [$this, 'scripts']);
        add_action('wp_head', [$this, 'wp_head']);
    }

    public function wp_head()
    {
        $comunas = $this->get_comunas();
        $comunas_json = json_encode($comunas);
        echo '<script>
        window.comunas = ' . $comunas_json . '; 
        </script>';
    }

    public function scripts()
    {
        wp_enqueue_script('em_regiones_comunas', plugin_dir_url(__FILE__) . 'assets/js/em-regiones-comunas.js', ['jquery'], "1.0." . time(), true);
    }

    public function get_comunas($region_name = null)
    {
        global $wpdb;

        if (is_array($this->comunas)) {
            return $this->comunas;
        }

        $st_where = '';

        if ($region_name) {
            $st_where = " AND r.region='{$region_name}' ";
        }

        $sql = "SELECT co.*, p.provincia, r.* FROM {$wpdb->prefix}cl_comunas AS co
                    INNER JOIN {$wpdb->prefix}cl_provincias AS p ON co.provincia_id=p.id 
                    INNER JOIN {$wpdb->prefix}cl_regiones AS r ON p.region_id=r.id 
                    WHERE 1=1 {$st_where} ORDER BY co.comuna";

        $this->comunas = $results = $wpdb->get_results($sql);
        return $results;
    }

    public function woocommerce_checkout_fields($fields)
    {
        $options = [];
        $data = $this->get_regiones();

        foreach ($data as $d) {
            $options[$d->region] = $d->region;
        }

        $city_args = wp_parse_args(array(
            'type' => 'select',
            'options' => $options,
        ), $fields['shipping']['shipping_city']);

        $fields['shipping']['shipping_city'] = $city_args;
        $fields['billing']['billing_city'] = $city_args;

        //$fields['shipping']['shipping_state']['type'] = 'select';
        //$fields['billing']['billing_state']['type'] = 'select';

        return $fields;
    }

    public function filter_woocommerce_get_country_locale($array)
    {
        $array['CL']['state']['label'] = 'Comuna';
        $array['CL']['city']['label'] = 'Región';
        return $array;
    }

    public function custom_woocommerce_states($states)
    {
        $data = $this->get_comunas();

        foreach ($data as $d) {
            $options[$d->comuna] = $d->comuna;
        }
        $states['CL'] = $options;
        return $states;
    }

    public function get_regiones()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}cl_regiones");
    }
}

new Em_Regiones_Comunas();