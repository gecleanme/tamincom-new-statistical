<?php
/**
 * Plugin Name: Tamincom Statistical Module (refactored)
 * Description: Help Tamincom CRM with a Statistical Module.
 * Version: 2.2.0
 * Author: Milad Khader
 * License: GPL2
 * Requires PHP: 7.0
 */

namespace TamincomStats;

date_default_timezone_set('Asia/Amman');

/**
 * Main plugin class
 */
class StatisticalModule {
    /** @var array $cache Stores query results to prevent duplicate queries */
    private $cache = [];

    /** @var string $date Current date in Y-m-d format */
    private $date;

    /** @var string $transient_key Key for transient cache */
    private $transient_key;

    /** @var int $cache_expiration Time in seconds before cache expires */
    private $cache_expiration = 259200; // 3 days

    /**
     * Constructor
     */
    public function __construct() {
        $this->date = date('Y-m-d');
        $this->transient_key = 'tamincom_stats_' . md5($this->date);

        add_filter('posts_where', [$this, 'modify_posts_where']);

        add_action('admin_menu', [$this, 'add_admin_menu']);

        add_action('init', [$this, 'register_cron_event']);

        add_action('tamincom_precalculate_statistics', [$this, 'precalculate_statistics']);
    }

    /**
     * Add admin menu page
     */
    public function add_admin_menu() {
        add_menu_page(
            'وحدة الإحصاء و المقاييس',
            'وحدة الإحصاء و المقاييس',
            'view_stats',
            'stats/main.php',
            [$this, 'render_admin_page'],
            'dashicons-chart-area',
            6
        );
    }

    /**
     * Render admin page with optional cache refresh
     */
    public function render_admin_page() {
        if (isset($_GET['force_refresh'])) {
            delete_transient($this->transient_key);
            
            echo '<div class="notice notice-success is-dismissible"><p>تم تحديث البيانات بنجاح.</p></div>';
        }

        echo $this->render_stats();
    }

    /**
     * Get post count by meta value with optimized query
     *
     * @param string $meta_key Meta key to search
     * @param mixed $meta_value Meta value to match
     * @param string $post_type Post type to query
     * @return int Count of matching posts
     */
    public function get_post_count_by_meta($meta_key, $meta_value, $post_type) {
        // cache key
        $cache_key = md5($meta_key . serialize($meta_value) . $post_type);

        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }

        global $wpdb;

        $meta_value_clause = '';
        $prepare_values = [$post_type, $meta_key];

        if ($meta_value) {
            if (is_array($meta_value)) {
                $placeholders = implode(',', array_fill(0, count($meta_value), '%s'));
                $meta_value_clause = "AND meta_value IN ($placeholders)";
                $prepare_values = array_merge($prepare_values, $meta_value);
            } else {
                $meta_value_clause = "AND meta_value = %s";
                $prepare_values[] = $meta_value;
            }
        }

