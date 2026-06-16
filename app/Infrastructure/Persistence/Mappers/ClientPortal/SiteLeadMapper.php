<?php

namespace App\Infrastructure\Persistence\Mappers\ClientPortal;

use App\Domain\ClientPortal\Entities\SiteLead;
use App\Infrastructure\Persistence\Eloquent\Models\ClientPortal\SiteLeadModel;

final class SiteLeadMapper
{
    public function toDomain(SiteLeadModel $model): SiteLead
    {
        return new SiteLead(
            id: $model->id,
            fullName: $model->full_name,
            phone: $model->phone,
            email: $model->email,
            serviceTypes: $model->service_types ?? [],
            comment: $model->comment,
            needsDelivery: $model->needs_delivery,
            deliveryAddress: $model->delivery_address,
            converted: $model->converted,
            orderId: $model->order_id,
        );
    }

    public function fillModel(SiteLead $lead, SiteLeadModel $model): void
    {
        $model->fill([
            'full_name' => $lead->fullName(),
            'phone' => $lead->phone(),
            'email' => $lead->email(),
            'service_types' => $lead->serviceTypes(),
            'comment' => $lead->comment(),
            'needs_delivery' => $lead->needsDelivery(),
            'delivery_address' => $lead->deliveryAddress(),
            'converted' => $lead->isConverted(),
            'order_id' => $lead->orderId(),
        ]);
    }
}
