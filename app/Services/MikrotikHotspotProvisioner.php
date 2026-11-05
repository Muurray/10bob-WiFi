<?php

namespace App\Services;

use App\Models\HotspotUser;
use App\Models\Package;
use InvalidArgumentException;
use RouterOS\Client;
use RouterOS\Query;
use RuntimeException;

class MikrotikHotspotProvisioner
{
    public function provision(HotspotUser $hotspotUser, Package $package): void
    {
        $host = config('mikrotik.host');
        $username = config('mikrotik.username');
        $password = config('mikrotik.password');

        if (! $host || ! $username || ! $password) {
            throw new RuntimeException('MikroTik API connection settings are incomplete.');
        }

        $client = new Client([
            'host' => $host,
            'user' => $username,
            'pass' => $password,
            'port' => (int) config('mikrotik.port', 8728),
            'ssl' => (bool) config('mikrotik.ssl', false),
            'timeout' => 5,
            'socket_timeout' => 5,
            'attempts' => 1,
        ]);

        $attributes = [
            'name' => $hotspotUser->username,
            'password' => $hotspotUser->password,
            'limit-uptime' => $this->uptimeLimit($package),
            'comment' => '10bob payment '.$hotspotUser->payment_id,
        ];

        $profile = $package->device_limit > 1
            ? config('mikrotik.multi_device_profile')
            : config('mikrotik.profile');

        if ($package->device_limit > 1 && ! $profile) {
            throw new RuntimeException('The MikroTik multi-device profile is not configured.');
        }

        if ($profile) {
            $attributes['profile'] = $profile;
        }

        $existingUsers = $client->query(
            (new Query('/ip/hotspot/user/print'))->where('name', $hotspotUser->username)
        )->read();
        $existingId = $existingUsers[0]['.id'] ?? null;

        $query = new Query($existingId ? '/ip/hotspot/user/set' : '/ip/hotspot/user/add');
        if ($existingId) {
            $query->equal('.id', $existingId);
        }

        foreach ($attributes as $key => $value) {
            $query->equal($key, $value);
        }

        $response = $client->query($query)->read(false);
        if (in_array('!trap', $response, true) || ! in_array('!done', $response, true)) {
            throw new RuntimeException('MikroTik did not accept the hotspot user.');
        }
    }

    private function uptimeLimit(Package $package): string
    {
        $duration = (int) $package->duration;
        $unit = strtolower(rtrim((string) $package->unit, 's'));

        if ($duration < 1 || ! in_array($unit, ['hour', 'day'], true)) {
            throw new InvalidArgumentException('Unsupported hotspot package duration.');
        }

        return $duration.($unit === 'day' ? 'd' : 'h');
    }
}