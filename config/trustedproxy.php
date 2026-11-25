<?php

/*
 * Daftar proxy tepercaya (dipakai middleware TrustProxies).
 *
 * bootstrap/app.php memanggil env('TRUSTED_PROXIES') saat kernel HTTP dibuat,
 * yaitu SEBELUM .env dimuat, sehingga daftarnya kosong. Bila kosong,
 * TrustProxies jatuh ke config('trustedproxy.proxies') ini, yang dibaca
 * setelah .env dimuat.
 *
 * Rantai di server prod: proxy depan 10.11.12.2 -> nginx host (real_ip) ->
 * 127.0.0.1:8000 -> docker-proxy -> container (REMOTE_ADDR = gateway 172.18.0.1).
 */
return [
    'proxies' => env('TRUSTED_PROXIES'),
];
