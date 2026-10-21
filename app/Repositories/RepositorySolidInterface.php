<?php
namespace App\Repositories;

interface RepositorySolidInterface
{
    public function all();

    public function store(array $data);

    public function update(array $data, string $id);

    public function destroy(string $id);

    public function show(string $id);
}
