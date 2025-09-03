<?php
namespace App\Services;

class Jwt
{

    public function generate(array $payload, string $secret, array $header = null, int $validity = 10800): string
    {
        if ($validity > 0) {
            $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
            $exp = $now->getTimestamp() + $validity;

            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }
        if ($header === null) {
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];
        }

        $base64Header = $this->cleanForUrl(base64_encode(json_encode($header)));
        $base64HeaderPayload = $this->cleanForUrl(base64_encode(json_encode($payload)));

        $secret = base64_encode($secret);
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64HeaderPayload, $secret, true);

        $base64Signature = $this->cleanForUrl(base64_encode($signature));

        $jwt = $base64Header . '.' . $base64HeaderPayload . '.' . $base64Signature;

        return $jwt;
    }

    public function cleanForUrl(string $string): string
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], $string);
    }

    public function isValid(string $token):bool
    {
        return preg_match(
            '/^[a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+$/',
            $token
        ) === 1;
    }

    public function getPayload(string $token): array
    {
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode($parts[1]), true);
        return $payload;
    }

    public function getHeader(string $token): array
    {
        $parts = explode('.', $token);
        $header = json_decode(base64_decode($parts[0]), true);
        return $header;
    }

    public function isExpired($token):bool
    {
        $payload = $this->getPayload($token);
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));

        return  $payload['exp'] > $now->getTimestamp();
    }

    public function check($token, string $secret):bool
    {
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        $verifyToken = $this->generate($payload, $secret, $header, 0);

        return $token === $verifyToken;
    }

}