        $query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT p.ID) 
            FROM {$wpdb->posts} p 
            JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
            WHERE p.post_type = %s 
            AND pm.meta_key = %s 
            $meta_value_clause",
            $prepare_values
        );

        $count = (int) $wpdb->get_var($query);
        $this->cache[$cache_key] = $count;

        return $count;
    }

    /**
     * Modify query for meta key searching
     */
    public function modify_posts_where($where) {
        $where = str_replace("meta_key = 'docs_list_renew_$", "meta_key LIKE 'docs_list_renew_%", $where);
        return $where;
    }

    /**
     * Get active contracts using a more reliable method
     *
     * @return array Array with contract IDs and count
     */
    private function get_active_contracts() {
        global $wpdb;

        $cache_key = 'active_contracts_new_' . md5($this->date);
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }

        $meta_check = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->postmeta} 
            WHERE meta_key = '_renew_exp_automated_copy'
        ");

        $contract_ids = [];

        if ($meta_check > 0) {
            $contract_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT p.ID 
                 FROM {$wpdb->posts} p
                 JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                 WHERE p.post_type = 'contract'
                 AND p.post_status IN ('publish', 'oldpublished', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit')
                 AND pm.meta_key = '_renew_exp_automated_copy'
                 AND pm.meta_value >= %s",
                $this->date
            ));
        }

        if (empty($contract_ids) && function_exists('get_field')) {
            $all_contracts = $wpdb->get_col("
                SELECT ID FROM {$wpdb->posts} 
                WHERE post_type = 'contract' 
                AND post_status IN ('publish', 'oldpublished', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit')
            ");

            foreach ($all_contracts as $contract_id) {
                $expiry_date = get_field('_renew_exp_automated_copy', $contract_id);

                if ($expiry_date && $expiry_date >= $this->date) {
                    $contract_ids[] = $contract_id;
                }
            }
        }

        $result = [
            'ids' => $contract_ids,
            'count' => count($contract_ids)
        ];

        $this->cache[$cache_key] = $result;
        return $result;
    }

    /**
     * Process active contract data with optimized loops
     *
     * @param array $contract_ids Array of contract post IDs
     * @return array Processed data
     */
    private function process_active_contracts($contract_ids) {
        $data = [
            'sum' => 0,
            'sum_payment' => 0,
            'sum_claimed' => 0,
            'unclaimed_grand' => 0,
            'active_client_ids' => [],
            'cycles_total' => 0,
            'new_count' => 0,
            'renew_count' => 0,
            'providers' => [
                'me' => ['count' => 0, 'sum' => 0, 'pending' => 0],
                'ae' => ['count' => 0, 'sum' => 0, 'pending' => 0],
                'qds' => ['count' => 0, 'sum' => 0, 'pending' => 0],
                'arab' => ['count' => 0, 'sum' => 0, 'pending' => 0],
                'arjo' => ['count' => 0, 'sum' => 0, 'pending' => 0],
                'sld' => ['count' => 0, 'sum' => 0, 'pending' => 0],
                'ag' => ['count' => 0, 'sum' => 0, 'pending' => 0],
                'mnr' => ['count' => 0, 'sum' => 0, 'pending' => 0],
                'other' => ['count' => 0, 'sum' => 0, 'pending' => 0],
            ]
        ];

        if (!function_exists('get_field')) {
            return $data;
        }

        $this->preload_post_meta($contract_ids);

        foreach ($contract_ids as $post_id) {
            $repeater_name = "docs_list_renew";
            $repeater = get_field($repeater_name, $post_id);

            if (!$repeater || empty($repeater)) {
                continue;
            }

            $last_row = end($repeater);

            // active client count
            $object = get_field('contract_client', $post_id);
            if ($object && isset($object->ID)) {
                $data['active_client_ids'][] = $object->ID;
            }

            $vendor = $last_row['new_provider'] ?? '';
            $claimed_status = $last_row['claimed'] ?? '';
            $pending_payment = isset($last_row['remaining']) ? (int)$last_row['remaining'] : 0;
            $renewal_value = isset($last_row['renewal_value']) ? (int)$last_row['renewal_value'] : 0;
            $renew_installment = isset($last_row['renew_installment']) ? (int)$last_row['renew_installment'] : 0;

            $ins_type = get_field('type', $post_id);
            if ($ins_type == 'صحي' || $ins_type == 'سفر') {
                $renewal_value = 0;
            }

            $provider_key = $this->get_provider_key($vendor);

            if ($provider_key) {
                $data['providers'][$provider_key]['count']++;
                $data['providers'][$provider_key]['sum'] += $renew_installment;

                if ($claimed_status == 'لا' && $pending_payment != '') {
                    $pending_payment = 0;
                }

                if ($claimed_status != 'نعم' && $pending_payment != '') {
                    $data['providers'][$provider_key]['pending'] += $pending_payment;
                } elseif ($claimed_status == 'لا') {
                    $data['providers'][$provider_key]['pending'] += $renew_installment;
                }
            }

            $data['sum'] += $renewal_value;
            $data['sum_payment'] += $renew_installment;

            $cycles = count($repeater);
            $data['cycles_total'] += $cycles;

            // see if new or renewal
            if ($cycles == 1) {
                $data['new_count']++;
            } else {
                $data['renew_count']++;
            }
        }

        $data['unclaimed_grand'] = array_sum(array_column($data['providers'], 'pending'));
        $data['sum_claimed'] = $data['sum_payment'] - $data['unclaimed_grand'];

        return $data;
    }

    /**
     * Preload post meta for a batch of posts to reduce individual queries
     *
     * @param array $post_ids Array of post IDs
     */
    private function preload_post_meta($post_ids) {
        if (empty($post_ids)) {
            return;
        }

        if (function_exists('acf_get_meta')) {
            acf_get_meta($post_ids);
        } else {
            // Fallback
            update_meta_cache('post', $post_ids);
        }
    }

    /**
     * Map provider name to key using static lookup table
     *
     * @param string $vendor Provider name
     * @return string Provider key
     */
    private function get_provider_key($vendor) {
        static $provider_map = [
            "الشرق الأوسط" => 'me',
            "العربية الاوروبية" => 'ae',
            "القدس" => 'qds',
            "العرب للتأمين" => 'arab',
            "الأولى للتأمين" => 'sld',
            "العربية الأردنية" => 'arjo',
            "المتوسط والخليج للتأمين" => 'ag',
            "المنارة الإسلامية" => 'mnr'
        ];

        return $provider_map[$vendor] ?? 'other';
    }

    /**
     * Process client demographics
     *
     * @param array $client_ids Array of client post IDs
     * @return array Demographics data
     */
    private function process_client_demographics($client_ids) {
        $data = [
            'bus' => 0,
            'ind' => 0,
            'active_male' => 0,
            'active_female' => 0,
        ];

        if (empty($client_ids) || !function_exists('get_field')) {
            return $data;
        }

        // Preload client meta data
        $this->preload_post_meta($client_ids);

        foreach ($client_ids as $clientID) {
            $active_client_gender = get_field("gender", $clientID);
            if (!empty($active_client_gender)) {
                if ($active_client_gender == 'ذكر') {
                    $data['active_male']++;
                } else {
                    $data['active_female']++;
                }
            }

            $active_client_type = get_field("customer_type", $clientID);
            if (!empty($active_client_type)) {
                if ($active_client_type == 'فرد') {
                    $data['ind']++;
                } else {
                    $data['bus']++;
                }
            }
        }

        return $data;
    }

    /**
     * Get auto contract statistics
     *
     * @return array Auto contract statistics
     */
    private function get_auto_contract_stats() {
        global $wpdb;

        $cache_key = 'auto_contract_stats_' . md5($this->date);
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }

        $query = $wpdb->prepare(
            "SELECT p.ID 
            FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id
            JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id
            WHERE p.post_type = 'contract'
            AND p.post_status IN ('publish', 'oldpublished', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit')
            AND pm1.meta_key = '_renew_exp_automated_copy'
            AND pm1.meta_value >= %s
            AND pm2.meta_key = 'type'
            AND pm2.meta_value = 'auto'",
            $this->date
        );

        $post_ids = $wpdb->get_col($query);
        $total_auto_over = count($post_ids);

        $sum_type_payment = 0;
        $sum_type_value = 0;

        if ($total_auto_over > 0 && function_exists('get_field')) {
            $this->preload_post_meta($post_ids);

            foreach ($post_ids as $post_id) {
                $repeater_name = "docs_list_renew";
                $repeater = get_field($repeater_name, $post_id);

                if (!$repeater || empty($repeater)) {
                    continue;
                }

                $last_row = end($repeater);

                $sum_type_payment += isset($last_row['renew_installment']) ? (int)$last_row['renew_installment'] : 0;
                $sum_type_value += isset($last_row['renewal_value']) ? (int)$last_row['renewal_value'] : 0;
            }
        }

        $result = [
            'total' => $total_auto_over,
            'value_avg' => $total_auto_over > 0 ? round($sum_type_value / $total_auto_over) : 0,
            'payment_avg' => $total_auto_over > 0 ? round($sum_type_payment / $total_auto_over) : 0,
        ];

        $this->cache[$cache_key] = $result;
        return $result;
    }

    /**
     * Get inactive contracts cycles
     *
     * @return int Total number of cycles for inactive contracts
     */
    private function get_inactive_contracts_cycles() {
        global $wpdb;

        $cache_key = 'inactive_cycles_' . md5($this->date);
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }

        $query = $wpdb->prepare(
            "SELECT p.ID 
            FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'contract'
            AND p.post_status IN ('publish', 'oldpublished', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit')
            AND pm.meta_key = '_renew_exp_automated_copy'
            AND pm.meta_value < %s",
            $this->date
        );

        $post_ids = $wpdb->get_col($query);
        $inact_cycle_total = 0;

        if (!empty($post_ids) && function_exists('get_field')) {
            $this->preload_post_meta($post_ids);

            foreach ($post_ids as $post_id) {
                $repeater_name_inact = "docs_list_renew";
                $repeater_inact = get_field($repeater_name_inact, $post_id);

                if (!$repeater_inact) {
                    continue;
                }

                $inact_cycle_total += count($repeater_inact);
            }
        }

        $this->cache[$cache_key] = $inact_cycle_total;
        return $inact_cycle_total;
    }

    /**
     * Format number with commas
     *
     * @param int $number Number to format
     * @return string Formatted number
     */
    private function format_number($number) {
        return number_format($number, 0, ".", ",");
    }

    /**
     * Calculate percentage with null safety and explicit type casting
     *
     * @param int $value Value to calculate percentage of
     * @param int $total Total value
     * @return float Percentage value
     */
    private function calculate_percentage($value, $total) {
        $value = (int)$value;
        $total = (int)$total;

        if ($total <= 0) {
            return 0;
        }

        // Calculate percentage
        return round(($value / $total) * 100, 2);
    }

    /**
     * Main function to render statistics with transient caching
     *
     * @return string HTML output
     */
    public function render_stats() {
        if (isset($_GET['force_refresh'])) {
            delete_transient($this->transient_key);
        }

        // cached data check
        $stats_data = get_transient($this->transient_key);

        if ($stats_data === false) {
            $count_client = wp_count_posts('client');
            $total_published_client = $count_client->publish ?? 0;
            $total_draft_client = $count_client->draft ?? 0;
            $rejected_total = $count_client->rejected ?? 0;
            $grand_total_client = $total_published_client + $total_draft_client + $rejected_total;

            $count_contract = wp_count_posts('contract');
            $total_published_contract = $count_contract->publish ?? 0;
            $total_oldpublished_contract = $count_contract->oldpublished ?? 0;
            $total_potential_contract = $count_contract->potential ?? 0;
            $total_draft_contract = $count_contract->draft ?? 0;
            $grand_total_contract = $total_published_contract + $total_draft_contract + $total_oldpublished_contract;

            // client meta stats
            $hi_risk_post_count = $this->get_post_count_by_meta('risk_meter', 'hi', 'client');
            $client_ind_count = $this->get_post_count_by_meta('customer_type', 'ind', 'client');
            $client_bus_count = $this->get_post_count_by_meta('customer_type', 'bus', 'client');
            $client_male_count = $this->get_post_count_by_meta('gender', 'ذكر', 'client');
            $client_female_count = $this->get_post_count_by_meta('gender', 'أنثى', 'client');

            // percentages
            $ind_pct = $this->calculate_percentage($client_ind_count, $grand_total_client);
            $bus_pct = $this->calculate_percentage($client_bus_count, $grand_total_client);
            $male_pct = $this->calculate_percentage($client_male_count, $client_ind_count);
            $female_pct = $this->calculate_percentage($client_female_count, $client_ind_count);

            $active_contracts = $this->get_active_contracts();
            $total_active_contracts = $active_contracts['count'];

            $inactive_contracts = $grand_total_contract > $total_active_contracts ?
                $grand_total_contract - $total_active_contracts : 0;

            $total_active_contracts = (int)$total_active_contracts;

            $active_pct = $this->calculate_percentage($total_active_contracts, $grand_total_contract);

            $contract_data = $this->process_active_contracts($active_contracts['ids']);

            $clients_with_active_contracts = array_unique($contract_data['active_client_ids']);
            $active_client_count = count($clients_with_active_contracts);

            $demographics = $this->process_client_demographics($clients_with_active_contracts);

            $active_pct_client = $this->calculate_percentage($active_client_count, $grand_total_client);
            $active_pct_client_ind = $this->calculate_percentage($demographics['ind'], $active_client_count);
            $active_pct_client_bus = $this->calculate_percentage($demographics['bus'], $active_client_count);
            $active_pct_client_male = $this->calculate_percentage($demographics['active_male'], $demographics['ind']);
            $active_pct_client_female = $this->calculate_percentage($demographics['active_female'], $demographics['ind']);

            $provider_percentages = array_map(function ($provider) use ($total_active_contracts) {
                return $this->calculate_percentage((int) $provider['count'], $total_active_contracts);
            }, $contract_data['providers']);

            $sum_print = $this->format_number($contract_data['sum']);
            $payment_print = $this->format_number($contract_data['sum_payment']);
            $claimed_print = $this->format_number($contract_data['sum_claimed']);
            $claimed_pct = $this->calculate_percentage($contract_data['sum_claimed'], $contract_data['sum_payment']);

            $new_pct = $this->calculate_percentage((int)$contract_data['new_count'], $total_active_contracts);
            $renew_pct = $this->calculate_percentage((int)$contract_data['renew_count'], $total_active_contracts);

            // Get auto contract statistics
            $auto_stats = $this->get_auto_contract_stats();
            $value_avg_print = $this->format_number($auto_stats['value_avg']);
            $payment_avg_print = $this->format_number($auto_stats['payment_avg']);

            // Get inactive contracts cycles
            $inact_cycle_total = $this->get_inactive_contracts_cycles();

            $stats_data = compact(
                'grand_total_client', 'grand_total_contract', 'hi_risk_post_count',
                'client_ind_count', 'client_bus_count', 'client_male_count', 'client_female_count',
                'ind_pct', 'bus_pct', 'male_pct', 'female_pct',
                'total_active_contracts', 'inactive_contracts', 'active_pct',
                'contract_data', 'active_client_count',
                'demographics', 'active_pct_client', 'active_pct_client_ind', 'active_pct_client_bus',
                'active_pct_client_male', 'active_pct_client_female',
                'provider_percentages', 'sum_print', 'payment_print', 'claimed_print', 'claimed_pct',
                'new_pct', 'renew_pct', 'auto_stats', 'value_avg_print', 'payment_avg_print',
                'inact_cycle_total'
            );

            // Cache the data
            set_transient($this->transient_key, $stats_data, $this->cache_expiration);
        }
        ob_start();
        $this->render_template('statistics-template.php', $stats_data);
        return ob_get_clean();
    }

    /**
     * Render a template with provided data
     *
     * @param string $template Template file name
     * @param array $data Data to pass to the template
     */
    private function render_template($template, $data) {
        foreach ($data as $key => $value) {
            $$key = $value;
        }
        
        include dirname(__FILE__) . '/templates/' . $template;
    }

    /**
     * Handle cron job to precalculate statistics with resource optimization
     */
    public function precalculate_statistics() {
        try {
            @ini_set('memory_limit', '3000M');
            @set_time_limit(300); // 5m

            $start_time = microtime(true);

            delete_transient($this->transient_key);

            $tomorrow = date('Y-m-d', strtotime('+1 day'));
            $tomorrow_key = 'tamincom_stats_' . md5($tomorrow);

            // Calculate statistics for today
            $this->render_stats();

            $original_date = $this->date;

            // Calculate statistics for tomorrow
            $this->date = $tomorrow;
            $this->transient_key = $tomorrow_key;
            $this->render_stats();

            $this->date = $original_date;
            $this->transient_key = 'tamincom_stats_' . md5($this->date);

            $execution_time = microtime(true) - $start_time;

            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log(sprintf(
                    'Tamincom statistics precalculation completed at %s (execution time: %.2f seconds)',
                    date('Y-m-d H:i:s'),
                    $execution_time
                ));
            }

            update_option('tamincom_stats_last_cron', [
                'timestamp' => time(),
                'execution_time' => $execution_time,
                'success' => true
            ]);
        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Tamincom statistics precalculation failed: ' . $e->getMessage());
            }

            update_option('tamincom_stats_last_cron', [
                'timestamp' => time(),
                'error' => $e->getMessage(),
                'success' => false
            ]);
        }
    }

    /**
     * Register cron event for statistics precalculation
     */
    public function register_cron_event() {
        if (!wp_next_scheduled('tamincom_precalculate_statistics')) {
            // 1:00 AM todo: work on using true cron
            wp_schedule_event(strtotime('tomorrow 1:00am'), 'daily', 'tamincom_precalculate_statistics');
        }
    }

    /**
     * Unregister cron event on plugin deactivation
     */
    public static function unregister_cron_event() {
        $timestamp = wp_next_scheduled('tamincom_precalculate_statistics');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'tamincom_precalculate_statistics');
        }
    }
}

add_action('plugins_loaded', function() {
    static $instance = null;

    if (null === $instance) {
        $instance = new StatisticalModule();
    }
});

/**
 * Render view
 */
function stats_do() {
    static $module = null;

    if (null === $module) {
        $module = new TamincomStats\StatisticalModule();
    }

    echo $module->render_stats();
}

register_deactivation_hook(__FILE__, ['TamincomStats\StatisticalModule', 'unregister_cron_event']);