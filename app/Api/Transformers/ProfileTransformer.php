<?php
namespace App\Api\Transformers;

use App\Traits\TransformerLibrary;
use App\Models\Auth\User;
use League\Fractal\TransformerAbstract;

class ProfileTransformer extends TransformerAbstract
{
    use TransformerLibrary;

    protected $defaultIncludes = [];

    protected $availableIncludes = [
        // 'companies', 'company_access',
    ];

    public function transform(User $user)
    {
        return $this->setField([
            'id'            => (int) $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
        ],array_merge(
            $user->attributesToArray(),
            [
                'permissions' => $user->getAllPermissions()->map(function($e) {return $e['name'];})
            ]
        ));
    }

    // public function includeCompanies(User $user)
    // {
    //     if ($companies = $user->companies) {
    //         return $this->collection($companies, new CompanyTransformer);
    //     }
    // }

    // public function includeAccessCompanies(User $user)
    // {
    //     if ($access_companies = $user->access_companies) {
    //         return $this->collection($access_companies, new CompanyTransformer);
    //     }
    // }
}
