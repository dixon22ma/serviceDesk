<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LegalTicket extends Model
{
    protected $table = 'legal_tickets';
	
    protected $fillable = [
        'id_client', 
        'id_company_to', 
        'unit_to_id',  

        'id_priority', 
        'subject', 
        'description', 
        'address', 
        
        'id_status',
        'id_executor',
        'confirmed_by_executor',  
        'confirmed_by_initiator', 
        'id_transaction', 
    ];
}
