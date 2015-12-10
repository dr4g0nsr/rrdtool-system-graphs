<?php

/*
 * Copyright (C) 2015 cira
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class rrd_tools {

    private $config = NULL;   // config file, loaded

    public function rrd_tools() {
        $this->load_config();
    }

    /**
     * Load config file
     * 
     * @param string $config_file
     */
    public function load_config() {
        global $config;
        $this->config = $config;
    }

    public function parse_proc($path, $index = 1) {
        if (!file_exists($path)) {
            return false;
        }
        if ($index < 1) {
            return false;
        }
        $lines = explode("\n", file_get_contents($path));
        $lines_clean = "";
        foreach ($lines as $line) {
            $line = trim($line);
            for ($c = 1; $c < 20; $c++) {
                $line = str_replace("  ", " ", $line);
            }
            $line = explode(" ", $line);
            $index_counter = $index;
            while ($index_counter > 0) {
                $name = array_shift($line);
                $index_counter--;
                if (!$name) {
                    continue;
                }
            }

            $lines_clean[$name] = $line;
        }
        return $lines_clean;
    }

    /**
     * Execute rrd
     * 
     * @param type $command
     */
    public function exec_rrd($command) {
        if (!$command) {
            return false;
        }
        $r = exec("rrdtool " . $command);    // run rrdtool
    }

    public function update_rrd() {

        foreach ($this->config["monitor_disk"] as $item) {
            $info = $this->parse_proc("/proc/diskstats", 3);
            $read = (int) $info[$item][0];
            $write = (int) $info[$item][4];
            $readtime = (int) $info[$item][3];
            $writetime = (int) $info[$item][7];
            $ioprogress = (int) $info[$item][8];
            $io_time = (int) $info[$item][9];
            $command = "update " . __DIR__ . "/rrd/" . $item . ".rrd N:{$read}:{$write}:{$readtime}:{$writetime}:{$ioprogress}:{$io_time}";
            $this->exec_rrd($command);
        }

        foreach ($this->config["monitor_network"] as $item) {
            $info = $this->parse_proc("/proc/net/dev");
            $item1 = $item . ":";
            $receive_bytes = (int) $info[$item1][0];
            $receive_packets = (int) $info[$item1][1];
            $receive_drop = (int) $info[$item1][3];
            $sent_bytes = (int) $info[$item1][8];
            $sent_packets = (int) $info[$item1][9];
            $sent_drop = (int) $info[$item1][11];
            $command = "update " . __DIR__ . "/rrd/" . $item . ".rrd N:{$receive_bytes}:{$receive_packets}:{$receive_drop}:{$sent_bytes}:{$sent_packets}:{$sent_drop}";
            $this->exec_rrd($command);
        }

        foreach ($this->config["monitor_memory"] as $item) {
            $info = $this->parse_proc("/proc/meminfo");
            $memtotal = $info["MemTotal:"][0];
            $memfree = $info["MemFree:"][0];
            $memavailable = $info["MemAvailable:"][0];
            $membuffers = $info["Buffers:"][0];
            $swapcached = $info["SwapCached:"][0];
            $command = "update " . __DIR__ . "/rrd/" . $item . ".rrd N:{$memtotal}:{$memfree}:{$memavailable}:{$membuffers}:{$swapcached}";
            $this->exec_rrd($command);
        }
    }

    public function draw_graphs() {
        $html_links = "";
        foreach ($this->config["monitor_disk"] as $item) {
            $command = 'graph ' . __DIR__ . '/graphs/' . $item . '.png \
-t "Disk '.$item.'" --end now --start end-120000s --width ' . $this->config["graph_width"] . ' \
DEF:read=' . __DIR__ . '/rrd/' . $item . '.rrd:read:AVERAGE \
DEF:write=' . __DIR__ . '/rrd/' . $item . '.rrd:write:AVERAGE \
DEF:readtime=' . __DIR__ . '/rrd/' . $item . '.rrd:readtime:AVERAGE \
DEF:writetime=' . __DIR__ . '/rrd/' . $item . '.rrd:writetime:AVERAGE \
DEF:io_progress=' . __DIR__ . '/rrd/' . $item . '.rrd:io_progress:AVERAGE \
DEF:io_time=' . __DIR__ . '/rrd/' . $item . '.rrd:io_time:AVERAGE \
LINE1:read#0000FF:"Read" \
LINE1:write#00CCFF:"Write" \
LINE1:readtime#CCCCFF:"Read time" \
LINE1:writetime#FF00FF:"Write time" \
LINE1:io_progress#FF0000:"IO progress" \
LINE1:io_time#00FF00:"IO time" \
';
            $this->exec_rrd($command);
            $html_links.="<img src='{$item}.png'><br>\n";
        }
        foreach ($this->config["monitor_network"] as $item) {
            $command = 'graph ' . __DIR__ . '/graphs/' . $item . '.png \
-t "Network card '.$item.'" --end now --start end-120000s --width ' . $this->config["graph_width"] . ' \
DEF:rxbytes=' . __DIR__ . '/rrd/' . $item . '.rrd:rxbytes:AVERAGE \
DEF:rxpackets=' . __DIR__ . '/rrd/' . $item . '.rrd:rxpackets:AVERAGE \
DEF:rxdrop=' . __DIR__ . '/rrd/' . $item . '.rrd:rxdrop:AVERAGE \
DEF:txbytes=' . __DIR__ . '/rrd/' . $item . '.rrd:txbytes:AVERAGE \
DEF:txpackets=' . __DIR__ . '/rrd/' . $item . '.rrd:txpackets:AVERAGE \
DEF:txdrop=' . __DIR__ . '/rrd/' . $item . '.rrd:txdrop:AVERAGE \
LINE1:rxbytes#0000FF:"RX for net ' . $item . '" \
LINE1:rxpackets#00CCFF:"RX packets net ' . $item . '" \
LINE1:rxdrop#CCCCFF:"RX drop for net ' . $item . '\l" \
LINE1:txbytes#FF00FF:"TX for net ' . $item . '" \
LINE1:txpackets#FF0000:"TX packets for net ' . $item . '" \
LINE1:txdrop#00FF00:"TX drop for net ' . $item . '" \
';
            $this->exec_rrd($command);
            $html_links.="<img src='{$item}.png'><br>\n";
        }
        foreach ($this->config["monitor_memory"] as $item) {
            $command = 'graph ' . __DIR__ . '/graphs/' . $item . '.png \
-t "Memory '.$item.'" --end now --start end-120000s --width ' . $this->config["graph_width"] . ' \
DEF:total=' . __DIR__ . '/rrd/' . $item . '.rrd:total:AVERAGE \
DEF:free=' . __DIR__ . '/rrd/' . $item . '.rrd:free:AVERAGE \
DEF:available=' . __DIR__ . '/rrd/' . $item . '.rrd:available:AVERAGE \
DEF:buffers=' . __DIR__ . '/rrd/' . $item . '.rrd:buffers:AVERAGE \
DEF:cached=' . __DIR__ . '/rrd/' . $item . '.rrd:cached:AVERAGE \
LINE1:total#0000FF:"RX for net ' . $item . '" \
LINE1:free#00CCFF:"RX packets net ' . $item . '" \
LINE1:available#CCCCFF:"RX drop for net ' . $item . '\l" \
LINE1:buffers#FF00FF:"TX for net ' . $item . '" \
LINE1:cached#FF0000:"TX packets for net ' . $item . '" \
';
            $this->exec_rrd($command);
            $html_links.="<img src='{$item}.png'><br>\n";
        }
        $html = '<!DOCTYPE html>
<html>
    <head>
        <title>' . $this->config["title"] . '</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div>' . $this->config["server"] . '</div>
        '.$html_links.'
    </body>
</html>';
        file_put_contents("graphs/index.html", $html);
    }

    /**
     * Create rrd
     * 
     */
    public function create_rrd() {
        foreach ($this->config["monitor_disk"] as $item) {
            $update = (int) $this->config["update"];
            $update1 = $update * 2;
            $records = $update1 * 2;
            $records1 = $update1 * 4;
            $filename = __DIR__ . "/rrd/" . $item . ".rrd";
            $command = "create {$filename} --step {$update} \
DS:read:COUNTER:{$update1}:0:9999999999999 \
DS:write:COUNTER:{$update1}:0:9999999999999 \
DS:readtime:COUNTER:{$update1}:0:9999999999999 \
DS:writetime:COUNTER:{$update1}:0:9999999999999 \
DS:io_progress:COUNTER:{$update1}:0:9999999999999 \
DS:io_time:COUNTER:{$update1}:0:9999999999999 \
RRA:AVERAGE:0.5:1:{$records} \
RRA:MIN:0.5:10:{$records1} \
RRA:MAX:0.5:10:{$records1} \
RRA:AVERAGE:0.5:10:{$records1}";
            if (file_exists($filename)) {
                unlink($filename);
            }
            $this->exec_rrd($command);
        }

        foreach ($this->config["monitor_network"] as $item) {
            $update = (int) $this->config["update"];
            $update1 = $update * 2;
            $records = $update1 * 2;
            $records1 = $update1 * 4;
            $filename = __DIR__ . "/rrd/" . $item . ".rrd";
            $command = "create {$filename} --step {$update} \
DS:rxbytes:COUNTER:{$update1}:0:9999999999999 \
DS:rxpackets:COUNTER:{$update1}:0:9999999999999 \
DS:rxdrop:COUNTER:{$update1}:0:9999999999999 \
DS:txbytes:COUNTER:{$update1}:0:9999999999999 \
DS:txpackets:COUNTER:{$update1}:0:9999999999999 \
DS:txdrop:COUNTER:{$update1}:0:9999999999999 \
RRA:AVERAGE:0.5:1:{$records} \
RRA:MIN:0.5:10:{$records1} \
RRA:MAX:0.5:10:{$records1} \
RRA:AVERAGE:0.5:10:{$records1}";
            if (file_exists($filename)) {
                unlink($filename);
            }
            $this->exec_rrd($command);
        }

        foreach ($this->config["monitor_memory"] as $item) {
            $update = (int) $this->config["update"];
            $update1 = $update * 2;
            $records = $update1 * 2;
            $records1 = $update1 * 4;
            $filename = __DIR__ . "/rrd/" . $item . ".rrd";
            $command = "create {$filename} --step {$update} \
DS:total:COUNTER:{$update1}:0:9999999999999 \
DS:free:COUNTER:{$update1}:0:9999999999999 \
DS:available:COUNTER:{$update1}:0:9999999999999 \
DS:buffers:COUNTER:{$update1}:0:9999999999999 \
DS:cached:COUNTER:{$update1}:0:9999999999999 \
RRA:AVERAGE:0.5:1:{$records} \
RRA:MIN:0.5:10:{$records1} \
RRA:MAX:0.5:10:{$records1} \
RRA:AVERAGE:0.5:10:{$records1}";
            if (file_exists($filename)) {
                unlink($filename);
            }
            $this->exec_rrd($command);
        }
    }

}
