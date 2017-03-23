<?php

namespace App\Seedbox;

class Server extends Users
{
    const VERSION = '3.0.0';

    public static function getUptime()
    {
        $data_uptime = @file_get_contents('/proc/uptime');
        $data_uptime = explode(' ', $data_uptime);
        $data_uptime = trim($data_uptime[0]);

        $time = [];
        $time['min'] = $data_uptime / 60;
        $time['hours'] = $time['min'] / 60;
        $time['days'] = floor($time['hours'] / 24);
        $time['hours'] = floor($time['hours'] - $time['days'] * 24);
        $time['min'] = floor($time['min'] - $time['days'] * 60 * 24 - $time['hours'] * 60);

        return [
            'days' => $time['days'],
            'hours'  => $time['hours'],
            'minutes' => $time['min']
        ];
    }

    public static function load_average()
    {
        $load_average = sys_getloadavg();
        for ($i=0; isset($load_average[$i]); $i++) {
            $load_average[$i] = round($load_average[$i], 2);
        }

        return $load_average;
    }

    public function FileDownload($file_config_name, $conf_ext_prog)
    {
        file_put_contents('../conf/users/' . $this->userName . '/' . $file_config_name, $conf_ext_prog);

        set_time_limit(0);
        $path_file_name = '../conf/users/' . $this->userName . '/' . $file_config_name;
        $file_name = $file_config_name;
        $file_size = filesize($path_file_name);

        ini_set('zlib.output_compression', 0);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $file_size);
        ob_clean();
        flush();
        readfile($path_file_name);

        //delete file config (transdroid|filezilla) for security.
        unlink('../conf/users/' . $this->userName . '/' . $file_config_name);

        exit;
    }

    public function CheckUpdate()
    {
        $lifetime_cookie = time() + 3600*24;
        if (!isset($_COOKIE['seedbox-manager']) && $this->is_admin === true) {
            setcookie('seedbox-manager', 'check-update', $lifetime_cookie, '/', null, false, true);
            $url_repository = 'https://raw.githubusercontent.com/Magicalex/seedbox-manager/master/version.json';
            $remote = json_decode(file_get_contents($url_repository));
            if (self::VERSION !== $remote->version) {
                $result = $remote;
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        return $result;
    }

    public function logout_url_redirect()
    {
        return $this->url_redirect;
    }

    public function version()
    {
        return self::VERSION;
    }
}