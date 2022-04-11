<?php


namespace App\Traits;


use App\Models\References\ContrAgent;
use Illuminate\Support\Str;

trait HasContrAgent
{

    /**
     * @return bool
     */
    public function hasCompany()
    {
        $contr_agent = ContrAgent::where('uuid', $this->company_IN ?? null)->first();
        return (bool)$contr_agent;
    }

    /**
     * @return ContrAgent | null
     */
    public function contr_agent()
    {
        if (isset($this->company_IN) && Str::isUuid($this->company_IN)) {
            return ContrAgent::where('uuid', $this->company_IN)->first();
        }
        return null;
    }

    /**
     * @return integer| null
     */
    public function contr_agent_id()
    {
        return $this->contr_agent()?->id;
    }
}
