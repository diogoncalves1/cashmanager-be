<?php

namespace App\Repositories;

use Illuminate\Http\Request;

interface RepositoryInterface
{
    public function all();

    public function store(Request $request);

    public function update(Request $request, string $id);

    public function destroy(string $id);

    public function show(string $id);
}
