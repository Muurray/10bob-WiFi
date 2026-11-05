# Captive Portal Setup

## Laravel

1. Set `APP_URL` to the public HTTPS portal URL.
2. Set `MIKROTIK_HOST`, `MIKROTIK_USERNAME`, and `MIKROTIK_PASSWORD` for the RouterOS API. Restrict API access in the router firewall to the Laravel server; prefer API-SSL and set `MIKROTIK_SSL=true` and `MIKROTIK_PORT=8729` when TLS is enabled.
3. Set `MIKROTIK_PORTAL_HOST` to the hostname or IP used by `link-login-only` (without a scheme or port). The application only returns login credentials to this exact configured host.
4. Set `MIKROTIK_PROFILE` to the normal paid-user profile. Set `MIKROTIK_MULTI_DEVICE_PROFILE` to a RouterOS hotspot user profile configured with `shared-users=2` for the 1-month/two-device plan.
5. Run `php artisan migrate` and build frontend assets with `npm install` and `npm run build`.

## MikroTik Hotspot

1. Enable the hotspot on the guest Wi-Fi interface and use a hotspot user profile that permits the required package limits.
2. Replace `YOUR-10BOB-PORTAL-HOST` in `public/mikrotik/login.html` with the Laravel portal hostname. Upload the file as `login.html` to the hotspot HTML directory.
3. Add the Laravel portal hostname and Paystack checkout/payment hosts to the hotspot walled garden so customers can load the portal and complete checkout before authentication.
4. In the hotspot server profile, configure a 12-hour trial limit and a long reset period, for example `trial-uptime-limit=12h` and `trial-uptime-reset=365d`. The trial button uses MikroTik's native `T-<MAC>` login and is shown only when valid captive-portal context is available.
5. Enable RouterOS `http-pap` for the hotspot if using the automatic browser login handoff. Protect the hotspot login with HTTPS. If the hotspot requires CHAP, the router's challenge fields must also be carried through checkout and the CHAP response calculated before auto-login.
6. Restrict RouterOS API service access to the application server. Do not expose the API port to the public internet.

The portal accepts the RouterOS `ip`, `mac`, `link-login-only`, and `link-orig` parameters. After Paystack verification and account provisioning, the customer's browser posts the credentials to `link-login-only`; if router context is absent or its host does not match `MIKROTIK_PORTAL_HOST`, the portal shows the normal manual credential fallback instead.

Captive mini-browser pop-ups depend on the device operating system. Customers can still open the portal URL manually while connected to the guest Wi-Fi.