<?php
namespace App\Api\Transformers;

use App\Traits\TransformerLibrary;
use App\Models\Auth\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    use TransformerLibrary;

    protected $availableIncludes = [
        'employee',
    ];

    public function transform(User $user)
    {
        return $this->setField([
            'id'            => (int) $user->id,
            'name'          => $user->name,
          ],[
            'email'         => $user->email,
            'created_at'    => $user->created_at ? $user->created_at : null,
            'updated_at'    => $user->updated_at ? $user->updated_at : null,
        ]);
    }

    public function includeEmployee(User $user) {
        if ($employee = $user->employee) {
            return $this->item($employee, new UserTransformer());
        }
    }
}
