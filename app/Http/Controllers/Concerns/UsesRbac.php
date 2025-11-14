<?php
// app/Http/Controllers/Concerns/UsesRbac.php
namespace App\Http\Controllers\Concerns;

trait UsesRbac
{
    protected function applyCrudPermissions(string $module): void
    {
        $this->middleware("permission:{$module}.view")->only(['index','show']);
        $this->middleware("permission:{$module}.create")->only('store');
        $this->middleware("permission:{$module}.update")->only('update');
        $this->middleware("permission:{$module}.delete")->only('destroy');
    }
}
