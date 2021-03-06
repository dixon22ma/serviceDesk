<?php

namespace App\Http\Controllers\EmployeeManagement;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Employee;
use App\Unit;
use App\Ticket;
use App\IndividualTicket;
use App\LegalTicket;
use App\Company;

use DB;
use Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;


class CRUDController extends Controller
{
	#
	# Shows management form
	#
	public function showManagementForm ()
	{
		return view('employee.manage');
	}

	#
	# Create new employee
	#
	protected function add_new_employee(Request $request)
    {    	
        Employee::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
            'phone_number' => $request['phone_number'],
            
            #'priv_add_employee' => $request['priv_add_employee'],
            #'priv_edit_employee' => $request['priv_edit_employee'], 
            #'priv_delete_employee' => $request['priv_delete_employee'], 
            'head_unit_id' => '',
            'id_unit' => $request['id_unit'],
            'head_unit_id' => $request['head_unit_id'],      
            'room' => $request['room'],               
            'id_company' => $request['id_company'], 
            'id_role' => $request['id_role'], 
        ]);

        return(redirect('/employee/management'));
        #return Employee::create($request->all());
    }

    #
    # Delete employee
    #
    public function deleteEmployee(Request $request)
    {
        $item = Employee::find($request->id);
        $item->delete();
        return $request->all();
    }

    #
    # Show Edit employee page
    #
    public function showEditEmployeeForm($id)
    {
        $employee = DB::table('employees')
                    ->where('id', $id)
                    ->get();

        foreach ($employee as $key) {
            $isChecked = $key->id_role;
        }

        $isChecked == 1 ? $isChecked = "checked" : '';

        $units = DB::table('units')
                    ->where('id_company', Auth::user()->id_company)
                    ->get();

        return view('employee.edit_employee')
                ->with('employee', $employee)
                ->with('units', $units)
                ->with('isChecked', $isChecked);
    }

    #
    # Save employee edit changes
    #
    protected function saveEditEmployeeChanges(Request $request)
    {
        $id = $request['id'];

        $request['id_role'] == 1 ? $id_role = 1 : $id_role = 2;

        DB::table('employees')
            ->where('id', $id)

            ->update([
                'name' => $request['name'],  
                'email' => $request['email'],
                'phone_number' => $request['phone_number'],
                'id_unit' => $request['id_unit'],
                'head_unit_id' => $request['head_unit_id'],
                'room' => $request['room'],
                'id_role' => $id_role,
            ]);            
        
        return(redirect('/employee/management')); 
       
    }

	#
    # Add new unit
    #
    protected function add_new_unit(Request $request)
    {
    	if (Auth::user()->id_role <> 0)
    	{
    		return(redirect('/employee/home'));
    	}


        Unit::create([
            'name' => $request['name'],               
            'id_company' => $request['id_company'], 
        ]);

        return(redirect('/employee/company_units'));   
    }

    #
    # Show About Company page and its info
    #
    protected function showAboutCompanyForm()
    {
    	$about_info = DB::table('about_company')
					->where('id_company', Auth::user()->id_company)
					->get();

		$tmpCompanyName = NULL;
		$tmpCity = NULL;
		$tmpAddress = NULL;
		$tmpEmail = NULL;
		$tmpTel = NULL;
		$tmpDescription = NULL;
		$isChecked = NULL;

		foreach($about_info as $info) {
			$tmpCompanyName = $info->name;
			$tmpCity = $info->city;
			$tmpAddress = $info->address;
			$tmpEmail = $info->email;
			$tmpTel = $info->tel;
			$tmpDescription = $info->description;
			$info->external_tickets == 1 ? $isChecked = "checked" : $isChecked = NULL;
		}



    	return view('employee.company.about_company')
    				->with('name', $tmpCompanyName)
    				->with('city', $tmpCity)
    				->with('address', $tmpAddress)
    				->with('email', $tmpEmail)
    				->with('tel', $tmpTel)
    				->with('description', $tmpDescription)
    				->with('isChecked', $isChecked);
    }

    #
    # Adds company discription to DB
	#
    protected function fillCompanyInfo(Request $request)
    {
    	$isExist = NULL;
    	$id_company = Auth::user()->id_company;
    	
    	# find the target record
    	$about_info = DB::table('about_company')
					->where('id_company', $id_company)
					->select('id_company')
					->get();
		# save the id_company value in a variable
		foreach ($about_info as $key) {
			$isExist = $key->id_company;
		}

		if ($isExist == $id_company) {			
			# if the record with the target id_company exist, run update func
    		self::UpdateCompanyInfo($request, $id_company);
    	} else {
    		# otherwise run creation script
    		$request['external_tickets'] == 1 ? $isChecked = 1 : $isChecked = 0;

    		Company::create([
	            'name' => $request['name'],   
	            'city' => $request['city'],            
	            'address' => $request['address'], 
	            'email' => $request['email'],
	            'tel' => $request['tel'],
	            'description' => $request['description'],
	            'external_tickets' => $isChecked,
	            'id_company' => $request['id_company'],
        	]);
    	}

        return(redirect('/employee/about_company')); 
    }

    /////////////////////////////////////////
    #		Ticket functions
    /////////////////////////////////////////////
    
    protected function showCreateTicketForm()
    {
    	$units = DB::table('units')->where('id_company', Auth::user()->id_company)->get();
	
		$employees = DB::table('employees')
					->where('id_company', Auth::user()->id_company)
					->where('id_unit', Auth::user()->id_unit)
					->get();

		$priorities = DB::table('priorities')->get();

		return view('employee.tickets.create_ticket')
				->with('units', $units)
				->with('employees', $employees)
				->with('priorities', $priorities);
    }


    #
    # This func create ticket
   	#
    protected function create_ticket(Request $request)
    {
    	Ticket::create([
    		'employee_init_id' => $request['employee_init_id'],
    		'unit_to_id' => $request['unit_to_id'],
    		'id_executor' => NULL,
    		'id_priority' => $request['id_priority'],
    		'subject' => $request['subject'],
    		'description' => $request['description'],
    		'id_status' => 1,
    		'id_company' => $request['id_company'],
    	]);

    	return(redirect('/employee/outgoing_tickets'));
    }

    #
    # this function gets all incoming tickets related to user
    #
    protected function getAllIncomingTickets()
    { 	 
        # BETA AJAX TEST

        if (Auth::user()->head_unit_id != NULL) {
            $tickets = DB::table('employee_tickets')        
                ->where('id_company', Auth::user()->id_company)
                ->where('id_status', '<>', 2)
                ->where('id_status', '<>', 7)
                ->where('unit_to_id', Auth::user()->head_unit_id)  
                ->orderBy('id_priority', 'desc')      
                ->get(); 


        # This query selects all employees of single unit
        $employees = DB::table('employees')
            ->where('id_company', Auth::user()->id_company)
            ->where('id_unit', Auth::user()->head_unit_id)
            ->get();

        } else { 
            # this query selects all tickets related to executor
            $tickets = DB::table('employee_tickets')
                ->orderBy('id_priority', 'desc')
                ->where('id_company', Auth::user()->id_company)
                ->where('id_executor', Auth::user()->id)
                ->get();
            $employees = NULL;
        }

        # This algorithm select the name of current executor by id
        $current_executor = [];
        $current_employee_init_name = "";
        $i=0;

        foreach($tickets as $ticket) {
            
            $tmpExecutorName = self::getExecutorName($ticket->id_executor);
            $tmpEmployeeInitName = self::getEmployeeInitName($ticket->employee_init_id);
            $tmpCurrentStatusName = self::getStatusName($ticket->id_status);
            
            $tickets[$i]->current_employee_init_name = $tmpEmployeeInitName;
            $tickets[$i]->current_executor_name = $tmpExecutorName;
            $tickets[$i]->current_status_name = $tmpCurrentStatusName;
            $i++;
            
        } 
    
        return view('employee.tickets.view_all_incoming_inner_tickets', compact('tickets'),  compact('employees'), compact('current_executor'));
    
    }

    #
    # Shows the incoming external tickets
    #
    protected function showAllIncomingExternalTickets()
    {
    	# This query selects all tickets related to head unit
		if (Auth::user()->head_unit_id != NULL) {
			#This code selects tickets from indi clints
			$tickets = DB::table('individual_tickets')
                ->orderBy('id_priority', 'desc')
				->where('id_company_to', Auth::user()->id_company)
				->where('id_status', '<>', 2)
				->where('id_status', '<>', 7)
				->where('unit_to_id', Auth::user()->head_unit_id)	
                
				->get(); 

			# This query selects all employees of single unit
			$employees = DB::table('employees')
				->where('id_company', Auth::user()->id_company)
				->where('id_unit', Auth::user()->head_unit_id)
				->get();

		} else { 
			# this query selects all tickets related to executor
			$tickets = DB::table('individual_tickets')
                ->orderBy('id_priority', 'desc')
				->where('id_company_to', Auth::user()->id_company)
				->where('id_executor', Auth::user()->id)

				->get();
			$employees = NULL;
		}

		# This algorithm select the name of current executor by id
		$current_executor = [];
		$current_employee_init_name = "";
		$i=0;

		foreach($tickets as $ticket) {
			
			$tmpExecutorName = self::getExecutorName($ticket->id_executor);
			$tmpEmployeeInitName = self::getClientInitName($ticket->id_client);
			$tmpCurrentStatusName = self::getStatusName($ticket->id_status);
			
			$tickets[$i]->current_employee_init_name = $tmpEmployeeInitName;
			$tickets[$i]->current_executor_name = $tmpExecutorName;
			$tickets[$i]->current_status_name = $tmpCurrentStatusName;
			$tickets[$i]->prio = $ticket->id_priority;
			$i++;
			
		} 

    	return view('employee.tickets.view_all_incoming_external_tickets', compact('tickets'), compact('employees'), compact('current_executor'));
    			//->with('tickets', $tickets)
				//->with('employees', $employees)
				//->with('current_executor', $current_executor);;
    } 

    #
    # Shows the incoming external tickets
    #
    protected function showAllIncomingExternalLegalTickets()
    {
        # This query selects all tickets related to head unit
        if (Auth::user()->head_unit_id != NULL) {
            #This code selects tickets from indi clints
            $tickets = DB::table('legal_tickets')      
                ->where('id_company_to', Auth::user()->id_company)
                ->where('id_status', '<>', 2)
                ->where('id_status', '<>', 7)
                ->where('unit_to_id', Auth::user()->head_unit_id) 
                ->orderBy('id_priority', 'desc')      
                ->get(); 

            # This query selects all employees of single unit
            $employees = DB::table('employees')
                ->where('id_company', Auth::user()->id_company)
                ->where('id_unit', Auth::user()->head_unit_id)
                ->get();

        } else { 
            # this query selects all tickets related to executor
            $tickets = DB::table('legal_tickets')
                ->where('id_company_to', Auth::user()->id_company)
                ->where('id_executor', Auth::user()->id)
                ->orderBy('id_priority', 'desc')
                ->get();
            $employees = NULL;
        }

        # This algorithm select the name of current executor by id
        $current_executor = [];
        $current_employee_init_name = "";
        $i=0;

        foreach($tickets as $ticket) {
            
            $tmpExecutorName = self::getExecutorName($ticket->id_executor);
            $tmpEmployeeInitName = self::getClientInitName($ticket->id_client);
            $tmpCurrentStatusName = self::getStatusName($ticket->id_status);
            
            $tickets[$i]->current_employee_init_name = $tmpEmployeeInitName;
            $tickets[$i]->current_executor_name = $tmpExecutorName;
            $tickets[$i]->current_status_name = $tmpCurrentStatusName;
            $tickets[$i]->prio = $ticket->id_priority;
            $i++;
            
        } 

        return view('employee.tickets.view_all_incoming_external_legal_tickets', compact('tickets', 'employees', 'current_executor'));
    }

    #
    # This func gets all outgoing tickets
    #
    protected function getAllOutgoingTickets()
    {		
		# This query selects all tickets related to head unit	
		$tickets = DB::table('employee_tickets')		
			->where('id_company', Auth::user()->id_company)
			->where('id_status', '<>', 2)
			->where('id_status', '<>', 7)
			->where('employee_init_id', Auth::user()->id)
            ->orderBy('id_priority', 'desc')		
			->get(); 

		# This query selects all employees of single unit
		$employees = DB::table('employees')
			->where('id_company', Auth::user()->id_company)
			->where('id_unit', Auth::user()->head_unit_id)
			->get();

		# This algorithm select the name of current executor by id
		$current_executor = [];
		$current_employee_init_name = "";
		$i=0;

		foreach($tickets as $ticket) {
			
			$tmpExecutorName = self::getExecutorName($ticket->id_executor);
			$tmpEmployeeInitName = self::getEmployeeInitName($ticket->employee_init_id);
			$tmpCurrentStatusName = self::getStatusName($ticket->id_status);
			
			$tickets[$i]->current_employee_init_name = $tmpEmployeeInitName;
			$tickets[$i]->current_executor_name = $tmpExecutorName;
			$tickets[$i]->current_status_name = $tmpCurrentStatusName;
			$i++;
			
		} 

		return view('employee.tickets.outgoing_tickets', compact('tickets', 'employees'));	
    }

    #
    # This func checks ticket's status & if it's <> 0 it will set status 3
    #
    public function checkTicketStatus($id_ticket)
    {
    	DB::table('employee_tickets')
    		->where('id', $id_ticket)
    		->having('id_executor', '>', 0)
    		->update(['id_status' => 3]);
    	return;
    } 

    #
    # This func checks ticket's status & if it's <> 0 it will set status 3
    #
    public function checkIndividualTicketStatus($id_ticket)
    {
    	DB::table('individual_tickets')
    		->where('id', $id_ticket)
    		->having('id_executor', '>', 0)
    		->update(['id_status' => 3]);
    
		return;
	}
  
    #
    # This func appoint exeecutor to the ticket
    #
    protected function appointExecutorToTicket(Request $request)
    {
    	$id = $request['id_ticket'];
    	$recordToUpdate = Ticket::findOrFail($id);

        DB::table('employee_tickets')
            ->where('id', $id)
            ->update(['id_executor' => $request['id_new_executor']]);

        # вызов функции для автоматической проверки и изменения статуса   
        self::checkTicketStatus($id);
    	
    	return(redirect('/employee/view_all_incoming_tickets'));
    }  

  	#
    # This func appoint executor to the inidividual ticket
    #
    protected function appointExecutorToIndividualTicket(Request $request)
    {
    	$id = $request['id_ticket'];
    	$recordToUpdate = IndividualTicket::findOrFail($id);

        DB::table('individual_tickets')
            ->where('id', $id)
            ->update(['id_executor' => $request['id_new_executor']]);

        # вызов функции для автоматической проверки и изменения статуса   
        self::checkIndividualTicketStatus($id);
    	
    	return(redirect('/employee/view_all_incoming_external_tickets'));
    }

    #
    # This func appoint executor to the legal ticket
    #
    protected function appointExecutorToLegalTicket(Request $request)
    {
        $id = $request['id_ticket'];
        $recordToUpdate = LegalTicket::findOrFail($id);

        DB::table('legal_tickets')
            ->where('id', $id)
            ->update(['id_executor' => $request['id_new_executor']]);

        # вызов функции для автоматической проверки и изменения статуса   
        self::checkLegalTicketStatus($id);
        
        return(redirect('/employee/view_all_incoming_external_legal_tickets'));
    }

    #
    # This function makes rejection of ticket by setting status 2
	#
	protected function rejectTicket($id)
	{
		(int) $id;
		$recordToUpdate = Ticket::findOrFail($id);
		DB::table('employee_tickets')
            ->where('id', $id)
            ->update(['id_status' => 2]);

        return(redirect('/employee/view_all_incoming_tickets'));
	}

	#
    # This function makes rejection of ticket by setting status 2
	#
	protected function rejectIndividualTicket($id)
	{
		(int) $id;
		$recordToUpdate = IndividualTicket::findOrFail($id);
		DB::table('individual_tickets')
            ->where('id', $id)
            ->update(['id_status' => 2]);

        return(redirect('/employee/view_all_incoming_external_tickets'));
	}

    #
    # This function makes rejection of ticket by setting status 2
    #
    protected function rejectLegalTicket($id)
    {
        (int) $id;
        $recordToUpdate = LegalTicket::findOrFail($id);
        DB::table('legal_tickets')
            ->where('id', $id)
            ->update(['id_status' => 2]);

        return(redirect('/employee/view_all_incoming_external_legal_tickets'));
    }

	#
    # This function show additional info about ticket
	#
	protected function moreInfoTicket($id)
	{
		(int) $id;
		
		$ticketInfo = DB::table('employee_tickets')
            ->where('id', $id)
            ->get();

        foreach ($ticketInfo as $ticket) {
        	$executorName = self::getExecutorName($ticket->id_executor);
        	$statusName = self::getStatusName($ticket->id_status);
        }

        # get prio name
        $prioName = self::getPriority($ticket->id_priority);
        

        # get unit name
        $allUnits = self::getUnits($ticket->unit_to_id);
        foreach ($allUnits as $unit) {
        	$unitName = $unit->name;
        }

        # get employee init name
        $allEmployyes = self::getAllEmployees($ticket->employee_init_id);
        foreach ($allEmployyes as $employee) {
        	$employeeName = $employee->name;
        }

        return view('employee.tickets.more_info_ticket')
        			->with('ticketInfo', $ticketInfo)
        			->with('statusName', $statusName)
        			->with('prioName', $prioName)
        			->with('unitName', $unitName)
        			->with('employeeName', $employeeName)
        			->with('executorName', $executorName);
	}

	#
    # This function show additional info about individual ticket
	#
	protected function moreInfoIndividualTicket($id)
	{
		(int) $id;
		
		$ticketInfo = DB::table('individual_tickets')
            ->where('id', $id)
            ->get();

        foreach ($ticketInfo as $ticket) {
        	$executorName = self::getExecutorName($ticket->id_executor);
        	$statusName = self::getStatusName($ticket->id_status);
        }

        # get prio name
        $prioName = self::getPriority($ticket->id_priority);
        

        # get unit name
        $allUnits = self::getUnits($ticket->unit_to_id);
        foreach ($allUnits as $unit) {
        	$unitName = $unit->name;
        }

        # get client init name
        $clientName = self::getClientInitName($ticket->id_client);

        #get client
        $client = self::getFullClient($ticket->id_client);

        foreach ($client as $key) {
        	$clientEmail = $key->email; 
        	$clientTel = $key->phone_number;
        }

        return view('employee.tickets.more_info_individual_ticket')
        			->with('ticketInfo', $ticketInfo)
        			->with('statusName', $statusName)
        			->with('prioName', $prioName)
        			->with('unitName', $unitName)
        			->with('clientName', $clientName)
        			->with('clientEmail', $clientEmail)
        			->with('clientTel', $clientTel)
        			->with('executorName', $executorName);
	}

    #
    # This function show additional info about individual ticket
    #
    protected function moreInfoLegalTicket($id)
    {
        (int) $id;
        
        $ticketInfo = DB::table('legal_tickets')
            ->where('id', $id)
            ->get();

        foreach ($ticketInfo as $ticket) {
            $executorName = self::getExecutorName($ticket->id_executor);
            $statusName = self::getStatusName($ticket->id_status);
        }

        # get prio name
        $prioName = self::getPriority($ticket->id_priority);
        

        # get unit name
        $allUnits = self::getUnits($ticket->unit_to_id);
        foreach ($allUnits as $unit) {
            $unitName = $unit->name;
        }

        # get client init name
        $clientName = self::getClientInitName($ticket->id_client);

        #get client
        $client = self::getFullClient($ticket->id_client);

        foreach ($client as $key) {
            $clientEmail = $key->email; 
            $clientTel = $key->phone_number;
        }

        return view('employee.tickets.more_info_legal_ticket')
                    ->with('ticketInfo', $ticketInfo)
                    ->with('statusName', $statusName)
                    ->with('prioName', $prioName)
                    ->with('unitName', $unitName)
                    ->with('clientName', $clientName)
                    ->with('clientEmail', $clientEmail)
                    ->with('clientTel', $clientTel)
                    ->with('executorName', $executorName);
    }

	#
    # This function reopen ticket
	#
	protected function reopenTicket($id)
	{
		(int) $id;
		$recordToUpdate = Ticket::findOrFail($id);
		DB::table('employee_tickets')
            ->where('id', $id)
            ->update(['id_status' => 1, 'id_executor' => NULL]);

        return(redirect('/employee/view_all_incoming_tickets'));
	}

	#
    # This function reopen individual ticket
	#
	protected function reopenIndividualTicket($id)
	{
		(int) $id;
		#$recordToUpdate = Ticket::findOrFail($id);
		DB::table('individual_tickets')
            ->where('id', $id)
            ->update(['id_status' => 1, 'id_executor' => NULL]);

        return(redirect('/employee/view_all_incoming_external_tickets'));
	}

    #
    # This function reopen legal ticket
    #
    protected function reopenLegalTicket($id)
    {
        (int) $id;
        $recordToUpdate = LegalTicket::findOrFail($id);
        DB::table('legal_tickets')
            ->where('id', $id)
            ->update(['id_status' => 1, 'id_executor' => NULL]);

        return(redirect('/employee/view_all_incoming_external_legal_tickets'));
    }

	#
	# This func marks ticket as processing
	#
	protected function takeTheTicket($id)
	{
		(int) $id;
		$recordToUpdate = Ticket::findOrFail($id);
		DB::table('employee_tickets')
            ->where('id', $id)
            ->update(['id_status' => 4]);

        return(redirect('/employee/view_all_incoming_tickets'));
	}

	#
	# This func marks indi ticket as processing
	#
	protected function takeTheIndividualTicket($id)
	{
		(int) $id;
		$recordToUpdate = IndividualTicket::findOrFail($id);
		DB::table('individual_tickets')
            ->where('id', $id)
            ->update(['id_status' => 4]);

        return(redirect('/employee/view_all_incoming_external_tickets'));
	}

    #
    # This func marks legal ticket as processing
    #
    protected function takeTheLegalTicket($id)
    {
        (int) $id;
        $recordToUpdate = LegalTicket::findOrFail($id);
        DB::table('legal_tickets')
            ->where('id', $id)
            ->update(['id_status' => 4]);

        return(redirect('/employee/view_all_incoming_external_legal_tickets'));
    }

	#
	# This func refuse the ticket
	#
	protected function refuseTheTicket($id)
	{
		(int) $id;
		$recordToUpdate = Ticket::findOrFail($id);
		DB::table('employee_tickets')
            ->where('id', $id)
            ->update(['id_executor' => NULL, 'id_status' => 1]);

        return(redirect('/employee/view_all_incoming_tickets'));
	}

	#
	# This func refuse the individual ticket
	#
	protected function refuseTheIndividualTicket($id)
	{
		(int) $id;
		$recordToUpdate = IndividualTicket::findOrFail($id);
		DB::table('individual_tickets')
            ->where('id', $id)
            ->update(['id_executor' => NULL, 'id_status' => 1]);

        return(redirect('/employee/view_all_incoming_external_tickets'));
	}

    #
    # This func refuse the legal ticket
    #
    protected function refuseTheLegalTicket($id)
    {
        (int) $id;
        $recordToUpdate = LegalTicket::findOrFail($id);
        DB::table('legal_tickets')
            ->where('id', $id)
            ->update(['id_executor' => NULL, 'id_status' => 1]);

        return(redirect('/employee/view_all_incoming_external_legal_tickets'));
    }

	#
	# This func marks ticket as completed by executor
	#
	protected function ticketComplete($id)
	{
		(int) $id;
		$recordToUpdate = Ticket::findOrFail($id);
		DB::table('employee_tickets')
            ->where('id', $id)
            ->update(['confirmed_by_executor' => True, 'id_status' => 5]);

        self::checkTicketBothConfiramtion($id);

        return(redirect('/employee/view_all_incoming_tickets'));
	}

	#
	# This func marks individual ticket as completed by executor
	#
	protected function individualTicketComplete($id)
	{
		(int) $id;
		$recordToUpdate = IndividualTicket::findOrFail($id);
		DB::table('individual_tickets')
            ->where('id', $id)
            ->update(['confirmed_by_executor' => True, 'id_status' => 5]);

        self::checkIndividualTicketBothConfiramtion($id);

        return(redirect('/employee/view_all_incoming_external_tickets'));
	}
        
    #
    # This func marks legal ticket as completed by executor
    #
    protected function legalTicketComplete($id)
    {
        (int) $id;
        $recordToUpdate = LegalTicket::findOrFail($id);
        DB::table('legal_tickets')
            ->where('id', $id)
            ->update(['confirmed_by_executor' => True, 'id_status' => 5]);

        self::checkLegalTicketBothConfiramtion($id);

        return(redirect('/employee/view_all_incoming_external_legal_tickets'));
    }

    #
    # confirm ticket by Initiator
    #
	protected function ticketCompleteByInitiator($id)
	{
		(int) $id;
		$recordToUpdate = Ticket::findOrFail($id);
		DB::table('employee_tickets')
            ->where('id', $id)
            ->update(['confirmed_by_initiator' => True, 'id_status' => 6]);

        self::checkTicketBothConfiramtion($id);

        return(redirect('/employee/outgoing_tickets'));
	}

	#
	# Mark ticket as incomplete
	#
	protected function ticketIsNotComplete($id)
	{
		(int) $id;
		$recordToUpdate = Ticket::findOrFail($id);
		DB::table('employee_tickets')
            ->where('id', $id)
            ->update(['id_status' => 1, 'id_executor' => NULL]);

        return(redirect('/employee/outgoing_tickets'));
	} 

	 
	#
	# show the employee profile
	#
	protected function showMyProfile()
	{
		return view('employee.my_profile');
	}



	#######################################################################
	#############			 Functions helpers				  ############
	######################################################################

	#
	#	This func check executor & initiator confirmation & and if both
	#	confirmed, it close the ticket.
	#
	protected function checkTicketBothConfiramtion($id)
	{	
		(int) $id;
		$recordToUpdate = Ticket::findOrFail($id);
		$tmpValues = DB::table('employee_tickets')
            ->where('id', $id)
            ->select('confirmed_by_executor', 'confirmed_by_initiator')
            ->get();
        
        foreach ($tmpValues as $value) {
            	if( $value->confirmed_by_executor == 1 && $value->confirmed_by_initiator == 1)
            	{
            		DB::table('employee_tickets')
			            ->where('id', $id)
			            ->update(['id_status' => 7]);
            	}
            }    

		return;
	}

	#
	#	This func check executor & initiator confirmation & and if both
	#	confirmed, it close the Individual ticket.
	#
	protected function checkIndividualTicketBothConfiramtion($id)
	{	
		(int) $id;
		$recordToUpdate = IndividualTicket::findOrFail($id);
		$tmpValues = DB::table('individual_tickets')
            ->where('id', $id)
            ->select('confirmed_by_executor', 'confirmed_by_initiator')
            ->get();
        
        foreach ($tmpValues as $value) {
            	if( $value->confirmed_by_executor == 1 && $value->confirmed_by_initiator == 1)
            	{
            		DB::table('individual_tickets')
			            ->where('id', $id)
			            ->update(['id_status' => 7]);
            	}
            }    

		return;
	} 

    #
    #   This func check executor & initiator confirmation & and if both
    #   confirmed, it close the legal ticket.
    #
    protected function checkLegalTicketBothConfiramtion($id)
    {   
        (int) $id;
        $recordToUpdate = LegalTicket::findOrFail($id);
        $tmpValues = DB::table('legal_tickets')
            ->where('id', $id)
            ->select('confirmed_by_executor', 'confirmed_by_initiator')
            ->get();
        
        foreach ($tmpValues as $value) {
                if( $value->confirmed_by_executor == 1 && $value->confirmed_by_initiator == 1)
                {
                    DB::table('legal_tickets')
                        ->where('id', $id)
                        ->update(['id_status' => 7]);
                }
            }    

        return;
    }

	#
	# get executor name
	#
	protected function getExecutorName($id)
    {
    	$current_executor_name = DB::table('employees')
				->where('id', $id)
				->select('name')
				->get();

		#this loop gets exatcly the name string from object
		# which was recieved above
		$tmpExecutorName = "Нет исполнителя";
		$tmpCurrentStatusName = NULL;
		foreach ($current_executor_name as $tmp) {
			$tmpExecutorName = $tmp->name;
		}
		return $tmpExecutorName;
    }

    #
    # Get employee init name
    #
    protected function getEmployeeInitName($id)
    {	
    	$tmpEmployeeInitName = '';
    	$current_employee_init_name = DB::table('employees')
    		->where('id', $id)
    		->select('name')
    		->get();

    	foreach ($current_employee_init_name as $tmp) {
    		$tmpEmployeeInitName = $tmp->name;
    	}
    	return $tmpEmployeeInitName;
    }

    #
    # get status name
    #
    protected function getStatusName($id) 
    {
		$current_status_name = DB::table('statuses')
			->where('id', $id)
			->select('name')
			->get();

		foreach ($current_status_name as $tmp) {
			$tmpCurrentStatusName = $tmp->name;
		}

		return $tmpCurrentStatusName;
	}

	#
	# get priority. It returns priority object with all fields
	#
	private function getPriority($id)
	{	
		$prioName = '';
		$priority = DB::table('priorities')
				->where('id', $id)
				->get();
		foreach ($priority as $prio) {
        	$prioName = $prio->name;
        }
        return $prioName;
	}

	#
	# get all units
	#
	protected function getUnits($id)
	{
		return DB::table('units')
				->where('id', $id)
				->get();
	}

	#
	# get all employees
	#
	protected function getAllEmployees($id)
	{
		return DB::table('employees')
				->where('id', $id)
				->get();
	}

	#
	# updates about_company record
	#
	protected function UpdateCompanyInfo(Request $request, $id_company)
	{
		$request['external_tickets'] == 1 ? $isChecked = 1 : $isChecked = 0;

		DB::table('about_company')
			->where('id_company', $id_company)

			->update([
				'name' => $request['name'], 
				'city' => $request['city'],
				'address' => $request['address'],  
				'email' => $request['email'],
				'tel' => $request['tel'],
				'description' => $request['description'],
				'external_tickets' => $isChecked 
			]);            
		
		return(redirect('/employee/about_company')); ;
	}



	############################
	# External legals tickets
	########################

	#
	# Get client name
	#
	protected function getClientInitName($id)
	{
		$client = DB::table('individuals')
			->where('id', $id)
			->get();
		foreach ($client as $key) {
			$clientName = $key->name;
		}
		return $clientName;
	}

	#
	# Get client name
	#
	protected function getFullClient($id)
	{
		$client = DB::table('individuals')
			->where('id', $id)
			->get();
		
		return $client;
	}



    ###########################################
    # External legals tickets
    ###########################################

    #
    # This func checks ticket's status & if it's <> 0 it will set status 3
    #
    public function checkLegalTicketStatus($id_ticket)
    {
        DB::table('legal_tickets')
            ->where('id', $id_ticket)
            ->having('id_executor', '>', 0)
            ->update(['id_status' => 3]);
    
        return;
    }



}