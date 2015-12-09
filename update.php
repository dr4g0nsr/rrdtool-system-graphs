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
    public function load_config($config_file = false) {
        if (!$config_file) {
            $config_file = __DIR__ . "/config.php";
        }
        if (!file_exists($config_file)) {
            log:logdie("No config file");
        }
        require_once($config_file);
        $this->config = $config;
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
        exec("rrdtool " . $command);    // run rrdtool
    }

    public function update_rrd() {
        foreach ($this->config["monitor_disk"] as $disk) {
            $command = "update " . __DIR__ . "/rrd/" . $disk . ".rrd N:3.44:5.11";
            $this->exec_rrd($command);
        }
    }

    public function draw_graphs() {
        foreach ($this->config["monitor_disk"] as $disk) {
            $command = 'graph ' . __DIR__ . '/graphs/' . $disk . '.png \
--end now --start end-120000s --width 400 \
DEF:read=' . __DIR__ . '/rrd/' . $disk . '.rrd:read:AVERAGE \
DEF:write=' . __DIR__ . '/rrd/' . $disk . '.rrd:write:AVERAGE:step=1800 \
LINE1:read#0000FF:"default resolution\l" \
LINE1:write#00CCFF:"resolution 1800 seconds per interval\l"';
            $this->exec_rrd($command);
        }
    }

    /**
     * Create rrd
     * 
     */
    public function create_rrd() {
        foreach ($this->config["monitor_disk"] as $disk) {
            $update = (int) $this->config["update"];
            $update1 = $update * 2;
            $records = $update1 * 2;
            $records1 = $update1 * 4;
            $command = "create " . __DIR__ . "/rrd/" . $disk . ".rrd --step {$update} \
DS:read:GAUGE:{$update1}:0:100 \
DS:write:GAUGE:{$update1}:0:100 \
RRA:AVERAGE:0.5:1:{$records} \
RRA:MIN:0.5:10:{$records1} \
RRA:MAX:0.5:10:{$records1} \
RRA:AVERAGE:0.5:10:{$records1}";
            $this->exec_rrd($command);
        }

        foreach ($this->config["monitor_network"] as $net) {
            $update = (int) $this->config["update"];
            $update1 = $update * 2;
            $records = $update1 * 2;
            $records1 = $update1 * 4;
            $command = "create " . __DIR__ . "/rrd/" . $net . ".rrd --step {$update} \
DS:read:GAUGE:{$update1}:0:100 \
DS:write:GAUGE:{$update1}:0:100 \
RRA:AVERAGE:0.5:1:{$records} \
RRA:MIN:0.5:10:{$records1} \
RRA:MAX:0.5:10:{$records1} \
RRA:AVERAGE:0.5:10:{$records1}";
            $this->exec_rrd($command);
        }
    }

}

$rrd = new rrd_tools;
//$rrd->create_rrd();
$rrd->update_rrd();
$rrd->draw_graphs();

