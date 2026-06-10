<?php

namespace System\Post;

class Post
{
    public function hasPost(): bool
    {
        return !empty($_POST);
    }

    public function data(): object
    {
        $data = $_POST;
        unset($data['_token']);
        unset($data['tab']);
        return (object) $data;
    }

    public function only(array $fields): array
    {
        return array_intersect_key($_POST, array_flip($fields));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }
}
