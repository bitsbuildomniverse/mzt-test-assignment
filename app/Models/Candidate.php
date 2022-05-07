<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidate extends Model
{
    use HasFactory;

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'negotiations', 'candidate_id', 'company_id')->withPivot('status');
    }

    public function getCompanyOnNegotiation($companyId)
    {
        return $this->companies->first(function ($v, $k) use ($companyId) {
            return $v->id === $companyId;
        });
    }

    public function isAbleToContact($companyId)
    {
        $companyOnNegotiation = $this->getCompanyOnNegotiation($companyId);
        return !$companyOnNegotiation || $companyOnNegotiation->pivot->status === 'onlist';
    }

    public function isAbleToHire($companyId)
    {
        $companyOnNegotiation = $this->getCompanyOnNegotiation($companyId);
        return $companyOnNegotiation && $companyOnNegotiation->pivot->status === 'contacted';
    }

    public function contact()
    {
        // Update or create status
        $negotiation = Negotiation::updateOrCreate(
            ['company_id' => 1, 'candidate_id' => $this->id],
            ['status' => 'contacted']
        );
    }

    public function hire()
    {
        // Update status
        $negotiation = Negotiation::where(['company_id' => 1, 'candidate_id' => $this->id])->update([
            'status' => 'hired',
        ]);
    }

    public static function getAllWithAvailabilities()
    {
        $companyId = 1;

        $candidates = Candidate::all();
        foreach ($candidates as $candidate) {
            $candidate->ableToContact = $candidate->isAbleToContact($companyId);
            $candidate->ableToHire = $candidate->isAbleToHire($companyId);
        }
        return $candidates;
    }
}
