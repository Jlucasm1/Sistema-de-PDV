<?php

namespace System\Session;

/**
 * PDV MASTER - Gerenciador de Sessão
 */
class Session
{
    private const TAB_PARAM = 'tab';
    private const TAB_HEADER = 'HTTP_X_TAB_ID';
    private const TAB_BUCKET = '_tabs';
    private const DEFAULT_TAB = '_default';

    private static string $currentTabId = self::DEFAULT_TAB;

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('pdv_master_session');
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
            ]);
        }

        self::$currentTabId = self::detectCurrentTabId();
        self::ensureTabBucketExists();
        self::migrateDefaultTabIfNeeded();
        self::restoreLoggedTabIfNeeded();
    }

    public static function set(string $key, mixed $value): void
    {
        $bucket = &self::currentBucket();
        $bucket[$key] = $value;
    }

    public static function get(string $key): mixed
    {
        $bucket = &self::currentBucket();
        return $bucket[$key] ?? null;
    }

    public static function has(string $key): bool
    {
        $bucket = &self::currentBucket();
        return isset($bucket[$key]);
    }

    public static function delete(string $key): void
    {
        $bucket = &self::currentBucket();
        unset($bucket[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $bucket = &self::currentBucket();
        $bucket['_flash'][$key] = $value;
    }

    public static function getFlash(string $key): mixed
    {
        $bucket = &self::currentBucket();
        $value = $bucket['_flash'][$key] ?? null;
        unset($bucket['_flash'][$key]);
        return $value;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::TAB_BUCKET][self::$currentTabId]);

        if (!empty($_SESSION[self::TAB_BUCKET])) {
            return;
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
            );
        }
        session_destroy();
    }

    public static function isLogged(): bool
    {
        $bucket = &self::currentBucket();
        return isset($bucket['logged']) && $bucket['logged'] === true;
    }

    public static function currentTabId(): string
    {
        return self::$currentTabId;
    }

    public static function appendTabToUrl(string $url): string
    {
        if (self::$currentTabId === self::DEFAULT_TAB || self::$currentTabId === '') {
            return $url;
        }

        if (preg_match('/([?&])' . self::TAB_PARAM . '=/', $url)) {
            return $url;
        }

        $separator = str_contains($url, '?') ? '&' : '?';
        return $url . $separator . self::TAB_PARAM . '=' . rawurlencode(self::$currentTabId);
    }

    private static function detectCurrentTabId(): string
    {
        $tabId = $_GET[self::TAB_PARAM]
            ?? $_POST[self::TAB_PARAM]
            ?? $_SERVER[self::TAB_HEADER]
            ?? self::DEFAULT_TAB;

        $tabId = is_string($tabId) ? trim($tabId) : self::DEFAULT_TAB;

        if ($tabId === '' || !preg_match('/^[a-zA-Z0-9_-]{1,80}$/', $tabId)) {
            return self::DEFAULT_TAB;
        }

        return $tabId;
    }

    private static function ensureTabBucketExists(): void
    {
        if (!isset($_SESSION[self::TAB_BUCKET]) || !is_array($_SESSION[self::TAB_BUCKET])) {
            $_SESSION[self::TAB_BUCKET] = [];
        }

        if (!isset($_SESSION[self::TAB_BUCKET][self::$currentTabId]) || !is_array($_SESSION[self::TAB_BUCKET][self::$currentTabId])) {
            $_SESSION[self::TAB_BUCKET][self::$currentTabId] = [];
        }
    }

    private static function migrateDefaultTabIfNeeded(): void
    {
        if (self::$currentTabId === self::DEFAULT_TAB) {
            return;
        }

        $currentBucket = &self::currentBucket();
        if (!empty($currentBucket)) {
            return;
        }

        $defaultBucket = $_SESSION[self::TAB_BUCKET][self::DEFAULT_TAB] ?? [];
        if (!is_array($defaultBucket) || empty($defaultBucket['logged'])) {
            return;
        }

        $_SESSION[self::TAB_BUCKET][self::$currentTabId] = $defaultBucket;
    }

    private static function restoreLoggedTabIfNeeded(): void
    {
        $currentBucket = &self::currentBucket();
        if (!empty($currentBucket['logged'])) {
            return;
        }

        foreach ($_SESSION[self::TAB_BUCKET] as $tabId => $bucket) {
            if ($tabId === self::$currentTabId || !is_array($bucket) || empty($bucket['logged'])) {
                continue;
            }

            $_SESSION[self::TAB_BUCKET][self::$currentTabId] = $bucket;
            return;
        }
    }

    private static function &currentBucket(): array
    {
        self::ensureTabBucketExists();
        return $_SESSION[self::TAB_BUCKET][self::$currentTabId];
    }
}
