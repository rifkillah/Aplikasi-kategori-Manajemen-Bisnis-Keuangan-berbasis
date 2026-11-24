<?php

/*
 * Reverse-proxy trust.
 *
 * Leaving this unset means no proxy headers are trusted, which is the safe
 * default for a directly exposed server. Deployments behind a load balancer
 * (Vercel, Cloudflare, Nginx) must set TRUSTED_PROXIES=* or the proxy CIDRs in
 * the environment, otherwise generated URLs and client IPs use the proxy IP.
 */
return [
    'proxies' => env('TRUSTED_PROXIES'),
];
