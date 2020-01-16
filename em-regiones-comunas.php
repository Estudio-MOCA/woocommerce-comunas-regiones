<?php
/**
 * Plugin Name: Regiones y Comunas de Chile
 * Plugin URI: https://estudiomoca.cl/
 * Description: Comunas y regiones para Woocommerce
 * Version: 20.01.16
 * Author: Estudio MOCA
 * Author URI: https://estudiomoca.cl/
 * Text Domain: em_regiones_comunas
 * Requires PHP: 5.6.20

 * WC requires at least: 3.6.0
 * WC tested up to: 3.7.0
 * @package Em_Regiones_Comunas
*/

/**
 * Class Em_Regiones_Comunas clase principal del plugin
 * @property Em_Regiones_Comunas_Install $installer
 * @property array $comunas
 */
class Em_Regiones_Comunas
{
    private $comunas;
    private $installer;

    public function __construct()
    {
        include "inc/em-install.php";

        $this->installer = new Em_Regiones_Comunas_Install;
        add_filter('woocommerce_get_country_locale', [$this, 'filter_woocommerce_get_country_locale'], 10);
        add_filter('woocommerce_states', [$this, 'custom_woocommerce_states']);
        add_filter('woocommerce_checkout_fields', [$this, 'woocommerce_checkout_fields']);
        add_action('wp_enqueue_scripts', [$this, 'scripts']);
        add_action('wp_head', [$this, 'wp_head']);
        register_activation_hook( __FILE__, [$this->installer, 'install'] );
    }

    public function wp_head()
    {
        if (is_cart() || is_checkout()) {
            $comunas = $this->get_comunas();
            $comunas_json = json_encode($comunas);
            echo '<script>
            window.comunas = ' . $comunas_json . '; 
            </script>';
        }
    }

    public function scripts()
    {
        wp_enqueue_script('em_regiones_comunas', plugin_dir_url(__FILE__) . 'assets/js/em-regiones-comunas.js', ['jquery'], "1.0", true);
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

        $sql = "SELECT co.*, p.provincia, r.* FROM {$this->installer->comunas_table} AS co
                    INNER JOIN {$this->installer->provincias_table} AS p ON co.provincia_id=p.id 
                    INNER JOIN {$this->installer->regiones_table} AS r ON p.region_id=r.id 
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
        return $wpdb->get_results("SELECT * FROM {$this->installer->regiones_table}");
    }
}

new Em_Regiones_Comunas();