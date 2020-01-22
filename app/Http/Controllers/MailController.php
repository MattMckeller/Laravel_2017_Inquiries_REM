<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function mail($to, $email, $data = null, $name = null, $from = 'appointments@liveatbrookside.com', $heading = null, $cc = null, $bcc = null) {

//        $to = 'msmckeller@integraconsultingsolutions.com, rchaslag@integraconsultingsolutions.com, kjmattingly@integraconsultingsolutions.com';
        $sendStatus = array('errors' => [], 'success' => true);
        if (!$to) {
            $errorString = $name ?: 'This person';
            $sendStatus['errors'][] = $errorString . ' doesn\'t have an associated email address, so they will not receive an email about this.';
        }
        Mail::send($email->view_path, $data, function ($message) use ($to, $from, $email, $cc, $bcc, $data) {
            $message->from($from, 'Brookside');
            $message->subject($email->subject);
            $message->to($to);
            if(!empty($data['propertyEmail'])){
                $message->cc(array($data['propertyEmail'],'inquiries@liveatbrookside.com', 'ajbeard@realequitymanagement.com'));
            }
//            $message->bcc(array('developers@liveatbrookside.com', 'dt_managers@liveatbrookside.com', 'mt_managers@liveatbrookside.com', 'th_managers@liveatbrookside.com', 'wku_managers@liveatbrookside.com', 'hcdemuth@midtownsw.com', 'br_managers@liveatbrookside.com', 'ajbeard@realequitymanagement.com', 'JLAroesty@liveatbrookside.com'));
            $message->bcc('developers@liveatbrookside.com');
        });
    }

    public function emailCustomer($to, $email, $data = null, $name = null, $from = 'appointments@liveatbrookside.com', $heading = null, $cc = null, $bcc = null) {
        $sendStatus = array('errors' => [], 'success' => true);
        if (!$to) {
            $errorString = $name ?: 'This person';
            $sendStatus['errors'][] = $errorString . ' doesn\'t have an associated email address, so they will not receive an email about this.';
        }
        $path = (!empty($data['view_path']))?($data['view_path']):($email->view_path);
        Mail::send($path, $data, function ($message) use ($to, $from, $email, $cc, $bcc, $data) {
            $message->from($from, 'Brookside');
            $message->subject($email->subject);
            $message->to($to);
            $message->bcc('developers@liveatbrookside.com');
        });
    }



    public function emailAgent($to, $email, $data = null, $name = null, $from = 'appointments@liveatbrookside.com', $heading = null, $cc = null, $bcc = null) {

        $sendStatus = array('errors' => [], 'success' => true);
        if (!$to) {
            $errorString = $name ?: 'This person';
            $sendStatus['errors'][] = $errorString . ' doesn\'t have an associated email address, so they will not receive an email about this.';
        }
        $path = (!empty($data['view_path']))?($data['view_path']):($email->view_path);
        Mail::send($path, $data, function ($message) use ($to, $from, $email, $cc, $bcc, $data) {
            $message->from($from, 'Brookside');
            $message->subject($email->subject);
            $message->to($to);
            if(!empty($data['propertyEmail'])) {
                $message->cc(array($data['propertyEmail'], 'developers@liveatbrookside.com',
                    'inquiries@liveatbrookside.com', 'ajbeard@realequitymanagement.com'));
            }
        });
    }




}
