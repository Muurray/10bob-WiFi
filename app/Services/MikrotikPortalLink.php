<?php

namespace App\Services;

class MikrotikPortalLink
{
    public function trustedLoginUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parts = parse_url($url);
        $configuredHost = strtolower((string) config('mikrotik.portal_host'));
        $loginHost = strtolower((string) ($parts['host'] ?? ''));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);

        if (! $configuredHost
            || $loginHost !== $configuredHost
            || ! in_array($scheme, ['http', 'https'], true)
            || ! in_array($port, [80, 443], true)
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            return null;
        }

        return $url;
    }

    public function trialUrl(?string $loginUrl, ?string $mac, ?string $destination): ?string
    {
        $loginUrl = $this->trustedLoginUrl($loginUrl);

        if (! $loginUrl || ! is_string($mac) || ! preg_match('/^(?:[0-9A-Fa-f]{2}:){5}[0-9A-Fa-f]{2}$/', $mac)) {
            return null;
        }

        $parts = parse_url($loginUrl);
        parse_str($parts['query'] ?? '', $query);
        $query['username'] = 'T-'.strtoupper($mac);
        $query['dst'] = $this->safeDestination($destination);

        return $this->withoutQuery($loginUrl).'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    private function safeDestination(?string $destination): string
    {
        if ($destination) {
            $parts = parse_url($destination);
            if (in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
                && ! empty($parts['host'])
                && ! isset($parts['user'])
                && ! isset($parts['pass'])
            ) {
                return $destination;
            }
        }

        return (string) config('app.url');
    }

    private function withoutQuery(string $url): string
    {
        $parts = parse_url($url);
        $base = ($parts['scheme'] ?? 'http').'://'.$parts['host'];

        if (isset($parts['port'])) {
            $base .= ':'.$parts['port'];
        }

        return $base.($parts['path'] ?? '/');
    }
}