<?php

/**
 * aos-emu2.inc.php
 *
 * LibreNMS sensors temp discovery module for APC EMU2
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

//pre-cache
$oids = SnmpQuery::cache()->walk([
    'PowerNet-MIB::emsProbeStatusEntry',
])->table(1);

$scale = SnmpQuery::enumStrings()->get('PowerNet-MIB::emsStatusSysTempUnits.0')->value();

foreach ($oids as $id => $temp) {
    if (isset($temp['PowerNet-MIB::emsProbeStatusProbeTemperature']) && $temp['PowerNet-MIB::emsProbeStatusProbeTemperature'] > 0) {
        $index = $temp['PowerNet-MIB::emsProbeStatusProbeIndex'];
        $oid = '.1.3.6.1.4.1.318.1.1.10.3.13.1.1.3.' . $index;
        $descr = $temp['PowerNet-MIB::emsProbeStatusProbeName'];
        $low_limit = fahrenheit_to_celsius($temp['PowerNet-MIB::emsProbeStatusProbeMinTempThresh'], $scale);
        $low_warn_limit = fahrenheit_to_celsius($temp['PowerNet-MIB::emsProbeStatusProbeLowTempThresh'], $scale);
        $high_limit = fahrenheit_to_celsius($temp['PowerNet-MIB::emsProbeStatusProbeMaxTempThresh'], $scale);
        $high_warn_limit = fahrenheit_to_celsius($temp['PowerNet-MIB::emsProbeStatusProbeHighTempThresh'], $scale);
        $value = fahrenheit_to_celsius($temp['PowerNet-MIB::emsProbeStatusProbeTemperature'], $scale);

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'temperature',
            'sensor_oid' => $oid,
            'sensor_index' => $index,
            'sensor_type' => 'aos-emu2',
            'sensor_descr' => $descr,
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_limit_low' => $low_limit,
            'sensor_limit_low_warn' => $low_warn_limit,
            'sensor_limit_warn' => $high_warn_limit,
            'sensor_limit' => $high_limit,
            'sensor_current' => $value,
            'entPhysicalIndex' => null,
            'entPhysicalIndex_measured' => null,
            'user_func' => null,
            'group' => null,
        ]));
    }
}
