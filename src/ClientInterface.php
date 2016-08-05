<?php

namespace mpyw\Coutte;

interface Client
{
    public function __construct(array $options = []);
    public function with(array $options = []);
    public function get($url, array $params = [], array $options = []);
    public function post($url, array $params = [], array $options = []);
    public function getAsync($url, array $params = [], array $options = []);
    public function postAsync($url, array $params = [], array $options = []);
    public function refresh(array $options = []);
}
