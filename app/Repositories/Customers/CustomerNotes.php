<?php
namespace App\Repositories\Customers;
use App\Libraries\DoubleMetaPhone;
use App\Models\Auth\Team\UserAccount;
use App\Models\Customers\EmailAddress;
use App\Models\Customers\PhoneNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customers\Customer;
use App\Models\Customers\Address;
use App\Models\Customers\Note;
use Illuminate\Support\Facades\App;

trait CustomerNotes
{
    function addNote(Customer $customer, $message){
        $newNote = new Note();
        $newNote->text = $this->_clean($message);
        $newNote->customer()->associate($customer);
        $newNote->userAccount()->associate($this->_getCurrentUser());
        return $newNote->save();
    }

    function editNote(Customer $customer, Note $note, $newMessage){
        $currentUser = $this->_getCurrentUser();
        if($this->_userCanEdit($currentUser, $note)) {
            $note->text = $this->_clean($newMessage);
            $note->customer()->associate($customer);
            $note->userAccount()->associate($currentUser);
            return $note->save();
        }else{
            throw new \Exception('This user cannot edit this note.');
        }
    }

    function getNotes(Customer $customer){
        return $customer->notes()->get();
    }

    function removeNote(Note $note){
        $note->is_active = 0;
        return $note->save();
    }

    private function _clean($message){
        return strip_tags($message);
    }

    private function _getCurrentUser(){
        $TeamAuth = App::make('TeamAuth');
        $currentUser = $TeamAuth->get_user();
        if(empty($currentUser)){
            throw new \Exception("No user was found.");
        }
        return $currentUser;
    }

    private function _userCanEdit(UserAccount $editingUser, Note $note){
        $currentUser = $note->userAccount()->first();
        return ($currentUser == $editingUser);
    }

}