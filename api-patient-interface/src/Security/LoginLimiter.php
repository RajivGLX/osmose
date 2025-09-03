<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class LoginLimiter
{
    private $cache;
    private $requestStack;
    private const MAX_ATTEMPTS = 5;
    private const RESET_TIME = 1800; // 30 minutes

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->cache = new FilesystemAdapter('login_attempts');
    }

    public function limitReached(): bool
    {
        $ip = $this->getClientIp();
        $key = 'login_' . md5($ip);

        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            return false;
        }

        $attempts = $item->get();

        return $attempts['count'] >= self::MAX_ATTEMPTS && time() - $attempts['time'] < self::RESET_TIME;
    }

    public function registerAttempt(bool $success): void
    {
        $ip = $this->getClientIp();
        $key = 'login_' . md5($ip);

        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            $attempts = [
                'count' => 0,
                'time' => time()
            ];
        } else {
            $attempts = $item->get();

            // Si la dernière tentative date de plus de RESET_TIME, réinitialiser
            if (time() - $attempts['time'] > self::RESET_TIME) {
                $attempts = [
                    'count' => 0,
                    'time' => time()
                ];
            }
        }

        // Si la connexion a échoué, incrémenter le compteur
        if (!$success) {
            $attempts['count']++;
            $attempts['time'] = time();
        } else {
            // Si la connexion a réussi, réinitialiser
            $attempts['count'] = 0;
        }

        $item->set($attempts);
        $item->expiresAfter(self::RESET_TIME);

        $this->cache->save($item);
    }

    public function getRemainingAttempts(): int
    {
        $ip = $this->getClientIp();
        $key = 'login_' . md5($ip);

        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            return self::MAX_ATTEMPTS;
        }

        $attempts = $item->get();

        // Vérifier si le temps de blocage est dépassé
        if (time() - $attempts['time'] > self::RESET_TIME) {
            return self::MAX_ATTEMPTS;
        }

        return max(0, self::MAX_ATTEMPTS - $attempts['count']);
    }

    private function getClientIp(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request->getClientIp();
    }
}