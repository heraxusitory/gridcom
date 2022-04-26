<?php

namespace App\Transformers\WebAPI\v1;

use App\Models\References\Nomenclature;
use League\Fractal\TransformerAbstract;

class NomenclatureTransfromer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected array $defaultIncludes = [
        //
    ];

    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected array $availableIncludes = [
        //
    ];

    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Nomenclature $nomenclature)
    {
        return [
            'mnemocode' => $nomenclature?->mnemocode,
            'price' => $nomenclature?->price,
            'name' => $nomenclature?->name,
        ];
    }
}
