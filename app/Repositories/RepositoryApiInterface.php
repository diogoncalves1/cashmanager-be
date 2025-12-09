<?php
namespace App\Repositories;

use Illuminate\Http\Request;

interface RepositoryApiInterface
{
    public function all();

    public function store(Request $request);

    public function update(Request $request, string $id);

    public function destroy(Request $request, string $id);

    public function show(string $id);
}
